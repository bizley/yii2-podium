<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\log\Log;
use bizley\podium\models\User;
use Exception;
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
 * @property string $topic
 * @property string $content
 * @property integer $replyto
 * @property integer $sender_status
 * @property integer $updated_at
 * @property integer $created_at
 */
class Message extends ActiveRecord
{

    const STATUS_NEW = 1;
    const STATUS_READ = 10;
    const STATUS_DELETED = 20;
    
    const MAX_RECEIVERS = 10;
    const SPAM_MESSAGES = 10;
    const SPAM_WAIT     = 1;
    
    /**
     * @var integer[] Receivers' IDs.
     */
    public $receiversId;
    
    /**
     * @var integer[] Friends' IDs.
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
                return HtmlPurifier::process($value);
            }],
            ['content', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig('minimal'));
            }],
        ];
    }
    
    /**
     * Returns Re: prefix for subject.
     * @return string
     */
    public static function re()
    {
        return Yii::t('podium/view', 'Re:');
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
     * @return User
     */
    public function getSender()
    {
        return $this->hasOne(User::className(), ['id' => 'sender_id']);
    }
    
    /**
     * Receivers relation.
     * @return MessageReceiver[]
     */
    public function getMessageReceivers()
    {
        return $this->hasMany(MessageReceiver::className(), ['message_id' => 'id']);
    }
    
    /**
     * Checks if user is a message receiver.
     * @param integer $user_id
     * @return boolean
     */
    public function isMessageReceiver($user_id)
    {
        if ($this->messageReceivers) {
            foreach ($this->messageReceivers as $receiver) {
                if ($receiver->receiver_id == $user_id) {
                    return true;
                }
            }
        }
        return false;
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
        $transaction = static::getDb()->beginTransaction();
        try {
            $this->sender_id = User::loggedId();
            $this->sender_status = self::STATUS_READ;
            
            if ($this->save()) {
                $count = count($this->receiversId);
                foreach ($this->receiversId as $receiver) {
                    if (!(new Query)->select('id')->from(User::tableName())->where(['id' => $receiver, 'status' => User::STATUS_ACTIVE])->exists()) {
                        if ($count == 1) {
                            throw new Exception('No active receivers to send message to!');
                        }
                        else {
                            continue;
                        }
                    }
                    $message = new MessageReceiver;
                    $message->message_id      = $this->id;
                    $message->receiver_id     = $receiver;
                    $message->receiver_status = self::STATUS_NEW;
                    if ($message->save()) {
                        Cache::getInstance()->deleteElement('user.newmessages', $receiver);
                    }
                    else {
                        throw new Exception('MessageReceiver saving error!');
                    }
                }
                $transaction->commit();
                $sessionKey = 'messages.' . $this->sender_id;
                if (Yii::$app->session->has($sessionKey)) {
                    $sentAlready = explode('|', Yii::$app->session->get($sessionKey));
                    $sentAlready[] = time();
                    Yii::$app->session->set($sessionKey, implode('|', $sentAlready));
                }
                else {
                    Yii::$app->session->set($sessionKey, time());
                }
                return true;
            }
            else {
                throw new Exception('Message saving error!');
            }
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), $this->id, __METHOD__);
        }
        
        return false;
    }
    
    /**
     * Checks if user sent already more than SPAM_MESSAGES in last SPAM_WAIT 
     * minutes.
     * @param integer $user_id
     * @return boolean
     */
    public static function tooMany($user_id)
    {
        $sessionKey = 'messages.' . $user_id;
        if (Yii::$app->session->has($sessionKey)) {
            $sentAlready = explode('|', Yii::$app->session->get($sessionKey));
            $validated = [];
            foreach ($sentAlready as $t) {
                if (preg_match('/^[0-9]+$/', $t)) {
                    if ($t > time() - self::SPAM_WAIT * 60) {
                        $validated[] = $t;
                    }
                }
            }
            Yii::$app->session->set($sessionKey, implode('|', $validated));
            if (count($validated) >= self::SPAM_MESSAGES) {
                return true;
            }
        }
        return false;
    }

    /**
     * Removes message.
     * @return boolean
     */
    public function remove()
    {
        $clearCache = false;
        if ($this->sender_status == self::STATUS_NEW) {
            $clearCache = true;
        }
        
        $transaction = static::getDb()->beginTransaction();
        try {
            if (empty($this->messageReceivers)) {
                if ($this->delete()) {
                    if ($clearCache) {
                        Cache::getInstance()->deleteElement('user.newmessages', $this->sender_id);
                    }
                    $transaction->commit();
                    return true;
                }
                else {
                    throw new Exception('Message removing error!');
                }
            }
            else {
                $allDeleted = true;
                foreach ($this->messageReceivers as $mr) {
                    if ($mr->receiver_status != MessageReceiver::STATUS_DELETED) {
                        $allDeleted = false;
                        break;
                    }
                }
                if ($allDeleted) {
                    foreach ($this->messageReceivers as $mr) {
                        if (!$mr->delete()) {
                            throw new Exception('Received message removing error!');
                        }
                    }
                    if ($this->delete()) {
                        if ($clearCache) {
                            Cache::getInstance()->deleteElement('user.newmessages', $this->sender_id);
                        }
                        $transaction->commit();
                        return true;
                    }
                    else {
                        throw new Exception('Message removing error!');
                    }
                }
                else {
                    $this->sender_status = self::STATUS_DELETED;
                    if ($this->save()) {
                        if ($clearCache) {
                            Cache::getInstance()->deleteElement('user.newmessages', $this->sender_id);
                        }
                        $transaction->commit();
                        return true;
                    }
                    else {
                        throw new Exception('Message status changing error!');
                    }
                }
            }
        }
        catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), $this->id, __METHOD__);
        }
        
        return false;
    }
}
