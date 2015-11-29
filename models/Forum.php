<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Cache;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Query;
use Zelenin\yii\behaviors\Slug;

/**
 * Forum model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
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
            [['name', 'sub'], 'validateName'],
            [['keywords', 'description'], 'string'],
        ];
    }
    
    /**
     * Category relation.
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::className(), ['id' => 'category_id']);
    }
    
    /**
     * Post relation. One latest post.
     * @return \yii\db\ActiveQuery
     */
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['forum_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
    
    /**
     * Returns list of moderators for this forum.
     * @return integer[]
     */
    public function getMods()
    {
        $mods = Cache::getInstance()->getElement('forum.moderators', $this->id);
        if ($mods === false) {
            $mods    = [];
            $modteam = User::find()->select(['id', 'role'])->where(['status' => User::STATUS_ACTIVE, 'role' => [User::ROLE_ADMIN, User::ROLE_MODERATOR]])->asArray()->all();

            foreach ($modteam as $user) {
                if ($user['role'] == User::ROLE_ADMIN) {
                    $mods[] = $user['id'];
                }
                else {
                    if ((new Query)->from(Mod::tableName())->where(['forum_id' => $this->id, 'user_id' => $user->id])->exists()) {
                        $mods[] = $user['id'];
                    }
                }
            }
            Cache::getInstance()->setElement('forum.moderators', $this->id, $mods);
        }
        
        return $mods;        
    }
    
    /**
     * Checks if user is moderator for this forum.
     * @param integer|null $user_id User's ID or null for current signed in.
     * @return boolean
     */
    public function isMod($user_id = null)
    {
        if (in_array($user_id ?: User::loggedId(), $this->getMods())) {
            return true;
        }
        return false;
    }
    
    /**
     * Searches forums.
     * @param integer|null $category_id
     * @return ActiveDataProvider
     */
    public function search($category_id = null, $onlyVisible = false)
    {
        $query = static::find();
        if ($category_id) {
            $query->andWhere(['category_id' => $category_id]);
        }
        if ($onlyVisible) {
            $query->joinWith(['category' => function ($query) {
                $query->andWhere([Category::tableName() . '.visible' => 1]);
            }]);
            $query->andWhere([static::tableName() . '.visible' => 1]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider;
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
}
