<?php

namespace bizley\podium\models;

use bizley\podium\log\Log;
use bizley\podium\models\db\EmailActiveRecord;
use Exception;

/**
 * Email model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class Email extends EmailActiveRecord
{
    /**
     * Adds email to queue.
     * @param string $address
     * @param string $subject
     * @param string $content
     * @param int|null $userId
     * @return bool
     */
    public static function queue($address, $subject, $content, $userId = null)
    {
        try {
            $email = new static;
            $email->user_id = $userId;
            $email->email = $address;
            $email->subject = $subject;
            $email->content = $content;
            $email->status = self::STATUS_PENDING;
            $email->attempt = 0;
            return $email->save();
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
