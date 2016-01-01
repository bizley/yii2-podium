<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use Zelenin\yii\behaviors\Slug;

/**
 * Category model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
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
class Category extends ActiveRecord
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
                'class' => Slug::className(),
                'attribute' => 'name',
                'immutable' => false,
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
            ['name', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(Html::encode($value));
            }],
            [['keywords', 'description'], 'string'],
        ];
    }
    
    /**
     * Searches users.
     * @return ActiveDataProvider
     */
    public function search()
    {
        $query = self::find();
        if (Yii::$app->user->isGuest) {
            $query->andWhere(['visible' => 1]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider;
    }
    
    /**
     * Returns categories.
     * @return Category[]
     */
    public function show()
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider->getModels();
    }
}
