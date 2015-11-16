<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\behaviors\FlashBehavior;
use bizley\podium\components\Config;
use bizley\podium\components\Messages;
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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {

            if (Config::getInstance()->get('maintenance_mode') == '1') {
                if ($action->id !== 'maintenance') {
                    $this->warning(Messages::MAINTENANCE_WARNING, [
                        'maintenancePage' => Html::a(Yii::t('podium/view', Messages::PAGE_MAINTENANCE), ['default/maintenance']),
                        'settingsPage' => Html::a(Yii::t('podium/view', Messages::PAGE_SETTINGS), ['admin/settings']),
                    ]);
                    if (!Yii::$app->user->can('podiumAdmin')) {
                        return $this->redirect(['default/maintenance']);
                    }
                }
            }
            
            return true;
        }
        return false;
    }
}