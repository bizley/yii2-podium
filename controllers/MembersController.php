<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\Log;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use Exception;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Json;

/**
 * Podium Members controller
 * All actions concerning forum members.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class MembersController extends BaseController
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'access' => [
                    'class'        => AccessControl::className(),
                    'denyCallback' => function ($rule, $action) {
                        return $this->redirect(['account/login']);
                    },
                    'rules'  => [
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
                            'roles' => Config::getInstance()->get('members_visible') ? ['@', '?'] : ['@'],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Listing the active users for ajax.
     * Entering 'forum#XX' (or any last part of 'forum' with #XX) looks for 
     * member of the XX ID without username.
     * Entering integer looks for member with XX in username or empty username 
     * and that ID.
     * @param string $q Username query
     * @return string|\yii\web\Response
     */
    public function actionFieldlist($q = null)
    {
        if (Yii::$app->request->isAjax) {
            if (!is_null($q) && is_string($q)) {
                $cache = Cache::getInstance()->get('members.fieldlist');
                if ($cache === false || empty($cache[$q])) {
                    if ($cache === false) {
                        $cache = [];
                    }
                    $users = User::find()->andWhere(['status' => User::STATUS_ACTIVE]);
                    $users->andWhere(['!=', 'id', User::loggedId()]);
                    if (preg_match('/^(forum|orum|rum|um|m)?#([0-9]+)$/', strtolower($q), $matches)) {
                        $users->andWhere(['username' => ['', null], 'id' => $matches[2]]);
                    }
                    elseif (preg_match('/^([0-9]+)$/', $q, $matches)) {
                        $users->andWhere([
                            'or', 
                            ['like', 'username', $q],
                            [
                                'username' => ['', null],
                                'id'       => $matches[1]
                            ]
                        ]);
                    }
                    else {
                        $users->andWhere(['like', 'username', $q]);
                    }
                    $users->orderBy(['username' => SORT_ASC, 'id' => SORT_ASC]);
                    $results = ['results' => []];
                    foreach ($users->each() as $user) {
                        $results['results'][] = ['id' => $user->id, 'text' => $user->getPodiumTag(true)];
                    }
                    if (!empty($results['results'])) {
                        $cache[$q] = Json::encode($results);
                        Cache::getInstance()->set('members.fieldlist', $cache);
                    }
                    else {
                        return Json::encode(['results' => []]);
                    }
                }

                return $cache[$q];
            }
            else {
                return Json::encode(['results' => []]);
            }
        }
        else {
            return $this->redirect(['default/index']);
        }
    }
    
    /**
     * Ignoring the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionIgnore($id = null)
    {
        if (!Yii::$app->user->isGuest) {
            try {
                $model = User::find()->where(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]])->limit(1)->one();

                if (empty($model)) {
                    $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
                }
                elseif ($model->id == User::loggedId()) {
                    $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore your own account.'));
                }
                elseif ($model->id == User::ROLE_ADMIN) {
                    $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore Administrator.'));
                }
                else {
                    if ($model->isIgnoredBy(User::loggedId())) {
                        Yii::$app->db->createCommand()->delete('{{%podium_user_ignore}}', 'user_id = :uid AND ignored_id = :iid', [':uid' => User::loggedId(), ':iid' => $model->id])->execute();
                        Log::info('User unignored', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success(Yii::t('podium/flash', 'User has been unignored.'));                    
                    }
                    else {
                        Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => User::loggedId(), 'ignored_id' => $model->id])->execute();
                        Log::info('User ignored', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success(Yii::t('podium/flash', 'User has been ignored.'));
                    }
                }
            }
            catch (Exception $e) {
                $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
                Log::error($e->getMessage(), null, __METHOD__);
            }
        }
        
        return $this->redirect(['members/index']);
    }

    /**
     * Listing the users.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new UserSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get(), true);
        
        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Listing the moderation team.
     * @return string
     */    
    public function actionMods()
    {
        $searchModel  = new UserSearch;
        $dataProvider = $searchModel->search(Yii::$app->request->get(), true, true);
        
        return $this->render('mods', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Listing posts created by user of given ID and slug.
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionPosts($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }

        $user = User::find()->where(['id' => (int)$id, 'slug' => [$slug, null, '']])->limit(1)->one();
        if (!$user) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }
        else {
            return $this->render('posts', ['user' => $user]);
        }
    }
    
    /**
     * Listing threads started by user of given ID and slug.
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionThreads($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }

        $user = User::find()->where(['id' => (int)$id, 'slug' => [$slug, null, '']])->limit(1)->one();
        if (!$user) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }
        else {
            return $this->render('threads', ['user' => $user]);
        }
    }
    
    /**
     * Viewing profile of user of given ID and slug.
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionView($id = null, $slug = null)
    {
        $model = User::find()->where(['and', ['id' => (int)$id, 'slug' => [$slug, null, '']], ['!=', 'status', User::STATUS_REGISTERED]])->limit(1)->one();

        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        
        return $this->render('view', ['model' => $model]);
    }
}
