<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * PostThumb model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $post_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class PostThumbActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_post_thumb}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [TimestampBehavior::className()];
    }
}
