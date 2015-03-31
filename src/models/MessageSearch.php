<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use Yii;
use yii\data\ActiveDataProvider;

class MessageSearch extends Message
{

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['senderName', 'receiverName', 'topic'], 'safe'],
        ];
    }
    
    public function search()
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort' => [
                'attributes' => ['id', 'topic', 'created_at', 
                    'senderName' => [
                        'asc' => ['podium_user.username' => SORT_ASC, 'podium_user.id' => SORT_ASC],
                        'desc' => ['podium_user.username' => SORT_DESC, 'podium_user.id' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                    'receiverName' => [
                        'asc' => ['podium_user.username' => SORT_ASC, 'podium_user.id' => SORT_ASC],
                        'desc' => ['podium_user.username' => SORT_DESC, 'podium_user.id' => SORT_DESC],
                        'default' => SORT_ASC
                    ]
                ],
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $dataProvider;
    }
    
    public function searchInbox($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['receiver_id' => Yii::$app->user->id, 'receiver_status' => Message::getInboxStatuses()]);

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['senderUser']);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        $dataProvider->query->joinWith(['senderUser' => function($q) {
            $q->where(['like', 'podium_user.username', $this->senderName]);
        }]);

        return $dataProvider;
    }
    
    public function searchSent($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['sender_id' => Yii::$app->user->id, 'sender_status' => Message::getSentStatuses()]);

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['receiverUser']);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        $dataProvider->query->joinWith(['receiverUser' => function($q) {
            $q->where(['like', 'podium_user.username', $this->receiverName]);
        }]);

        return $dataProvider;
    }
    
    public function searchDeleted($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['or', 
            ['and', ['sender_id' => Yii::$app->user->id], ['sender_status' => Message::getDeletedStatuses()]], 
            ['and', ['receiver_id' => Yii::$app->user->id], ['receiver_status' => Message::getDeletedStatuses()]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith([
                'receiverUser' => function($q) {
                    $q->from('podium_user pdu_receiver');
                }, 
                'senderUser' => function($q) {
                    $q->from('podium_user pdu_sender');
                }
            ]);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        $dataProvider->query->joinWith(['receiverUser' => function($q) {
            $q->from('podium_user pdu_receiver')->where(['like', 'pdu_receiver.username', $this->receiverName]);
        }]);
        $dataProvider->query->joinWith(['senderUser' => function($q) {
            $q->from('podium_user pdu_sender')->where(['like', 'pdu_sender.username', $this->senderName]);
        }]);

        return $dataProvider;
    }
}
