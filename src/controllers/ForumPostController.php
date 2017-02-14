<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\helpers\Helper;
use bizley\podium\models\Category;
use bizley\podium\models\Forum;
use bizley\podium\models\Message;
use bizley\podium\models\Post;
use bizley\podium\models\Thread;
use bizley\podium\models\User;
use bizley\podium\rbac\Rbac;
use bizley\podium\services\ThreadVerifier;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Podium Forum controller
 * All actions concerning posts.
 * Not accessible directly.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 */
class ForumPostController extends ForumThreadController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [['allow' => false]],
            ],
        ];
    }

    /**
     * Deleting the post of given category ID, forum ID, thread ID and ID.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid post ID
     * @return string|Response
     */
    public function actionDeletepost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $post = Post::verify($cid, $fid, $tid, $pid);
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if ($post->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $post->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect([
                'forum/thread',
                'cid' => $post->forum->category->id,
                'fid' => $post->forum->id,
                'id' => $post->thread->id,
                'slug' => $post->thread->slug
            ]);
        }

        if (!User::can(Rbac::PERM_DELETE_OWN_POST, ['post' => $post]) && !User::can(Rbac::PERM_DELETE_POST, ['item' => $post])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        $postData = Yii::$app->request->post('post');
        if ($postData) {
            if ($postData != $post->id) {
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the post.'));
            } else {
                if ($post->podiumDelete()) {
                    $this->success(Yii::t('podium/flash', 'Post has been deleted.'));
                    if (Thread::find()->where(['id' => $post->thread->id])->exists()) {
                        return $this->redirect([
                            'forum/forum',
                            'cid' => $post->forum->category->id,
                            'id' => $post->forum->id,
                            'slug' => $post->forum->slug
                        ]);
                    }
                    return $this->redirect([
                        'forum/thread',
                        'cid' => $post->forum->category->id,
                        'fid' => $post->forum->id,
                        'id' => $post->thread->id,
                        'slug' => $post->thread->slug
                    ]);
                }
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the post.'));
            }
        }
        return $this->render('deletepost', ['model' => $post]);
    }

    /**
     * Deleting the posts of given category ID, forum ID, thread ID and slug.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $id thread ID
     * @param string $slug thread slug
     * @return string|Response
     */
    public function actionDeleteposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        $thread = (new ThreadVerifier([
            'categoryId' => $cid,
            'forumId' => $fid,
            'threadId' => $id,
            'threadSlug' => $slug
        ]))->verify();
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if (!User::can(Rbac::PERM_DELETE_POST, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        $posts = Yii::$app->request->post('post');
        if ($posts) {
            if (!is_array($posts)) {
                $this->error(Yii::t('podium/flash', 'You have to select at least one post.'));
            } else {
                if ($thread->podiumDeletePosts($posts)) {
                    $this->success(Yii::t('podium/flash', 'Posts have been deleted.'));
                    if (Thread::find()->where(['id' => $thread->id])->exists()) {
                        return $this->redirect([
                            'forum/thread',
                            'cid' => $thread->forum->category->id,
                            'fid' => $thread->forum->id,
                            'id' => $thread->id,
                            'slug' => $thread->slug
                        ]);
                    }
                    return $this->redirect([
                        'forum',
                        'cid' => $thread->forum->category->id,
                        'id' => $thread->forum->id,
                        'slug' => $thread->forum->slug
                    ]);
                }
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the posts.'));
            }
        }
        return $this->render('deleteposts', [
            'model' => $thread,
            'dataProvider' => (new Post())->search($thread->forum->id, $thread->id)
        ]);
    }

    /**
     * Editing the post of given category ID, forum ID, thread ID and own ID.
     * If this is the first post in thread user can change the thread name.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid post ID
     * @return string|Response
     */
    public function actionEdit($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $post = Post::verify($cid, $fid, $tid, $pid);
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if ($post->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $post->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect([
                'forum/thread',
                'cid' => $post->forum->category->id,
                'fid' => $post->forum->id,
                'id' => $post->thread->id,
                'slug' => $post->thread->slug
            ]);
        }
        if (!User::can(Rbac::PERM_UPDATE_OWN_POST, ['post' => $post]) && !User::can(Rbac::PERM_UPDATE_POST, ['item' => $post])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        $isFirstPost = false;
        $firstPost = Post::find()->where([
                'thread_id' => $post->thread->id,
                'forum_id'  => $post->forum->id
            ])->orderBy(['id' => SORT_ASC])->limit(1)->one();
        if ($firstPost->id == $post->id) {
            $post->scenario = 'firstPost';
            $post->topic = $post->thread->name;
            $isFirstPost = true;
        }

        $postData = Yii::$app->request->post();
        $preview = false;
        if ($post->load($postData)) {
            if ($post->validate()) {
                if (isset($postData['preview-button'])) {
                    $preview = true;
                } else {
                    if ($post->podiumEdit($isFirstPost)) {
                        $this->success(Yii::t('podium/flash', 'Post has been updated.'));
                        return $this->redirect(['show', 'id' => $post->id]);
                    }
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the post. Contact administrator about this problem.'));
                }
            }
        }
        return $this->render('edit', [
            'preview' => $preview,
            'model' => $post,
            'isFirstPost' => $isFirstPost
        ]);
    }

    /**
     * Moving the posts of given category ID, forum ID, thread ID and slug.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $id thread ID
     * @param string $slug thread slug
     * @return string|Response
     */
    public function actionMoveposts($cid = null, $fid = null, $id = null, $slug = null)
    {
        $thread = (new ThreadVerifier([
            'categoryId' => $cid,
            'forumId' => $fid,
            'threadId' => $id,
            'threadSlug' => $slug
        ]))->verify();
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if (!User::can(Rbac::PERM_MOVE_POST, ['item' => $thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        if (Yii::$app->request->post()) {
            $posts = Yii::$app->request->post('post');
            $newthread = Yii::$app->request->post('newthread');
            $newname = Yii::$app->request->post('newname');
            $newforum = Yii::$app->request->post('newforum');
            if (empty($posts) || !is_array($posts)) {
                $this->error(Yii::t('podium/flash', 'You have to select at least one post.'));
            } else {
                if (!is_numeric($newthread) || $newthread < 0) {
                    $this->error(Yii::t('podium/flash', 'You have to select a thread for this posts to be moved to.'));
                } else {
                    if ($newthread == 0 && (empty($newname) || empty($newforum) || !is_numeric($newforum) || $newforum < 1)) {
                        $this->error(Yii::t('podium/flash', 'If you want to move posts to a new thread you have to enter its name and select parent forum.'));
                    } else {
                        if ($newthread == $thread->id) {
                            $this->error(Yii::t('podium/flash', 'Are you trying to move posts from this thread to this very same thread?'));
                        } else {
                            if ($thread->podiumMovePostsTo($newthread, $posts, $newname, $newforum)) {
                                $this->success(Yii::t('podium/flash', 'Posts have been moved.'));
                                if (Thread::find()->where(['id' => $thread->id])->exists()) {
                                    return $this->redirect([
                                        'forum/thread',
                                        'cid' => $thread->forum->category->id,
                                        'fid' => $thread->forum->id,
                                        'id' => $thread->id,
                                        'slug' => $thread->slug
                                    ]);
                                }
                                return $this->redirect([
                                    'forum/forum',
                                    'cid' => $thread->forum->category->id,
                                    'id' => $thread->forum->id,
                                    'slug' => $thread->forum->slug
                                ]);
                            }
                            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while moving the posts.'));
                        }
                    }
                }
            }
        }

        $categories = Category::find()->orderBy(['name' => SORT_ASC]);
        $forums = Forum::find()->orderBy(['name' => SORT_ASC]);
        $threads = Thread::find()->orderBy(['name' => SORT_ASC]);

        $list = [0 => Yii::t('podium/view', 'Create new thread')];
        $listforum = [];
        $options = [];
        foreach ($categories->each() as $cat) {
            $catlist = [];
            foreach ($forums->each() as $for) {
                $forlist = [];
                if ($for->category_id == $cat->id) {
                    $catlist[$for->id] = (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $for]) ? '* ' : '')
                                        . Html::encode($cat->name)
                                        . ' &raquo; '
                                        . Html::encode($for->name);
                    foreach ($threads->each() as $thr) {
                        if ($thr->category_id == $cat->id && $thr->forum_id == $for->id) {
                            $forlist[$thr->id] = (User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thr]) ? '* ' : '')
                                                . Html::encode($cat->name)
                                                . ' &raquo; '
                                                . Html::encode($for->name)
                                                . ' &raquo; '
                                                . Html::encode($thr->name);
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
            'model' => $thread,
            'list' => $list,
            'options' => $options,
            'listforum' => $listforum,
            'dataProvider' => (new Post())->search($thread->forum->id, $thread->id)
        ]);
    }

    /**
     * Creating the post of given category ID, forum ID and thread ID.
     * This can be reply to selected post of given ID.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid ID of post to reply to
     * @return string|Response
     */
    public function actionPost($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $thread = Thread::find()->where([
                'id' => $tid,
                'category_id' => $cid,
                'forum_id' => $fid
            ])->limit(1)->one();
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if ($thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect([
                'forum/thread',
                'cid' => $thread->forum->category->id,
                'fid' => $thread->forum->id,
                'id' => $thread->id,
                'slug' => $thread->slug
            ]);
        }

        if (!User::can(Rbac::PERM_CREATE_POST)) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        $model = new Post();
        $model->subscribe = 1;
        $postData = Yii::$app->request->post();
        $replyFor = null;
        if (is_numeric($pid) && $pid > 0) {
            $replyFor = Post::find()->where(['id' => $pid])->limit(1)->one();
            if ($replyFor) {
                $model->content = Helper::prepareQuote($replyFor, Yii::$app->request->post('quote'));
            }
        }

        $preview = false;
        $previous = Post::find()->where(['thread_id' => $thread->id])->orderBy(['id' => SORT_DESC])->limit(1)->one();
        if ($model->load($postData)) {
            $model->thread_id = $thread->id;
            $model->forum_id = $thread->forum->id;
            $model->author_id = User::loggedId();
            if ($model->validate()) {
                if (isset($postData['preview-button'])) {
                    $preview = true;
                } else {
                    if ($model->podiumNew($previous)) {
                        $this->success(Yii::t('podium/flash', 'New reply has been added.'));
                        if (!empty($previous) && $previous->author_id == User::loggedId() && $this->module->podiumConfig->get('merge_posts')) {
                            return $this->redirect(['forum/show', 'id' => $previous->id]);
                        }
                        return $this->redirect(['forum/show', 'id' => $model->id]);
                    }
                    $this->error(Yii::t('podium/flash', 'Sorry! There was an error while adding the reply. Contact administrator about this problem.'));
                }
            }
        }
        return $this->render('post', [
            'replyFor' => $replyFor,
            'preview' => $preview,
            'model' => $model,
            'thread' => $thread,
            'previous' => $previous,
        ]);
    }

    /**
     * Reporting the post of given category ID, forum ID, thread ID, own ID and slug.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid post ID
     * @return string|Response
     */
    public function actionReport($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $post = Post::verify($cid, $fid, $tid, $pid);
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if (User::can(Rbac::PERM_UPDATE_POST, ['item' => $post])) {
            $this->info(Yii::t('podium/flash', "You don't have to report this post since you are allowed to modify it."));
            return $this->redirect([
                'forum/edit',
                'cid' => $post->forum->category->id,
                'fid' => $post->forum->id,
                'tid' => $post->thread->id,
                'pid' => $post->id
            ]);
        }

        if ($post->author_id == User::loggedId()) {
            $this->info(Yii::t('podium/flash', 'You can not report your own post. Please contact the administrator or moderators if you have got any concerns regarding your post.'));
            return $this->redirect([
                'forum/thread',
                'cid' => $post->forum->category->id,
                'fid' => $post->forum->id,
                'id' => $post->thread->id,
                'slug' => $post->thread->slug
            ]);
        }

        $model = new Message();
        $model->scenario = 'report';
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->podiumReport($post)) {
                $this->success(Yii::t('podium/flash', 'Thank you for your report. The moderation team will take a look at this post.'));
                return $this->redirect([
                    'forum/thread',
                    'cid' => $post->forum->category->id,
                    'fid' => $post->forum->id,
                    'id' => $post->thread->id,
                    'slug' => $post->thread->slug
                ]);
            }
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while notifying the moderation team. Contact administrator about this problem.'));
        }
        return $this->render('report', ['model' => $model, 'post' => $post]);
    }

    /**
     * Voting on the post.
     * @return string|Response
     */
    public function actionThumb()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }

        $data = [
            'error' => 1,
            'msg' => Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Error while voting on this post!'),
                ['class' => 'text-danger']
            ),
        ];

        if ($this->module->user->isGuest) {
            $data['msg'] = Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Please sign in to vote on this post'),
                ['class' => 'text-info']
            );
            return Json::encode($data);
        }

        $postId = Yii::$app->request->post('post');
        $thumb = Yii::$app->request->post('thumb');

        if (is_numeric($postId) && $postId > 0 && in_array($thumb, ['up', 'down'])) {
            $post = Post::find()->where(['id' => $postId])->limit(1)->one();
            if ($post) {
                if ($post->thread->locked) {
                    $data['msg'] = Html::tag('span',
                        Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                        . ' ' . Yii::t('podium/view', 'This thread is locked.'),
                        ['class' => 'text-info']
                    );
                    return Json::encode($data);
                }

                if ($post->author_id == User::loggedId()) {
                    $data['msg'] = Html::tag('span',
                        Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                        . ' ' . Yii::t('podium/view', 'You can not vote on your own post!'),
                        ['class' => 'text-info']
                    );
                    return Json::encode($data);
                }

                $count = 0;
                $votes = $this->module->podiumCache->get('user.votes.' . User::loggedId());
                if ($votes !== false) {
                    if ($votes['expire'] < time()) {
                        $votes = false;
                    } elseif ($votes['count'] >= 10) {
                        $data['msg'] = Html::tag('span',
                            Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                            . ' ' . Yii::t('podium/view', '{max} votes per hour limit reached!', ['max' => 10]),
                            ['class' => 'text-danger']
                        );
                        return Json::encode($data);
                    } else {
                        $count = $votes['count'];
                    }
                }

                if ($post->podiumThumb($thumb == 'up', $count)) {
                    $data = [
                        'error' => 0,
                        'likes' => '+' . $post->likes,
                        'dislikes' => '-' . $post->dislikes,
                        'summ' => $post->likes - $post->dislikes,
                        'msg' => Html::tag('span',
                            Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle'])
                            . ' ' . Yii::t('podium/view', 'Your vote has been saved!'),
                            ['class' => 'text-success']
                        ),
                    ];
                }
            }
        }
        return Json::encode($data);
    }

    /**
     * Marking all unread posts as seen.
     * @return Response
     */
    public function actionMarkSeen()
    {
        if (Thread::podiumMarkAllSeen()) {
            $this->success(Yii::t('podium/flash', 'All unread threads have been marked as seen.'));
            return $this->redirect(['forum/index']);
        }
        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while marking threads as seen. Contact administrator about this problem.'));
        return $this->redirect(['forum/unread-posts']);
    }
}
