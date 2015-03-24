<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use Yii;
use yii\db\ActiveRecord;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\web\IdentityInterface;
use bizley\podium\Podium;

/**
 * Meta model
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $location
 * @property string $signature
 * @property integer $gravatar
 * @property string $avatar
 * @property integer $updated_at
 */
class Meta extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_user_meta}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['location', 'match', 'pattern' => '/^[\w\s]*$/', 'message' => Yii::t('podium/view', 'Location must contain only letters, digits, underscores and spaces.')],
            ['location', 'trim'],
            ['gravatar', 'boolean'],
        ];
    }

}