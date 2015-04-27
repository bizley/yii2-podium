<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use Yii;
use yii\helpers\HtmlPurifier;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use bizley\podium\models\User;
use bizley\podium\components\Helper;

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

}
