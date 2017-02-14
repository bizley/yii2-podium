<?php

namespace bizley\podium\controllers;

use bizley\podium\helpers\Helper;
use bizley\podium\db\Query;
use bizley\podium\maintenance\Installation;
use bizley\podium\maintenance\Update;
use bizley\podium\Podium;
use bizley\podium\traits\FlashTrait;
use Yii;
use yii\helpers\Json;
use yii\web\Controller;

/**
 * Podium Install controller
 * All actions concerning module installation.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 */
class InstallController extends Controller
{
    use FlashTrait;

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
            if (!empty(Yii::$app->log->targets['podium'])) {
                Yii::$app->log->targets['podium']->enabled = false;
            }
            return $this->checkAccess();
        }
        return false;
    }

    /**
     * Checks if user's IP is on the allowed list.
     * @see Podium::$allowedIPs
     * This method is copied from yii2-gii module.
     * @author Qiang Xue <qiang.xue@gmail.com>
     * @return bool
     */
    public function checkAccess()
    {
        if (YII_ENV === 'test') {
            return true;
        }
        $ip = Yii::$app->request->getUserIP();
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
            $drop = Yii::$app->request->post('drop');
            if ($drop !== null) {
                $installation = new Installation();
                if ((is_bool($drop) && $drop) || $drop === 'true') {
                    $result = $installation->nextDrop();
                } else {
                    $result = $installation->nextStep();
                }
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
        Yii::$app->session->set(Installation::SESSION_KEY, 0);

        if ($this->module->userComponent !== true && empty($this->module->adminId)) {
            $this->warning(Yii::t('podium/flash', "{userComponent} is set to custom but no administrator ID has been set with {adminId} parameter. Administrator privileges will not be set.", [
                'userComponent' => '$userComponent',
                'adminId' => '$adminId'
            ]));
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
            $result = (new Update())->nextStep();
        }
        return Json::encode($result);
    }

    /**
     * Running the upgrade.
     * @return string
     * @since 0.2
     */
    public function actionLevelUp()
    {
        Yii::$app->session->set(Update::SESSION_KEY, 0);

        $error = '';
        $info = '';
        $dbVersion = 0;
        $mdVersion = $this->module->version;
        $dbQuery = (new Query())->from('{{%podium_config}}')->select('value')->where(['name' => 'version'])->limit(1)->one();
        if (!isset($dbQuery['value'])) {
            $error = Yii::t('podium/flash', 'Error while checking current database version! Please verify your database.');
        } else {
            $dbVersion = $dbQuery['value'];
            $result = Helper::compareVersions(explode('.', $mdVersion), explode('.', $dbVersion));
            if ($result == '=') {
                $info = Yii::t('podium/flash', 'Module and database versions are the same!');
            } elseif ($result == '<') {
                $error = Yii::t('podium/flash', 'Module version appears to be older than database! Please verify your database.');
            }
        }

        Yii::$app->session->set(Update::SESSION_VERSION, $dbVersion);

        return $this->render('level-up', [
            'currentVersion' => $mdVersion,
            'dbVersion' => $dbVersion,
            'error' => $error,
            'info' => $info
        ]);
    }
}
