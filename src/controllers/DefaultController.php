<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Helper;
use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Message;
use bizley\podium\models\Post;
use bizley\podium\models\PostThumb;
use bizley\podium\models\SearchForm;
use bizley\podium\models\Thread;
use bizley\podium\models\Vocabulary;
use Exception;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Podium Default controller
 * All actions concerning viewing and moderating forums and posts.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class DefaultController extends Controller
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

    /**
     * Checking the thread of given category ID, forum ID, own ID and slug.
     * @param integer $category_id
     * @param integer $forum_id
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */  
    protected function _verifyThread($category_id = null, $forum_id = null, $id = null, $slug = null)
    {
        if (!is_numeric($category_id) || $category_id < 1 || !is_numeric($forum_id) || $forum_id < 1 || !is_numeric($id) || $id < 1 || empty($slug)) {
            return false;
        }

        $conditions = ['id' => (int) $category_id];
        if (Yii::$app->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $category = Category::findOne($conditions);

        if (!$category) {
            return false;
        }
        else {
            $conditions = ['id' => (int) $forum_id, 'category_id' => $category->id];
            if (Yii::$app->user->isGuest) {
                $conditions['visible'] = 1;
            }
            $forum = Forum::findOne($conditions);
            
            if (!$forum) {
                return false;
            }
            else {
                $thread = Thread::findOne(['id' => (int) $id, 'category_id' => $category->id, 'forum_id' => $forum->id, 'slug' => $slug]);
                
                if (!$thread) {
                    return false;
                }
                else {
                    return [$category, $forum, $thread];
                }
            }
        }
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
    
    /**
     * Deleting the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionDelete($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list($category, $forum, $thread) = $verify;
        
        if (Yii::$app->user->can('deleteThread', ['item' => $thread])) {

            $postData = Yii::$app->request->post();
            if ($postData) {
                $delete = $postData['thread'];
                if (is_numeric($delete) && $delete > 0 && $delete == $thread->id) {
                    
                    $transaction = Thread::getDb()->beginTransaction();
                    try {
                        if ($thread->delete()) {
                            $forum->updateCounters(['threads' => -1, 'posts' => -$thread->posts]);
                            $transaction->commit();

                            Cache::getInstance()->delete('forum.threadscount');
                            Cache::getInstance()->delete('forum.postscount');
                            Cache::getInstance()->delete('user.postscount');

                            $this->success('Thread has been deleted.');
                            return $this->redirect(['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]);
                        }
                    }
                    catch (Exception $e) {
                        $transaction->rollBack();
                        Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                        $this->error('Sorry! There was an error while deleting the thread.');
                    }
                }
                else {
                    $this->error('Incorrect thread ID.');
                }
            }
            
            return $this->render('delete', [
                'category' => $category,
                'forum'    => $forum,
                'thread'   => $thread,
            ]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to delete the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }
    
    /**
     * Deleting the post of given category ID, forum ID, thread ID and ID.
     * @param integer $cid
     * @param integer $fid
     * @param integer $tid
     * @param integer $pid
     * @return string|\yii\web\Response
     */
    public function actionDeletepost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($tid) || $tid < 1 || !is_numeric($pid) || $pid < 1) {
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
                    if ($thread->locked == 0 || ($thread->locked == 1 && Yii::$app->user->can('updateThread', ['item' => $thread]))) {
                        $model = Post::findOne(['id' => (int)$pid, 'forum_id' => $forum->id, 'thread_id' => $thread->id]);
                        
                        if (!$model) {
                            $this->error('Sorry! We can not find the post you are looking for.');
                            return $this->redirect(['index']);
                        }
                        else {
                            if (Yii::$app->user->can('deleteOwnPost', ['post' => $model]) || Yii::$app->user->can('deletePost', ['item' => $model])) {

                                $postData = Yii::$app->request->post();
                                if ($postData) {
                                    $delete = $postData['post'];
                                    if (is_numeric($delete) && $delete > 0 && $delete == $model->id) {

                                        $transaction = Post::getDb()->beginTransaction();
                                        try {
                                            if ($model->delete()) {
                                                
                                                $wholeThread = false;
                                                if ((new Query)->from(Post::tableName())->where(['thread_id' => $thread->id, 'forum_id' => $forum->id])->count()) {
                                                    $thread->updateCounters(['posts' => -1]);
                                                    $forum->updateCounters(['posts' => -1]);
                                                }
                                                else {
                                                    $wholeThread = true;
                                                    $thread->delete();
                                                    $forum->updateCounters(['posts' => -1, 'threads' => -1]);
                                                }
                                                
                                                $transaction->commit();

                                                Cache::getInstance()->delete('forum.threadscount');
                                                Cache::getInstance()->delete('forum.postscount');
                                                Cache::getInstance()->delete('user.postscount');

                                                $this->success('Post has been deleted.');
                                                if ($wholeThread) {
                                                    return $this->redirect(['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]);
                                                }
                                                else {
                                                    return $this->redirect(['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]);
                                                }
                                            }
                                        }
                                        catch (Exception $e) {
                                            $transaction->rollBack();
                                            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                            $this->error('Sorry! There was an error while deleting the post.');
                                        }
                                    }
                                    else {
                                        $this->error('Incorrect thread ID.');
                                    }
                                }

                                return $this->render('deletepost', [
                                            'model'       => $model,
                                            'category'    => $category,
                                            'forum'       => $forum,
                                            'thread'      => $thread,
                                ]);
                            }
                            else {
                                if (Yii::$app->user->isGuest) {
                                    $this->warning('Please sign in to delete the post.');
                                    return $this->redirect(['account/login']);
                                }
                                else {
                                    $this->error('Sorry! You do not have the required permission to perform this action.');
                                    return $this->redirect(['default/index']);
                                }
                            }
                        }
                    }
                    else {
                        $this->info('This thread is locked.');
                        return $this->redirect(['thread', 'cid' => $category->id, 'fid' => $forum->id, 'thread' => $thread->id, 'slug' => $thread->slug]);
                    }
                }
            }
        }
    }
    
    /**
     * Deleting the posts of given category ID, forum ID, thread ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionDeleteposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list($category, $forum, $thread) = $verify;
        
        if (Yii::$app->user->can('deletePost', ['item' => $thread])) {

            if (Yii::$app->request->post()) {
                
                $posts     = Yii::$app->request->post('post');
                
                if (empty($posts) || !is_array($posts)) {
                    $this->error('You have to select at least one post.');
                }
                else {
                    $transaction = Post::getDb()->beginTransaction();
                    try {
                        $error = false;
                        foreach ($posts as $post) {
                            if (!is_numeric($post) || $post < 1) {
                                $this->error('Incorrect post ID.');
                                $error = true;
                                break;
                            }
                            else {
                                $nPost = Post::findOne(['id' => $post, 'thread_id' => $thread->id, 'forum_id' => $forum->id]);
                                if (!$nPost) {
                                    $this->error('We can not find the post with this ID.');
                                    $error = true;
                                    break;
                                }
                                else {
                                    $nPost->delete();
                                }
                            }
                        }
                        if (!$error) {
                            
                            $wholeThread = false;
                            if ((new Query)->from(Post::tableName())->where(['thread_id' => $thread->id, 'forum_id' => $forum->id])->count()) {
                                $thread->updateCounters(['posts' => -count($posts)]);
                                $forum->updateCounters(['posts' => -count($posts)]);
                            }
                            else {
                                $wholeThread = true;
                                $thread->delete();
                                $forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
                            }
                            
                            $transaction->commit();

                            Cache::getInstance()->delete('forum.threadscount');
                            Cache::getInstance()->delete('forum.postscount');
                            Cache::getInstance()->delete('user.postscount');

                            $this->success('Posts have been deleted.');
                            if ($wholeThread) {
                                return $this->redirect(['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]);
                            }
                            else {
                                return $this->redirect(['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]);
                            }
                        }
                    }
                    catch (Exception $e) {
                        $transaction->rollBack();
                        Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                        $this->error('Sorry! There was an error while deleting the posts.');
                    }
                }
            }
            
            return $this->render('deleteposts', [
                'category'     => $category,
                'forum'        => $forum,
                'thread'       => $thread,
                'dataProvider' => (new Post)->search($forum->id, $thread->id)
            ]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to update the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }
    
    /**
     * Editing the post of given category ID, forum ID, thread ID and oen ID.
     * If this is first post in thread user can change the thread name.
     * @param integer $cid
     * @param integer $fid
     * @param integer $tid
     * @param integer $pid
     * @return string|\yii\web\Response
     */
    public function actionEdit($cid = null, $fid = null, $tid = null, $pid = null)
    {
        if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($tid) || $tid < 1 || !is_numeric($pid) || $pid < 1) {
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
                    if ($thread->locked == 0 || ($thread->locked == 1 && Yii::$app->user->can('updateThread', ['item' => $thread]))) {                 
                        $model = Post::findOne(['id' => (int)$pid, 'forum_id' => $forum->id, 'thread_id' => $thread->id]);

                        if (!$model) {
                            $this->error('Sorry! We can not find the post you are looking for.');
                            return $this->redirect(['index']);
                        }
                        else {
                            if (Yii::$app->user->can('updateOwnPost', ['post' => $model]) || Yii::$app->user->can('updatePost', ['item' => $model])) {

                                $isFirstPost = false;
                                $firstPost   = Post::find()->where(['forum_id' => $forum->id, 'thread_id' => $thread->id])->orderBy(['id' => SORT_ASC])->one();
                                if ($firstPost->id == $model->id) {
                                    $model->setScenario('firstPost');
                                    $model->topic = $thread->name;
                                    $isFirstPost = true;
                                }                            

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

                                                $model->edited    = 1;
                                                $model->edited_at = time();

                                                if ($model->save()) {

                                                    if ($isFirstPost) {
                                                        $thread->name = $model->topic;
                                                        $thread->save();
                                                    }

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
                                            'preview'     => $preview,
                                            'model'       => $model,
                                            'category'    => $category,
                                            'forum'       => $forum,
                                            'thread'      => $thread,
                                            'isFirstPost' => $isFirstPost
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
                    else {
                        $this->info('This thread is locked.');
                        return $this->redirect(['thread', 'cid' => $category->id, 'fid' => $forum->id, 'thread' => $thread->id, 'slug' => $thread->slug]);
                    }
                }
            }
        }
    }

    /**
     * Displaying the forum of given category ID, own ID and slug.
     * @param integer $cid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
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
    
    /**
     * Displaying the list of categories.
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
                    'dataProvider' => (new Category())->search()
        ]);
    }
    
    /**
     * Locking the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return \yii\web\Response
     */
    public function actionLock($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list(,, $thread) = $verify;
        
        if (Yii::$app->user->can('lockThread', ['item' => $thread])) {
            if ($thread->locked) {
                $thread->locked = 0;
            }
            else {
                $thread->locked = 1;
            }
            if ($thread->save()) {
                if ($thread->locked) {
                    $this->success('Thread has been locked.');
                }
                else {
                    $this->success('Thread has been unlocked.');
                }
            }
            else {
                $this->error('Sorry! There was an error while updating the thread.');
            }
            return $this->redirect(['thread', 'cid' => $cid, 'fid' => $fid, 'id' => $id, 'slug' => $slug]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to update the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }
    
    /**
     * Moving the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionMove($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list($category, $forum, $thread) = $verify;
        
        if (Yii::$app->user->can('moveThread', ['item' => $thread])) {

            $postData = Yii::$app->request->post();
            if ($postData) {
                $moveTo = $postData['forum'];
                if (is_numeric($moveTo) && $moveTo > 0 && $moveTo != $forum->id) {
                    
                    $newParent = Forum::findOne(['id' => $moveTo]);
                    if ($newParent) {
                    
                        $postsCount = $thread->posts;
                        
                        $oldParent = Forum::findOne(['id' => $thread->forum_id]);
                        if ($oldParent) {
                            $transaction = Forum::getDb()->beginTransaction();
                            try {
                                $oldParent->updateCounters(['threads' => -1, 'posts' => -$postsCount]);
                                $newParent->updateCounters(['threads' => 1, 'posts' => $postsCount]);
                                
                                $thread->forum_id    = $newParent->id;
                                $thread->category_id = $newParent->category_id;
                                if ($thread->save()) {
                                    Post::updateAll(['forum_id' => $newParent->id], 'thread_id = :tid', [':tid' => $thread->id]);
                                }
                                
                                $transaction->commit();
                                $this->success('Thread has been moved.');
                                return $this->redirect(['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]);
                            }
                            catch (Exception $e) {
                                $transaction->rollBack();
                                Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                $this->error('Sorry! There was an error while moving the thread.');
                            }
                        }
                        else {
                            $this->error('Sorry! There was an error while moving the thread.');
                        }
                    }
                    else {
                        $this->error('Sorry! We can not find the forum you want to move this thread to.');
                    }
                }
                else {
                    $this->error('Incorrect forum ID.');
                }
            }
            
            $categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
            $forums     = Forum::find()->orderBy(['name' => SORT_ASC])->all();
            
            $list    = [];
            $options = [];
            foreach ($categories as $cat) {
                $catlist = [];
                foreach ($forums as $for) {
                    if ($for->category_id == $cat->id) {
                        $catlist[$for->id] = (Yii::$app->user->can('updateThread', ['item' => $for]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name);
                        if ($for->id == $forum->id) {
                            $options[$for->id] = ['disabled' => true];
                        }
                    }
                }
                $list[Html::encode($cat->name)] = $catlist;
            }
            
            return $this->render('move', [
                'category' => $category,
                'forum'    => $forum,
                'thread'   => $thread,
                'list'     => $list,
                'options'  => $options
            ]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to update the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }
    
    /**
     * Moving the posts of given category ID, forum ID, thread ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionMoveposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list($category, $forum, $thread) = $verify;
        
        if (Yii::$app->user->can('movePost', ['item' => $thread])) {

            if (Yii::$app->request->post()) {
                
                $posts     = Yii::$app->request->post('post');
                $newthread = Yii::$app->request->post('newthread');
                $newname   = Yii::$app->request->post('newname');
                $newforum  = Yii::$app->request->post('newforum');
                
                if (empty($posts) || !is_array($posts)) {
                    $this->error('You have to select at least one post.');
                }
                else {
                    if (!is_numeric($newthread) || $newthread < 0) {
                        $this->error('You have to select a thread for this posts to be moved to.');
                    }
                    else {
                        if ($newthread == 0 && (empty($newname) || empty($newforum) || !is_numeric($newforum) || $newforum < 1)) {
                            $this->error('If you want to move posts to a new thread you have to enter its name and select parent forum.');
                        }
                        else {
                            if ($newthread == $thread->id) {
                                $this->error('Are you trying to move posts from this thread to this very same thread?');
                            }
                            else {
                                $transaction = Thread::getDb()->beginTransaction();
                                try {
                                    if ($newthread == 0) {
                                        $parent = Forum::findOne(['id' => $newforum]);
                                        if (!$parent) {
                                            $this->error('We can not find the parent forum with this ID.');
                                        }
                                        else {
                                            $nThread = new Thread;
                                            $nThread->name        = $newname;
                                            $nThread->posts       = 0;
                                            $nThread->views       = 0;
                                            $nThread->category_id = $parent->category_id;
                                            $nThread->forum_id    = $parent->id;
                                            $nThread->author_id   = Yii::$app->user->id;
                                            $nThread->save();
                                        }
                                    }
                                    else {
                                        $nThread = Thread::findOne(['id' => $newthread]);
                                        if (!$nThread) {
                                            $this->error('We can not find the thread with this ID.');
                                        }
                                    }
                                    if (!empty($nThread)) {
                                        $error = false;
                                        foreach ($posts as $post) {
                                            if (!is_numeric($post) || $post < 1) {
                                                $this->error('Incorrect post ID.');
                                                $error = true;
                                                break;
                                            }
                                            else {
                                                $nPost = Post::findOne(['id' => $post, 'thread_id' => $thread->id, 'forum_id' => $forum->id]);
                                                if (!$nPost) {
                                                    $this->error('We can not find the post with this ID.');
                                                    $error = true;
                                                    break;
                                                }
                                                else {
                                                    $nPost->thread_id = $nThread->id;
                                                    $nPost->forum_id  = $nThread->forum_id;
                                                    $nPost->save();
                                                }
                                            }
                                        }
                                        if (!$error) {
                                            
                                            $wholeThread = false;
                                            if ((new Query)->from(Post::tableName())->where(['thread_id' => $thread->id, 'forum_id' => $forum->id])->count()) {
                                                $thread->updateCounters(['posts' => -count($posts)]);
                                                $forum->updateCounters(['posts' => -count($posts)]);
                                            }
                                            else {
                                                $wholeThread = true;
                                                $thread->delete();
                                                $forum->updateCounters(['posts' => -count($posts), 'threads' => -1]);
                                            }
                                            
                                            $nThread->updateCounters(['posts' => count($posts)]);
                                            $nThread->forum->updateCounters(['posts' => count($posts)]);
                                            
                                            $transaction->commit();
                                            
                                            Cache::getInstance()->delete('forum.threadscount');
                                            Cache::getInstance()->delete('forum.postscount');
                                            Cache::getInstance()->delete('user.postscount');

                                            $this->success('Posts have been moved.');
                                            if ($wholeThread) {
                                                return $this->redirect(['forum', 'cid' => $forum->category_id, 'id' => $forum->id, 'slug' => $forum->slug]);
                                            }
                                            else {
                                                return $this->redirect(['thread', 'cid' => $thread->category_id, 'fid' => $thread->forum_id, 'id' => $thread->id, 'slug' => $thread->slug]);
                                            }
                                        }
                                    }
                                }
                                catch (Exception $e) {
                                    $transaction->rollBack();
                                    Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                    $this->error('Sorry! There was an error while moving the posts.');
                                }
                            }
                        }
                    }
                }
            }
            
            $categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
            $forums     = Forum::find()->orderBy(['name' => SORT_ASC])->all();
            $threads    = Thread::find()->orderBy(['name' => SORT_ASC])->all();
            
            $list      = [0 => Yii::t('podium/view', 'Create new thread')];
            $listforum = [];
            $options   = [];
            foreach ($categories as $cat) {
                $catlist = [];
                foreach ($forums as $for) {
                    $forlist = [];
                    if ($for->category_id == $cat->id) {
                        $catlist[$for->id] = (Yii::$app->user->can('updateThread', ['item' => $for]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name);
                        foreach ($threads as $thr) {
                            if ($thr->category_id == $cat->id && $thr->forum_id == $for->id) {
                                $forlist[$thr->id] = (Yii::$app->user->can('updateThread', ['item' => $thr]) ? '* ' : '') . Html::encode($cat->name) . ' &raquo; ' . Html::encode($for->name) . ' &raquo; ' . Html::encode($thr->name);
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
                'category'     => $category,
                'forum'        => $forum,
                'thread'       => $thread,
                'list'         => $list,
                'options'      => $options,
                'listforum'    => $listforum,
                'dataProvider' => (new Post)->search($forum->id, $thread->id)
            ]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to update the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }

    /**
     * Creating the thread of given category ID and forum ID.
     * @param integer $cid
     * @param integer $fid
     * @return string|\yii\web\Response
     */
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
                                    Cache::getInstance()->deleteElement('user.postscount', Yii::$app->user->id);
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
    
    /**
     * Pinning the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return \yii\web\Response
     */
    public function actionPin($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list(,, $thread) = $verify;
        
        if (Yii::$app->user->can('pinThread', ['item' => $thread])) {
            if ($thread->pinned) {
                $thread->pinned = 0;
            }
            else {
                $thread->pinned = 1;
            }
            if ($thread->save()) {
                if ($thread->pinned) {
                    $this->success('Thread has been pinned.');
                }
                else {
                    $this->success('Thread has been unpinned.');
                }
            }
            else {
                $this->error('Sorry! There was an error while updating the thread.');
            }
            return $this->redirect(['thread', 'cid' => $cid, 'fid' => $fid, 'id' => $id, 'slug' => $slug]);
        }
        else {
            if (Yii::$app->user->isGuest) {
                $this->warning('Please sign in to update the thread.');
                return $this->redirect(['account/login']);
            }
            else {
                $this->error('Sorry! You do not have the required permission to perform this action.');
                return $this->redirect(['default/index']);
            }
        }
    }
    
    /**
     * Creating the post of given category ID, forum ID, thread ID and slug.
     * This can be reply to selected post.
     * @param integer $cid
     * @param integer $fid
     * @param integer $tid
     * @param integer $pid
     * @return string|\yii\web\Response
     */
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
                        if ($thread->locked == 0 || ($thread->locked == 1 && Yii::$app->user->can('updateThread', ['item' => $thread]))) {
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
                            $previous = Post::find()->where(['thread_id' => $thread->id])->orderBy(['id' => SORT_DESC])->one();

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
                                            Cache::getInstance()->deleteElement('user.postscount', Yii::$app->user->id);
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
                        else {
                            $this->info('This thread is locked.');
                            return $this->redirect(['thread', 'cid' => $category->id, 'fid' => $forum->id, 'thread' => $thread->id, 'slug' => $thread->slug]);
                        }
                    }
                }
            }
        }
    }
    
    /**
     * Reporting the post of given category ID, forum ID, thread ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $tid
     * @param integer $pid
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionReport($cid = null, $fid = null, $tid = null, $pid = null, $slug = null)
    {
        if (!Yii::$app->user->isGuest) {
            if (!is_numeric($cid) || $cid < 1 || !is_numeric($fid) || $fid < 1 || !is_numeric($tid) || $tid < 1 || !is_numeric($pid) || $pid < 1 || empty($slug)) {
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
                    $thread = Thread::findOne(['id' => (int) $tid, 'category_id' => $category->id, 'forum_id' => $forum->id, 'slug' => $slug]);

                    if (!$thread) {
                        $this->error('Sorry! We can not find the post you are looking for.');
                        return $this->redirect(['index']);
                    }
                    else {
                        $post = Post::findOne(['id' => (int)$pid, 'forum_id' => $forum->id, 'thread_id' => $thread->id]);

                        if (!$post) {
                            $this->error('Sorry! We can not find the post you are looking for.');
                            return $this->redirect(['index']);
                        }
                        else {
                            if ($post->author_id == Yii::$app->user->id) {
                                $this->info('You can not report your own post. Please contact the administrator or moderators if you have got any concerns regarding your post.');
                                return $this->redirect(['thread', 'cid' => $category->id, 'fid' => $forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
                            }
                            else {

                                $model = new Message;
                                $model->setScenario('report');
                                
                                if ($model->load(Yii::$app->request->post())) {

                                    if ($model->validate()) {

                                        try {

                                            $mods    = $forum->getMods();
                                            $package = [];
                                            foreach ($mods as $mod) {
                                                if ($mod != Yii::$app->user->id) {
                                                    $package[] = [
                                                        'sender_id'       => Yii::$app->user->id,
                                                        'receiver_id'     => $mod,
                                                        'topic'           => Yii::t('podium/view', 'Complaint about the post #{id}', ['id' => $post->id]),
                                                        'content'         => $model->content . '<hr>' . 
                                                            Html::a(Yii::t('podium/view', 'Direct link to the post'), ['show', 'id' => $post->id]) . '<hr>' .
                                                            '<strong>' . Yii::t('podium/view', 'Post contents') . '</strong><br><blockquote>' . $post->content . '</blockquote>',
                                                        'sender_status'   => Message::STATUS_REMOVED,
                                                        'receiver_status' => Message::STATUS_NEW,
                                                        'created_at'      => time(),
                                                        'updated_at'      => time(),
                                                    ];
                                                }
                                            }
                                            if (!empty($package)) {
                                                Yii::$app->db->createCommand()->batchInsert(Message::tableName(), 
                                                    ['sender_id', 'receiver_id', 'topic', 'content', 'sender_status', 'receiver_status', 'created_at', 'updated_at'], 
                                                        array_values($package))->execute();
                                                
                                                Cache::getInstance()->delete('user.newmessages');
                                                
                                                $this->success('Thank you for your report. The moderation team will take a look at this post.');
                                                return $this->redirect(['thread', 'cid' => $category->id, 'fid' => $forum->id, 'id' => $thread->id, 'slug' => $thread->slug]);
                                            }
                                            else {
                                                $this->warning('Apparently there is no one we can send this report to except you and you already reporting it so...');
                                            }
                                        }
                                        catch (Exception $e) {
                                            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
                                            $this->error('Sorry! There was an error while notifying the moderation team. Contact administrator about this problem.');
                                        }
                                    }
                                }

                                return $this->render('report', [
                                            'model'    => $model,
                                            'category' => $category,
                                            'forum'    => $forum,
                                            'thread'   => $thread,
                                            'post'     => $post,
                                ]);
                            }
                        }
                    }
                }
            }
        }
        else {
            $this->warning('Please sign in to report the post.');
            return $this->redirect(['account/login']);
        }
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
            
            $categories = Category::find()->orderBy(['name' => SORT_ASC])->all();
            $forums     = Forum::find()->orderBy(['name' => SORT_ASC])->all();
            
            $list = [];
            foreach ($categories as $cat) {
                $catlist = [];
                foreach ($forums as $for) {
                    if ($for->category_id == $cat->id) {
                        $catlist[$for->id] = '|-- ' . Html::encode($for->name);
                    }
                }
                $list[Html::encode($cat->name)] = $catlist;
            }
            
            if ($model->load(Yii::$app->request->post()) && $model->validate()) {
                if (empty($model->query) && empty($model->author)) {
                    $this->error('You have to enter words or author\'s name first.');
                }
                else {
                    $dataProvider = $model->searchAdvanced();
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
                $count = (new Query)->from(Post::tableName())->where(['and', ['thread_id' => $post->thread_id], ['<', 'id', $post->id]])->orderBy(['id' => SORT_ASC])->count();
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

    /**
     * Displaying the thread of given category ID, forum ID, own ID and slug.
     * @param integer $cid
     * @param integer $fid
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionThread($cid = null, $fid = null, $id = null, $slug = null)
    {
        $verify = $this->_verifyThread($cid, $fid, $id, $slug);
        
        if ($verify === false) {
            $this->error('Sorry! We can not find the thread you are looking for.');
            return $this->redirect(['index']);
        }

        list($category, $forum, $thread) = $verify;
        
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

    /**
     * Voting on the post.
     * @return string|\yii\web\Response
     */
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
                        
                        if ($post->thread->locked) {
                            $data = [
                                'error' => 1,
                                'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'This thread is locked.'), ['class' => 'text-info']),
                            ];
                        }
                        else {
                        
                            if ($post->author_id == Yii::$app->user->id) {
                                return Json::encode([
                                    'error' => 1,
                                    'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'You can not vote on your own post!'), ['class' => 'text-info']),
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
            }
            else {
                $data = [
                    'error' => 1,
                    'msg'   => Html::tag('span', Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign']) . ' ' . Yii::t('podium/view', 'Please sign in to vote on this post'), ['class' => 'text-info']),
                ];
            }
            
            return Json::encode($data);
        }
        else {
            return $this->redirect(['index']);
        }
    }
}