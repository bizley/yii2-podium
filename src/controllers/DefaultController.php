<?php

namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Post;
use bizley\podium\models\Thread;
use Exception;
use Yii;
use yii\filters\AccessControl;
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
            $this->error('Sorry! You do not have the required permission to perform this action.');
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

                    if ($model->load(Yii::$app->request->post())) {

                        $model->category_id = $category->id;
                        $model->forum_id    = $forum->id;
                        $model->author_id   = Yii::$app->user->id;

                        if ($model->validate()) {

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

                                        $forum->updateCounters(['posts' => 1]);
                                        $this->updateCounters(['posts' => 1, 'views' => 1]);
                                        $this->touch('new_post_at');
                                        $this->touch('edited_post_at');
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

            return $this->render('new-thread', [
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
                                    if ($model->save()) {

                                        $forum->updateCounters(['posts' => 1]);
                                        $thread->updateCounters(['posts' => 1, 'views' => 1]);
                                        $thread->touch('new_post_at');
                                        $thread->touch('edited_post_at');
                                    }

                                    $transaction->commit();

                                    Cache::getInstance()->delete('forum.postscount');
                                    $this->success('New reply has been added.');

                                    return $this->redirect(['thread', 'cid'  => $category->id,
                                                'fid'  => $forum->id, 'id'   => $thread->id,
                                                'slug' => $thread->slug]);
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
                                'previous' => Post::find()->where(['thread_id' => $thread->id])->orderBy(['id' => SORT_ASC])->one(),
                    ]);
                }
            }
        }
    }

}        