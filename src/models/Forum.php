<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\log\Log;
use bizley\podium\Module as Podium;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use Zelenin\yii\behaviors\Slug;

/**
 * Forum model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
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
            [['name', 'sub'], 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(Html::encode($value));
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
    
    /**
     * Returns list of moderators for this forum.
     * @return int[]
     */
    public function getMods()
    {
        $mods = Podium::getInstance()->cache->getElement('forum.moderators', $this->id);
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
            Podium::getInstance()->cache->setElement('forum.moderators', $this->id, $mods);
        }
        return $mods;        
    }
    
    /**
     * Checks if user is moderator for this forum.
     * @param int|null $user_id User's ID or null for current signed in.
     * @return bool
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
     * @param int|null $category_id
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

        $dataProvider = new ActiveDataProvider(['query' => $query]);
        $dataProvider->sort->defaultOrder = ['sort' => SORT_ASC, 'id' => SORT_ASC];

        return $dataProvider;
    }
    
    /**
     * Returns the verified forum.
     * @param int $category_id forum's category ID
     * @param int $id forum's ID
     * @param string $slug forum's slug
     * @param bool $guest whether caller is guest or registered user
     * @return Thread
     * @since 0.2
     */
    public static function verify($category_id = null, $id = null, $slug = null,  $guest = true)
    {
        if (!is_numeric($category_id) || $category_id < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            return null;
        }
        
        return static::find()->joinWith(['category' => function ($query) use ($guest) {
                if ($guest) {
                    $query->andWhere([Category::tableName() . '.visible' => 1]);
                }
            }])->where([
                    static::tableName() . '.id'          => $id, 
                    static::tableName() . '.slug'        => $slug,
                    static::tableName() . '.category_id' => $category_id,
                ])->limit(1)->one();
    }
    
    /**
     * Sets new forums order.
     * @param int $order new forum sorting order number
     * @return bool
     * @throws Exception
     * @since 0.2
     */
    public function newOrder($order)
    {
        try {
            $next    = 0;
            $newSort = -1;
            $query   = (new Query)->from(Forum::tableName())->where('id != :id AND category_id = :cid')->
                params([':id' => $this->id, ':cid' => $this->category_id])->orderBy(['sort' => SORT_ASC, 'id' => SORT_ASC])->indexBy('id');
            foreach ($query->each() as $id => $forum) {
                if ($next == $order) {
                    $newSort = $next;
                    $next++;
                }
                Yii::$app->db->createCommand()->update(Forum::tableName(), ['sort' => $next], 'id = :id', [':id' => $id])->execute();
                $next++;
            }
            
            if ($newSort == -1) {
                $newSort = $next;
            }
            
            $this->sort = $newSort;
            
            if (!$this->save()) {
                throw new Exception('Forums order saving error');
            }
            
            Log::info('Forums orded updated', $this->id, __METHOD__);
            return true;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
