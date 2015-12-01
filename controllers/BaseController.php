<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\Messages;
use bizley\podium\log\Log;
use bizley\podium\models\User;
use bizley\podium\rbac\Rbac;
use Exception;
use Yii;
use yii\helpers\Html;
use yii\web\Controller as YiiController;

/**
 * Podium base controller
 * 
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

            if (Config::getInstance()->get('maintenance_mode') == '1') {
                if ($action->id !== 'maintenance') {
                    $this->warning(Yii::t('podium/flash', Messages::MAINTENANCE_WARNING, [
                        'maintenancePage' => Html::a(Yii::t('podium/flash', Messages::PAGE_MAINTENANCE), ['default/maintenance']),
                        'settingsPage' => Html::a(Yii::t('podium/flash', Messages::PAGE_SETTINGS), ['admin/settings']),
                    ]));
                    if (!User::can(Rbac::ROLE_ADMIN)) {
                        return $this->redirect(['default/maintenance']);
                    }
                }
            }
            else {
                $user = User::findMe();
                if ($user && empty($user->email)) {
                    $this->warning(Yii::t('podium/flash', Messages::NO_EMAIL_SET, ['link' => Html::a(Yii::t('podium/layout', 'Profile') . ' > ' . Yii::t('podium/view', 'Account Details'), ['profile/details'])]));
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
            $user = User::findMe();
            if (empty($user)) {
                $new = new User;
                $new->setScenario('installation');
                $new->inherited_id = Yii::$app->user->id;
                $new->status       = User::STATUS_ACTIVE;
                $new->role         = User::ROLE_MEMBER;
                $new->timezone     = User::DEFAULT_TIMEZONE;
                if ($new->save()) {
                    $this->success(Yii::t('podium/flash', Messages::ACCOUNT_INHERITED, ['link' => Html::a(Yii::t('podium/layout', 'Profile'))]));
                    Cache::clearAfterActivate();
                    Log::info('Inherited account created', $new->id, __METHOD__);
                }
                else {
                    throw new Exception(Yii::t('podium/view', Messages::ACCOUNT_INHERITED_ERROR));
                }
            }
        }
    }
}
