<?php

namespace bizley\podium\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;

/**
 * MessageSearch model
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
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
        $subquery = (new Query)
                    ->select(['m2.replyto'])
                    ->from(['m1' => Message::tableName()])
                    ->leftJoin(['m2' => Message::tableName()], 'm1.replyto = m2.id')
                    ->where(['is not', 'm2.replyto', null]);
        $query = static::find()->where(['and',
            [
                'sender_id' => User::loggedId(), 
                'sender_status' => Message::getSentStatuses()
            ],
            ['not in', Message::tableName() . '.id', $subquery]
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'attributes' => ['id', 'topic', 'created_at'],
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['messageReceivers' => function ($q) {
                $q->joinWith(['receiver']);
            }]);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        
        if (preg_match('/^(forum|orum|rum|um|m)?#([0-9]+)$/', strtolower($this->receiverName), $matches)) {
            $dataProvider->query->joinWith(['messageReceivers' => function($q) use ($matches) {
                $q->joinWith(['receiver' => function ($q) use ($matches) {
                    $q->andFilterWhere(['username' => ['', null], User::tableName() . '.id' => $matches[2]]);
                }]);
            }]);
        } elseif (preg_match('/^([0-9]+)$/', $this->receiverName, $matches)) {
            $dataProvider->query->joinWith(['messageReceivers' => function($q) use ($matches) {
                $q->joinWith(['receiver' => function ($q) use ($matches) {
                    $q->andFilterWhere(['or', 
                        ['like', 'username', $this->receiverName],
                        [
                            'username' => ['', null],
                            'id'       => $matches[1]
                        ]
                    ]);
                }]);
            }]);
        } else {
            $dataProvider->query->joinWith(['messageReceivers' => function($q) {
                $q->joinWith(['receiver' => function ($q) {
                    $q->andFilterWhere(['like', User::tableName() . '.username', $this->receiverName]);
                }]);
            }]);
        }
        return $dataProvider;
    }
}
