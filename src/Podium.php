<?php

namespace bizley\podium;

use bizley\podium\components\Config;
use bizley\podium\components\Installation;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\GroupUrlRule;

class Podium extends Module implements BootstrapInterface
{

    public $params;
    public $version = '1.0';
    public $controllerNamespace = 'bizley\podium\controllers';
    protected $_config;
    protected $_installed       = false;

    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            new GroupUrlRule([
                'prefix' => 'podium',
                'rules'  => [
                    'home'                        => 'default/index',
                    'install'                     => 'install/run',
                    'login'                       => 'account/login',
                    'logout'                      => 'profile/logout',
                    'register'                    => 'account/register',
                    'reactivate'                  => 'account/reactivate',
                    'reset'                       => 'account/reset',
                    'activate/<token:[\w\-]+>'    => 'account/activate',
                    'password/<token:[\w\-]+>'    => 'account/password',
                    'new-email/<token:[\w\-]+>'   => 'account/new-email',
                    'admin/view/<id:\d+>'         => 'admin/view',
                    'admin/pm/<id:\d+>'           => 'admin/pm',
                    'admin/update/<id:\d+>'       => 'admin/update',
                    'admin/delete/<id:\d+>'       => 'admin/delete',
                    'admin/ban/<id:\d+>'          => 'admin/ban',
                    'admin/edit-forum/<id:\d+>'   => 'admin/edit-forum',
                    'admin/delete-forum/<id:\d+>' => 'admin/delete-forum',
                    'admin'                       => 'admin/index',
                    'profile'                     => 'profile/index',
                    'messages/view/<id:\d+>'      => 'messages/view',
                    'messages/reply/<id:\d+>'     => 'messages/reply',
                    'messages/delete/<id:\d+>'    => 'messages/delete',
                    'users/view/<id:\d+>'         => 'users/view',
                ],
                    ])
                ], false);
    }

    public function init()
    {
        parent::init();

        $this->setAliases(['@podium' => '@vendor/bizley/podium']);

        $this->registerIdentity();
        $this->registerAuthorization();
        $this->registerTranslations();
        $this->registerFormatter();

        $this->layout     = 'main';
        $this->_installed = Installation::check();
    }

    public function registerIdentity()
    {
        Yii::$app->setComponents([
            'user' => [
                'class'           => 'yii\web\User',
                'identityClass'   => 'bizley\podium\models\User',
                'enableAutoLogin' => true,
                'loginUrl'        => ['login'],
                'identityCookie'  => ['name' => 'podium', 'httpOnly' => true],
                'idParam'         => '__id_podium',
            ],
        ]);
    }

    public function registerAuthorization()
    {
        Yii::$app->setComponents([
            'authManager' => [
                'class'           => 'yii\rbac\DbManager',
                'itemTable'       => '{{%podium_auth_item}}',
                'itemChildTable'  => '{{%podium_auth_item_child}}',
                'assignmentTable' => '{{%podium_auth_assignment}}',
                'ruleTable'       => '{{%podium_auth_rule}}',
            ],
        ]);
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['podium/*'] = [
            'class'          => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath'       => '@podium/messages',
            'fileMap'        => [
                'podium/mail'   => 'mail.php',
                'podium/flash'  => 'flash.php',
                'podium/layout' => 'layout.php',
                'podium/view'   => 'view.php',
            ],
        ];
    }

    public function registerFormatter()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->setComponents([
                'formatter' => [
                    'class'    => 'yii\i18n\Formatter',
                    'timeZone' => Yii::$app->user->getIdentity()->getTimeZone(),
                ],
            ]);
        }
    }

    public function getInstalled()
    {
        return $this->_installed;
    }

    public function getParam($name, $default = null)
    {
        $params = $this->params;
        if (!isset($params[$name])) {
            return $default;
        }

        return $params[$name];
    }

    public function goPodium()
    {
        return Yii::$app->getResponse()->redirect(['podium/default/index']);
    }

    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = Config::getInstance();
        }

        return $this->_config;
    }

}
