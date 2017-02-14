<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use bizley\podium\Podium;
use Yii;
use yii\web\Response;

/**
 * Podium Members controller
 * All actions concerning forum members.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
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
                'class' => AccessControl::className(),
                'denyCallback' => function ($rule, $action) {
                    return $this->redirect(['account/login']);
                },
                'rules' => [
                    ['class' => 'bizley\podium\filters\InstallRule'],
                    [
                        'allow' => true,
                        'roles' => $this->module->podiumConfig->get('members_visible') ? ['@', '?'] : ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns separated admin actions.
     * @return array
     * @since 0.6
     */
    public function actions()
    {
        return [
            'posts' => [
                'class' => 'bizley\podium\actions\MemberAction',
                'view' => 'posts',
            ],
            'threads' => [
                'class' => 'bizley\podium\actions\MemberAction',
                'view' => 'threads',
            ],
        ];
    }

    /**
     * Listing the active users for ajax.
     * @param string $q Username query
     * @return string|Response
     */
    public function actionFieldlist($q = null)
    {
        if (!Yii::$app->request->isAjax) {
            return $this->redirect(['forum/index']);
        }
        return User::getMembersList($q);
    }

    /**
     * Ignoring the user of given ID.
     * @param int $id
     * @return Response
     */
    public function actionIgnore($id = null)
    {
        if (Podium::getInstance()->user->isGuest) {
            return $this->redirect(['forum/index']);
        }

        $model = User::find()->where([
                'and',
                ['id' => (int)$id],
                ['!=', 'status', User::STATUS_REGISTERED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }

        $logged = User::loggedId();

        if ($model->id == $logged) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->id == User::ROLE_ADMIN) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ignore Administrator.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->updateIgnore($logged)) {
            if ($model->isIgnoredBy($logged)) {
                $this->success(Yii::t('podium/flash', 'User is now ignored.'));
            } else {
                $this->success(Yii::t('podium/flash', 'User is not ignored anymore.'));
            }
        } else {
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
        $searchModel = new UserSearch();
        return $this->render('index', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get(), true),
            'searchModel' => $searchModel
        ]);
    }

    /**
     * Listing the moderation team.
     * @return string
     */
    public function actionMods()
    {
        $searchModel = new UserSearch();
        return $this->render('mods', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get(), true, true),
            'searchModel' => $searchModel
        ]);
    }

    /**
     * Viewing profile of user of given ID and slug.
     * @param int $id
     * @param string $slug
     * @return string|Response
     */
    public function actionView($id = null, $slug = null)
    {
        $model = User::find()->where(['and',
                ['id' => $id],
                ['!=', 'status', User::STATUS_REGISTERED],
                ['or',
                    ['slug' => $slug],
                    ['slug' => ''],
                    ['slug' => null],
                ]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }
        return $this->render('view', ['model' => $model]);
    }

    /**
     * Adding or removing user as a friend.
     * @param int $id user ID
     * @return Response
     * @since 0.2
     */
    public function actionFriend($id = null)
    {
        if (Podium::getInstance()->user->isGuest) {
            return $this->redirect(['forum/index']);
        }

        $model = User::find()->where(['and',
                ['id' => $id],
                ['!=', 'status', User::STATUS_REGISTERED]
            ])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['members/index']);
        }

        $logged = User::loggedId();

        if ($model->id == $logged) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not befriend your own account.'));
            return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
        }

        if ($model->updateFriend($logged)) {
            if ($model->isBefriendedBy($logged)) {
                $this->success(Yii::t('podium/flash', 'User is your friend now.'));
            } else {
                $this->success(Yii::t('podium/flash', 'User is not your friend anymore.'));
            }
        } else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while performing this action.'));
        }
        return $this->redirect(['members/view', 'id' => $model->id, 'slug' => $model->podiumSlug]);
    }
}
