<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Post;
use bizley\podium\models\PostThumb;
use bizley\podium\models\Thread;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;

class DefaultController extends Controller
{

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'         => false,
                        'matchCallback' => function () {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function () {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
    }

    public function actionIndex()
    {
        $dataProvider = (new Category())->search();

        return $this->render('index', [
                    'dataProvider' => $dataProvider
        ]);
    }

    public function actionCategory($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the category you are looking for.');
            return $this->redirect(['index']);
        }

        $conditions = ['id' => (int) $id, 'slug' => $slug];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $model = Category::findOne($conditions);

        if (!$model) {
            $this->error('Sorry! We can not find the category you are looking for.');
            return $this->redirect(['index']);
        }

        return $this->render('category', [
                    'model' => $model
        ]);
    }

    public function actionForum($cid = null, $id = null, $slug = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the forum you are looking for.');
            return $this->redirect(['index']);
        }

        $conditions = ['id' => (int) $cid];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $category = Category::findOne($conditions);

        if (!$category) {
            $this->error('Sorry! We can not find the forum you are looking for.');
            return $this->redirect(['index']);
        }
        else {
            $conditions = ['id' => (int) $id];
            if (Yii::$app->user->isGuest) {
                $conditions['visible'] = 1;
            }
            $model = Forum::findOne($conditions);
        }

        return $this->render('forum', [
                    'model'    => $model,
                    'category' => $category,
        ]);
    }

    public function actionNewThread($cid = null, $fid = null)
    {
        if (!Yii::$app->user->can('createThread')) {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to create a new thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
        else {
            if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1) {
                $this->error('Sorry! We can not find the forum you are looking for.');
                return $this->redirect(['index']);
            }

            $category = Category::findOne((int) $cid);

            if (!$category) {
                $this->error('Sorry! We can not find the forum you are looking for.');
                return $this->redirect(['index']);
            }
            else {
                $forum = Forum::findOne(['id' => (int) $fid, 'category_id' => $category->id]);
                if (!$forum) {
                    $this->error('Sorry! We can not find the forum you are looking for.');
                    return $this->redirect(['index']);
                }
                else {
                    $model = new Thread;
                    $model->setScenario('new');

                    $postData = Yii::$app->request->post();
                    
                    $preview = '';
                    
                    if ($model->load($postData)) {

                        $model->posts       = 0;
                        $model->views       = 0;
                        $model->category_id = $category->id;
                        $model->forum_id    = $forum->id;
                        $model->author_id   = Yii::$app->user->id;

                        if ($model->validate()) {
                            
                            if (isset($postData['preview-button'])) {
                                $preview = $model->post;
                            }
                            else {

                                $transaction = Thread::getDb()->beginTransaction();
                                try {
                                    if ($model->save()) {

                                        $forum->updateCounters(['threads' => 1]);

                                        $post            = new Post;
                                        $post->content   = $model->post;
                                        $post->thread_id = $model->id;
                                        $post->forum_id  = $model->forum_id;
                                        $post->author_id = Yii::$app->user->id;
                                        $post->likes     = 0;
                                        $post->dislikes  = 0;
                                        if ($post->save()) {
                                            $post->markSeen();
                                            $forum->updateCounters(['posts' => 1]);
                                            $model->updateCounters(['posts' => 1]);
                                            $model->touch('new_post_at');
                                            $model->touch('edited_post_at');
                                        }
                                    }

                                    $transaction->commit();
                                    
                                    Cache::getInstance()->delete('forum.threadscount');
                                    Cache::getInstance()->delete('forum.postscount');
                                    $this->success('New thread has been created.');

                                    return $this->redirect(['thread', 'cid'  => $category->id,
                                                'fid'  => $forum->id, 'id'   => $model->id,
                                                'slug' => $model->slug]);
                                }
                                catch (Exception $e) {
                                    $transaction->rollBack();
                                    Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                    $this->error('Sorry! There was an error while creating the thread. Contact administrator about this problem.');
                                }
                            }
                        }
                    }
                }
            }

            return $this->render('new-thread', [
                        'preview'  => $preview,
                        'model'    => $model,
                        'category' => $category,
                        'forum'    => $forum,
            ]);
        }
    }

    public function actionThread($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        $conditions = ['id' => (int) $cid];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $category = Category::findOne($conditions);

        if (!$category) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }
        else {
            $conditions = ['id' => (int) $fid, 'category_id' => $category->id];
            if (Yii::$app->user->isGuest) {
                $conditions['visible'] = 1;
            }
            $forum = Forum::findOne($conditions);
            if (!$forum) {
                $this->error('Sorry! We can not find the thread you are looking for.');
                return $this->redirect(['index']);
            }
            else {
                $thread = Thread::findOne(['id' => (int) $id, 'category_id' => $category->id,
                            'forum_id' => $forum->id]);
                if (!$thread) {
                    $this->error('Sorry! We can not find the thread you are looking for.');
                    return $this->redirect(['index']);
                }
                else {
                    $dataProvider = (new Post)->search($forum->id, $thread->id);
                    $model = new Post;

                    return $this->render('thread', [
                                'model'        => $model,
                                'dataProvider' => $dataProvider,
                                'category'     => $category,
                                'forum'        => $forum,
                                'thread'       => $thread,
                    ]);
                }
            }
        }
    }

    public function actionPost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (!Yii::$app->user->can('createPost')) {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to post a reply.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
        else {
            if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($tid) || $tid < 1) {
                $this->error('Sorry! We can not find the thread you are looking for.');
                return $this->redirect(['index']);
            }

            $category = Category::findOne(['id' => (int) $cid]);

            if (!$category) {
                $this->error('Sorry! We can not find the thread you are looking for.');
                return $this->redirect(['index']);
            }
            else {
                $forum = Forum::findOne(['id' => (int) $fid, 'category_id' => $category->id]);

                if (!$forum) {
                    $this->error('Sorry! We can not find the thread you are looking for.');
                    return $this->redirect(['index']);
                }
                else {
                    $thread = Thread::findOne(['id' => (int) $tid, 'category_id' => $category->id,
                                'forum_id' => $forum->id]);

                    if (!$thread) {
                        $this->error('Sorry! We can not find the thread you are looking for.');
                        return $this->redirect(['index']);
                    }
                    else {

                        $model = new Post;

                        $postData = Yii::$app->request->post();

                        $replyFor = null;
                        if (is_numeric($pid) && $pid > 0) {
                            $replyFor = Post::findOne((int)$pid);
                            if ($replyFor) {

                                if (isset($postData['quote']) && !empty($postData['quote'])) {
                                    $model->content = Helper::prepareQuote($replyFor, $postData['quote']);
                                }
                                else {
                                    $model->content = Helper::prepareQuote($replyFor);
                                }                            
                            }
                        }

                        $preview = '';
                        $previous = Post::find()->where(['thread_id' => $thread->id])->orderBy(['id' => SORT_ASC])->one();

                        if ($model->load($postData)) {

                            $model->thread_id = $thread->id;
                            $model->forum_id  = $forum->id;
                            $model->author_id = Yii::$app->user->id;

                            if ($model->validate()) {

                                if (isset($postData['preview-button'])) {
                                    $preview = $model->content;
                                }
                                else {

                                    $transaction = Post::getDb()->beginTransaction();
                                    try {
                                        
                                        if ($previous->author_id == Yii::$app->user->id) {
                                            $previous->content .= '<hr>' . $model->content;
                                            $previous->edited = 1;
                                            $previous->edited_at = time();
                                            
                                            if ($previous->save()) {
                                                $previous->markSeen();
                                                $thread->touch('edited_post_at');
                                                $id = $previous->id;
                                            }
                                        }
                                        else {
                                            if ($model->save()) {
                                                $model->markSeen();
                                                $forum->updateCounters(['posts' => 1]);
                                                $thread->updateCounters(['posts' => 1]);
                                                $thread->touch('new_post_at');
                                                $thread->touch('edited_post_at');
                                                $id = $model->id;
                                            }
                                        }

                                        $transaction->commit();

                                        Cache::getInstance()->delete('forum.postscount');
                                        $this->success('New reply has been added.');

                                        return $this->redirect(['show', 'id' => $id]);
                                    }
                                    catch (Exception $e) {
                                        $transaction->rollBack();
                                        Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                        $this->error('Sorry! There was an error while adding the reply. Contact administrator about this problem.');
                                    }
                                }
                            }
                        }

                        return $this->render('post', [
                                    'replyFor' => $replyFor,
                                    'preview'  => $preview,
                                    'model'    => $model,
                                    'category' => $category,
                                    'forum'    => $forum,
                                    'thread'   => $thread,
                                    'previous' => $previous,
                        ]);
                    }
                }
            }
        }
    }

    public function actionShow($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error('Sorry! We can not find the post you are looking for.');
            return $this->redirect(['index']);
        }
        
        $post = Post::findOne((int)$id);
        if (!$post) {
            $this->error('Sorry! We can not find the post you are looking for.');
            return $this->redirect(['index']);
        }
        
        if ($post->thread) {
            
            $url = [
                'thread', 
                'cid'  => $post->thread->category_id,
                'fid'  => $post->forum_id, 
                'id'   => $post->thread_id, 
                'slug' => $post->thread->slug
            ];
            
            try {
                $count = (new Query)->from('{{%podium_post}}')->where(['and', ['thread_id' => $post->thread_id], ['<', 'id', $post->id]])->orderBy(['id' => SORT_ASC])->count();
                $page = floor($count / 10) + 1;
                
                if ($page > 1) {
                    $url['page'] = $page;
                }
                $url['#'] = 'post' . $post->id;

                return $this->redirect($url);
            }
            catch (Exception $e) {
                $this->error('Sorry! We can not find the post you are looking for.');
                return $this->redirect(['index']);
            }
        }
        else {
            $this->error('Sorry! We can not find the post you are looking for.');
            return $this->redirect(['index']);
        }        
    }
    
    public function actionEdit($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($tid) || $tid < 1) {
            $this->error('Sorry! We can not find the post you are looking for.');
            return $this->redirect(['index']);
        }

        $category = Category::findOne(['id' => (int) $cid]);

        if (!$category) {
            $this->error('Sorry! We can not find the post you are looking for.');
            return $this->redirect(['index']);
        }
        else {
            $forum = Forum::findOne(['id' => (int) $fid, 'category_id' => $category->id]);

            if (!$forum) {
                $this->error('Sorry! We can not find the post you are looking for.');
                return $this->redirect(['index']);
            }
            else {
                $thread = Thread::findOne(['id' => (int) $tid, 'category_id' => $category->id, 'forum_id' => $forum->id]);

                if (!$thread) {
                    $this->error('Sorry! We can not find the post you are looking for.');
                    return $this->redirect(['index']);
                }
                else {
                    $model = Post::findOne(['id' => (int)$pid, 'forum_id' => $forum->id, 'thread_id' => $thread->id, 'author_id' => Yii::$app->user->id]);
                    
                    if (!$model) {
                        $this->error('Sorry! We can not find the post you are looking for.');
                        return $this->redirect(['index']);
                    }
                    else {
                        if (Yii::$app->user->can('updateOwnPost', ['post' => $model]) || Yii::$app->user->can('updatePost', ['item' => $model])) {
                            $postData = Yii::$app->request->post();

                            $preview = '';

                            if ($model->load($postData)) {

                                if ($model->validate()) {

                                    if (isset($postData['preview-button'])) {
                                        $preview = $model->content;
                                    }
                                    else {

                                        $transaction = Post::getDb()->beginTransaction();
                                        try {

                                            $model->edited = 1;
                                            $model->edited_at = time();

                                            if ($model->save()) {
                                                $model->markSeen();
                                                $thread->touch('edited_post_at');
                                            }

                                            $transaction->commit();

                                            $this->success('Post has been updated.');

                                            return $this->redirect(['show', 'id' => $model->id]);
                                        }
                                        catch (Exception $e) {
                                            $transaction->rollBack();
                                            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                            $this->error('Sorry! There was an error while adding the reply. Contact administrator about this problem.');
                                        }
                                    }
                                }
                            }

                            return $this->render('edit', [
                                        'preview'  => $preview,
                                        'model'    => $model,
                                        'category' => $category,
                                        'forum'    => $forum,
                                        'thread'   => $thread,
                            ]);
                        }
                        else {
                            if (Yii::$app->user->isGuest) {
                                $this->warning('Please sign in to edit the post.');
                                return $this->redirect(['account/login']);
                            }
                            else {
                                $this->error('Sorry! You do not have the required permission to perform this action.');
                                return $this->redirect(['default/index']);
                            }
                        }
                    }
                }
            }
        }
    }
    
    public function actionThumb()
    {
        if (Yii::$app->request->isAjax) {
            
            $data = [
                'error' => 1,
                'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Error while voting on this post!'), ['class' => 'text-danger']),
            ];
            
            if (!Yii::$app->user->isGuest) {
                $postId = Yii::$app->request->post('post');
                $thumb  = Yii::$app->request->post('thumb');
                
                if (is_numeric($postId) && $postId > 0 && in_array($thumb, ['up', 'down'])) {
                    
                    $post = Post::findOne((int)$postId);
                    if ($post) {
                        
                        if ($post->author_id == Yii::$app->user->id) {
                            return Json::encode([
                                'error' => 1,
                                'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'You can not vote on your own post!'), ['class' => 'text-danger']),
                            ]);
                        }
                        
                        $count = 0;
                        $votes = Cache::getInstance()->get('user.votes.' . Yii::$app->user->id);
                        if ($votes !== false) {
                            if ($votes['expire'] < time()) {
                                $votes = false;
                            }
                            elseif ($votes['count'] >= 10) {
                                return Json::encode([
                                    'error' => 1,
                                    'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', '10 votes per hour limit reached!'), ['class' => 'text-danger']),
                                ]);
                            }
                            else {
                                $count = $votes['count'];
                            }
                        }
                        
                        if ($post->thumb) {
                            if ($post->thumb->thumb == 1 && $thumb == 'down') {
                                $post->thumb->thumb = -1;
                                if ($post->thumb->save()) {
                                    $post->updateCounters(['likes' => -1, 'dislikes' => 1]);
                                }
                            }
                            elseif ($post->thumb->thumb == -1 && $thumb == 'up') {
                                $post->thumb->thumb = 1;
                                if ($post->thumb->save()) {
                                    $post->updateCounters(['likes' => 1, 'dislikes' => -1]);
                                }
                            }
                        }
                        else {
                            $postThumb          = new PostThumb;
                            $postThumb->post_id = $post->id;
                            $postThumb->user_id = Yii::$app->user->id;
                            $postThumb->thumb   = $thumb == 'up' ? 1 : -1;
                            if ($postThumb->save()) {
                                if ($thumb == 'up') {
                                    $post->updateCounters(['likes' => 1]);
                                }
                                else {
                                    $post->updateCounters(['dislikes' => 1]);
                                }
                            }
                        }
                        $data = [
                            'error'    => 0,
                            'likes'    => '+' . $post->likes,
                            'dislikes' => '-' . $post->dislikes,
                            'summ'     => $post->likes - $post->dislikes,
                            'msg'      => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle']) . ' ' . Yii::t('podium/view', 'Your vote has been saved!'), ['class' => 'text-success']),
                        ];
                        if ($count == 0) {
                            Cache::getInstance()->set('user.votes.' . Yii::$app->user->id, ['count' => 1, 'expire' => time() + 3600]);
                        }
                        else {
                            Cache::getInstance()->setElement('user.votes.' . Yii::$app->user->id, 'count', $count + 1);
                        }
                    }
                }
            }
            
            return Json::encode($data);
        }
        else {
            return $this->redirect(['index']);
        }
    }
}        