<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Podium Members controller
 * All actions concerning forum members.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class MembersController extends Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class'        => AccessControl::className(),
                'denyCallback' => function () {
                    return $this->redirect(['account/login']);
                },
                'rules'  => [
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
                        'roles' => Config::getInstance()->get('members_visible') ? ['@', '?'] : ['@'],
                    ],
                ],
            ],
            'flash' => FlashBehavior::className(),
        ];
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
                $model = User::findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);

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
                        $this->success('User has been unignored.');                    
                    }
                    else {
                        Yii::$app->db->createCommand()->insert('{{%podium_user_ignore}}', ['user_id' => Yii::$app->user->id, 'ignored_id' => $model->id])->execute();
                        $this->success('User has been ignored.');
                    }
                }
            }
            catch (Exception $e) {
                $this->error('Sorry! There was some error while performing this action.');
                Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
            }
        }
        
        return $this->redirect(['index']);
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
        $searchModel  = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get(), true, true);

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
            return $this->redirect(['index']);
        }

        $user = User::findOne(['id' => (int)$id, 'slug' => $slug]);
        if (!$user) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['index']);
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
            return $this->redirect(['index']);
        }

        $user = User::findOne(['id' => (int)$id, 'slug' => $slug]);
        if (!$user) {
            $this->error('Sorry! We can not find the user you are looking for.');
            return $this->redirect(['index']);
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
        $model = User::findOne(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]]);
        
        if (empty($model)) {
            $this->error('Sorry! We can not find Member with this ID.');
            return $this->redirect(['index']);
        }
        
        return $this->render('view', ['model' => $model]);
    }
}                