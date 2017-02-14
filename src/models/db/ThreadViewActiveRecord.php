<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;

/**
 * ThreadView AR
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $thread_id
 * @property integer $new_last_seen
 * @property integer $edited_last_seen
 */
class ThreadViewActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_thread_view}}';
    }
}
