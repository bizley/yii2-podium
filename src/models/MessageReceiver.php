<?php

namespace bizley\podium\models;

use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\models\db\MessageReceiverActiveRecord;
use bizley\podium\models\User;
use bizley\podium\Podium;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * MessageReceiver model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class MessageReceiver extends MessageReceiverActiveRecord
{
    /**
     * Removes message.
     * @return bool
     */
    public function remove()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $clearCache = false;
            if ($this->receiver_status == self::STATUS_NEW) {
                $clearCache = true;
            }
            $deleteParent = null;
            $this->scenario = 'remove';
            if ($this->message->sender_status != Message::STATUS_DELETED) {
                $this->receiver_status = self::STATUS_DELETED;
                if (!$this->save()) {
                    throw new Exception('Message status changing error!');
                }
                if ($clearCache) {
                    Podium::getInstance()->podiumCache->deleteElement('user.newmessages', $this->receiver_id);
                }
                $transaction->commit();
                return true;
            }
            if ($this->message->sender_status == Message::STATUS_DELETED && count($this->message->messageReceivers) == 1) {
                $deleteParent = $this->message;
            }
            if (!$this->delete()) {
                throw new Exception('Message removing error!');
            }
            if ($clearCache) {
                Podium::getInstance()->podiumCache->deleteElement('user.newmessages', $this->receiver_id);
            }
            if ($deleteParent) {
                if (!$deleteParent->delete()) {
                    throw new Exception('Sender message deleting error!');
                }
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), $this->id, __METHOD__);
        }
        return false;
    }

    /**
     * Searches for messages.
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $subquery = (new Query())
                    ->select(['m2.replyto'])
                    ->from(['m1' => Message::tableName()])
                    ->leftJoin(['m2' => Message::tableName()], 'm1.replyto = m2.id')
                    ->where(['is not', 'm2.replyto', null]);
        $query = static::find()->where(['and',
            ['receiver_id' => User::loggedId()],
            ['!=', 'receiver_status', self::STATUS_DELETED],
            ['not in', 'message_id', $subquery]
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);
        $dataProvider->sort->attributes['senderName'] = [
            'asc' => [
                User::tableName() . '.username' => SORT_ASC,
                User::tableName() . '.id' => SORT_ASC
            ],
            'desc' => [
                User::tableName() . '.username' => SORT_DESC,
                User::tableName() . '.id' => SORT_DESC
            ],
            'default' => SORT_ASC
        ];
        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        $dataProvider->pagination->pageSize = Yii::$app->session->get('per-page', 20);

        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['message' => function ($q) {
                $q->joinWith(['sender']);
            }]);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);

        if (preg_match('/^(forum|orum|rum|um|m)?#([0-9]+)$/', strtolower($this->senderName), $matches)) {
            $dataProvider->query->joinWith(['message' => function ($q) use ($matches) {
                $q->joinWith(['sender' => function ($q) use ($matches) {
                    $q->andFilterWhere(['and',
                        [User::tableName() . '.id' => $matches[2]],
                        ['or',
                            ['username' => ''],
                            ['username' => null]
                        ]
                    ]);
                }]);
            }]);
        } elseif (preg_match('/^([0-9]+)$/', $this->senderName, $matches)) {
            $dataProvider->query->joinWith(['message' => function ($q) use ($matches) {
                $q->joinWith(['sender' => function ($q) use ($matches) {
                    $q->andFilterWhere(['or',
                        ['like', 'username', $this->senderName],
                        ['and',
                            ['id' => $matches[1]],
                            ['or',
                                ['username' => ''],
                                ['username' => null]
                            ]
                        ],
                    ]);
                }]);
            }]);
        } else {
            $dataProvider->query->joinWith(['message' => function ($q) {
                $q->joinWith(['sender' => function ($q) {
                    $q->andFilterWhere(['like', User::tableName() . '.username', $this->senderName]);
                }]);
            }]);
        }
        return $dataProvider;
    }

    /**
     * Marks message read.
     * @since 0.2
     */
    public function markRead()
    {
        if ($this->receiver_status == Message::STATUS_NEW) {
            $this->receiver_status = Message::STATUS_READ;
            if ($this->save()) {
                Podium::getInstance()->podiumCache->deleteElement('user.newmessages', $this->receiver_id);
            }
        }
    }
}
