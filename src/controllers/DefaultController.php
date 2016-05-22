<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\Helper;
use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Message;
use bizley\podium\models\Post;
use bizley\podium\models\SearchForm;
use bizley\podium\models\Thread;
use bizley\podium\models\User;
use bizley\podium\models\Vocabulary;
use bizley\podium\rbac\Rbac;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\StringHelper;
use yii\helpers\Url;
use Zelenin\yii\extensions\Rss\RssView;

/**
 * Podium Default controller
 * All actions concerning viewing and moderating forums and posts.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class DefaultController extends BaseController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow'         => false,
                        'matchCallback' => function ($rule, $action) {
                            return !$this->module->getInstalled();
                        },
                        'denyCallback' => function ($rule, $action) {
                            return $this->redirect(['install/run']);
                        }
                    ],
                    [
                        'allow' => true,
                    ],
                ],
            ],
        ];
    }

    /**
     * Showing ban info.
     */
    public function actionBan()
    {
        $this->layout = 'maintenance';
        return $this->render('ban');
    }
    
    /**
     * Displaying the category of given ID and slug.
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionCategory($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the category you are looking for.'));
            return $this->redirect(['default/index']);
        }

        $conditions = ['id' => (int)$id, 'slug' => $slug];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $model = Category::find()->where($conditions)->limit(1)->one();

        if (!$model) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the category you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        $this->setMetaTags($model->keywords, $model->description);

        return $this->render('category', ['model' => $model]);
    }
    
    /**
     * Deleting the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return string|\yii\web\Response
     */
    public function actionDelete($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to delete the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_DELETE_THREAD, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        $postData = Yii::$app->request->post('thread');
        if ($postData) {
            if ($postData['thread'] != $thread->id) {
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the thread.'));
            }
            else {
                if ($thread->podiumDelete()) {
                    $this->success(Yii::t('podium/flash', 'Thread has been deleted.'));
                    return $this->redirect(['forum', 'cid' => $thread->forum->category_id, 'id' => $thread->forum->id, 'slug' => $thread->forum->slug]);
                }
                else {
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the thread.'));
                }
            }
        }

        return $this->render('delete', ['model' => $thread]);
    }
    
    /**
     * Deleting the post of given category ID, forum ID, thread ID and ID.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $tid thread's ID
     * @param integer $pid post's ID
     * @return string|\yii\web\Response
     */
    public function actionDeletepost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to delete the post.'));
            return $this->redirect(['account/login']);
        }

        $post = Post::verify($cid, $fid, $tid, $pid);

        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if ($post->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $post->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect(['thread', 'cid' => $post->forum->category->id, 'fid' => $post->forum->id, 'thread' => $post->thread->id, 'slug' => $post->thread->slug]);
        }

        if (!User::can(Rbac::PERM_DELETE_OWN_POST, ['post' => $post]) && !User::can(Rbac::PERM_DELETE_POST, ['item' => $post])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }

        $postData = Yii::$app->request->post('post');
        if ($postData) {
            if ($postData != $post->id) {
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the post.'));
            }
            else {
                if ($post->podiumDelete()) {
                    $this->success(Yii::t('podium/flash', 'Post has been deleted.'));
                    if (Thread::find()->where(['id' => $post->thread->id])->exists()) {
                        return $this->redirect(['forum', 'cid' => $post->forum->category->id, 'id' => $post->forum->id, 'slug' => $post->forum->slug]);
                    }
                    return $this->redirect(['thread', 'cid' => $post->forum->category->id, 'fid' => $post->forum->id, 'id' => $post->thread->id, 'slug' => $post->thread->slug]);
                }
                else {
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the post.'));
                }
            }
        }

        return $this->render('deletepost', ['model' => $post]);
    }
    
    /**
     * Deleting the posts of given category ID, forum ID, thread ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return string|\yii\web\Response
     */
    public function actionDeleteposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);

        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_DELETE_POST, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }

        $posts = Yii::$app->request->post('post');
        if ($posts) {
            if (!is_array($posts)) {
                $this->error(Yii::t('podium/flash', 'You have to select at least one post.'));
            }
            else {
                if ($thread->podiumDeletePosts($posts)) {
                    $this->success(Yii::t('podium/flash', 'Posts have been deleted.'));
                    if (Thread::find()->where(['id' => $thread->id])->exists()) {
                        return $this->redirect(['thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
                    }
                    return $this->redirect(['forum', 'cid' => $thread->forum->category->id, 'id' => $thread->forum->id, 'slug' => $thread->forum->slug]);
                }
                else {
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the posts.'));
                }
            }
        }

        return $this->render('deleteposts', [
                'model'        => $thread,
                'dataProvider' => (new Post)->search($thread->forum->id, $thread->id)
            ]);
    }
    
    /**
     * Editing the post of given category ID, forum ID, thread ID and own ID.
     * If this is the first post in thread user can change the thread's name.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $tid thread's ID
     * @param integer $pid post's ID
     * @return string|\yii\web\Response
     */
    public function actionEdit($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to edit the post.'));
            return $this->redirect(['account/login']);
        }
        
        $post = Post::verify($cid, $fid, $tid, $pid);
        
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        if ($post->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $post->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect(['thread', 'cid' => $post->forum->category->id, 'fid' => $post->forum->id, 'id' => $post->thread->id, 'slug' => $post->thread->slug]);
        }
        
        if (!User::can(Rbac::PERM_UPDATE_OWN_POST, ['post' => $post]) && !User::can(Rbac::PERM_UPDATE_POST, ['item' => $post])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        $isFirstPost = false;
        $firstPost   = Post::find()->where(['thread_id' => $post->thread->id, 'forum_id' => $post->forum->id])->orderBy(['id' => SORT_ASC])->limit(1)->one();
        if ($firstPost->id == $post->id) {
            $post->scenario = 'firstPost';
            $post->topic    = $post->thread->name;
            $isFirstPost    = true;
        }   
        
        $postData = Yii::$app->request->post();
        $preview  = '';

        if ($post->load($postData)) {
            if ($post->validate()) {
                if (isset($postData['preview-button'])) {
                    $preview = $post->content;
                }
                else {
                    if ($post->podiumEdit($isFirstPost)) {
                        $this->success(Yii::t('podium/flash', 'Post has been updated.'));
                        return $this->redirect(['show', 'id' => $post->id]);
                    }
                    else {
                        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the post. Contact administrator about this problem.'));
                    }
                }
            }
        }
        
        return $this->render('edit', [
                'preview'     => $preview,
                'model'       => $post,
                'isFirstPost' => $isFirstPost
            ]);
    }

    /**
     * Displaying the forum of given category ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $id forum's ID
     * @param string $slug forum's slug
     * @return string|\yii\web\Response
     */
    public function actionForum($cid = null, $id = null, $slug = null, $toggle = null)
    {
        $filters = Yii::$app->session->get('forum-filters');
        if (in_array(strtolower($toggle), ['new', 'edit', 'hot', 'pin', 'lock', 'all'])) {
            if (strtolower($toggle) == 'all') {
                $filters = null;
            }
            else {
                $filters[strtolower($toggle)] = empty($filters[strtolower($toggle)]) || $filters[strtolower($toggle)] == 0 ? 1 : 0;
            }
            Yii::$app->session->set('forum-filters', $filters);
            return $this->redirect(['default/forum', 'cid' => $cid, 'id' => $id, 'slug' => $slug]);
        }
        
        $forum = Forum::verify($cid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($forum)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the forum you are looking for.'));
            return $this->redirect(['default/index']);
        }

        $this->setMetaTags($forum->keywords ?: $forum->category->keywords, $forum->description ?: $forum->category->description);
        
        return $this->render('forum', [
                'model'    => $forum,
                'filters'  => $filters
            ]);
    }
    
    /**
     * Displaying the list of categories.
     * @return string
     */
    public function actionIndex()
    {
        $this->setMetaTags();
        return $this->render('index', ['dataProvider' => (new Category)->search()]);
    }
    
    /**
     * Direct link for the last post in thread of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionLast($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        $thread = Thread::find()->where(['id' => $id])->limit(1)->one();
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        $url = [
            'default/thread', 
            'cid'  => $thread->category_id,
            'fid'  => $thread->forum_id, 
            'id'   => $thread->id, 
            'slug' => $thread->slug
        ];

        $count = $thread->postsCount;
        $page  = floor($count / 10) + 1;
        if ($page > 1) {
            $url['page'] = $page;
        }
        return $this->redirect($url);
    }
    
    /**
     * Locking / unlocking the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return \yii\web\Response
     */
    public function actionLock($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_LOCK_THREAD, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
            
        if ($thread->podiumLock()) {
            $this->success($thread->locked ? Yii::t('podium/flash', 'Thread has been locked.') : Yii::t('podium/flash', 'Thread has been unlocked.'));
        }
        else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the thread.'));
        }
        
        return $this->redirect(['default/thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
    }
    
    /**
     * Showing maintenance info.
     */
    public function actionMaintenance()
    {
        $this->layout = 'maintenance';
        return $this->render('maintenance');
    }
    
    /**
     * Moving the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return string|\yii\web\Response
     */
    public function actionMove($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
            
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_MOVE_THREAD, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        $forum = Yii::$app->request->post('forum');
        if ($forum) {
            if (!is_numeric($forum) || $forum < 1 || $forum == $thread->forum->id) {
                $this->error(Yii::t('podium/flash', 'You have to select the new forum.'));
            }
            else {
                if ($thread->podiumMoveTo($forum)) {
                    $this->success(Yii::t('podium/flash', 'Thread has been moved.'));
                    return $this->redirect(['thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
                }
                else {
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while moving the thread.'));
                }
            }
        }
        
        $categories = Category::find()->orderBy(['name' => SORT_ASC]);
        $forums     = Forum::find()->orderBy(['name' => SORT_ASC]);

        $list    = [];
        $options = [];
        foreach ($categories->each() as $cat) {
            $catlist = [];
            foreach ($forums->each() as $for) {
                if ($for->category_id == $cat->id) {
                    $catlist[$for->id] = (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $for]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name);
                    if ($for->id == $thread->forum->id) {
                        $options[$for->id] = ['disabled' => true];
                    }
                }
            }
            $list[Html::encode($cat->name)] = $catlist;
        }

        return $this->render('move', [
                'model'   => $thread,
                'list'    => $list,
                'options' => $options
            ]);
    }
    
    /**
     * Moving the posts of given category ID, forum ID, thread ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return string|\yii\web\Response
     */
    public function actionMoveposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_MOVE_POST, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        if (Yii::$app->request->post()) {
            $posts     = Yii::$app->request->post('post');
            $newthread = Yii::$app->request->post('newthread');
            $newname   = Yii::$app->request->post('newname');
            $newforum  = Yii::$app->request->post('newforum');

            if (empty($posts) || !is_array($posts)) {
                $this->error(Yii::t('podium/flash', 'You have to select at least one post.'));
            }
            else {
                if (!is_numeric($newthread) || $newthread < 0) {
                    $this->error(Yii::t('podium/flash', 'You have to select a thread for this posts to be moved to.'));
                }
                else {
                    if ($newthread == 0 && (empty($newname) || empty($newforum) || !is_numeric($newforum) || $newforum < 1)) {
                        $this->error(Yii::t('podium/flash', 'If you want to move posts to a new thread you have to enter its name and select parent forum.'));
                    }
                    else {
                        if ($newthread == $thread->id) {
                            $this->error(Yii::t('podium/flash', 'Are you trying to move posts from this thread to this very same thread?'));
                        }
                        else {
                            if ($thread->podiumMovePostsTo($newthread, $posts, $newname, $newforum)) {
                                $this->success(Yii::t('podium/flash', 'Posts have been moved.'));
                                if (Thread::find()->where(['id' => $thread->id])->exists()) {
                                    return $this->redirect(['default/thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
                                }
                                else {
                                    return $this->redirect(['default/forum', 'cid' => $thread->forum->category->id, 'id' => $thread->forum->id, 'slug' => $thread->forum->slug]);
                                }
                            }
                            else {
                                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while moving the posts.'));
                            }
                        }
                    }
                }
            }
        }

        $categories = Category::find()->orderBy(['name' => SORT_ASC]);
        $forums     = Forum::find()->orderBy(['name' => SORT_ASC]);
        $threads    = Thread::find()->orderBy(['name' => SORT_ASC]);

        $list      = [0 => Yii::t('podium/view', 'Create new thread')];
        $listforum = [];
        $options   = [];
        foreach ($categories->each() as $cat) {
            $catlist = [];
            foreach ($forums->each() as $for) {
                $forlist = [];
                if ($for->category_id == $cat->id) {
                    $catlist[$for->id] = (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $for]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name);
                    foreach ($threads->each() as $thr) {
                        if ($thr->category_id == $cat->id && $thr->forum_id == $for->id) {
                            $forlist[$thr->id] = (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thr]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name) . ' &raquo; ' . Html::encode($thr->name);
                            if ($thr->id == $thread->id) {
                                $options[$thr->id] = ['disabled' => true];
                            }
                        }
                    }
                    $list[Html::encode($cat->name) . ' > ' . Html::encode($for->name)] = $forlist;
                }
            }
            $listforum[Html::encode($cat->name)] = $catlist;
        }

        return $this->render('moveposts', [
            'model'        => $thread,
            'list'         => $list,
            'options'      => $options,
            'listforum'    => $listforum,
            'dataProvider' => (new Post)->search($thread->forum->id, $thread->id)
        ]);
    }

    /**
     * Creating the thread of given category ID and forum ID.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @return string|\yii\web\Response
     */
    public function actionNewThread($cid = null, $fid = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to create a new thread.'));
            return $this->redirect(['account/login']);
        }
        
        if (!User::can(Rbac::PERM_CREATE_THREAD)) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        $forum = Forum::find()->where(['id' => $fid, 'category_id' => $cid])->limit(1)->one();
        
        if (empty($forum)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the forum you are looking for.'));
            return $this->redirect(['default/index']);
        }

        $model = new Thread;
        $model->scenario  = 'new';
        $model->subscribe = 1;
        $preview = '';
        
        $postData = Yii::$app->request->post();
        if ($model->load($postData)) {
            $model->posts       = 0;
            $model->views       = 0;
            $model->category_id = $forum->category->id;
            $model->forum_id    = $forum->id;
            $model->author_id   = User::loggedId();
            if ($model->validate()) {
                if (isset($postData['preview-button'])) {
                    $preview = $model->post;
                }
                else {
                    if ($model->podiumNew()) {
                        $this->success(Yii::t('podium/flash', 'New thread has been created.'));
                        return $this->redirect(['thread', 
                                'cid'  => $forum->category->id,
                                'fid'  => $forum->id, 
                                'id'   => $model->id,
                                'slug' => $model->slug]);
                    }
                    else {
                        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while creating the thread. Contact administrator about this problem.'));
                    }
                }
            }
        }

        return $this->render('new-thread', [
                    'preview' => $preview,
                    'model'   => $model,
                    'forum'   => $forum,
        ]);
    }
    
    /**
     * Pinning the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return \yii\web\Response
     */
    public function actionPin($cid = null, $fid = null, $id = null, $slug = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
            
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if (!User::can(Rbac::PERM_PIN_THREAD, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }

        if ($thread->podiumPin()) {
            $this->success($thread->pinned ? Yii::t('podium/flash', 'Thread has been pinned.') : Yii::t('podium/flash', 'Thread has been unpinned.'));
        }
        else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the thread.'));
        }
        
        return $this->redirect(['default/thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
    }
    
    /**
     * Creating the post of given category ID, forum ID and thread ID.
     * This can be reply to selected post of given ID.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $tid thread's ID
     * @param integer $pid ID of post to reply to
     * @return string|\yii\web\Response
     */
    public function actionPost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to update the thread.'));
            return $this->redirect(['account/login']);
        }
        
        $thread = Thread::find()->where(['id' => $tid, 'category_id' => $cid, 'forum_id' => $fid])->limit(1)->one();
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        if ($thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect(['default/thread', 'cid' => $thread->forum->category->id, 'fid' => $thread->forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
        }
        
        if (!User::can(Rbac::PERM_CREATE_POST)) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['default/index']);
        }
        
        $model = new Post;
        $model->subscribe = 1;

        $postData = Yii::$app->request->post();

        $replyFor = null;
        if (is_numeric($pid) && $pid > 0) {
            $replyFor = Post::find()->where(['id' => $pid])->limit(1)->one();
            if ($replyFor) {
                $model->content = Helper::prepareQuote($replyFor, Yii::$app->request->post('quote'));
            }
        }

        $preview = '';
        $previous = Post::find()->where(['thread_id' => $thread->id])->orderBy(['id' => SORT_DESC])->limit(1)->one();

        if ($model->load($postData)) {
            $model->thread_id = $thread->id;
            $model->forum_id  = $thread->forum->id;
            $model->author_id = User::loggedId();
            if ($model->validate()) {
                if (isset($postData['preview-button'])) {
                    $preview = $model->content;
                }
                else {
                    if ($model->podiumNew($previous)) {
                        $this->success(Yii::t('podium/flash', 'New reply has been added.'));
                        if (!empty($previous) && $previous->author_id == User::loggedId()) {
                            return $this->redirect(['default/show', 'id' => $previous->id]);
                        }
                        return $this->redirect(['default/show', 'id' => $model->id]);
                    }
                    else {
                        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while adding the reply. Contact administrator about this problem.'));
                    }
                }
            }
        }

        return $this->render('post', [
                    'replyFor' => $replyFor,
                    'preview'  => $preview,
                    'model'    => $model,
                    'thread'   => $thread,
                    'previous' => $previous,
        ]);
    }
    
    /**
     * Reporting the post of given category ID, forum ID, thread ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $tid thread's ID
     * @param integer $pid post's ID
     * @return string|\yii\web\Response
     */
    public function actionReport($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (Yii::$app->user->isGuest) {
            $this->warning(Yii::t('podium/flash', 'Please sign in to report the post.'));
            return $this->redirect(['account/login']);
        }
        
        $post = Post::verify($cid, $fid, $tid, $pid);

        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }

        if ($post->author_id == User::loggedId()) {
            $this->info(Yii::t('podium/flash', 'You can not report your own post. Please contact the administrator or moderators if you have got any concerns regarding your post.'));
            return $this->redirect(['default/thread', 'cid' => $post->forum->category->id, 'fid' => $post->forum->id, 'id' => $post->thread->id, 'slug' => $post->thread->slug]);
        }

        $model = new Message;
        $model->scenario = 'report';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->podiumReport($post)) {
                $this->success(Yii::t('podium/flash', 'Thank you for your report. The moderation team will take a look at this post.'));
                return $this->redirect(['default/thread', 'cid' => $post->forum->category->id, 'fid' => $post->forum->id, 'id' => $post->thread->id, 'slug' => $post->thread->slug]);
            }
            else {
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while notifying the moderation team. Contact administrator about this problem.'));
            }
        }

        return $this->render('report', ['model' => $model, 'post' => $post]);
    }
    
    /**
     * Main RSS channel.
     * @return string
     */
    public function actionRss()
    {
        $response = Yii::$app->getResponse();
        $headers = $response->getHeaders();

        $headers->set('Content-Type', 'application/rss+xml; charset=utf-8');

        $response->content = RssView::widget([
            'dataProvider' => (new Forum)->search(null, true),
            'channel'      => [
                'title'       => Config::getInstance()->get('name'),
                'link'        => Url::to(['default/index'], true),
                'description' => Config::getInstance()->get('meta_description'),
                'language'    => Yii::$app->language
            ],
            'items' => [
                'title' => function ($model, $widget) {
                        return Html::encode(!empty($model->latest) ? $model->latest->thread->name : $model->name);
                    },
                'description' => function ($model, $widget) {
                        return !empty($model->latest) ? StringHelper::truncateWords($model->latest->content, 50, '...', true) : '';
                    },
                'link' => function ($model, $widget) {
                        return Url::to(!empty($model->latest) ? 
                            ['default/show', 'id' => $model->latest->id] : 
                                ['default/forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug], true);
                    },
                'author' => function ($model, $widget) {
                        return !empty($model->latest) ? $model->latest->author->username : Config::getInstance()->get('name');
                    },
                'guid' => function ($model, $widget) {
                        if (!empty($model->latest)) {
                            return Url::to(['default/show', 'id' => $model->latest->id], true) . ' ' . Yii::$app->formatter->asDatetime($model->latest->updated_at, 'php:' . DATE_RSS);
                        }
                        else {
                            return Url::to(['default/forum', 'cid' => $model->category_id, 'id' => $model->id, 'slug' => $model->slug], true) . ' ' . Yii::$app->formatter->asDatetime($model->updated_at, 'php:' . DATE_RSS);
                        }
                    },
                'pubDate' => function ($model, $widget) {
                        return Yii::$app->formatter->asDatetime(!empty($model->latest) ? $model->latest->updated_at : $model->updated_at, 'php:' . DATE_RSS);
                    }
            ]
        ]);
    }
    
    /**
     * Searching through the forum.
     * @return string
     */
    public function actionSearch()
    {
        $dataProvider = null;
        $searchModel  = new Vocabulary;
        if ($searchModel->load(Yii::$app->request->get(), '')) {
            $dataProvider = $searchModel->search();
        }
        else {
            $model = new SearchForm;
            $model->match   = 'all';
            $model->type    = 'posts';
            $model->display = 'topics';
            
            $categories = Category::find()->orderBy(['name' => SORT_ASC]);
            $forums     = Forum::find()->orderBy(['name' => SORT_ASC]);
            
            $list = [];
            foreach ($categories->each() as $cat) {
                $catlist = [];
                foreach ($forums->each() as $for) {
                    if ($for->category_id == $cat->id) {
                        $catlist[$for->id] = '|-- ' . Html::encode($for->name);
                    }
                }
                $list[Html::encode($cat->name)] = $catlist;
            }
            
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (empty($model->query) && empty($model->author)) {
                    $this->error(Yii::t('podium/flash', "You have to enter words or author's name first."));
                }
                else {
                    $stop = false;
                    if (!empty($model->query)) {
                        $words = explode(' ', preg_replace('/\s+/', ' ', $model->query));
                        $checkedWords = [];
                        foreach ($words as $word) {
                            if (mb_strlen($word, 'UTF-8') > 2) {
                                $checkedWords[] = $word;
                            }
                        }
                        $model->query = implode(' ', $checkedWords);
                        if (mb_strlen($model->query, 'UTF-8') < 3) {
                            $this->error(Yii::t('podium/flash', 'You have to enter word at least 3 characters long.'));
                            $stop = true;
                        }
                    }
                    if (!$stop) {
                        $dataProvider = $model->searchAdvanced();
                    }
                }
            }
            
            return $this->render('search', [
                'model'        => $model,
                'list'         => $list,
                'dataProvider' => $dataProvider,
                'query'        => $model->query,
                'author'       => $model->author,
            ]);
        }
        
        return $this->render('search', [
            'dataProvider' => $dataProvider,
            'query'        => $searchModel->query,
        ]);
    }
    
    /**
     * Direct link for the post of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionShow($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        $post = Post::find()->where(['id' => $id])->limit(1)->one();
        
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }
        
        $url = [
            'default/thread', 
            'cid'  => $post->thread->category_id,
            'fid'  => $post->forum_id, 
            'id'   => $post->thread_id, 
            'slug' => $post->thread->slug
        ];

        try {
            $count = (new Query)->from(Post::tableName())->where(['and', ['thread_id' => $post->thread_id], ['<', 'id', $post->id]])->orderBy(['id' => SORT_ASC])->count();
            $page = floor($count / 10) + 1;
            if ($page > 1) {
                $url['page'] = $page;
            }
            $url['#'] = 'post' . $post->id;
            return $this->redirect($url);
        }
        catch (Exception $e) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['default/index']);
        }
    }

    /**
     * Displaying the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid category's ID
     * @param integer $fid forum's ID
     * @param integer $id thread's ID
     * @param string $slug thread's slug
     * @return string|\yii\web\Response
     */
    public function actionThread($cid = null, $fid = null, $id = null, $slug = null)
    {
        $thread = Thread::verify($cid, $fid, $id, $slug, Yii::$app->user->isGuest);
        
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['default/index']);
        }

        $this->setMetaTags($thread->forum->keywords ?: $thread->forum->category->keywords, $thread->forum->description ?: $thread->forum->category->description);
        
        $dataProvider = (new Post)->search($thread->forum->id, $thread->id);
        $model = new Post;
        $model->subscribe = 1;

        return $this->render('thread', [
                'model'        => $model,
                'dataProvider' => $dataProvider,
                'thread'       => $thread,
            ]);
    }

    /**
     * Voting on the post.
     * @return string|\yii\web\Response
     */
    public function actionThumb()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['default/index']);
        }

        $data = [
            'error' => 1,
            'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Error while voting on this post!'), ['class' => 'text-danger']),
        ];
            
        if (!Yii::$app->user->isGuest) {
            $data['msg'] = Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Please sign in to vote on this post'), ['class' => 'text-info']);
            return Json::encode($data);
        }
        
        $postId = Yii::$app->request->post('post');
        $thumb  = Yii::$app->request->post('thumb');

        if (is_numeric($postId) && $postId > 0 && in_array($thumb, ['up', 'down'])) {
            $post = Post::find()->where(['id' => $postId])->limit(1)->one();
            if ($post) {
                if ($post->thread->locked) {
                    $data['msg'] = Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'This thread is locked.'), ['class' => 'text-info']);
                    return Json::encode($data);
                }

                if ($post->author_id == User::loggedId()) {
                    $data['msg'] = Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'You can not vote on your own post!'), ['class' => 'text-info']);
                    return Json::encode($data);
                }

                $count = 0;
                $votes = Cache::getInstance()->get('user.votes.' . User::loggedId());
                if ($votes !== false) {
                    if ($votes['expire'] < time()) {
                        $votes = false;
                    }
                    elseif ($votes['count'] >= 10) {
                        $data['msg'] = Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', '{max} votes per hour limit reached!', ['max' => 10]), ['class' => 'text-danger']);
                        return Json::encode($data);
                    }
                    else {
                        $count = $votes['count'];
                    }
                }

                if ($post->podiumThumb($thumb == 'up', $count)) {
                    $data = [
                        'error'    => 0,
                        'likes'    => '+' . $post->likes,
                        'dislikes' => '-' . $post->dislikes,
                        'summ'     => $post->likes - $post->dislikes,
                        'msg'      => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle']) . ' ' . Yii::t('podium/view', 'Your vote has been saved!'), ['class' => 'text-success']),
                    ];
                }
            }
        }

        return Json::encode($data);
    }
    
    /**
     * Setting meta tags.
     * @param string $keywords
     * @param string $description
     */
    public function setMetaTags($keywords = null, $description = null)
    {
        if (empty($keywords)) {
            $keywords = Config::getInstance()->get('meta_keywords');
        }
        if ($keywords) {
            $this->getView()->registerMetaTag([
                'name'    => 'keywords',
                'content' => $keywords
            ]);
        }
        
        if (empty($description)) {
            $description = Config::getInstance()->get('meta_description');
        }
        if ($description) {
            $this->getView()->registerMetaTag([
                'name'    => 'description',
                'content' => $description
            ]);
        }
    }
    
    /**
     * Listing all unread posts.
     * @return string|\yii\web\Response
     */
    public function actionUnreadPosts()
    {
        if (Yii::$app->user->isGuest) {
            $this->info(Yii::t('podium/flash', 'This page is available for registered users only.'));
            return $this->redirect(['account/login']);
        }
        return $this->render('unread-posts');
    }
    
    /**
     * Marking all unread posts as seen.
     * @return \yii\web\Response
     */
    public function actionMarkSeen()
    {
        if (Yii::$app->user->isGuest) {
            $this->info(Yii::t('podium/flash', 'This action is available for registered users only.'));
            return $this->redirect(['account/login']);
        }
        
        if (Thread::podiumMarkAllSeen()) {
            $this->success(Yii::t('podium/flash', 'All unread threads have been marked as seen.'));
            return $this->redirect(['default/index']);
        }

        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while marking threads as seen. Contact administrator about this problem.'));
        return $this->redirect(['default/unread-posts']);
    }
}
