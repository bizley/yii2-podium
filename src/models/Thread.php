<?php

namespace bizley\podium\models;

use bizley\podium\log\Log;
use bizley\podium\models\db\ThreadActiveRecord;
use bizley\podium\Podium;
use bizley\podium\PodiumCache;
use bizley\podium\rbac\Rbac;
use cebe\markdown\GithubMarkdown;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Expression;

/**
 * Thread model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 *
 * @property string $parsedPost
 */
class Thread extends ThreadActiveRecord
{
    /**
     * Color classes.
     */
    const CLASS_DEFAULT = 'default';
    const CLASS_EDITED  = 'warning';
    const CLASS_NEW     = 'success';

    /**
     * Icon classes.
     */
    const ICON_HOT    = 'fire';
    const ICON_LOCKED = 'lock';
    const ICON_NEW    = 'leaf';
    const ICON_NO_NEW = 'comment';
    const ICON_PINNED = 'pushpin';

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
     * @param int $forumId
     * @return ActiveDataProvider
     */
    public function search($forumId = null, $filters = null)
    {
        $query = static::find();
        if ($forumId) {
            $query->where(['forum_id' => (int)$forumId]);
        }
        if (!empty($filters)) {
            $loggedId = User::loggedId();
            if (!empty($filters['pin']) && $filters['pin'] == 1) {
                $query->andWhere(['pinned' => 1]);
            }
            if (!empty($filters['lock']) && $filters['lock'] == 1) {
                $query->andWhere(['locked' => 1]);
            }
            if (!empty($filters['hot']) && $filters['hot'] == 1) {
                $query->andWhere(['>=', 'posts', Podium::getInstance()->podiumConfig->get('hot_minimum')]);
            }
            if (!empty($filters['new']) && $filters['new'] == 1 && !Podium::getInstance()->user->isGuest) {
                $query->joinWith(['threadView tvn' => function ($q) use ($loggedId) {
                    $q->onCondition(['tvn.user_id' => $loggedId]);
                    $q->andWhere(['or',
                            new Expression('tvn.new_last_seen < new_post_at'),
                            ['tvn.new_last_seen' => null]
                        ]);
                }], false);
            }
            if (!empty($filters['edit']) && $filters['edit'] == 1 && !Podium::getInstance()->user->isGuest) {
                $query->joinWith(['threadView tve' => function ($q) use ($loggedId) {
                    $q->onCondition(['tve.user_id' => $loggedId]);
                    $q->andWhere(['or',
                            new Expression('tve.edited_last_seen < edited_post_at'),
                            ['tve.edited_last_seen' => null]
                        ]);
                }], false);
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
     * @param int $userId
     * @return ActiveDataProvider
     */
    public function searchByUser($userId)
    {
        $query = static::find();
        $query->where(['author_id' => $userId]);
        if (Podium::getInstance()->user->isGuest) {
            $query->joinWith(['forum' => function ($q) {
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
        } elseif ($this->posts >= Podium::getInstance()->podiumConfig->get('hot_minimum')) {
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
        } elseif ($this->posts >= Podium::getInstance()->podiumConfig->get('hot_minimum')) {
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
     * Checks if user is this thread moderator.
     * @param int $userId
     * @return bool
     */
    public function isMod($userId = null)
    {
        if (User::can(Rbac::ROLE_ADMIN)) {
            return true;
        }
        if (in_array($userId, $this->forum->getMods())) {
            return true;
        }
        return false;
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
            if (!$this->delete()) {
                throw new Exception('Thread deleting error!');
            }
            $this->forum->updateCounters([
                'threads' => -1,
                'posts'   => -$this->postsCount
            ]);
            $transaction->commit();
            PodiumCache::clearAfter('threadDelete');
            Log::info('Thread deleted', $this->id, __METHOD__);
            return true;
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
                                'id' => $post,
                                'thread_id' => $this->id,
                                'forum_id' => $this->forum->id
                            ])
                            ->limit(1)
                            ->one();
                if (!$nPost) {
                    throw new Exception('No post of given ID found');
                }
                if (!$nPost->delete()) {
                    throw new Exception('Post deleting error!');
                }
            }
            $wholeThread = false;
            if ($this->postsCount) {
                $this->updateCounters(['posts' => -count($posts)]);
                $this->forum->updateCounters(['posts' => -count($posts)]);
            } else {
                $wholeThread = true;
                if (!$this->delete()) {
                    throw new Exception('Thread deleting error!');
                }
                $this->forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
            }
            $transaction->commit();
            PodiumCache::clearAfter('postDelete');
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
        if (empty($newParent)) {
            Log::error('No parent forum of given ID found', $this->id, __METHOD__);
            return false;
        }

        $postsCount = $this->postsCount;
        $transaction = Forum::getDb()->beginTransaction();
        try {
            $this->forum->updateCounters(['threads' => -1, 'posts' => -$postsCount]);
            $newParent->updateCounters(['threads' => 1, 'posts' => $postsCount]);
            $this->forum_id = $newParent->id;
            $this->category_id = $newParent->category_id;
            if (!$this->save()) {
                throw new Exception('Thread saving error!');
            }
            Post::updateAll(['forum_id' => $newParent->id], ['thread_id' => $this->id]);
            $transaction->commit();
            PodiumCache::clearAfter('threadMove');
            Log::info('Thread moved', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Performs thread posts move with counters update.
     * @param int $target new parent thread ID
     * @param array $posts IDs of posts to move
     * @param string $name new thread name if $target = 0
     * @param type $forum new thread parent forum if $target = 0
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
                $newThread = new Thread();
                $newThread->name = $name;
                $newThread->posts = 0;
                $newThread->views = 0;
                $newThread->category_id = $parent->category_id;
                $newThread->forum_id = $parent->id;
                $newThread->author_id = User::loggedId();
                if (!$newThread->save()) {
                    throw new Exception('Thread saving error!');
                }
            } else {
                $newThread = Thread::find()->where(['id' => $target])->limit(1)->one();
                if (empty($newThread)) {
                    throw new Exception('No thread of given ID found');
                }
            }
            if (empty($newThread)) {
                throw new Exception('No target thread selected!');
            }
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
                if (!$newPost->save()) {
                    throw new Exception('Post saving error!');
                }
            }
            $wholeThread = false;
            if ($this->postCount) {
                $this->updateCounters(['posts' => -count($posts)]);
                $this->forum->updateCounters(['posts' => -count($posts)]);
            } else {
                $wholeThread = true;
                if (!$this->delete()) {
                    throw new Exception('Thread deleting error!');
                }
                $this->forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
            }
            $newThread->updateCounters(['posts' => count($posts)]);
            $newThread->forum->updateCounters(['posts' => count($posts)]);
            $transaction->commit();
            PodiumCache::clearAfter('postMove');
            Log::info('Posts moved', null, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Performs new thread with first post creation and subscription.
     * Saves thread poll.
     * @return bool
     * @since 0.2
     */
    public function podiumNew()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if (!$this->save()) {
                throw new Exception('Thread saving error!');
            }

            $loggedIn = User::loggedId();

            if ($this->pollAdded && Podium::getInstance()->podiumConfig->get('allow_polls')) {
                $poll = new Poll();
                $poll->thread_id = $this->id;
                $poll->question = $this->pollQuestion;
                $poll->votes = $this->pollVotes;
                $poll->hidden = $this->pollHidden;
                $poll->end_at = !empty($this->pollEnd) ? Podium::getInstance()->formatter->asTimestamp($this->pollEnd . ' 23:59:59') : null;
                $poll->author_id = $loggedIn;
                if (!$poll->save()) {
                    throw new Exception('Poll saving error!');
                }

                foreach ($this->pollAnswers as $answer) {
                    $pollAnswer = new PollAnswer();
                    $pollAnswer->poll_id = $poll->id;
                    $pollAnswer->answer = $answer;
                    if (!$pollAnswer->save()) {
                        throw new Exception('Poll Answer saving error!');
                    }
                }
                Log::info('Poll added', $poll->id, __METHOD__);
            }

            $this->forum->updateCounters(['threads' => 1]);

            $post = new Post();
            $post->content = $this->post;
            $post->thread_id = $this->id;
            $post->forum_id = $this->forum_id;
            $post->author_id = $loggedIn;
            $post->likes = 0;
            $post->dislikes = 0;
            if (!$post->save()) {
                throw new Exception('Post saving error!');
            }

            $post->markSeen();
            $this->forum->updateCounters(['posts' => 1]);
            $this->updateCounters(['posts' => 1]);

            $this->touch('new_post_at');
            $this->touch('edited_post_at');

            if ($this->subscribe) {
                $subscription = new Subscription();
                $subscription->user_id = $loggedIn;
                $subscription->thread_id = $this->id;
                $subscription->post_seen = Subscription::POST_SEEN;
                if (!$subscription->save()) {
                    throw new Exception('Subscription saving error!');
                }
            }

            $transaction->commit();
            PodiumCache::clearAfter('newThread');
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
            $threadsPrevMarked = Thread::find()->joinWith(['threadView' => function ($q) use ($loggedId) {
                $q->onCondition(['user_id' => $loggedId]);
                $q->andWhere(['or',
                        new Expression('new_last_seen < new_post_at'),
                        new Expression('edited_last_seen < edited_post_at'),
                    ]);
            }], false);
            $time = time();
            foreach ($threadsPrevMarked->each() as $thread) {
                $updateBatch[] = $thread->id;
            }
            if (!empty($updateBatch)) {
                Podium::getInstance()->db->createCommand()->update(ThreadView::tableName(), [
                    'new_last_seen' => $time,
                    'edited_last_seen' => $time
                ], ['thread_id' => $updateBatch, 'user_id' => $loggedId])->execute();
            }

            $insertBatch = [];
            $threadsNew = Thread::find()->joinWith(['threadView' => function ($q) use ($loggedId) {
                $q->onCondition(['user_id' => $loggedId]);
                $q->andWhere(['new_last_seen' => null]);
            }], false);
            foreach ($threadsNew->each() as $thread) {
                $insertBatch[] = [$loggedId, $thread->id, $time, $time];
            }
            if (!empty($insertBatch)) {
                Podium::getInstance()->db->createCommand()->batchInsert(ThreadView::tableName(), [
                    'user_id', 'thread_id', 'new_last_seen', 'edited_last_seen'
                ], $insertBatch)->execute();
            }
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Returns post Markdown-parsed if WYSIWYG editor is switched off.
     * @return string
     * @since 0.6
     */
    public function getParsedPost()
    {
        if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
            $parser = new GithubMarkdown();
            $parser->html5 = true;
            return $parser->parse($this->post);
        }
        return $this->post;
    }
}
