<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use yii\db\ActiveRecord;

/**
 * Subscription model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $thread_id
 * @property integer $post_seen
 */
class Subscription extends ActiveRecord
{

    const POST_SEEN = 1;
    const POST_NEW  = 0;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_subscription}}';
    }

    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
    
    public function getThread()
    {
        return $this->hasOne(Thread::className(), ['id' => 'thread_id']);
    }
}
