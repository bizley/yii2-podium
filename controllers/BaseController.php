<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\Log;
use bizley\podium\models\User;
use bizley\podium\Module as PodiumModule;
use bizley\podium\rbac\Rbac;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\web\Controller as YiiController;

/**
 * Podium base controller
 * Prepares account in case of new inheritet identity user.
 * Redirects users in case of maintenance.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class BaseController extends YiiController
{
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [FlashBehavior::className()];
    }
    
    /**
     * Adds warning for maintenance mode.
     * Redirects all users except administrators (if this mode is on).
     * Adds warning about missing email.
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            $warnings = Yii::$app->session->getFlash('warning');
            if (Config::getInstance()->get('maintenance_mode') == '1') {
                if ($action->id !== 'maintenance') {
                    if ($warnings) {
                        foreach ($warnings as $warning) {
                            if ($warning == Yii::t('podium/flash', 'Podium is currently in the Maintenance mode. All users without Administrator privileges are redirected to {maintenancePage}. You can switch the mode off at {settingsPage}.', [
                                    'maintenancePage' => Html::a(Yii::t('podium/flash', 'Maintenance page'), ['default/maintenance']),
                                    'settingsPage' => Html::a(Yii::t('podium/flash', 'Settings page'), ['admin/settings']),
                                ])) {
                                if (!User::can(Rbac::ROLE_ADMIN)) {
                                    return $this->redirect(['default/maintenance']);
                                }
                                else {
                                    return true;
                                }
                            }
                        }
                    }
                    $this->warning(Yii::t('podium/flash', 'Podium is currently in the Maintenance mode. All users without Administrator privileges are redirected to {maintenancePage}. You can switch the mode off at {settingsPage}.', [
                        'maintenancePage' => Html::a(Yii::t('podium/flash', 'Maintenance page'), ['default/maintenance']),
                        'settingsPage' => Html::a(Yii::t('podium/flash', 'Settings page'), ['admin/settings']),
                    ]), false);
                    if (!User::can(Rbac::ROLE_ADMIN)) {
                        return $this->redirect(['default/maintenance']);
                    }
                }
            }
            else {
                if ($warnings) {
                    foreach ($warnings as $warning) {
                        if ($warning == Yii::t('podium/flash', 'No e-mail address has been set for your account! Go to {link} to add one.', ['link' => Html::a(Yii::t('podium/view', 'Profile') . ' > ' . 
                            Yii::t('podium/view', 'Account Details'), ['profile/details'])])) {
                            return true;
                        }
                    }
                }
                
                $user = User::findMe();
                if ($user && empty($user->email)) {
                    $this->warning(Yii::t('podium/flash', 'No e-mail address has been set for your account! Go to {link} to add one.', ['link' => Html::a(Yii::t('podium/view', 'Profile') . ' > ' . 
                            Yii::t('podium/view', 'Account Details'), ['profile/details'])]), false);
                }
            }
            
            return true;
        }
        return false;
    }
    
    /**
     * Creates inherited user account.
     */
    public function init()
    {
        parent::init();
        
        if (!Yii::$app->user->isGuest) {
            if (PodiumModule::getInstance()->userComponent == PodiumModule::USER_INHERIT) {
                $user = User::findMe();
                if (empty($user)) {
                    $new = new User;
                    $new->setScenario('installation');
                    $new->inherited_id = Yii::$app->user->id;
                    $new->status       = User::STATUS_ACTIVE;
                    $new->role         = User::ROLE_MEMBER;
                    $new->timezone     = User::DEFAULT_TIMEZONE;
                    if ($new->save()) {
                        $this->success(Yii::t('podium/flash', 'Hey! Your new forum account has just been automatically created! Go to {link} to complement it.', ['link' => Html::a(Yii::t('podium/view', 'Profile'))]));
                        Cache::clearAfterActivate();
                        Log::info('Inherited account created', $new->id, __METHOD__);
                    }
                    else {
                        throw new Exception(Yii::t('podium/view', 'There was an error while creating inherited user account. Podium can not run with the current configuration. Please contact administrator about this problem.'));
                    }
                }
                elseif ($user->status == User::STATUS_BANNED) {
                    return $this->redirect(['default/ban']);
                }
            }
            else {
                $user = Yii::$app->user->identity;
            }
            if ($user && !empty($user->timezone)) {
                Yii::$app->formatter->timeZone = $user->timezone;
            }
        }
    }
}
