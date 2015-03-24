<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use Yii;
use yii\base\Model;

/**
 * Meta model
 *
 * @property string $location
 * @property string $signature
 * @property integer $gravatar
 * @property string $avatar
 */
class UserMeta extends User
{

    public $location;
    public $signature;
    public $gravatar;
    public $avatar;

    public function scenarios()
    {
        return Model::scenarios();
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

    public function getMeta()
    {
        return $this->hasOne(Meta::className(), ['user_id' => 'id']);
    }
}