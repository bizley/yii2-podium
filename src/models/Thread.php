<?php

namespace bizley\podium\models;

use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\log\Log;
use bizley\podium\Module as Podium;
use bizley\podium\rbac\Rbac;
use Exception;
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
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
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
    /**
     * Colour classes.
     */
    const CLASS_DEFAULT = 'default';
    const CLASS_EDITED  = 'warning';
    const CLASS_NEW     = 'success';
    
    /**
     * Icon classes.
     */
    const ICON_HOT      = 'fire';
    const ICON_LOCKED   = 'lock';
    const ICON_NEW      = 'leaf';
    const ICON_NO_NEW   = 'comment';
    const ICON_PINNED   = 'pushpin';

    /**
     * @var string attached post's content
     */
    public $post;
    
    /**
     * @var bool thread subscription flag
     */
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
            ['post', 'filter', 'filter' => function ($value) {
                return HtmlPurifier::process($value, Helper::podiumPurifierConfig('full'));
            }, 'on' => ['new']],
            ['pinned', 'boolean'],
            ['subscribe', 'boolean'],
            ['name', 'filter', 'filter' => function ($value) {
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
    public function getUserView()
    {
        return $this->hasOne(ThreadView::className(), ['thread_id' => 'id'])->where(['user_id' => User::loggedId()]);
    }
    
    /**
     * ThreadView relation general.
     * @return ThreadView[]
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
     * Posts count.
     * @return int
     * @since 0.2
     */
    public function getPostsCount()
    {
        return Post::find()->where(['thread_id' => $this->id])->count('id');
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
        return $this
                ->hasOne(Post::className(), ['thread_id' => 'id'])
                ->where(['>', 'created_at', $this->userView ? $this->userView->new_last_seen : 0])
                ->orderBy(['id' => SORT_ASC]);
    }
    
    /**
     * First edited not seen post relation.
     * @return Post
     */
    public function getFirstEditedNotSeen()
    {
        return $this
                ->hasOne(Post::className(), ['thread_id' => 'id'])
                ->where(['>', 'edited_at', $this->userView ? $this->userView->edited_last_seen : 0])
                ->orderBy(['id' => SORT_ASC]);
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
        if ($this->firstEditedNotSeen) {
            return $this->firstEditedNotSeen;
        }
        return $this->latest;
    }

    /**
     * Searches for thread.
     * @param int $forum_id
     * @return ActiveDataProvider
     */
    public function search($forum_id = null, $filters = null)
    {
        $query = static::find();
        if ($forum_id) {
            $query->where(['forum_id' => (int)$forum_id]);
        }
        if (!empty($filters)) {
            if (!empty($filters['pin']) && $filters['pin'] == 1) {
                $query->andWhere(['pinned' => 1]);
            }
            if (!empty($filters['lock']) && $filters['lock'] == 1) {
                $query->andWhere(['locked' => 1]);
            }
            if (!empty($filters['hot']) && $filters['hot'] == 1) {
                $query->andWhere(['>=', 'posts', Podium::getInstance()->config->get('hot_minimum')]);
            }
            if (!empty($filters['new']) && $filters['new'] == 1 && !Yii::$app->user->isGuest) {
                $query->joinWith(['threadView' => function ($q) {
                    $q->andWhere(['or', ['and', ['user_id' => User::loggedId()],
                            new Expression('new_last_seen < new_post_at')
                        ], ['user_id' => null]]);
                }]);
            }
            if (!empty($filters['edit']) && $filters['edit'] == 1 && !Yii::$app->user->isGuest) {
                $query->joinWith(['threadView' => function ($q) {
                    $q->andWhere(['or', ['and', ['user_id' => User::loggedId()],
                            new Expression('edited_last_seen < edited_post_at')
                        ], ['user_id' => null]]);
                }]);
            }
        }
        
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $dataProvider->sort->defaultOrder = [
            'pinned' => SORT_DESC, 
            'updated_at' => SORT_DESC, 
            'id' => SORT_ASC
        ];

        return $dataProvider;
    }
    
    /**
     * Searches for threads created by user of given ID.
     * @param int $user_id
     * @return ActiveDataProvider
     */
    public function searchByUser($user_id)
    {
        $query = static::find();
        $query->where(['author_id' => (int)$user_id]);
        if (Yii::$app->user->isGuest) {
            $query->joinWith(['forum' => function($q) {
                $q->where([Forum::tableName() . '.visible' => 1]);
            }]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false,
        ]);
        $dataProvider->sort->defaultOrder = [
            'updated_at' => SORT_DESC, 
            'id' => SORT_ASC
        ];

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
            $icon = self::ICON_LOCKED;
            $append = true;
        } elseif ($this->pinned) {
            $icon = self::ICON_PINNED;
            $append = true;
        } elseif ($this->posts >= Podium::getInstance()->config->get('hot_minimum')) {
            $icon = self::ICON_HOT;
            $append = true;
        }
        if ($this->userView) {
            if ($this->new_post_at > $this->userView->new_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            } elseif ($this->edited_post_at > $this->userView->edited_last_seen) {
                if (!$append) {
                    $icon = self::ICON_NEW;
                }
            }
        } else {
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
        $append = false;

        if ($this->locked) {
            $description = Yii::t('podium/view', 'Locked Thread');
            $append = true;
        } elseif ($this->pinned) {
            $description = Yii::t('podium/view', 'Pinned Thread');
            $append = true;
        } elseif ($this->posts >= Podium::getInstance()->config->get('hot_minimum')) {
            $description = Yii::t('podium/view', 'Hot Thread');
            $append = true;
        }
        if ($this->userView) {
            if ($this->new_post_at > $this->userView->new_last_seen) {
                if (!$append) {
                    $description = Yii::t('podium/view', 'New Posts');
                } else {
                    $description .= ' (' . Yii::t('podium/view', 'New Posts') . ')';
                }
            } elseif ($this->edited_post_at > $this->userView->edited_last_seen) {
                if (!$append) {
                    $description = Yii::t('podium/view', 'Edited Posts');
                } else {
                    $description = ' (' . Yii::t('podium/view', 'Edited Posts') . ')';
                }
            }
        } else {
            if (!$append) {
                $description = Yii::t('podium/view', 'New Posts');
            } else {
                $description .= ' (' . Yii::t('podium/view', 'New Posts') . ')';
            }
        }
        return $description;
    }

    /**
     * Returns proper CSS class for thread.
     * @return string
     */
    public function getCssClass()
    {
        $class = self::CLASS_DEFAULT;

        if ($this->userView) {
            if ($this->new_post_at > $this->userView->new_last_seen) {
                $class = self::CLASS_NEW;
            } elseif ($this->edited_post_at > $this->userView->edited_last_seen) {
                $class = self::CLASS_EDITED;
            }
        } else {
            $class = self::CLASS_NEW;
        }
        return $class;
    }
    
    /**
     * Checks if user is this thread's moderator.
     * @param int $user_id
     * @return bool
     */
    public function isMod($user_id = null)
    {
        if (User::can(Rbac::ROLE_ADMIN)) {
            return true;
        }
        if (in_array($user_id, $this->forum->getMods())) {
            return true;
        }
        return false;
    }
    
    /**
     * Returns the verified thread.
     * @param int $category_id thread's category ID
     * @param int $forum_id thread's forum ID
     * @param int $id thread's ID
     * @param string $slug thread's slug
     * @param bool $guest whether caller is guest or registered user
     * @return Thread
     * @since 0.2
     */
    public static function verify($category_id = null, $forum_id = null, $id = null, $slug = null,  $guest = true)
    {
        if (!is_numeric($category_id) 
                || $category_id < 1 
                || !is_numeric($forum_id) 
                || $forum_id < 1 
                || !is_numeric($id) 
                || $id < 1 
                || empty($slug)) {
            return null;
        }
        return static::find()
                ->joinWith([
                    'forum' => function ($query) use ($guest) {
                        if ($guest) {
                            $query->andWhere([Forum::tableName() . '.visible' => 1]);
                        }
                        $query->joinWith(['category' => function ($query) use ($guest) {
                            if ($guest) {
                                $query->andWhere([Category::tableName() . '.visible' => 1]);
                            }
                        }]);
                    }
                ])
                ->where([
                    static::tableName() . '.id' => $id, 
                    static::tableName() . '.slug' => $slug,
                    static::tableName() . '.forum_id' => $forum_id,
                    static::tableName() . '.category_id' => $category_id,
                ])
                ->limit(1)
                ->one();
    }
    
    /**
     * Performs thread delete with parent forum counters update.
     * @return bool
     * @since 0.2
     */
    public function podiumDelete()
    {
        $transaction = Thread::getDb()->beginTransaction();
        try {
            if ($this->delete()) {
                $this->forum->updateCounters([
                    'threads' => -1, 
                    'posts'   => -$this->postsCount
                ]);
                $transaction->commit();
                Cache::clearAfter('threadDelete');
                Log::info('Thread deleted', $this->id, __METHOD__);
                return true;
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Performs thread posts delete with parent forum counters update.
     * @param array $posts posts IDs
     * @return bool
     * @throws Exception
     * @since 0.2
     */
    public function podiumDeletePosts($posts)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            foreach ($posts as $post) {
                if (!is_numeric($post) || $post < 1) {
                    throw new Exception('Incorrect post ID');
                }
                $nPost = Post::find()
                            ->where([
                                'id'        => $post, 
                                'thread_id' => $this->id, 
                                'forum_id'  => $this->forum->id
                            ])
                            ->limit(1)
                            ->one();
                if (!$nPost) {
                    throw new Exception('No post of given ID found');
                }
                $nPost->delete();
            }
            $wholeThread = false;
            if ($this->postsCount) {
                $this->updateCounters(['posts' => -count($posts)]);
                $this->forum->updateCounters(['posts' => -count($posts)]);
            } else {
                $wholeThread = true;
                $this->delete();
                $this->forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
            }
            $transaction->commit();
            Cache::clearAfter('postDelete');
            Log::info('Posts deleted', null, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Performs thread lock / unlock.
     * @return bool
     * @since 0.2
     */
    public function podiumLock()
    {
        $this->locked = !$this->locked;
        if ($this->save()) {
            Log::info($this->locked ? 'Thread locked' : 'Thread unlocked', $this->id, __METHOD__);
            return true;
        }
        return false;
    }
    
    /**
     * Performs thread move with counters update.
     * @param int $target new parent forum's ID
     * @return bool
     * @since 0.2
     */
    public function podiumMoveTo($target = null)
    {
        $newParent = Forum::find()->where(['id' => $target])->limit(1)->one();
        if ($newParent) {
            $postsCount = $this->postsCount;
            $transaction = Forum::getDb()->beginTransaction();
            try {
                $this->forum->updateCounters(['threads' => -1, 'posts' => -$postsCount]);
                $newParent->updateCounters(['threads' => 1, 'posts' => $postsCount]);
                $this->forum_id = $newParent->id;
                $this->category_id = $newParent->category_id;
                if ($this->save()) {
                    Post::updateAll(['forum_id' => $newParent->id], ['thread_id' => $this->id]);
                }
                $transaction->commit();
                Cache::clearAfter('threadMove');
                Log::info('Thread moved', $this->id, __METHOD__);
                return true;
            } catch (Exception $e) {
                $transaction->rollBack();
                Log::error($e->getMessage(), null, __METHOD__);
            }
        } else {
            Log::error('No parent forum of given ID found', $this->id, __METHOD__);
        }
        return false;
    }
    
    /**
     * Performs thread posts move with counters update.
     * @param int $target new parent thread's ID
     * @param array $posts IDs of posts to move
     * @param string $name new thread's name if $target = 0
     * @param type $forum new thread's parent forum if $target = 0
     * @return bool
     * @throws Exception
     * @since 0.2
     */
    public function podiumMovePostsTo($target = null, $posts = [], $name = null, $forum = null)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if ($target == 0) {
                $parent = Forum::find()->where(['id' => $forum])->limit(1)->one();
                if (empty($parent)) {
                    throw new Exception('No parent forum of given ID found');
                }
                $newThread = new Thread;
                $newThread->name = $name;
                $newThread->posts = 0;
                $newThread->views = 0;
                $newThread->category_id = $parent->category_id;
                $newThread->forum_id = $parent->id;
                $newThread->author_id = User::loggedId();
                $newThread->save();                
            } else {
                $newThread = Thread::find()->where(['id' => $target])->limit(1)->one();
                if (empty($newThread)) {
                    throw new Exception('No thread of given ID found');
                }
            }
            if (!empty($newThread)) {
                foreach ($posts as $post) {
                    if (!is_numeric($post) || $post < 1) {
                        throw new Exception('Incorrect post ID');
                    }
                    $newPost = Post::find()
                                ->where([
                                    'id'        => $post, 
                                    'thread_id' => $this->id, 
                                    'forum_id'  => $this->forum->id
                                ])
                                ->limit(1)
                                ->one();
                    if (empty($newPost)) {
                        throw new Exception('No post of given ID found');
                    }
                    $newPost->thread_id = $newThread->id;
                    $newPost->forum_id = $newThread->forum_id;
                    $newPost->save();                    
                }
                $wholeThread = false;
                if ($this->postCount) {
                    $this->updateCounters(['posts' => -count($posts)]);
                    $this->forum->updateCounters(['posts' => -count($posts)]);
                } else {
                    $wholeThread = true;
                    $this->delete();
                    $this->forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
                }
                $newThread->updateCounters(['posts' => count($posts)]);
                $newThread->forum->updateCounters(['posts' => count($posts)]);
                $transaction->commit();
                Cache::clearAfter('postMove');
                Log::info('Posts moved', null, __METHOD__);
                return true;
            }
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Performs new thread with first post creation and subscription.
     * @return bool
     * @since 0.2
     */
    public function podiumNew()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if ($this->save()) {
                $this->forum->updateCounters(['threads' => 1]);

                $post = new Post;
                $post->content = $this->post;
                $post->thread_id = $this->id;
                $post->forum_id = $this->forum_id;
                $post->author_id = User::loggedId();
                $post->likes = 0;
                $post->dislikes = 0;

                if ($post->save()) {
                    $post->markSeen();
                    $this->forum->updateCounters(['posts' => 1]);
                    $this->updateCounters(['posts' => 1]);

                    $this->touch('new_post_at');
                    $this->touch('edited_post_at');

                    if ($this->subscribe) {
                        $subscription = new Subscription();
                        $subscription->user_id = User::loggedId();
                        $subscription->thread_id = $this->id;
                        $subscription->post_seen = Subscription::POST_SEEN;
                        $subscription->save();
                    }
                }
            }
            $transaction->commit();
            Cache::clearAfter('newThread');
            Log::info('Thread added', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Performs thread pin / unpin.
     * @return bool
     * @since 0.2
     */
    public function podiumPin()
    {
        $this->pinned = !$this->pinned;
        if ($this->save()) {
            Log::info($this->pinned ? 'Thread pinned' : 'Thread unpinned', $this->id, __METHOD__);
            return true;
        }
        return false;
    }
    
    /**
     * Performs marking all unread threads as seen for user.
     * @return bool
     * @throws Exception
     * @since 0.2
     */
    public static function podiumMarkAllSeen()
    {
        try {
            $loggedId = User::loggedId();
            if (empty($loggedId)) {
                throw new Exception('User ID missing');
            }
            $updateBatch = [];
            $threadsPrevMarked = Thread::find()->joinWith('threadView')
                    ->where(['and',
                        ['user_id' => $loggedId],
                        ['or',
                            new Expression('new_last_seen < new_post_at'),
                            new Expression('edited_last_seen < edited_post_at')
                        ],
                    ]);
            $time = time();
            foreach ($threadsPrevMarked->each() as $thread) {
                $updateBatch[] = $thread->id;
            }
            if (!empty($updateBatch)) {
                Yii::$app->db->createCommand()->update(ThreadView::tableName(), [
                    'new_last_seen' => $time, 
                    'edited_last_seen' => $time
                ], ['thread_id' => $updateBatch, 'user_id' => $loggedId])->execute();
            }

            $insertBatch = [];
            $threadsNew = Thread::find()->joinWith('threadView')->where(['user_id' => null]);
            foreach ($threadsNew->each() as $thread) {
                $insertBatch[] = [$loggedId, $thread->id, $time, $time];
            }
            if (!empty($insertBatch)) {
                Yii::$app->db->createCommand()->batchInsert(ThreadView::tableName(), [
                    'user_id', 'thread_id', 'new_last_seen', 'edited_last_seen'
                ], $insertBatch)->execute();
            }
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
