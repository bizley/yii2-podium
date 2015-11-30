<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use yii\db\ActiveRecord;

/**
 * ThreadView model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $user_id
 * @property integer $thread_id
 * @property integer $new_last_seen
 * @property integer $edited_last_seen
 */
class ThreadView extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_thread_view}}';
    }
}
