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
        return [
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
        ];
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
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['default/index']);
        }
            
        if (is_null($q) || !is_string($q)) {
            return Json::encode(['results' => []]);
        }
            
        return User::getMembersList($q);
    }
    
    /**
     * Ignoring the user of given ID.
     * @param integer $id
     * @return \yii\web\Response
     */
    public function actionIgnore($id = null)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['default/index']);
        }
            
        $model = User::find()->where(['and', ['id' => (int)$id], ['!=', 'status', User::STATUS_REGISTERED]])->limit(1)->one();
        
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        
        if ($model->id == User::loggedId()) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }
        
        if ($model->id == User::ROLE_ADMIN) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore Administrator.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }
        
        if ($model->updateIgnore()) {
            if ($model->isIgnoredBy(User::loggedId())) {
                $this->success(Yii::t('podium/flash', 'User is now ignored.'));
            }
            else {
                $this->success(Yii::t('podium/flash', 'User is not ignored anymore.'));
            }
        }
        else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
        }
        
        return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
    }

    /**
     * Listing the users.
     * @return string
     */
    public function actionIndex()
    {
        $searchModel  = new UserSearch;
        
        return $this->render('index', [
                'dataProvider' => $searchModel->search(Yii::$app->request->get(), true),
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
        
        return $this->render('mods', [
                'dataProvider' => $searchModel->search(Yii::$app->request->get(), true, true),
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

        $user = User::find()->where(['id' => $id, 'slug' => [$slug, null, '']])->limit(1)->one();
        
        if (empty($user)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }
        
        return $this->render('posts', ['user' => $user]);
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

        $user = User::find()->where(['id' => $id, 'slug' => [$slug, null, '']])->limit(1)->one();
        
        if (empty($user)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the user you are looking for.'));
            return $this->redirect(['members/index']);
        }
        
        return $this->render('threads', ['user' => $user]);
    }
    
    /**
     * Viewing profile of user of given ID and slug.
     * @param integer $id
     * @param string $slug
     * @return string|\yii\web\Response
     */
    public function actionView($id = null, $slug = null)
    {
        $model = User::find()->where(['and', ['id' => $id, 'slug' => [$slug, null, '']], ['!=', 'status', User::STATUS_REGISTERED]])->limit(1)->one();

        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        
        return $this->render('view', ['model' => $model]);
    }
    
    /**
     * Adding or removing user as a friend.
     * @param integer $id user ID
     * @return \yii\web\Response
     * @since 0.2
     */
    public function actionFriend($id = null)
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['default/index']);
        }
    
        $model = User::find()->where(['and', ['id' => $id], ['!=', 'status', User::STATUS_REGISTERED]])->limit(1)->one();
        
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        
        if ($model->id == User::loggedId()) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not befriend your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }
        
        if ($model->updateFriend()) {
            if ($model->isBefriendedBy(User::loggedId())) {
                $this->success(Yii::t('podium/flash', 'User is your friend now.'));
            }
            else {
                $this->success(Yii::t('podium/flash', 'User is not your friend anymore.'));
            }
        }
        else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
        }
        
        return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
    }
}
