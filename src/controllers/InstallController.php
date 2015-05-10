<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Installation;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Podium Install controller
 * All actions concerning module installation.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class InstallController extends Controller
{
    
    /**
     * @var string Layout name
     */
    public $layout = 'installation';
    
    /**
     * Checking for the required configuration parameter.
     * For installation set 'mode' parameter with 'INSTALL' value in module's configuration.
     * @return \yii\web\Response
     */
    protected function _passCheck()
    {
        if ($this->module->getParam('mode') !== 'INSTALL') {
            return $this->redirect(['prereq']);
        }
    }
    
    /**
     * Importing the databases structures.
     * @return string
     */
    public function actionImport()
    {
        $this->_passCheck();
        
        $result = ['error' => Yii::t('podium/view', 'Error')];

        if (Yii::$app->request->isPost) {
            $step = Yii::$app->request->post('step');
            if (is_numeric($step)) {
                $result = (new Installation())->step($step);
            }
        }

        return Json::encode($result);
    }
    
    /**
     * Displaying the prerequirements.
     * @return \yii\web\Response
     */
    public function actionPrereq()
    {
        return $this->render('prereq');
    }
    
    /**
     * Running the installation.
     * @return string
     */
    public function actionRun()
    {
        $this->_passCheck();
        
        return $this->render('run');
    }    
}