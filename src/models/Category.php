<?php

namespace bizley\podium\models;

use bizley\podium\log\Log;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use Zelenin\yii\behaviors\Slug;

/**
 * Category model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
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
        $query = static::find();        
        if (Yii::$app->user->isGuest) {
            $query->andWhere(['visible' => 1]);
        }
        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider;
    }
    
    /**
     * Returns categories.
     * @return Category[]
     */
    public function show()
    {
        $dataProvider = new ActiveDataProvider(['query' => static::find()]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];
        return $dataProvider->getModels();
    }
    
    /**
     * Sets new categories order.
     * @param int $order new category sorting order number
     * @return bool
     * @throws Exception
     * @since 0.2
     */
    public function newOrder($order)
    {
        try {
            $next = 0;
            $newSort = -1;
            $query = (new Query)
                        ->from(Category::tableName())
                        ->where(['!=', 'id', $this->id])
                        ->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])
                        ->indexBy('id');
            foreach ($query->each() as $id => $forum) {
                if ($next == $order) {
                    $newSort = $next;
                    $next++;
                }
                Yii::$app->db->createCommand()->update(Category::tableName(), ['sort' => $next], ['id' => $id])->execute();
                $next++;
            }
            if ($newSort == -1) {
                $newSort = $next;
            }
            $this->sort = $newSort;
            if (!$this->save()) {
                throw new Exception('Categories order saving error');
            }
            Log::info('Categories orded updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
