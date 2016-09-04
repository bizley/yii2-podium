<?php

namespace bizley\podium\models;

use bizley\podium\log\Log;
use Exception;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * Email model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $email
 * @property string $subject
 * @property string $content
 * @property integer $status
 * @property integer $attempt
 * @property integer $created_at
 * @property integer $updated_at
 */
class Email extends ActiveRecord
{
    /**
     * Statuses.
     */
    const STATUS_PENDING = 0;
    const STATUS_SENT    = 1;
    const STATUS_GAVEUP  = 9;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_email}}';
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
            [['email', 'subject', 'content'], 'required'],
            ['email', 'email'],
            [['subject', 'content'], 'string'],
            ['status', 'default', 'value' => self::STATUS_PENDING],
            ['attempt', 'default', 'value' => 0],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_SENT, self::STATUS_GAVEUP]]
        ];
    }
    
    /**
     * Adds email to queue.
     * @param string $address
     * @param string $subject
     * @param string $content
     * @param int|null $user_id
     * @return bool
     */
    public static function queue($address, $subject, $content, $user_id = null)
    {
        try {
            $email = new Email;
            $email->user_id = $user_id;
            $email->email   = $address;
            $email->subject = $subject;
            $email->content = $content;
            $email->status  = Email::STATUS_PENDING;
            $email->attempt = 0;

            return $email->save();
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
