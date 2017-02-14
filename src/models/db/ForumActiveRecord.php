<?php

namespace bizley\podium\models\db;

use bizley\podium\db\ActiveRecord;
use bizley\podium\models\Category;
use bizley\podium\models\Post;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\helpers\HtmlPurifier;

/**
 * Forum AR
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 *
 * @property integer $id
 * @property integer $category_id
 * @property string $name
 * @property string $sub
 * @property string $slug
 * @property string $keywords
 * @property string $description
 * @property integer $visible
 * @property integer $sort
 * @property integer $updated_at
 * @property integer $created_at
 */
class ForumActiveRecord extends ActiveRecord
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
            [['name', 'sub'], 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process(trim($value));
            }],
            [['keywords', 'description'], 'string'],
        ];
    }

    /**
     * Category relation.
     * @return ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }

    /**
     * Post relation. One latest post.
     * @return ActiveQuery
     */
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['forum_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
}
