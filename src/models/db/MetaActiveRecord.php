<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use bizley\podium\helpers\Helper;
use yii\behaviors\TimestampBehavior;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;

/**
 * Meta AR
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 * 
 * @property integer $id
 * @property integer $user_id
 * @property string $location
 * @property string $signature
 * @property integer $gravatar
 * @property string $avatar
 * @property integer $created_at
 * @property integer $updated_at
 */
class MetaActiveRecord extends ActiveRecord
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
        return [TimestampBehavior::className()];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['location', 'signature'], 'trim'],        
            ['location', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(Html::encode($value));
            }],            
            ['gravatar', 'boolean'],
            ['signature', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig());
            }],
            ['signature', 'string', 'max' => 512],
        ];
    }
}
