<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\models\User;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\HtmlPurifier;

/**
 * Message model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $sender_id
 * @property integer $receiver_id
 * @property string $topic
 * @property string $content
 * @property integer $replyto
 * @property integer $sender_status
 * @property integer $receiver_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class Message extends ActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_READ = 10;
    const STATUS_DELETED = 20;
    const STATUS_REMOVED = 99;
    
    const RE = 'Re:';
    
    /**
     * @var string Sender's name
     */
    public $senderName;
    
    /**
     * @var string Receiver's name
     */
    public $receiverName;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_message}}';
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
            ['report' => ['content']]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver_id', 'topic', 'content'], 'required'],
            ['receiver_id', 'validateReceiver'],
            ['topic', 'string', 'max' => 255],
            ['topic', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value);
            }],
            ['content', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
            }],
        ];
    }
    
    /**
     * Validates receiver ID.
     */
    public function validateReceiver()
    {
        if (!is_numeric($this->receiver_id) || $this->receiver_id < 1) {
            $this->addError('receiver_id', Yii::t('podium/view', 'Invalid receiver.'));
        }
        else {
            if ($this->receiver_id == User::loggedId()) {
                $this->addError('receiver_id', Yii::t('podium/view', 'You can not send message to yourself.'));
                $this->receiver_id = null;
            }
        }
    }

    /**
     * Returns Re: prefix for subject.
     * @return string
     */
    public static function re()
    {
        return Yii::t('podium/view', self::RE);
    }

    /**
     * Returns list of inbox statuse.
     * @return string[]
     */
    public static function getInboxStatuses()
    {
        return [self::STATUS_NEW, self::STATUS_READ];
    }
    
    /**
     * Returns list of sent statuses.
     * @return string[]
     */
    public static function getSentStatuses()
    {
        return [self::STATUS_READ];
    }
    
    /**
     * Returns list of deleted statuses.
     * @return string[]
     */
    public static function getDeletedStatuses()
    {
        return [self::STATUS_DELETED];
    }

    /**
     * Sender relation.
     * @return User
     */
    public function getSenderUser()
    {
        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }
    
    /**
     * Receiver relation.
     * @return User
     */
    public function getReceiverUser()
    {
        return $this->hasOne(User::className(), ['id' => 'receiver_id']);
    }
    
    /**
     * Returns sender's name.
     * @return string
     */
    public function getSenderName()
    {
        return !empty($this->senderUser) ? $this->senderUser->getTag() : Helper::deletedUserTag();
    }
    
    /**
     * Returns receiver's name.
     * @return string
     */
    public function getReceiverName()
    {
        return !empty($this->receiverUser) ? $this->receiverUser->getTag() : Helper::deletedUserTag();
    }
    
    /**
     * Returns reply Message.
     * @return Message
     */
    public function getReply()
    {
        return $this->hasOne(self::className(), ['id' => 'replyto']);
    }
    
    /**
     * Sends message.
     * @return boolean
     */
    public function send()
    {
        if (!(new Query)->select('id')->from(User::tableName())->where(['id' => $this->receiver_id, 'status' => User::STATUS_ACTIVE])->exists()) {
            return false;
        }
        
        $this->sender_id = User::loggedId();
        $this->sender_status = self::STATUS_READ;
        $this->receiver_status = self::STATUS_NEW;
        
        if ($this->save()) {
            Cache::getInstance()->deleteElement('user.newmessages', $this->receiver_id);
            return true;
        }
        
        return false;
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
}
