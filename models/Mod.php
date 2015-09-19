<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use yii\db\ActiveRecord;

/**
 * Mod model
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $forum_id
 */
class Mod extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_moderator}}';
    }

    public function getForum()
    {
        return $this->hasOne(Forum::className(), ['id' => 'forum_id']);
    }
}
