<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\controllers;

use bizley\podium\components\Messages;
use bizley\podium\maintenance\Installation;
use bizley\podium\maintenance\Update;
use bizley\podium\Module as PodiumModule;
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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        if (parent::beforeAction($action)) {
            if (!empty(Yii::$app->getLog()->targets['podium'])) {
                Yii::$app->getLog()->targets['podium']->enabled = false;
            }
            return $this->checkAccess();
        }

        return false;
    }
    
    /**
     * Checks if user's IP is on the allowed list.
     * @see \bizley\podium\Module::$allowedIPs
     * This method is copied from yii2-gii module.
     * @author Qiang Xue <qiang.xue@gmail.com>
     * @return boolean
     */
    public function checkAccess()
    {
        $ip = Yii::$app->getRequest()->getUserIP();
        foreach ($this->module->allowedIPs as $filter) {
            if ($filter === '*' || $filter === $ip || (($pos = strpos($filter, '*')) !== false && !strncmp($ip, $filter, $pos))) {
                return true;
            }
        }
        
        echo Yii::t('podium/view', 'Access to Podium installation is denied due to IP address restriction.');
        Yii::warning('Access to Podium installation is denied due to IP address restriction. The requested IP is ' . $ip, __METHOD__);
        return false;
    }

    /**
     * Importing the databases structures.
     * @return string
     */
    public function actionImport()
    {
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
     * Running the installation.
     * @return string
     */
    public function actionRun()
    {
        if ($this->module->userComponent == PodiumModule::USER_INHERIT && empty($this->module->adminId)) {
            Yii::$app->session->addFlash('warning', Yii::t(
                    'podium/flash', 
                    Messages::NO_ADMIN_ID_SET, 
                    [
                        'userComponent' => '$userComponent',
                        'inheritParam'  => PodiumModule::USER_INHERIT,
                        'adminId'       => '$adminId'
                    ]
                ));
        }
        
        return $this->render('run', ['version' => $this->module->version]);
    }
    
    /**
     * Updating the databases structures.
     * @return string
     */
    public function actionUpdate()
    {
        $result = ['error' => Yii::t('podium/view', 'Error')];

        if (Yii::$app->request->isPost) {
            $step    = Yii::$app->request->post('step');
            $version = Yii::$app->request->post('version');
            
            if (is_numeric($step)) {
                $result = (new Update)->step($step, $version);
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
     * Comparing versions.
     * @param array $a
     * @param array $b
     * @return string
     */
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
