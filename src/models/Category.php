<?php

/**
 * @author Bizley
 */
namespace bizley\podium\models;

use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;

/**
 * Category model
 *
 * @property integer $id
 * @property string $name
 * @property string $slug
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
                'class' => SluggableBehavior::className(),
                'attribute' => 'name'
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
            ['name', 'validateName'],
        ];
    }
    
    /**
     * Validates name
     * Custom method is required because JS ES5 (and so do Yii 2) doesn't support regex unicode features.
     * @param string $attribute
     */
    public function validateName($attribute)
    {
        if (!$this->hasErrors()) {
            if (!preg_match('/^[\w\s\p{L}]{1,255}$/u', $this->$attribute)) {
                $this->addError($attribute, Yii::t('podium/view', 'Name must contain only letters, digits, underscores and spaces (255 characters max).'));
            }
        }
    }
    
    public function show()
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider->getModels();
    }
    
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
}
