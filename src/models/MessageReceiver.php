<?php

namespace bizley\podium\models;

use bizley\podium\log\Log;
use bizley\podium\models\User;
use bizley\podium\Module as Podium;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;

/**
 * MessageReceive rmodel
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property integer $id
 * @property integer $message_id
 * @property integer $receiver_id
 * @property integer $receiver_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class MessageReceiver extends ActiveRecord
{
    /**
     * Statuses.
     */
    const STATUS_NEW     = 1;
    const STATUS_READ    = 10;
    const STATUS_DELETED = 20;
    
    /**
     * @var string Sender's name
     */
    public $senderName;
    
    /**
     * @var string Message topic
     */
    public $topic;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_message_receiver}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        return array_merge(
            parent::scenarios(),
            ['remove' => ['receiver_status']]
        );
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver_id', 'message_id'], 'required'],
            [['receiver_id', 'message_id'], 'integer', 'min' => 1],
            ['receiver_status', 'in', 'range' => static::getStatuses()],
            [['senderName', 'topic'], 'string']
        ];
    }
    
    /**
     * Returns list of statuses.
     * @return string[]
     */
    public static function getStatuses()
    {
        return [self::STATUS_NEW, self::STATUS_READ, self::STATUS_DELETED];
    }
    
    /**
     * Message relation.
     * @return Message
     */
    public function getMessage()
    {
        return $this->hasOne(Message::className(), ['id' => 'message_id']);
    }
    
    /**
     * Receiver relation.
     * @return User
     */
    public function getReceiver()
    {
        return $this->hasOne(User::className(), ['id' => 'receiver_id']);
    }
    
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
                    Podium::getInstance()->cache->deleteElement('user.newmessages', $this->receiver_id);
                }
                $transaction->commit();
                return true;
            } else {
                if ($this->message->sender_status == Message::STATUS_DELETED && count($this->message->messageReceivers) == 1) {
                    $deleteParent = $this->message;
                }
                if (!$this->delete()) {
                    throw new Exception('Message removing error!');
                }
                if ($clearCache) {
                    Podium::getInstance()->cache->deleteElement('user.newmessages', $this->receiver_id);
                }
                if ($deleteParent) {
                    if (!$deleteParent->delete()) {
                        throw new Exception('Sender message deleting error!');
                    }
                }
                $transaction->commit();
                return true;
            }
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
        $subquery = (new Query)
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
            'sort' => [
                'attributes' => [
                    'id', 
                    'topic', 
                    'created_at', 
                    'senderName' => [
                        'asc' => [
                            User::tableName() . '.username' => SORT_ASC, 
                            User::tableName() . '.id' => SORT_ASC
                        ],
                        'desc' => [
                            User::tableName() . '.username' => SORT_DESC, 
                            User::tableName() . '.id' => SORT_DESC
                        ],
                        'default' => SORT_ASC
                    ],
                ],
            ],
        ]);

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
            $dataProvider->query->joinWith(['message' => function($q) use ($matches) {
                $q->joinWith(['sender' => function ($q) use ($matches) {
                    $q->andFilterWhere(['username' => ['', null], User::tableName() . '.id' => $matches[2]]);
                }]);
            }]);
        } elseif (preg_match('/^([0-9]+)$/', $this->senderName, $matches)) {
            $dataProvider->query->joinWith(['message' => function($q) use ($matches) {
                $q->joinWith(['sender' => function ($q) use ($matches) {
                    $q->andFilterWhere(['or', 
                        ['like', 'username', $this->senderName],
                        [
                            'username' => ['', null],
                            'id'       => $matches[1]
                        ]
                    ]);
                }]);
            }]);
        } else {
            $dataProvider->query->joinWith(['message' => function($q) {
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
                Podium::getInstance()->cache->deleteElement('user.newmessages', $this->receiver_id);
            }
        }
    }
}
