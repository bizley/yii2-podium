<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\models\User;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * MessageReceive rmodel
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $message_id
 * @property integer $receiver_id
 * @property integer $receiver_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class MessageReceiver extends ActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_READ = 10;
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
    public function rules()
    {
        return [
            [['receiver_id'], 'required', 'on' => 'new'],
            ['receiver_id', 'integer', 'min' => 1],
            [['senderName', 'topic'], 'string']
        ];
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
     * @param integer $perm Permanent removal flag
     * @return boolean
     */
    public function remove($perm = 0)
    {
        $clearCache = false;
        if ($this->receiver_status == self::STATUS_NEW) {
            $clearCache = true;
        }
        if ($this->receiver_id == User::loggedId()) {
            $this->receiver_status = $perm ? self::STATUS_REMOVED : self::STATUS_DELETED;
        }
        if ($this->sender_id == User::loggedId()) {
            $this->sender_status = $perm ? self::STATUS_REMOVED : self::STATUS_DELETED;
        }
        if ($this->receiver_status == self::STATUS_REMOVED && $this->sender_status == self::STATUS_REMOVED) {
            if ($this->delete()) {
                if ($clearCache) {
                    Cache::getInstance()->deleteElement('user.newmessages', User::loggedId());
                }
                return true;
            }
            else {
                return false;
            }
        }
        if ($this->save()) {
            if ($clearCache) {
                Cache::getInstance()->deleteElement('user.newmessages', User::loggedId());
            }
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Searches for messages.
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = self::find()->where(['receiver_id' => User::loggedId()]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => [
                'attributes' => ['id', 'topic', 'created_at', 
                    'senderName' => [
                        'asc' => [User::tableName() . '.username' => SORT_ASC, User::tableName() . '.id' => SORT_ASC],
                        'desc' => [User::tableName() . '.username' => SORT_DESC, User::tableName() . '.id' => SORT_DESC],
                        'default' => SORT_ASC
                    ],
                ],
            ],
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_DESC];
        
        if (!($this->load($params) && $this->validate())) {
            $dataProvider->query->joinWith(['message' => function ($q) {
                $q->joinWith(['sender']);
            }]);
            return $dataProvider;
        }

        $dataProvider->query->andFilterWhere(['like', 'topic', $this->topic]);
        $dataProvider->query->joinWith(['message' => function($q) {
            $q->joinWith(['sender' => function ($q) {
                $q->andFilterWhere(['like', User::tableName() . '.username', $this->senderName]);
            }]);
        }]);

        return $dataProvider;
    }
}
