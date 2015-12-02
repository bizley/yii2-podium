<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @version 0.1 (beta)
 * @license Apache License 2.0
 * 
 * https://github.com/bizley-code/yii2-podium
 * Please report all issues at GitHub
 * https://github.com/bizley-code/yii2-podium/issues
 * 
 * Podium requires Yii 2
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii2
 * Podium requires PECL intl >= 2.0.0
 * http://php.net/manual/en/intro.intl.php
 */
namespace bizley\podium;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\DbTarget;
use bizley\podium\maintenance\Installation;
use bizley\podium\models\Activity;
use Yii;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;

/**
 * Podium Module
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Module extends BaseModule implements BootstrapInterface
{

    const USER_INHERIT = 'inherit';
    const USER_OWN     = 'own';
    
    const RBAC_INHERIT = 'inherit';
    const RBAC_OWN     = 'own';
    
    const ROUTE_DEFAULT  = '/podium/default/index';
    const ROUTE_LOGIN    = '/podium/account/login';
    const ROUTE_REGISTER = '/podium/account/register';
    const MAIN_LAYOUT    = 'main';
    
    const FIELD_PASSWORD = 'password_hash';
    
    /**
     * @var null|integer Admin account ID if $user is set to 'inherit'.
     */
    public $adminId;
    
    /**
     * @var array the list of IPs that are allowed to access installation mode 
     * of this module.
     * Each array element represents a single IP filter which can be either an 
     * IP address or an address with wildcard (e.g. 192.168.0.*) to represent a 
     * network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can 
     * only be accessed by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    /**
     * @var string Controller namespace
     */
    public $controllerNamespace = 'bizley\podium\controllers';

    /**
     * @var string|array the URL for user login.
     * If this property is null login link is not provided in menu.
     * Login link is not provided for inherited user component.
     */
    public $loginUrl = [self::ROUTE_LOGIN];
    
    /**
     * @var string Module RBAC component
     */
    public $rbacComponent = self::RBAC_OWN;
    
    /**
     * @var string|array the URL for new user registering.
     * If this property is null registration link is not provided in menu.
     * Registration link is not provided for inherited user component.
     */
    public $registerUrl = [self::ROUTE_REGISTER];
    
    /**
     * @var string Module user component
     */
    public $userComponent = self::USER_OWN;
    
    /**
     * @var string Module inherited user password_hash field.
     * This will be used for profile updating comfirmation.
     * Default value is 'password_hash'.
     */
    public $userPasswordField = self::FIELD_PASSWORD;

    /**
     * @var Config Module configuration instance
     */
    protected $_config;

    /**
     * @var boolean Installation flag
     */
    protected $_installed = false;
    
    /**
     * @var string Module version
     */
    protected $_version = '0.1';

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
        $parentResult = parent::afterAction($action, $result);

        if (Yii::$app instanceof WebApplication && !in_array($action->id, ['import', 'run', 'update', 'upgrade'])) {
            Activity::add();
        }

        return $parentResult;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * Adding routing rules and log target.
     * 
     * @param \yii\base\Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            $app->getUrlManager()->addRules([
                new GroupUrlRule([
                    'prefix' => 'podium',
                    'rules'  => [
                        'activate/<token:[\w\-]+>'                                      => 'account/activate',
                        'admin/ban/<id:\d+>'                                            => 'admin/ban',
                        'admin/contents/<name:[\w\-]+>'                                 => 'admin/contents',
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
                        'last/<id:\d+>'                                                 => 'default/last',
                        'lock/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'              => 'default/lock',
                        'login'                                                         => 'account/login',
                        'logout'                                                        => 'profile/logout',
                        'maintenance'                                                   => 'default/maintenance',
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
                        'profile/add/<id:\d+>'                                          => 'profile/add',
                        'profile/delete/<id:\d+>'                                       => 'profile/delete',
                        'profile/mark/<id:\d+>'                                         => 'profile/mark',
                        'promote/<id:\d+>'                                              => 'admin/promote',
                        'reactivate'                                                    => 'account/reactivate',
                        'register'                                                      => 'account/register',
                        'report/<cid:\d+>/<fid:\d+>/<tid:\d+>/<pid:\d+>/<slug:[\w\-]+>' => 'default/report',
                        'reset'                                                         => 'account/reset',
                        'rss'                                                           => 'default/rss',
                        'search'                                                        => 'default/search',
                        'show/<id:\d+>'                                                 => 'default/show',
                        'thread/<cid:\d+>/<fid:\d+>/<id:\d+>/<slug:[\w\-]+>'            => 'default/thread',
                        'upgrade'                                                       => 'install/upgrade',
                    ],
                ])], false);

            $dbTarget = new DbTarget;
            $dbTarget->logTable   = '{{%podium_log}}';
            $dbTarget->categories = ['bizley\podium\*'];
            $dbTarget->logVars    = [];

            $app->getLog()->targets['podium'] = $dbTarget;
        }
        elseif ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'bizley\podium\console';
        }
    }

    /**
     * Returns Podium configuration instance.
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
     * Returns Podium installation flag.
     * 
     * @return boolean
     */
    public function getInstalled()
    {
        return $this->_installed;
    }
    
    /**
     * Returns Podium version.
     * 
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Redirects to Podium main controller's action.
     * 
     * @return \yii\web\Response
     */
    public function goPodium()
    {
        return Yii::$app->getResponse()->redirect([self::ROUTE_DEFAULT]);
    }

    /**
     * Initializes the module for web app.
     * Sets Podium alias and layout.
     * Registers user identity, authorization, translations and formatter.
     * Verifies the installation.
     */
    public function init()
    {
        parent::init();
        
        if (in_array($this->userComponent, [self::USER_INHERIT, self::USER_OWN])) {
            if (in_array($this->rbacComponent, [self::RBAC_INHERIT, self::RBAC_OWN])) {

                $this->setAliases(['@podium' => '@vendor/bizley/podium']);

                if (Yii::$app instanceof WebApplication) {

                    if ($this->userComponent == self::USER_OWN) {
                        $this->registerIdentity();
                    }
                    if ($this->rbacComponent == self::RBAC_OWN) {
                        $this->registerAuthorization();
                    }
                    $this->registerTranslations();
                    $this->registerFormatter();

                    $this->layout     = self::MAIN_LAYOUT;
                    $this->_installed = Installation::check();
                }
                elseif (Yii::$app instanceof ConsoleApplication) {
                    if ($this->rbacComponent == self::RBAC_OWN) {
                        $this->registerAuthorization();
                    }
                }
            }
            else {
                throw InvalidConfigException('Invalid value for the rbac parameter.');
            }
        }
        else {
            throw InvalidConfigException('Invalid value for the user parameter.');
        }
    }

    /**
     * Registers user authorization.
     * @see \bizley\podium\maintenance\Installation
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
     * Registers formatter with chosen timezone.
     */
    public function registerFormatter()
    {
        Yii::$app->setComponents([
            'formatter' => [
                'class'    => 'yii\i18n\Formatter',
                'timeZone' => 'UTC',
            ],
        ]);
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
                'loginUrl'        => $this->loginUrl,
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
                'podium/flash'  => 'flash.php',
                'podium/layout' => 'layout.php',
                'podium/view'   => 'view.php',
            ],
        ];
    }
}
