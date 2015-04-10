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
 * Forum model
 *
 * @property integer $id
 * @property string $name
 * @property string $sub
 * @property string $slug
 * @property integer $visible
 * @property integer $updated_at
 * @property integer $created_at
 */
class Forum extends ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_forum}}';
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
            [['name', 'sub'], 'validateName'],
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
    
    public function search()
    {
        $query = self::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        return $dataProvider->getModels();
    }
}
