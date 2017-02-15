<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\log\Log;
use bizley\podium\models\Activity;
use bizley\podium\models\Forum;
use bizley\podium\models\ForumSearch;
use bizley\podium\models\User;
use bizley\podium\models\UserSearch;
use bizley\podium\PodiumCache;
use bizley\podium\rbac\Rbac;
use Yii;
use yii\web\Response;

/**
 * Podium Admin controller
 * All actions concerning module administration.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class AdminController extends AdminForumController
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'ruleConfig' => ['class' => 'bizley\podium\filters\PermissionDeniedRule'],
                'rules' => [
                    ['class' => 'bizley\podium\filters\InstallRule'],
                    [
                        'actions' => ['ban'],
                        'perm' => Rbac::PERM_BAN_USER,
                        'redirect' => 'admin/members'
                    ],
                    [
                        'actions' => ['delete'],
                        'perm' => Rbac::PERM_DELETE_USER,
                        'redirect' => 'admin/members'
                    ],
                    [
                        'actions' => ['demote', 'promote'],
                        'perm' => Rbac::PERM_PROMOTE_USER,
                        'redirect' => 'admin/members'
                    ],
                    [
                        'actions' => ['mod'],
                        'perm' => Rbac::PERM_PROMOTE_USER,
                        'redirect' => 'admin/mods'
                    ],
                    [
                        'actions' => ['delete-category'],
                        'perm' => Rbac::PERM_DELETE_CATEGORY,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'actions' => ['delete-forum'],
                        'perm' => Rbac::PERM_DELETE_FORUM,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'actions' => ['edit-category'],
                        'perm' => Rbac::PERM_UPDATE_CATEGORY,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'actions' => ['edit-forum'],
                        'perm' => Rbac::PERM_UPDATE_FORUM,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'actions' => ['new-category'],
                        'perm' => Rbac::PERM_CREATE_CATEGORY,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'actions' => ['new-forum'],
                        'perm' => Rbac::PERM_CREATE_FORUM,
                        'redirect' => 'admin/categories'
                    ],
                    [
                        'class' => 'bizley\podium\filters\PodiumRoleRule',
                        'allow' => true,
                        'roles' => [Rbac::ROLE_ADMIN]
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
            'promote' => [
                'class' => 'bizley\podium\actions\AdminAction',
                'fromRole' => User::ROLE_MEMBER,
                'toRole' => User::ROLE_MODERATOR,
                'method' => 'promoteTo',
                'restrictMessage' => Yii::t('podium/flash', 'You can only promote Members to Moderators.'),
                'successMessage' => Yii::t('podium/flash', 'User has been promoted.'),
                'errorMessage' => Yii::t('podium/flash', 'Sorry! There was an error while promoting the user.')
            ],
            'demote' => [
                'class' => 'bizley\podium\actions\AdminAction',
                'fromRole' => User::ROLE_MODERATOR,
                'toRole' => User::ROLE_MEMBER,
                'method' => 'demoteTo',
                'restrictMessage' => Yii::t('podium/flash', 'You can only demote Moderators to Members.'),
                'successMessage' => Yii::t('podium/flash', 'User has been demoted.'),
                'errorMessage' => Yii::t('podium/flash', 'Sorry! There was an error while demoting the user.')
            ],
        ];
    }

    /**
     * Banning the user of given ID.
     * @param int $id
     * @return Response
     */
    public function actionBan($id = null)
    {
        $model = User::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['admin/members']);
        }
        if ($model->id == User::loggedId()) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not ban or unban your own account.'));
            return $this->redirect(['admin/members']);
        }
        if ($model->status == User::STATUS_ACTIVE) {
            if ($model->ban()) {
                $this->module->podiumCache->delete('members.fieldlist');
                Log::info('User banned', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'User has been banned.'));
            } else {
                Log::error('Error while banning user', $model->id, __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There was some error while banning the user.'));
            }
            return $this->redirect(['admin/members']);
        }
        if ($model->status == User::STATUS_BANNED) {
            if ($model->unban()) {
                $this->module->podiumCache->delete('members.fieldlist');
                Log::info('User unbanned', $model->id, __METHOD__);
                $this->success(Yii::t('podium/flash', 'User has been unbanned.'));
            } else {
                Log::error('Error while unbanning user', $model->id, __METHOD__);
                $this->error(Yii::t('podium/flash', 'Sorry! There was some error while unbanning the user.'));
            }
            return $this->redirect(['admin/members']);
        }
        $this->error(Yii::t('podium/flash', 'Sorry! User has got the wrong status.'));
        return $this->redirect(['admin/members']);
    }

    /**
     * Deleting the user of given ID.
     * @param int $id
     * @return Response
     */
    public function actionDelete($id = null)
    {
        $model = User::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['admin/members']);
        }
        if ($model->id == User::loggedId()) {
            $this->error(Yii::t('podium/flash', 'Sorry! You can not delete your own account.'));
            return $this->redirect(['admin/members']);
        }
        if ($model->delete()) {
            PodiumCache::clearAfter('userDelete');
            Activity::deleteUser($model->id);
            Log::info('User deleted', $model->id, __METHOD__);
            $this->success(Yii::t('podium/flash', 'User has been deleted.'));
        } else {
            Log::error('Error while deleting user', $model->id, __METHOD__);
            $this->error(Yii::t('podium/flash', 'Sorry! There was some error while deleting the user.'));
        }
        return $this->redirect(['admin/members']);
    }

    /**
     * Listing the users.
     * @return string
     */
    public function actionMembers()
    {
        $searchModel = new UserSearch();
        return $this->render('members', [
            'dataProvider' => $searchModel->search(Yii::$app->request->get()),
            'searchModel'  => $searchModel,
        ]);
    }

    /**
     * Adding/removing forum from the moderation list for user of given ID.
     * @param int $uid user ID
     * @param int $fid forum ID
     * @return Response
     */
    public function actionMod($uid = null, $fid = null)
    {
        if (!is_numeric($uid) || $uid < 1 || !is_numeric($fid) || $fid < 1) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the moderator or forum with this ID.'));
            return $this->redirect(['admin/mods']);
        }
        $mod = User::find()->where(['id' => $uid, 'role' => User::ROLE_MODERATOR])->limit(1)->one();
        if (empty($mod)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the moderator with this ID.'));
            return $this->redirect(['admin/mods']);
        }
        $forum = Forum::find()->where(['id' => $fid])->limit(1)->one();
        if (empty($forum)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find the forum with this ID.'));
            return $this->redirect(['admin/mods']);
        }
        if ($mod->updateModeratorForOne($forum->id)) {
            $this->success(Yii::t('podium/flash', 'Moderation list has been updated.'));
        } else {
            $this->error(Yii::t('podium/flash', 'Sorry! There was an error while updating the moderation list.'));
        }
        return $this->redirect(['admin/mods', 'id' => $uid]);
    }

    /**
     * Listing and updating moderation list for the forum of given ID.
     * @param int $id forum ID
     * @return string|Response
     */
    public function actionMods($id = null)
    {
        $mod = null;
        $moderators = User::find()->where(['role' => User::ROLE_MODERATOR])->indexBy('id')->all();
        if (is_numeric($id) && $id > 0) {
            if (isset($moderators[$id])) {
                $mod = $moderators[$id];
            }
        } else {
            reset($moderators);
            $mod = current($moderators);
        }
        $searchModel = new ForumSearch();
        $dataProvider = $searchModel->searchForMods(Yii::$app->request->get());
        $postData = Yii::$app->request->post();
        if ($postData) {
            if (!User::can(Rbac::PERM_PROMOTE_USER)) {
                $this->error(Yii::t('podium/flash', 'You are not allowed to perform this action.'));
            } else {
                $mod_id = !empty($postData['mod_id']) && is_numeric($postData['mod_id']) && $postData['mod_id'] > 0 ? $postData['mod_id'] : 0;
                $selection = !empty($postData['selection']) ? $postData['selection'] : [];
                $pre = !empty($postData['pre']) ? $postData['pre'] : [];
                if ($mod_id == $mod->id) {
                    if ($mod->updateModeratorForMany($selection, $pre)) {
                        $this->success(Yii::t('podium/flash', 'Moderation list has been saved.'));
                    } else {
                        $this->error(Yii::t('podium/flash', 'Sorry! There was an error while saving the moderation list.'));
                    }
                    return $this->refresh();
                }
                $this->error(Yii::t('podium/flash', 'Sorry! There was an error while selecting the moderator ID.'));
            }
        }
        return $this->render('mods', [
            'moderators' => $moderators,
            'mod' => $mod,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Listing the details of user of given ID.
     * @param int $id
     * @return string|Response
     */
    public function actionView($id = null)
    {
        $model = User::find()->where(['id' => $id])->limit(1)->one();
        if (empty($model)) {
            $this->error(Yii::t('podium/flash', 'Sorry! We can not find Member with this ID.'));
            return $this->redirect(['admin/members']);
        }
        return $this->render('view', ['model' => $model]);
    }
}
