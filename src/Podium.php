<?php

/**
 * TODO:
 * -----------------------------------------------------------------------------
 * panel glowny admina
 * seo
 * grupy userow
 * subskrypcje watkow
 * massmailing
 * regulamin
 * -----------------------------------------------------------------------------
 * 
 * Podium Module
 * Yii 2 Forum Module
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @version 0.1 (beta)
 * @license TBA
 * 
 * https://github.com/bizley-code/Yii2-Podium
 * Please report all issues at GitHub
 * https://github.com/bizley-code/Yii2-Podium/issues
 * 
 * Podium requires Yii 2
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii2
 */
namespace bizley\podium;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\components\DbTarget;
use bizley\podium\components\Installation;
use bizley\podium\models\Activity;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\web\GroupUrlRule;

/**
 * Podium Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 * requires 'bootstrap' => ['log', 'podium'],
 */
class Podium extends Module implements BootstrapInterface
{

    /**
     * @var string Controller namespace
     */
    public $controllerNamespace = 'bizley\podium\controllers';

    /**
     * @var array Module parameters
     */
    public $params;

    /**
     * @var string Module version
     */
    public $version = '1.0';

    /**
     * @var Config Module configuration instance
     */
    protected $_config;

    /**
     * @var boolean Installation flag
     */
    protected $_installed = false;

    /**
     * Registers user activity after every action.
     * @see \bizley\podium\models\Activity
     * 
     * @param \yii\base\Action $action the action just executed.
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);

        Activity::add();

        return $result;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * Adding routing rules and log target.
     * 
     * @param \yii\base\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        $app->getUrlManager()->addRules([
            new GroupUrlRule([
                'prefix' => 'podium',
                'rules'  => [
                    'activate/<token:[\w\-]+>'                                      => 'account/activate',
                    'admin/ban/<id:\d+>'                                            => 'admin/ban',
                    'admin/delete/<id:\d+>'                                         => 'admin/delete',
                    'admin/delete-category/<id:\d+>'                                => 'admin/delete-category',
                    'admin/delete-forum/<cid:\d+>/<id:\d+>'                         => 'admin/delete-forum',
                    'admin/edit-category/<id:\d+>'                                  => 'admin/edit-category',
                    'admin/edit-forum/<cid:\d+>/<id:\d+>'                           => 'admin/edit-forum',
                    'admin/forums/<cid:\d+>'                                        => 'admin/forums',
                    'admin/mod/<uid:\d+>/<fid:\d+>'                                 => 'admin/mod',
                    'admin/mods/<id:\d+>'                                           => 'admin/mods',
                    'admin/new-forum/<cid:\d+>'                                     => 'admin/new-forum',
                    'admin/pm/<id:\d+>'                                             => 'admin/pm',
                    'admin/update/<id:\d+>'                                         => 'admin/update',
                    'admin/view/<id:\d+>'                                           => 'admin/view',
                    'admin'                                                         => 'admin/index',
                    'category/<id:\d+>/<slug:[\w\-]+>'                              => 'default/category',
                    'delete/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'            => 'default/delete',
                    'deletepost/<cid:\d+>/<fid:\d+>/<tid:\d+>/<pid:\d+>'            => 'default/deletepost',
                    'deleteposts/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'       => 'default/deleteposts',
                    'demote/<id:\d+>'                                               => 'admin/demote',
                    'edit/<cid:\d+>/<fid:\d+>/<tid:\d+>/<pid:\d+>'                  => 'default/edit',
                    'forum/<cid:\d+>/<id:\d+>/<slug:[\w\-]+>'                       => 'default/forum',
                    'home'                                                          => 'default/index',
                    'install'                                                       => 'install/run',
                    'lock/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'              => 'default/lock',
                    'login'                                                         => 'account/login',
                    'logout'                                                        => 'profile/logout',
                    'members/posts/<id:\d+>/<slug:[\w\-]+>'                         => 'members/posts',
                    'members/threads/<id:\d+>/<slug:[\w\-]+>'                       => 'members/threads',
                    'members/view/<id:\d+>/<slug:[\w\-]+>'                          => 'members/view',
                    'members'                                                       => 'members/index',
                    'members/ignore/<id:\d+>'                                       => 'members/ignore',
                    'messages/delete/<id:\d+>'                                      => 'messages/delete',
                    'messages/new/<user:\d+>'                                       => 'messages/new',
                    'messages/reply/<id:\d+>'                                       => 'messages/reply',
                    'messages/view/<id:\d+>'                                        => 'messages/view',
                    'move/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'              => 'default/move',
                    'moveposts/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'         => 'default/moveposts',
                    'new-email/<token:[\w\-]+>'                                     => 'account/new-email',
                    'new-thread/<cid:\d+>/<fid:\d+>'                                => 'default/new-thread',
                    'pin/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'               => 'default/pin',
                    'post/<cid:\d+>/<fid:\d+>/<tid:\d+>/<pid:\d+>'                  => 'default/post',
                    'post/<cid:\d+>/<fid:\d+>/<tid:\d+>'                            => 'default/post',
                    'profile'                                                       => 'profile/index',
                    'promote/<id:\d+>'                                              => 'admin/promote',
                    'reactivate'                                                    => 'account/reactivate',
                    'register'                                                      => 'account/register',
                    'report/<cid:\d+>/<fid:\d+>/<tid:\d+>/<pid:\d+>/<slug:[\w\-]+>' => 'default/report',
                    'reset'                                                         => 'account/reset',
                    'search'                                                        => 'default/search',
                    'show/<id:\d+>'                                                 => 'default/show',
                    'thread/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'            => 'default/thread',
                ],
            ])], false);
        
        $dbTarget = new DbTarget;
        $dbTarget->logTable   = '{{%podium_log}}';
        $dbTarget->categories = ['bizley\podium\*'];
        $dbTarget->logVars    = [];
        
        $app->getLog()->targets['podium'] = $dbTarget;
    }

    /**
     * Gets Podium configuration instance.
     * 
     * @return Config configuration instance
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = Config::getInstance();
        }

        return $this->_config;
    }

    /**
     * Checks wheter Podium has been already installed.
     * 
     * @return boolean
     */
    public function getInstalled()
    {
        return $this->_installed;
    }

    /**
     * Gets parameter by its name.
     * If not set returns $default.
     *
     * @param string $name Parameter name
     * @param mixed $default Default value if parameter not found
     * @return mixed Parameter value or $default
     */
    public function getParam($name, $default = null)
    {
        $params = $this->params;
        if (!isset($params[$name])) {
            return $default;
        }

        return $params[$name];
    }

    /**
     * Redirects to Podium main controller's action.
     * 
     * @return \yii\web\Response
     */
    public function goPodium()
    {
        return Yii::$app->getResponse()->redirect(['podium/default/index']);
    }

    /**
     * Initializes the module.
     * Sets Podium alias and layout.
     * Registers user identity, authorization, translations and formatter.
     * Verifies the installation.
     */
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

    /**
     * Registers user authorization.
     * @see \bizley\podium\components\Installation
     */
    public function registerAuthorization()
    {
        Yii::$app->setComponents([
            'authManager' => [
                'class'           => 'yii\rbac\DbManager',
                'itemTable'       => '{{%podium_auth_item}}',
                'itemChildTable'  => '{{%podium_auth_item_child}}',
                'assignmentTable' => '{{%podium_auth_assignment}}',
                'ruleTable'       => '{{%podium_auth_rule}}',
                'cache'           => Cache::getInstance()->cache
            ],
        ]);
    }

    /**
     * Registers formatter for signed users with chosen timezone.
     */
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

    /**
     * Registers user identity.
     * @see \bizley\podium\models\User
     */
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

    /**
     * Registers translations.
     */
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
}