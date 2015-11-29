<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\PodiumUser;
use yii\data\ActiveDataProvider;

/**
 * MessageSearch model
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
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
    
    /**
     * Searches for messages.
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes' => ['id', 'topic', 'created_at', 
//                    'senderName' => [
//                        'asc' => [User::tableName() . '.username' => SORT_ASC, User::tableName() . '.id' => SORT_ASC],
//                        'desc' => [User::tableName() . '.username' => SORT_DESC, User::tableName() . '.id' => SORT_DESC],
//                        'default' => SORT_ASC
//                    ],
//                    'receiverName' => [
//                        'asc' => [User::tableName() . '.username' => SORT_ASC, User::tableName() . '.id' => SORT_ASC],
//                        'desc' => [User::tableName() . '.username' => SORT_DESC, User::tableName() . '.id' => SORT_DESC],
//                        'default' => SORT_ASC
//                    ]
                ],
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        return $dataProvider;
    }
    
    /**
     * Searches for inbox messages.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchInbox($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['receiver_id' => User::loggedId(), 'receiver_status' => Message::getInboxStatuses()]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->senderName) {
            $podiumSender = (new PodiumUser)->findOne(['like', 'username', $this->senderName]);
            if ($podiumSender && $podiumSender->getId()) {
                $dataProvider->query->andFilterWhere(['sender_id' => $podiumSender->getId()]);
            }
            else {
                $dataProvider->query->andFilterWhere(['sender_id' => 0]);
            }
        }
        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);

        return $dataProvider;
    }
    
    /**
     * Searches for sent messages.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchSent($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['sender_id' => User::loggedId(), 'sender_status' => Message::getSentStatuses()]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        if ($this->receiverName) {
            $podiumReceiver = (new PodiumUser)->findOne(['like', 'username', $this->receiverName]);
            if ($podiumReceiver && $podiumReceiver->getId()) {
                $dataProvider->query->andFilterWhere(['receiver_id' => $podiumReceiver->getId()]);
            }
            else {
                $dataProvider->query->andFilterWhere(['receiver_id' => 0]);
            }
        }
        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);

        return $dataProvider;
    }
    
    /**
     * Searches for deleted messages.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function searchDeleted($params)
    {
        $dataProvider = $this->search();
        
        $dataProvider->query->where(['or', 
            ['and', ['sender_id' => User::loggedId()], ['sender_status' => Message::getDeletedStatuses()]], 
            ['and', ['receiver_id' => User::loggedId()], ['receiver_status' => Message::getDeletedStatuses()]]
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);

        return $dataProvider;
    }
}
