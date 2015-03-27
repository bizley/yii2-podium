<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use bizley\podium\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Message model
 *
 * @property integer $id
 * @property integer $sender
 * @property integer $receiver
 * @property string $topic
 * @property string $content
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
    
    public $senderName;
    public $receiverName;
    
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
}
