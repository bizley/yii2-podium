<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\HtmlPurifier;

/**
 * Category model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property integer $visible
 * @property integer $sort
 * @property integer $updated_at
 * @property integer $created_at
 */
class CategoryActiveRecord extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_category}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => SluggableBehavior::className(),
                'attribute' => 'name',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'visible'], 'required'],
            ['visible', 'boolean'],
            ['name', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim($value));
            }],
            [['keywords', 'description'], 'string'],
        ];
    }
}
