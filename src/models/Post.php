<?php

namespace bizley\podium\models;

use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\models\db\PostActiveRecord;
use bizley\podium\Podium;
use bizley\podium\PodiumCache;
use cebe\markdown\GithubMarkdown;
use Exception;
use yii\data\ActiveDataProvider;
use yii\helpers\HtmlPurifier;

/**
 * Post model
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 *
 * @property string $parsedContent
 */
class Post extends PostActiveRecord
{
    /**
     * Returns latest posts for registered users.
     * @param int $limit Number of latest posts.
     * @return Post[]
     */
    public static function getLatestPostsForMembers($limit = 5)
    {
        return static::find()->orderBy(['created_at' => SORT_DESC])->limit($limit)->all();
    }

    /**
     * Returns latest visible posts for guests.
     * @param int $limit Number of latest posts.
     * @return Post[]
     */
    public static function getLatestPostsForGuests($limit = 5)
    {
        return static::find()->joinWith(['forum' => function ($query) {
            $query->andWhere([Forum::tableName() . '.visible' => 1])->joinWith(['category' => function ($query) {
                $query->andWhere([Category::tableName() . '.visible' => 1]);
            }]);
        }])->orderBy(['created_at' => SORT_DESC])->limit($limit)->all();
    }

    /**
     * Updates post tag words.
     * @param bool $insert
     * @param array $changedAttributes
     * @throws Exception
     */
    public function afterSave($insert, $changedAttributes)
    {
        try {
            if ($insert) {
                $this->insertWords();
            } else {
                $this->updateWords();
            }
        } catch (Exception $e) {
            throw $e;
        }
        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Prepares tag words.
     * @return string[]
     */
    protected function prepareWords()
    {
        $cleanHtml = HtmlPurifier::process(trim($this->content));
        $purged = preg_replace('/<[^>]+>/', ' ', $cleanHtml);
        $wordsRaw = array_unique(preg_split('/[\s,\.\n]+/', $purged));
        $allWords = [];
        foreach ($wordsRaw as $word) {
            if (mb_strlen($word, 'UTF-8') > 2 && mb_strlen($word, 'UTF-8') <= 255) {
                $allWords[] = $word;
            }
        }
        return $allWords;
    }

    /**
     * Adds new tag words.
     * @param string[] $allWords All words extracted from post
     * @throws Exception
     */
    protected function addNewWords($allWords)
    {
        try {
            $newWords = $allWords;
            $query = (new Query())->from(Vocabulary::tableName())->where(['word' => $allWords]);
            foreach ($query->each() as $vocabularyFound) {
                if (($key = array_search($vocabularyFound['word'], $allWords)) !== false) {
                    unset($newWords[$key]);
                }
            }
            $formatWords = [];
            foreach ($newWords as $word) {
                $formatWords[] = [$word];
            }
            if (!empty($formatWords)) {
                if (!Podium::getInstance()->db->createCommand()->batchInsert(
                        Vocabulary::tableName(), ['word'], $formatWords
                    )->execute()) {
                    throw new Exception('Words saving error!');
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
            throw $e;
        }
    }

    /**
     * Inserts tag words.
     * @throws Exception
     */
    protected function insertWords()
    {
        try {
            $vocabulary = [];
            $allWords = $this->prepareWords();
            $this->addNewWords($allWords);
            $query = (new Query())->from(Vocabulary::tableName())->where(['word' => $allWords]);
            foreach ($query->each() as $vocabularyNew) {
                $vocabulary[] = [$vocabularyNew['id'], $this->id];
            }
            if (!empty($vocabulary)) {
                if (!Podium::getInstance()->db->createCommand()->batchInsert(
                        '{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], $vocabulary
                    )->execute()) {
                    throw new Exception('Words connections saving error!');
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
            throw $e;
        }
    }

    /**
     * Updates tag words.
     * @throws Exception
     */
    protected function updateWords()
    {
        try {
            $vocabulary = [];
            $allWords = $this->prepareWords();
            $this->addNewWords($allWords);
            $queryVocabulary = (new Query())->from(Vocabulary::tableName())->where(['word' => $allWords]);
            foreach ($queryVocabulary->each() as $vocabularyNew) {
                $vocabulary[$vocabularyNew['id']] = [$vocabularyNew['id'], $this->id];
            }
            if (!empty($vocabulary)) {
                if (!Podium::getInstance()->db->createCommand()->batchInsert(
                        '{{%podium_vocabulary_junction}}', ['word_id', 'post_id'], array_values($vocabulary)
                    )->execute()) {
                    throw new Exception('Words connections saving error!');
                }
            }
            $queryJunction = (new Query())->from('{{%podium_vocabulary_junction}}')->where(['post_id' => $this->id]);
            foreach ($queryJunction->each() as $junk) {
                if (!array_key_exists($junk['word_id'], $vocabulary)) {
                    if (!Podium::getInstance()->db->createCommand()->delete(
                            '{{%podium_vocabulary_junction}}', ['id' => $junk['id']]
                        )->execute()) {
                        throw new Exception('Words connections deleting error!');
                    }
                }
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
            throw $e;
        }
    }

    /**
     * Verifies if user is this forum's moderator.
     * @param int|null $userId user ID or null for current signed in.
     * @return bool
     */
    public function isMod($userId = null)
    {
        return $this->forum->isMod($userId);
    }

    /**
     * Searches for posts.
     * @param int $forumId
     * @param int $threadId
     * @return ActiveDataProvider
     */
    public function search($forumId, $threadId)
    {
        $dataProvider = new ActiveDataProvider([
            'query' => static::find()->where(['forum_id' => $forumId, 'thread_id' => $threadId]),
            'pagination' => [
                'defaultPageSize' => 10,
                'pageSizeLimit' => false,
                'forcePageParam' => false
            ],
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];
        return $dataProvider;
    }

    /**
     * Searches for posts added by given user.
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
            'pagination' => [
                'defaultPageSize' => 10,
                'pageSizeLimit' => false,
                'forcePageParam' => false
            ],
        ]);
        $dataProvider->sort->defaultOrder = ['id' => SORT_ASC];

        return $dataProvider;
    }

    /**
     * Marks post as seen by current user.
     * @param bool $updateCounters Whether to update view counter
     */
    public function markSeen($updateCounters = true)
    {
        if (!Podium::getInstance()->user->isGuest) {
            $transaction = static::getDb()->beginTransaction();
            try {
                $loggedId = User::loggedId();
                $threadView = ThreadView::find()->where([
                        'user_id' => $loggedId,
                        'thread_id' => $this->thread_id
                    ])->limit(1)->one();

                if (empty($threadView)) {
                    $threadView = new ThreadView();
                    $threadView->user_id = $loggedId;
                    $threadView->thread_id = $this->thread_id;
                    $threadView->new_last_seen = $this->created_at;
                    $threadView->edited_last_seen = !empty($this->edited_at) ? $this->edited_at : $this->created_at;
                    if (!$threadView->save()) {
                        throw new Exception('Thread View saving error!');
                    }
                    if ($updateCounters) {
                        if (!$this->thread->updateCounters(['views' => 1])) {
                            throw new Exception('Thread views adding error!');
                        }
                    }
                } else {
                    if ($this->edited) {
                        if ($threadView->edited_last_seen < $this->edited_at) {
                            $threadView->edited_last_seen = $this->edited_at;
                            if (!$threadView->save()) {
                                throw new Exception('Thread View saving error!');
                            }
                            if ($updateCounters) {
                                if (!$this->thread->updateCounters(['views' => 1])) {
                                    throw new Exception('Thread views adding error!');
                                }
                            }
                        }
                    } else {
                        $save = false;
                        if ($threadView->new_last_seen < $this->created_at) {
                            $threadView->new_last_seen = $this->created_at;
                            $save = true;
                        }
                        if ($threadView->edited_last_seen < max($this->created_at, $this->edited_at)) {
                            $threadView->edited_last_seen = max($this->created_at, $this->edited_at);
                            $save = true;
                        }
                        if ($save) {
                            if (!$threadView->save()) {
                                throw new Exception('Thread View saving error!');
                            }
                            if ($updateCounters) {
                                if (!$this->thread->updateCounters(['views' => 1])) {
                                    throw new Exception('Thread views adding error!');
                                }
                            }
                        }
                    }
                }
                if ($this->thread->subscription) {
                    if ($this->thread->subscription->post_seen == Subscription::POST_NEW) {
                        $this->thread->subscription->post_seen = Subscription::POST_SEEN;
                        if (!$this->thread->subscription->save()) {
                            throw new Exception('Thread Subscription saving error!');
                        }
                    }
                }
                $transaction->commit();
            } catch (Exception $e) {
                $transaction->rollBack();
                Log::error($e->getMessage(), null, __METHOD__);
            }
        }
    }

    /**
     * Returns latest post.
     * @param int $limit
     * @return array
     */
    public static function getLatest($limit = 5)
    {
        $cacheKey = Podium::getInstance()->user->isGuest ? 'guest' : 'member';
        $method = Podium::getInstance()->user->isGuest ? 'getLatestPostsForGuests' : 'getLatestPostsForMembers';
        $latest = Podium::getInstance()->podiumCache->getElement('forum.latestposts', $cacheKey);
        if ($latest === false) {
            $posts = static::$method($limit);
            foreach ($posts as $post) {
                $latest[] = [
                    'id' => $post->id,
                    'title' => $post->thread->name,
                    'created' => $post->created_at,
                    'author' => $post->author->podiumTag
                ];
            }
            Podium::getInstance()->podiumCache->setElement('forum.latestposts', $cacheKey, $latest);
        }
        return $latest;
    }

    /**
     * Returns the verified post.
     * @param int $categoryId post category ID
     * @param int $forumId post forum ID
     * @param int $threadId post thread ID
     * @param int $id post ID
     * @return Post
     * @since 0.2
     */
    public static function verify($categoryId = null, $forumId = null, $threadId = null, $id = null)
    {
        if (!is_numeric($categoryId) || $categoryId < 1
                || !is_numeric($forumId) || $forumId < 1
                || !is_numeric($threadId) || $threadId < 1
                || !is_numeric($id) || $id < 1) {
            return null;
        }
        return static::find()->joinWith(['thread', 'forum' => function ($query) use ($categoryId) {
                $query->joinWith(['category'])->andWhere([Category::tableName() . '.id' => $categoryId]);
            }])->where([
                static::tableName() . '.id' => $id,
                static::tableName() . '.thread_id' => $threadId,
                static::tableName() . '.forum_id' => $forumId,
            ])->limit(1)->one();
    }

    /**
     * Performs post delete with parent forum and thread counters update.
     * @return bool
     * @since 0.2
     */
    public function podiumDelete()
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            if (!$this->delete()) {
                throw new Exception('Post deleting error!');
            }
            $wholeThread = false;
            if ($this->thread->postsCount) {
                if (!$this->thread->updateCounters(['posts' => -1])) {
                    throw new Exception('Thread Post counter subtracting error!');
                }
                if (!$this->forum->updateCounters(['posts' => -1])) {
                    throw new Exception('Forum Post counter subtracting error!');
                }
            } else {
                $wholeThread = true;
                if (!$this->thread->delete()) {
                    throw new Exception('Thread deleting error!');
                }
                if (!$this->forum->updateCounters(['posts' => -1, 'threads' => -1])) {
                    throw new Exception('Forum Post and Thread counter subtracting error!');
                }
            }
            $transaction->commit();
            PodiumCache::clearAfter('postDelete');
            Log::info('Post deleted', !empty($this->id) ? $this->id : '', __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Performs post update with parent thread topic update in case of first post in thread.
     * @param bool $isFirstPost whether post is first in thread
     * @return bool
     * @since 0.2
     */
    public function podiumEdit($isFirstPost = false)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $this->edited = 1;
            $this->touch('edited_at');
            if (!$this->save()) {
                throw new Exception('Post saving error!');
            }
            if ($isFirstPost) {
                $this->thread->name = $this->topic;
                if (!$this->thread->save()) {
                    throw new Exception('Thread saving error!');
                }
            }
            $this->markSeen();
            $this->thread->touch('edited_post_at');

            $transaction->commit();
            Log::info('Post updated', $this->id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Performs new post creation and subscription.
     * Depending on the settings previous post can be merged.
     * @param Post $previous previous post
     * @return bool
     * @since 0.2
     */
    public function podiumNew($previous = null)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $id = null;
            $loggedId = User::loggedId();
            $sameAuthor = !empty($previous->author_id) && $previous->author_id == $loggedId;
            if ($sameAuthor && Podium::getInstance()->podiumConfig->get('merge_posts')) {
                $separator = '<hr>';
                if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
                    $separator = "\n\n---\n";
                }
                $previous->content .= $separator . $this->content;
                $previous->edited = 1;
                $previous->touch('edited_at');
                if (!$previous->save()) {
                    throw new Exception('Previous Post saving error!');
                }
                $previous->markSeen(false);
                $previous->thread->touch('edited_post_at');
                $id = $previous->id;
                $thread = $previous->thread;
            } else {
                if (!$this->save()) {
                    throw new Exception('Post saving error!');
                }
                $this->markSeen(!$sameAuthor);
                if (!$this->forum->updateCounters(['posts' => 1])) {
                    throw new Exception('Forum Post counter adding error!');
                }
                if (!$this->thread->updateCounters(['posts' => 1])) {
                    throw new Exception('Thread Post counter adding error!');
                }
                $this->thread->touch('new_post_at');
                $this->thread->touch('edited_post_at');
                $id = $this->id;
                $thread = $this->thread;
            }
            if (empty($id)) {
                throw new Exception('Saved Post ID missing');
            }
            Subscription::notify($thread->id);
            if ($this->subscribe && !$thread->subscription) {
                $subscription = new Subscription();
                $subscription->user_id = $loggedId;
                $subscription->thread_id = $thread->id;
                $subscription->post_seen = Subscription::POST_SEEN;
                if (!$subscription->save()) {
                    throw new Exception('Subscription saving error!');
                }
            }
            $transaction->commit();
            PodiumCache::clearAfter('newPost');
            Log::info('Post added', $id, __METHOD__);
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Performs vote processing.
     * @param bool $up whether this is upvote
     * @param int $count number of user cached votes
     * @return bool
     * @since 0.2
     */
    public function podiumThumb($up = true, $count = 0)
    {
        $transaction = static::getDb()->beginTransaction();
        try {
            $loggedId = User::loggedId();
            if ($this->thumb) {
                if ($this->thumb->thumb == 1 && !$up) {
                    $this->thumb->thumb = -1;
                    if (!$this->thumb->save()) {
                        throw new Exception('Thumb saving error!');
                    }
                    if (!$this->updateCounters(['likes' => -1, 'dislikes' => 1])) {
                        throw new Exception('Thumb counters saving error!');
                    }
                } elseif ($this->thumb->thumb == -1 && $up) {
                    $this->thumb->thumb = 1;
                    if (!$this->thumb->save()) {
                        throw new Exception('Thumb saving error!');
                    }
                    if (!$this->updateCounters(['likes' => 1, 'dislikes' => -1])) {
                        throw new Exception('Thumb counters saving error!');
                    }
                }
            } else {
                $postThumb = new PostThumb();
                $postThumb->post_id = $this->id;
                $postThumb->user_id = $loggedId;
                $postThumb->thumb = $up ? 1 : -1;
                if (!$postThumb->save()) {
                    throw new Exception('PostThumb saving error!');
                }
                if ($postThumb->thumb) {
                    if (!$this->updateCounters(['likes' => 1])) {
                        throw new Exception('Thumb counters saving error!');
                    }
                } else {
                    if (!$this->updateCounters(['dislikes' => 1])) {
                        throw new Exception('Thumb counters saving error!');
                    }
                }
            }
            if ($count == 0) {
                Podium::getInstance()->podiumCache->set('user.votes.' . $loggedId, ['count' => 1, 'expire' => time() + 3600]);
            } else {
                Podium::getInstance()->podiumCache->setElement('user.votes.' . $loggedId, 'count', $count + 1);
            }
            $transaction->commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollBack();
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Returns content Markdown-parsed if WYSIWYG editor is switched off.
     * @return string
     * @since 0.6
     */
    public function getParsedContent()
    {
        if (Podium::getInstance()->podiumConfig->get('use_wysiwyg') == '0') {
            $parser = new GithubMarkdown();
            $parser->html5 = true;
            return $parser->parse($this->content);
        }
        return $this->content;
    }
}
