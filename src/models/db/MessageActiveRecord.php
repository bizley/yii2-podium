<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use bizley\podium\helpers\Helper;
use bizley\podium\models\MessageReceiver;
use bizley\podium\models\User;
use bizley\podium\Podium;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;

/**
 * Message model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property integer $sender_id
 * @property string $topic
 * @property string $content
 * @property integer $replyto
 * @property integer $sender_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class MessageActiveRecord extends ActiveRecord
{
    /**
     * Statuses.
     */
    const STATUS_NEW     = 1;
    const STATUS_READ    = 10;
    const STATUS_DELETED = 20;

    /**
     * Limits.
     */
    const MAX_RECEIVERS = 10;
    const SPAM_MESSAGES = 10;
    const SPAM_WAIT     = 1;

    /**
     * @var int[] Receivers' IDs.
     */
    public $receiversId;

    /**
     * @var int[] Friends' IDs.
     * @since 0.2
     */
    public $friendsId;

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
            [
                'report' => ['content'],
                'remove' => ['sender_status'],
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['topic', 'content'], 'required'],
            [['receiversId', 'friendsId'], 'each', 'rule' => ['integer', 'min' => 1]],
            ['sender_status', 'in', 'range' => self::getStatuses()],
            ['topic', 'string', 'max' => 255],
            ['topic', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(trim($value));
            }],
            ['content', 'filter', 'filter' => function($value) {
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    return HtmlPurifier::process(trim($value), Helper::podiumPurifierConfig('markdown'));
                }
                return HtmlPurifier::process(trim($value), Helper::podiumPurifierConfig());
            }],
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
     * Returns list of inbox statuses.
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
     * @return ActiveQuery
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }

    /**
     * Receivers relation.
     * @return ActiveQuery
     */
    public function getMessageReceivers()
    {
        return $this->hasMany(MessageReceiver::className(), ['message_id' => 'id']);
    }

    /**
     * Returns reply Message.
     * @return ActiveQuery
     */
    public function getReply()
    {
        return $this->hasOne(static::className(), ['id' => 'replyto']);
    }
}
