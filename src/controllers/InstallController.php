<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Installation;
use bizley\podium\components\Update;
use Yii;
use yii\db\Query;
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
                $result = (new Installation)->step($step == -1 ? 0 : $step, $step == -1 ? true : false);
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
        
        return $this->render('run', ['version' => $this->module->version]);
    }
    
    /**
     * Updating the databases structures.
     * @return string
     */
    public function actionUpdate()
    {
        $this->_passCheck();
        
        $result = ['error' => Yii::t('podium/view', 'Error')];

        if (Yii::$app->request->isPost) {
            $step = Yii::$app->request->post('step');
            
            if (is_numeric($step)) {
                $result = (new Update)->step($step);
            }
        }

        return Json::encode($result);
    }
    
    /**
     * Running the upgrade.
     * @return string
     */
    public function actionUpgrade()
    {
        $this->_passCheck();
        
        $error = '';
        $info  = '';
        
        $mdVersion = $this->module->version;
        $extractMd = explode('.', $mdVersion);
        $dbVersion = (new Query)->from('{{%podium_config}}')->select('value')->where(['name' => 'version'])->one();
        if (!isset($dbVersion['value'])) {
            $error = Yii::t('podium/flash', 'Error while checking current database version! Please verify your database.');
        }
        else {
            $extractDb = explode('.', $dbVersion['value']);
            
            $result = $this->compareVersions($extractMd, $extractDb);
            if ($result == '=') {
                $info = Yii::t('podium/flash', 'Module and database versions are the same!');
            }
            elseif ($result == '<') {
                $error = Yii::t('podium/flash', 'Module version appears to be older than database! Please verify your database.');
            }
        }
        
        return $this->render('upgrade', ['currentVersion' => $mdVersion, 'dbVersion' => $dbVersion['value'], 'error' => $error, 'info' => $info]);
    }
    
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (in_array($action->id, ['import', 'run'])) {
            if (!empty(Yii::$app->getLog()->targets['podium'])) {
                Yii::$app->getLog()->targets['podium']->enabled = false;
            }
        }
        
        if (!parent::beforeAction($action)) {
            return false;
        }

        return true;
    }
    
    public function compareVersions($a, $b)
    {
        $versionPos = max(count($a), count($b));
        while (count($a) < $versionPos) {
            $a[] = 0;
        }
        while (count($b) < $versionPos) {
            $b[] = 0;
        }
        
        for ($v = 0; $v < count($a); $v++) {
            if ((int)$a[$v] < (int)$b[$v]) {
                return '<';
            }
            elseif ((int)$a[$v] > (int)$b[$v]) {
                return '>';
            }
        }
        return '=';
    }
}