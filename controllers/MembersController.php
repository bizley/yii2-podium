<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\PodiumUser;
use bizley\podium\log\Log;
use bizley\podium\models\User;
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
                    
                    $users = User::find()->where(['and', ['status' => User::STATUS_ACTIVE], ['or', ['like', 'username', $q], ['username' => null]]])->orderBy('username, id');
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
     * @return \yii\web\Response
     */
    public function actionIgnore($id = null)
    {
        if (!Yii::$app->user->isGuest) {
            try {
                $model = (new PodiumUser)->findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);

                if (empty($model)) {
                    $this->error('Sorry! We can not find Member with this ID.');
                }
                elseif ($model->id == Yii::$app->user->id) {
                    $this->error('Sorry! You can not ignore your own account.');
                }
                elseif ($model->id == User::ROLE_ADMIN) {
                    $this->error('Sorry! You can not ignore Administrator.');
                }
                else {

                    if ($model->isIgnoredBy(Yii::$app->user->id)) {

                        Yii::$app->db->createCommand()->delete('{{%podium_user_ignore}}', 'user_id = :uid AND ignored_id = :iid', [':uid' => Yii::$app->user->id, ':iid' => $model->id])->execute();
                        Log::info('User unignored', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success('User has been unignored.');                    
                    }
                    else {
                        Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => Yii::$app->user->id, 'ignored_id' => $model->id])->execute();
                        Log::info('User ignored', !empty($model->id) ? $model->id : '', __METHOD__);
                        $this->success('User has been ignored.');
                    }
                }
            }
            catch (Exception $e) {
                $this->error('Sorry! There was some error while performing this action.');
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
        list($searchModel, $dataProvider) = (new PodiumUser)->userSearch(Yii::$app->request->get(), true);
        
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
        list($searchModel, $dataProvider) = (new PodiumUser)->userSearch(Yii::$app->request->get(), true, true);

        return $this->render('mods', [
                    'dataProvider' => $dataProvider,
                    'searchModel'  => $searchModel
        ]);
    }
    
    /**
     * Listing posts created by user of given ID.
     * @return string|\yii\web\Response
     */
    public function actionPosts($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['members/index']);
        }

        $user = (new PodiumUser)->findOne(['id' => (int)$id, 'slug' => $slug]);
        if (!$user) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['members/index']);
        }
        else {
            return $this->render('posts', ['user' => $user]);
        }
    }
    
    /**
     * Listing threads started by user of given ID.
     * @return string|\yii\web\Response
     */
    public function actionThreads($id = null, $slug = null)
    {
        if (!is_numeric($id) || $id < 1 || empty($slug)) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['members/index']);
        }

        $user = (new PodiumUser)->findOne(['id' => (int)$id, 'slug' => $slug]);
        if (!$user) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['members/index']);
        }
        else {
            return $this->render('threads', ['user' => $user]);
        }
    }
    
    /**
     * Viewing user profile.
     * @return string|\yii\web\Response
     */
    public function actionView($id = null)
    {
        $model = (new PodiumUser)->findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);
        
        if (empty($model->user)) {
            $this->error('Sorry! We can not find Member with this ID.');
            return $this->redirect(['members/index']);
        }
        
        return $this->render('view', ['model' => $model]);
    }
}                