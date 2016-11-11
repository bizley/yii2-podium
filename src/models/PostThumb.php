<?php

namespace bizley\podium\models;

use bizley\podium\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * PostThumb model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property integer $id
 * @property integer $user_id
 * @property integer $post_id
 * @property integer $created_at
 * @property integer $updated_at
 */
class PostThumb extends ActiveRecord
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
