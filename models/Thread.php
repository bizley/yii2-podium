<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\models;

use bizley\podium\components\Config;
use bizley\podium\components\Helper;
use bizley\podium\rbac\Rbac;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\Html;
use yii\helpers\HtmlPurifier;
use Zelenin\yii\behaviors\Slug;

/**
 * Thread model
 *
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * @property integer $id
 * @property string $name
 * @property string $slug
 * @property integer $category_id
 * @property integer $forum_id
 * @property integer $author_id
 * @property integer $pinned
 * @property integer $updated_at
 * @property integer $created_at
 */
class Thread extends ActiveRecord
{

    const CLASS_DEFAULT = 'default';
    const CLASS_EDITED  = 'warning';
    const CLASS_NEW     = 'success';
    
    const ICON_HOT      = 'fire';
    const ICON_LOCKED   = 'lock';
    const ICON_NEW      = 'leaf';
    const ICON_NO_NEW   = 'comment';
    const ICON_PINNED   = 'pushpin';

    public $post;
    public $subscribe;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%podium_thread}}';
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class'     => Slug::className(),
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
            ['name', 'required', 'message' => Yii::t('podium/view', 'Topic can not be blank.')],
            ['post', 'required', 'on' => ['new']],
            ['post', 'string', 'min' => 10, 'on' => ['new']],
            ['post', 'filter', 'filter' => function($value) {
                    return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
                }, 'on' => ['new']],
            ['pinned', 'boolean'],
            ['subscribe', 'boolean'],
            ['name', 'filter', 'filter' => function($value) {
                return HtmlPurifier::process(Html::encode($value));
            }],
        ];
    }

    /**
     * Forum relation.
     * @return Forum
     */
    public function getForum()
    {
        return $this->hasOne(Forum::className(), ['id' => 'forum_id']);
    }

    /**
     * ThreadView relation for user.
     * @return ThreadView
     */
    public function getView()
    {
        return $this->hasOne(ThreadView::className(), ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }
    
    /**
     * ThreadView relation general.
     * @return ThreadView
     */
    public function getThreadView()
    {
        return $this->hasMany(ThreadView::className(), ['thread_id' => 'id']);
    }
    
    /**
     * Subscription relation.
     * @return Subscription
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }
    
    /**
     * Latest post relation.
     * @return Post
     */
    public function getLatest()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->orderBy(['id' => SORT_DESC]);
    }
    
    /**
     * First post relation.
     * @return Post
     */
    public function getPostData()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * First new not seen post relation.
     * @return Post
     */
    public function getFirstNewNotSeen()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->where(['>', 'created_at', $this->view ? $this->view->new_last_seen : 0])->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * First edited not seen post relation.
     * @return Post
     */
    public function getFirstEditedNotSeen()
    {
        return $this->hasOne(Post::className(), ['thread_id' => 'id'])->where(['>', 'edited_at', $this->view ? $this->view->edited_last_seen : 0])->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * Author relation.
     * @return User
     */
    public function getAuthor()
    {
        return $this->hasOne(User::className(), ['id' => 'author_id']);
    }
    
    /**
     * Returns first post to see.
     * @return Post
     */
    public function firstToSee()
    {
        if ($this->firstNewNotSeen) {
            return $this->firstNewNotSeen;
        }
        elseif ($this->firstEditedNotSeen) {
            return $this->firstEditedNotSeen;
        }
        else {
            return $this->latest;
        }
    }

    /**
     * Searches for thread.
     * @param integer $forum_id
     * @return ActiveDataProvider
     */
    public function search($forum_id = null, $filters = null)
    {
        $query = self::find();
        if ($forum_id) {
            $query->where(['forum_id' => (int) $forum_id]);
        }
        if (!empty($filters)) {
            if (!empty($filters['pin']) && $filters['pin'] == 1) {
                $query->andWhere(['pinned' => 1]);
            }
            if (!empty($filters['lock']) && $filters['lock'] == 1) {
                $query->andWhere(['locked' => 1]);
            }
            if (!empty($filters['hot']) && $filters['hot'] == 1) {
                $query->andWhere(['>=', 'posts', Config::getInstance()->get('hot_minimum')]);
            }
            if (!empty($filters['new']) && $filters['new'] == 1 && !Yii::$app->user->isGuest) {
                $query->joinWith(['view' => function ($q) {
                    $q->andWhere([
                            'or', 
                            [
                                'and',
                                ['user_id' => User::loggedId()],
                                new Expression('`new_last_seen` < `new_post_at`')
                            ],
                            ['user_id' => null]
                        ]);
                }]);
            }
            if (!empty($filters['edit']) && $filters['edit'] == 1 && !Yii::$app->user->isGuest) {
                $query->joinWith(['view' => function ($q) {
                    $q->andWhere([
                            'or', 
                            [
                                'and',
                                ['user_id' => User::loggedId()],
                                new Expression('`edited_last_seen` < `edited_post_at`')
                            ],
                            ['user_id' => null]
                        ]);
                }]);
            }
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $dataProvider->sort->defaultOrder = ['pinned' => SORT_DESC, 'updated_at' => SORT_DESC, 'id' => SORT_ASC];

        return $dataProvider;
    }
    
    /**
     * Searches for threads created by user of given ID.
     * @param integer $user_id
     * @return ActiveDataProvider
     */
    public function searchByUser($user_id)
    {
        $query = self::find();
        $query->where(['author_id' => (int) $user_id]);
        if (Yii::$app->user->isGuest) {
            $query->joinWith(['forum' => function($q) {
                $q->where([Forum::tableName() . '.visible' => 1]);
            }]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);

        $dataProvider->sort->defaultOrder = ['updated_at' => SORT_DESC, 'id' => SORT_ASC];

        return $dataProvider;
    }

    /**
     * Returns proper icon for thread.
     * @return string
     */
    public function getIcon()
    {
        $icon   = self::ICON_NO_NEW;
        $append = false;

        if ($this->locked) {
            $icon   = self::ICON_LOCKED;
            $append = true;
        }
        elseif ($this->pinned) {
            $icon   = self::ICON_PINNED;
            $append = true;
        }
        elseif ($this->posts >= Config::getInstance()->get('hot_minimum')) {
            $icon   = self::ICON_HOT;
            $append = true;
        }

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            }
        }
        else {
            if (!$append) {
                $icon = self::ICON_NEW;
            }
        }

        return $icon;
    }

    /**
     * Returns proper description for thread.
     * @return string
     */
    public function getDescription()
    {
        $description = Yii::t('podium/view', 'No New Posts');
        $append      = false;

        if ($this->locked) {
            $description = Yii::t('podium/view', 'Locked Thread');
            $append      = true;
        }
        elseif ($this->pinned) {
            $description = Yii::t('podium/view', 'Pinned Thread');
            $append      = true;
        }
        elseif ($this->posts >= Config::getInstance()->get('hot_minimum')) {
            $description = Yii::t('podium/view', 'Hot Thread');
            $append      = true;
        }

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                if (!$append) {
                    $description = Yii::t('podium/view', 'New Posts');
                }
                else {
                    $description .= ' (' . Yii::t('podium/view', 'New Posts') . ')';
                }
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                if (!$append) {
                    $description = Yii::t('podium/view', 'Edited Posts');
                }
                else {
                    $description = ' (' . Yii::t('podium/view', 'Edited Posts') . ')';
                }
            }
        }
        else {
            if (!$append) {
                $description = Yii::t('podium/view', 'New Posts');
            }
            else {
                $description .= ' (' . Yii::t('podium/view', 'New Posts') . ')';
            }
        }

        return $description;
    }

    /**
     * Returns proper CSS class for thread.
     * @return string
     */
    public function getClass()
    {
        $class = self::CLASS_DEFAULT;

        if ($this->view) {
            if ($this->new_post_at > $this->view->new_last_seen) {
                $class = self::CLASS_NEW;
            }
            elseif ($this->edited_post_at > $this->view->edited_last_seen) {
                $class = self::CLASS_EDITED;
            }
        }
        else {
            $class = self::CLASS_NEW;
        }

        return $class;
    }
    
    /**
     * Checks if user is this thread's moderator.
     * @param integer $user_id
     * @return boolean
     */
    public function isMod($user_id = null)
    {
        if (User::can(Rbac::ROLE_ADMIN)) {
            return true;
        }
        else {
            if (in_array($user_id, $this->forum->getMods())) {
                return true;
            }
            return false;
        }
    }
}
