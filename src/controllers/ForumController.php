<?php

namespace bizley\podium\controllers;

use bizley\podium\db\Query;
use bizley\podium\filters\AccessControl;
use bizley\podium\models\Category;
use bizley\podium\models\forms\SearchForm;
use bizley\podium\models\Forum;
use bizley\podium\models\Poll;
use bizley\podium\models\Post;
use bizley\podium\models\Thread;
use bizley\podium\models\User;
use bizley\podium\models\Vocabulary;
use bizley\podium\rbac\Rbac;
use bizley\podium\services\ThreadVerifier;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\Response;

/**
 * Podium Forum controller
 * All actions concerning viewing and moderating forums and posts.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.2
 */
class ForumController extends ForumPostController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => ['class' => 'bizley\podium\filters\LoginRequiredRule'],
                'rules' => [
                    ['class' => 'bizley\podium\filters\InstallRule'],
                    [
                        'actions' => ['unread-posts'],
                        'type' => 'info',
                        'message' => Yii::t('podium/flash', 'This page is available for registered users only.')
                    ],
                    [
                        'actions' => ['deletepoll'],
                        'message' => Yii::t('podium/flash', 'Please sign in to delete the poll.')
                    ],
                    [
                        'actions' => ['editpoll'],
                        'message' => Yii::t('podium/flash', 'Please sign in to edit the poll.')
                    ],
                    [
                        'actions' => ['deletepost'],
                        'message' => Yii::t('podium/flash', 'Please sign in to delete the post.')
                    ],
                    [
                        'actions' => ['deleteposts', 'moveposts', 'post', 'lock', 'move', 'pin'],
                        'message' => Yii::t('podium/flash', 'Please sign in to update the thread.')
                    ],
                    [
                        'actions' => ['edit'],
                        'message' => Yii::t('podium/flash', 'Please sign in to edit the post.')
                    ],
                    [
                        'actions' => ['report'],
                        'message' => Yii::t('podium/flash', 'Please sign in to report the post.')
                    ],
                    [
                        'actions' => ['mark-seen'],
                        'type' => 'info',
                        'message' => Yii::t('podium/flash', 'This action is available for registered users only.')
                    ],
                    [
                        'actions' => ['delete'],
                        'message' => Yii::t('podium/flash', 'Please sign in to delete the thread.')
                    ],
                    [
                        'actions' => ['new-thread'],
                        'message' => Yii::t('podium/flash', 'Please sign in to create a new thread.')
                    ],
                    [
                        'class' => 'bizley\podium\filters\PodiumRoleRule',
                        'allow' => true
                    ],
                ],
            ],
        ];
    }

    /**
     * Showing ban info.
     * @return string
     */
    public function actionBan()
    {
        $this->layout = 'maintenance';
        return $this->render('ban');
    }

    /**
     * Displaying the category of given ID and slug.
     * @param int $id
     * @param string $slug
     * @return string|Response
     */
    public function actionCategory($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the category you are looking for.'));
            return $this->redirect(['forum/index']);
        }
        $conditions = ['id' => $id, 'slug' => $slug];
        if ($this->module->user->isGuest) {
            $conditions['visible'] = 1;
        }
        $model = Category::find()->where($conditions)->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the category you are looking for.'));
            return $this->redirect(['forum/index']);
        }
        $this->setMetaTags($model->keywords, $model->description);
        return $this->render('category', ['model' => $model]);
    }

    /**
     * Displaying the forum of given category ID, own ID and slug.
     * @param int $cid category ID
     * @param int $id forum ID
     * @param string $slug forum slug
     * @return string|Response
     */
    public function actionForum($cid = null, $id = null, $slug = null, $toggle = null)
    {
        $filters = Yii::$app->session->get('forum-filters');
        if (in_array(strtolower($toggle), ['new', 'edit', 'hot', 'pin', 'lock', 'all'])) {
            if (strtolower($toggle) == 'all') {
                $filters = null;
            } else {
                $filters[strtolower($toggle)] = empty($filters[strtolower($toggle)]) || $filters[strtolower($toggle)] == 0 ? 1 : 0;
            }
            Yii::$app->session->set('forum-filters', $filters);
            return $this->redirect([
                'forum/forum',
                'cid' => $cid,
                'id' => $id,
                'slug' => $slug
            ]);
        }

        $forum = Forum::verify($cid, $id, $slug, $this->module->user->isGuest);
        if (empty($forum)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the forum you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        $this->setMetaTags(
            $forum->keywords ?: $forum->category->keywords,
            $forum->description ?: $forum->category->description
        );

        return $this->render('forum', [
            'model' => $forum,
            'filters' => $filters
        ]);
    }

    /**
     * Displaying the list of categories.
     * @return string
     */
    public function actionIndex()
    {
        $this->setMetaTags();
        return $this->render('index', ['dataProvider' => (new Category())->search()]);
    }

    /**
     * Direct link for the last post in thread of given ID.
     * @param int $id
     * @return Response
     */
    public function actionLast($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        $thread = Thread::find()->where(['id' => $id])->limit(1)->one();
        if (empty($thread)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the thread you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        $url = [
            'forum/thread',
            'cid' => $thread->category_id,
            'fid' => $thread->forum_id,
            'id' => $thread->id,
            'slug' => $thread->slug
        ];

        $count = $thread->postsCount;
        $page = floor($count / 10) + 1;
        if ($page > 1) {
            $url['page'] = $page;
        }
        return $this->redirect($url);
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
     * Direct link for the post of given ID.
     * @param int $id
     * @return Response
     */
    public function actionShow($id = null)
    {
        if (!is_numeric($id) || $id < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        $post = Post::find()->where(['id' => $id])->limit(1)->one();
        if (empty($post)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        $url = [
            'forum/thread',
            'cid' => $post->thread->category_id,
            'fid' => $post->forum_id,
            'id' => $post->thread_id,
            'slug' => $post->thread->slug
        ];

        try {
            $count = (new Query())->from(Post::tableName())->where([
                    'and',
                    ['thread_id' => $post->thread_id],
                    ['<', 'id', $post->id]
                ])->count();
            $page = floor($count / 10) + 1;
            if ($page > 1) {
                $url['page'] = $page;
            }
            $url['#'] = 'post' . $post->id;
            return $this->redirect($url);
        } catch (Exception $e) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the post you are looking for.'));
            return $this->redirect(['forum/index']);
        }
    }

    /**
     * Displaying the thread of given category ID, forum ID, own ID and slug.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $id thread ID
     * @param string $slug thread slug
     * @return string|Response
     */
    public function actionThread($cid = null, $fid = null, $id = null, $slug = null)
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

        $this->setMetaTags(
            $thread->forum->keywords ?: $thread->forum->category->keywords,
            $thread->forum->description ?: $thread->forum->category->description
        );

        $dataProvider = (new Post())->search($thread->forum->id, $thread->id);
        $model = new Post();
        $model->subscribe = 1;

        return $this->render('thread', [
            'model' => $model,
            'dataProvider' => $dataProvider,
            'thread' => $thread,
        ]);
    }

    /**
     * Setting meta tags.
     * @param string $keywords
     * @param string $description
     */
    public function setMetaTags($keywords = null, $description = null)
    {
        if (empty($keywords)) {
            $keywords = $this->module->podiumConfig->get('meta_keywords');
        }
        if ($keywords) {
            $this->getView()->registerMetaTag([
                'name' => 'keywords',
                'content' => $keywords
            ]);
        }
        if (empty($description)) {
            $description = $this->module->podiumConfig->get('meta_description');
        }
        if ($description) {
            $this->getView()->registerMetaTag([
                'name' => 'description',
                'content' => $description
            ]);
        }
    }

    /**
     * Listing all unread posts.
     * @return string|Response
     */
    public function actionUnreadPosts()
    {
        return $this->render('unread-posts');
    }

    /**
     * Deleting the poll of given category ID, forum ID, thread ID and ID.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid poll ID
     * @return string|Response
     * @since 0.5
     */
    public function actionDeletepoll($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $poll = Poll::find()->joinWith('thread')->where([
            Poll::tableName() . '.id' => $pid,
            Poll::tableName() . '.thread_id' => $tid,
            Thread::tableName() . '.category_id' => $cid,
            Thread::tableName() . '.forum_id' => $fid,
        ])->limit(1)->one();
        if (empty($poll)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the poll you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if ($poll->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $poll->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect([
                'forum/thread',
                'cid' => $poll->thread->category_id,
                'fid' => $poll->thread->forum_id,
                'id' => $poll->thread->id,
                'slug' => $poll->thread->slug
            ]);
        }

        if ($poll->author_id != User::loggedId() && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $poll->thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/index']);
        }

        $postData = Yii::$app->request->post('poll');
        if ($postData) {
            if ($postData != $poll->id) {
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the poll.'));
            } else {
                if ($poll->podiumDelete()) {
                    $this->success(Yii::t('podium/flash', 'Poll has been deleted.'));
                    return $this->redirect([
                        'forum/thread',
                        'cid' => $poll->thread->category_id,
                        'fid' => $poll->thread->forum_id,
                        'id' => $poll->thread->id,
                        'slug' => $poll->thread->slug
                    ]);
                }
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while deleting the poll.'));
            }
        }

        return $this->render('deletepoll', ['model' => $poll]);
    }

    /**
     * Searching through the forum.
     * @return string
     */
    public function actionSearch()
    {
        $dataProvider = null;
        $searchModel = new Vocabulary();

        if ($searchModel->load(Yii::$app->request->get(), '')) {
            return $this->render('search', [
                'dataProvider' => $searchModel->search(),
                'query' => $searchModel->query,
            ]);
        }

        $model = new SearchForm();
        $model->match = 'all';
        $model->type = 'posts';
        $model->display = 'topics';

        $categories = Category::find()->orderBy(['name' => SORT_ASC]);
        $forums = Forum::find()->orderBy(['name' => SORT_ASC]);
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
            } else {
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
            'model' => $model,
            'list' => $list,
            'dataProvider' => $dataProvider,
            'query' => $model->query,
            'author' => $model->author,
        ]);
    }

    /**
     * Voting in poll.
     * @return array
     * @since 0.5
     */
    public function actionPoll()
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }

        $data = [
            'error' => 1,
            'msg' => Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Error while voting in this poll!'),
                ['class' => 'text-danger']
            ),
        ];

        if ($this->module->user->isGuest) {
            $data['msg'] = Html::tag('span',
                Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                . ' ' . Yii::t('podium/view', 'Please sign in to vote in this poll'),
                ['class' => 'text-info']
            );
            return Json::encode($data);
        }

        $pollId = Yii::$app->request->post('poll_id');
        $votes = Yii::$app->request->post('poll_vote');

        if (is_numeric($pollId) && $pollId > 0 && !empty($votes)) {
            /* @var $poll Poll */
            $poll = Poll::find()->where([
                'and',
                ['id' => $pollId],
                [
                    'or',
                    ['>', 'end_at', time()],
                    ['end_at' => null]
                ]
            ])->limit(1)->one();
            if (empty($poll)) {
                $data['msg'] = Html::tag('span',
                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                    . ' ' . Yii::t('podium/view', 'This poll is not active.'),
                    ['class' => 'text-danger']
                );
                return Json::encode($data);
            }

            $loggedId = User::loggedId();

            if ($poll->getUserVoted($loggedId)) {
                $data['msg'] = Html::tag('span',
                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-info-sign'])
                    . ' ' . Yii::t('podium/view', 'You have already voted in this poll.'),
                    ['class' => 'text-info']
                );
                return Json::encode($data);
            }

            $checkedAnswers = [];
            foreach ($votes as $vote) {
                if (!$poll->hasAnswer((int)$vote)) {
                    $data['msg'] = Html::tag('span',
                        Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                        . ' ' . Yii::t('podium/view', 'Invalid poll answer given.'),
                        ['class' => 'text-danger']
                    );
                    return Json::encode($data);
                }
                $checkedAnswers[] = (int)$vote;
            }
            if (empty($checkedAnswers)) {
                $data['msg'] = Html::tag('span',
                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                    . ' ' . Yii::t('podium/view', 'You need to select at least one answer.'),
                    ['class' => 'text-warning']
                );
                return Json::encode($data);
            }
            if (count($checkedAnswers) > $poll->votes) {
                $data['msg'] = Html::tag('span',
                    Html::tag('span', '', ['class' => 'glyphicon glyphicon-warning-sign'])
                    . ' ' . Yii::t('podium/view', 'This poll allows maximum {n, plural, =1{# answer} other{# answers}}.', ['n' => $poll->votes]),
                    ['class' => 'text-danger']
                );
                return Json::encode($data);
            }
            if ($poll->vote($loggedId, $checkedAnswers)) {
                $data = [
                    'error' => 0,
                    'votes' => $poll->currentVotes,
                    'count' => $poll->votesCount,
                    'msg' => Html::tag('span',
                        Html::tag('span', '', ['class' => 'glyphicon glyphicon-ok-circle'])
                        . ' ' . Yii::t('podium/view', 'Your vote has been saved!'),
                        ['class' => 'text-success']
                    ),
                ];
            }
        }
        return Json::encode($data);
    }

    /**
     * Editing the poll of given category ID, forum ID, thread ID and own ID.
     * @param int $cid category ID
     * @param int $fid forum ID
     * @param int $tid thread ID
     * @param int $pid poll ID
     * @return string|Response
     * @since 0.5
     */
    public function actionEditpoll($cid = null, $fid = null, $tid = null, $pid = null)
    {
        $poll = Poll::find()->joinWith('thread')->where([
            Poll::tableName() . '.id' => $pid,
            Poll::tableName() . '.thread_id' => $tid,
            Thread::tableName() . '.category_id' => $cid,
            Thread::tableName() . '.forum_id' => $fid,
        ])->limit(1)->one();
        if (empty($poll)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the poll you are looking for.'));
            return $this->redirect(['forum/index']);
        }

        if ($poll->thread->locked == 1 && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $poll->thread])) {
            $this->info(Yii::t('podium/flash', 'This thread is locked.'));
            return $this->redirect(['forum/thread',
                'cid' => $poll->thread->forum->category->id,
                'fid' => $poll->thread->forum->id,
                'id' => $poll->thread->id,
                'slug' => $poll->thread->slug
            ]);
        }
        if ($poll->author_id != User::loggedId() && !User::can(Rbac::PERM_UPDATE_THREAD, ['item' => $poll->thread])) {
            $this->error(Yii::t('podium/flash', 'Sorry! You do not have the required permission to perform this action.'));
            return $this->redirect(['forum/thread',
                'cid' => $poll->thread->forum->category->id,
                'fid' => $poll->thread->forum->id,
                'id' => $poll->thread->id,
                'slug' => $poll->thread->slug
            ]);
        }
        if ($poll->votesCount) {
            $this->error(Yii::t('podium/flash', 'Sorry! Someone has already voted and this poll can no longer be edited.'));
            return $this->redirect(['forum/thread',
                'cid' => $poll->thread->forum->category->id,
                'fid' => $poll->thread->forum->id,
                'id' => $poll->thread->id,
                'slug' => $poll->thread->slug
            ]);
        }

        foreach ($poll->answers as $answer) {
            $poll->editAnswers[] = $answer->answer;
        }

        $postData = Yii::$app->request->post();
        if ($poll->load($postData)) {
            if ($poll->validate()) {
                if ($poll->podiumEdit()) {
                    $this->success(Yii::t('podium/flash', 'Poll has been updated.'));
                    return $this->redirect(['forum/thread',
                        'cid' => $poll->thread->forum->category->id,
                        'fid' => $poll->thread->forum->id,
                        'id' => $poll->thread->id,
                        'slug' => $poll->thread->slug
                    ]);
                }
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the post. Contact administrator about this problem.'));
            }
        }
        return $this->render('editpoll', ['model' => $poll]);
    }
}
