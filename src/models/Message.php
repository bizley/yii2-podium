<?php

/**
 * @author Bizley
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
    
    public $senderName;
    public $receiverName;
    
    public static function re()
    {
        return Yii::t('podium/view', self::RE);
    }

    public static function getInboxStatuses()
    {
        return [
            self::STATUS_NEW, self::STATUS_READ
        ];
    }
    
    public static function getSentStatuses()
    {
        return [
            self::STATUS_READ
        ];
    }
    
    public static function getDeletedStatuses()
    {
        return [
            self::STATUS_DELETED
        ];
    }

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
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['receiver_id', 'topic', 'content'], 'required'],
            ['receiver_id', 'number', 'min' => 1],
            ['topic', 'string', 'max' => 255],
            ['topic', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value);
            }],
            ['content', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig());
            }],
        ];
    }
    
    public function getSenderUser()
    {
        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }
    
    public function getReceiverUser()
    {
        return $this->hasOne(User::className(), ['id' => 'receiver_id']);
    }
    
    public function getSenderName()
    {
        return !empty($this->senderUser) ? $this->senderUser->getPodiumTag() : Helper::deletedUserTag();
    }
    
    public function getReceiverName()
    {
        return !empty($this->receiverUser) ? $this->receiverUser->getPodiumTag() : Helper::deletedUserTag();
    }
    
    public function getReply()
    {
        return $this->hasOne(self::className(), ['id' => 'replyto']);
    }
    
    public function send()
    {
        $query = new Query;
        if (!$query->select('id')->from('{{%podium_user}}')->where(['id' => $this->receiver_id, 'status' => User::STATUS_ACTIVE])->exists()) {
            return false;
        }
        
        $this->sender_id = Yii::$app->user->id;
        $this->sender_status = self::STATUS_READ;
        $this->receiver_status = self::STATUS_NEW;
        
        if ($this->save()) {
            
            Cache::getInstance()->deleteElement('user.newmessages', $this->receiver_id);
            return true;
        }
        
        return false;
    }
    
    public function remove($perm = 0)
    {
        $clearCache = false;
        if ($this->receiver_status == self::STATUS_NEW) {
            $clearCache = true;
        }
        if ($this->receiver_id == Yii::$app->user->id) {
            $this->receiver_status = $perm ? self::STATUS_REMOVED : self::STATUS_DELETED;
        }
        if ($this->sender_id == Yii::$app->user->id) {
            $this->sender_status = $perm ? self::STATUS_REMOVED : self::STATUS_DELETED;
        }
        if ($this->receiver_status == self::STATUS_REMOVED && $this->sender_status == self::STATUS_REMOVED) {
            if ($this->delete()) {
                if ($clearCache) {
                    Cache::getInstance()->deleteElement('user.newmessages', Yii::$app->user->id);
                }
                return true;
            }
            else {
                return false;
            }
        }
        if ($this->save()) {
            if ($clearCache) {
                Cache::getInstance()->deleteElement('user.newmessages', Yii::$app->user->id);
            }
            return true;
        }
        else {
            return false;
        }
    }
}
