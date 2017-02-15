<?php

namespace bizley\podium\controllers;

use bizley\podium\filters\AccessControl;
use bizley\podium\helpers\Helper;
use bizley\podium\models\User;
use bizley\podium\rbac\Rbac;
use bizley\podium\traits\FlashTrait;
use Exception;
use Yii;
use yii\base\Action;
use yii\helpers\Html;
use yii\web\Controller as YiiController;

/**
 * Podium base controller
 * Prepares account in case of new inherited identity user.
 * Redirects users in case of maintenance.
 * Not accessible directly.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class BaseController extends YiiController
{
    use FlashTrait;

    /**
     * @var int Podium access type. Possible values are:
     *  1 => member access
     *  0 => guest access
     * -1 => no access
     * Access type can be modified with $accessChecker property of the module.
     * @since 0.6
     */
    public $accessType = 1;

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
     * Adds warning for maintenance mode.
     * Redirects all users except administrators (if this mode is on).
     * Adds warning about missing email.
     * @param Action $action the action to be executed.
     */
    public function beforeAction($action)
    {
        if (!parent::beforeAction($action)) {
            return false;
        }
        $warnings = Yii::$app->session->getFlash('warning');
        $maintenance = $this->maintenanceCheck($action, $warnings);
        if ($maintenance !== false) {
            return $maintenance;
        }
        $email = $this->emailCheck($warnings);
        if ($email !== false) {
            return $email;
        }
        $upgrade = $this->upgradeCheck($warnings);
        if ($upgrade !== false) {
            return $upgrade;
        }
        return true;
    }

    /**
     * Returns warning messages.
     * @return array
     * @since 0.2
     */
    public static function warnings()
    {
        return [
            'maintenance' => Yii::t('podium/flash', 'Podium is currently in the Maintenance mode. All users without Administrator privileges are redirected to {maintenancePage}. You can switch the mode off at {settingsPage}.', [
                'maintenancePage' => Html::a(Yii::t('podium/flash', 'Maintenance page'), ['forum/maintenance']),
                'settingsPage' => Html::a(Yii::t('podium/flash', 'Settings page'), ['admin/settings']),
            ]),
            'email' => Yii::t('podium/flash', 'No e-mail address has been set for your account! Go to {link} to add one.', [
                'link' => Html::a(Yii::t('podium/view', 'Profile') . ' > ' . Yii::t('podium/view', 'Account Details'), ['profile/details'])
            ]),
            'old_version' => Yii::t('podium/flash', 'It looks like there is a new version of Podium database! {link}', [
                'link' => Html::a(Yii::t('podium/view', 'Update Podium'), ['install/level-up'])
            ]),
            'new_version' => Yii::t('podium/flash', 'Module version appears to be older than database! Please verify your database.')
        ];
    }

    /**
     * Performs maintenance check.
     * @param Action $action the action to be executed.
     * @param array $warnings Flash warnings
     * @return bool
     * @since 0.2
     */
    public function maintenanceCheck($action, $warnings)
    {
        if ($this->module->podiumConfig->get('maintenance_mode') != '1') {
            return false;
        }
        if ($action->id === 'maintenance') {
            return false;
        }
        if ($warnings) {
            foreach ($warnings as $warning) {
                if ($warning == static::warnings()['maintenance']) {
                    if (!User::can(Rbac::ROLE_ADMIN)) {
                        return $this->redirect(['forum/maintenance']);
                    }
                    return false;
                }
            }
        }
        $this->warning(static::warnings()['maintenance'], false);
        if (!User::can(Rbac::ROLE_ADMIN)) {
            return $this->redirect(['forum/maintenance']);
        }
        return false;
    }

    /**
     * Performs email check.
     * @param array $warnings Flash warnings
     * @return bool
     * @since 0.2
     */
    public function emailCheck($warnings)
    {
        if ($warnings) {
            foreach ($warnings as $warning) {
                if ($warning == static::warnings()['email']) {
                    return false;
                }
            }
        }
        $user = User::findMe();
        if ($user && empty($user->email)) {
            $this->warning(static::warnings()['email'], false);
        }
        return false;
    }

    /**
     * Performs upgrade check.
     * @param array $warnings Flash warnings
     * @return bool
     * @since 0.2
     */
    public function upgradeCheck($warnings)
    {
        if (!User::can(Rbac::ROLE_ADMIN)) {
            return false;
        }
        if ($warnings) {
            foreach ($warnings as $warning) {
                if ($warning == static::warnings()['old_version']) {
                    return false;
                }
                if ($warning == static::warnings()['new_version']) {
                    return false;
                }
            }
        }
        $result = Helper::compareVersions(
            explode('.', $this->module->version),
            explode('.', $this->module->podiumConfig->get('version'))
        );
        if ($result == '>') {
            $this->warning(static::warnings()['old_version'], false);
        } elseif ($result == '<') {
            $this->warning(static::warnings()['new_version'], false);
        }
        return false;
    }

    /**
     * Creates inherited user account.
     * Redirects banned user to proper view.
     * Sets user's time zone.
     */
    public function init()
    {
        parent::init();
        try {
            if (!empty($this->module->accessChecker)) {
                $this->accessType = call_user_func($this->module->accessChecker, $this->module->user);
            }
            if ($this->accessType === -1) {
                if (!empty($this->module->denyCallback)) {
                    call_user_func($this->module->denyCallback, $this->module->user);
                    return false;
                }
                return $this->goHome();
            }

            if (!$this->module->user->isGuest) {
                $user = User::findMe();
                if ($this->module->userComponent !== true && empty($user) && $this->accessType === 1) {
                    if (!User::createInheritedAccount()) {
                        throw new Exception('There was an error while creating inherited user account. Podium can not run with the current configuration. Please contact administrator about this problem.');
                    }
                    $this->success(Yii::t('podium/flash', 'Hey! Your new forum account has just been automatically created! Go to {link} to complement it.', [
                        'link' => Html::a(Yii::t('podium/view', 'Profile'), ['profile/details'])
                    ]));
                }
                if ($user && $user->status == User::STATUS_BANNED) {
                    return $this->redirect(['forum/ban']);
                }
                if ($user && !empty($user->meta->timezone)) {
                    $this->module->formatter->timeZone = $user->meta->timezone;
                }
            }
        } catch (Exception $exc) {
            Yii::$app->response->redirect([$this->module->prepareRoute('install/run')]);
        }
    }
}
