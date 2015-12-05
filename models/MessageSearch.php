<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

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
     * @var string Receiver' name.
     */
    public $receiverName;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiverName', 'topic'], 'string'],
        ];
    }
    
    /**
     * Searches for sent messages.
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->where(['sender_id' => User::loggedId(), 'sender_status' => Message::getSentStatuses()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes' => ['id', 'topic', 'created_at'],
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['messageReceivers' => function ($q) {
                $q->joinWith(['receiver']);
            }]);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        $dataProvider->query->joinWith(['messageReceivers' => function($q) {
            $q->joinWith(['receiver' => function ($q) {
                $q->andFilterWhere(['like', User::tableName() . '.username', $this->receiverName]);
            }]);
        }]);
        
        return $dataProvider;
    }
}
