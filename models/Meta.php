<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Helper;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\HtmlPurifier;

/**
 * Meta model
 * User's meta data.
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property integer $user_id
 * @property string $location
 * @property string $signature
 * @property integer $gravatar
 * @property string $avatar
 * @property integer $created_at
 * @property integer $updated_at
 */
class Meta extends ActiveRecord
{

    /**
     * @var mixed Avatar image
     */
    public $image;

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
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['location', 'trim'],        
            ['location', 'validateLocation'],            
            ['gravatar', 'boolean'],
            ['image', 'image', 'mimeTypes' => 'image/png, image/jpeg, image/gif', 'maxWidth' => 500, 'maxHeight' => 500, 'maxSize' => 500 * 1024],
            ['signature', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig('minimal'));
            }],
        ];
    }

    /**
     * Validates location
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateLocation($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\w\s\p{L}]{0,32}$/u', $this->location)) {
                $this->addError($attribute, Yii::t('podium/view', 'Location must contain only letters, digits, underscores and spaces (32 characters max).'));
            }
        }
    }
}
