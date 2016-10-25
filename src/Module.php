<?php

namespace bizley\podium;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\DbTarget;
use bizley\podium\maintenance\Installation;
use bizley\podium\models\Activity;
use bizley\podium\models\User;
use Yii;
use yii\base\Action;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\InvalidConfigException;
use yii\base\Module as BaseModule;
use yii\console\Application as ConsoleApplication;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;
use yii\web\Response;

/**
 * Podium Module
 * Yii 2 Forum Module
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @version 0.3 (beta)
 * @license Apache License 2.0
 * 
 * https://github.com/bizley/yii2-podium
 * Please report all issues at GitHub
 * https://github.com/bizley/yii2-podium/issues
 * 
 * Podium requires Yii 2
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii2
 * Podium requires PECL intl >= 2.0.0
 * http://php.net/manual/en/intro.intl.php
 * 
 * @property Cache $cache
 * @property Config $config
 * @property boolean $installed
 * @property string $version
 */
class Module extends BaseModule implements BootstrapInterface
{
    const USER_INHERIT = 'inherit';
    const USER_OWN     = 'own';
    
    const RBAC_INHERIT = 'inherit';
    const RBAC_OWN     = 'own';
    
    const ROUTE_DEFAULT  = '/podium/forum/index';
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
     * This will be used for profile updating confirmation.
     * Default value is 'password_hash'.
     */
    public $userPasswordField = self::FIELD_PASSWORD;
    
    /**
     * @var string Default route for Podium
     * @since 0.2
     */
    public $defaultRoute = 'forum';
    
    /**
     * @var bool Value of identity Cookie 'secure' parameter.
     * @since 0.2
     */
    public $secureIdentityCookie = false;

    /**
     * @var Cache Module cache instance
     * @since 0.2
     */
    protected $_cache;
    
    /**
     * @var Config Module configuration instance
     */
    protected $_config;

    /**
     * @var bool Installation flag
     */
    protected $_installed;
    
    /**
     * @var string Module version
     */
    protected $_version = '0.3';

    /**
     * Registers user activity after every action.
     * @see Activity::add()
     * @param Action $action the action just executed.
     * @param mixed $result the action return result.
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        $parentResult = parent::afterAction($action, $result);
        if (Yii::$app instanceof WebApplication && !in_array($action->id, ['import', 'run', 'update', 'level-up'])) {
            Activity::add();
        }
        return $parentResult;
    }

    /**
     * Bootstrap method to be called during application bootstrap stage.
     * Adding routing rules and log target.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if ($app instanceof WebApplication) {
            $this->addUrlManagerRules($app);
            $this->setPodiumLogTarget($app);            
        } elseif ($app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'bizley\podium\console';
        }
    }
    
    /**
     * Adds UrlManager rules.
     * @param Application $app the application currently running
     * @since 0.2
     */
    protected function addUrlManagerRules($app)
    {
        $app->urlManager->addRules([
                new GroupUrlRule([
                    'prefix' => 'podium',
                    'rules' => require(__DIR__ . '/url-rules.php'),
                ])
            ], false);
    }
    
    /**
     * Sets Podium log target.
     * @param Application $app the application currently running
     * @since 0.2
     */
    protected function setPodiumLogTarget($app)
    {
        $dbTarget = new DbTarget;
        $dbTarget->logTable = '{{%podium_log}}';
        $dbTarget->categories = ['bizley\podium\*'];
        $dbTarget->logVars = [];

        $app->log->targets['podium'] = $dbTarget;
    }

    /**
     * Returns Podium cache instance.
     * @return Cache
     */
    public function getCache()
    {
        if (!$this->_cache) {
            $this->_cache = new Cache;
        }
        return $this->_cache;
    }
    
    /**
     * Returns Podium configuration instance.
     * @return Config
     */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = new Config(['cache' => $this->cache]);
        }
        return $this->_config;
    }

    /**
     * Returns Podium installation flag.
     * @return bool
     */
    public function getInstalled()
    {
        if ($this->_installed === null) {
            $this->_installed = Installation::check();
        }            
        return $this->_installed;
    }
    
    /**
     * Returns Podium version.
     * @return string
     */
    public function getVersion()
    {
        return $this->_version;
    }

    /**
     * Redirects to Podium main controller's action.
     * @return Response
     */
    public function goPodium()
    {
        return Yii::$app->response->redirect([self::ROUTE_DEFAULT]);
    }

    /**
     * Initializes the module for web app.
     * Sets Podium alias (@podium) and layout.
     * Registers user identity, authorization, translations and formatter.
     * Verifies the installation.
     */
    public function init()
    {
        parent::init();
        
        if (!in_array($this->userComponent, [self::USER_INHERIT, self::USER_OWN])) {
            throw InvalidConfigException('Invalid value for the user parameter.');
        }
        if (!in_array($this->rbacComponent, [self::RBAC_INHERIT, self::RBAC_OWN])) {
            throw InvalidConfigException('Invalid value for the rbac parameter.');
        }

        $this->setAliases(['@podium' => '@vendor/bizley/podium/src']);

        if (Yii::$app instanceof WebApplication) {
            if ($this->userComponent == self::USER_OWN) {
                $this->registerIdentity();
            }
            if ($this->rbacComponent == self::RBAC_OWN) {
                $this->registerAuthorization();
            }
            $this->registerTranslations();
            $this->registerFormatter();

            $this->layout = self::MAIN_LAYOUT;
            $this->_installed = Installation::check();
        } elseif (Yii::$app instanceof ConsoleApplication) {
            if ($this->rbacComponent == self::RBAC_OWN) {
                $this->registerAuthorization();
            }
        }
    }

    /**
     * Registers user authorization.
     * @see Installation
     */
    public function registerAuthorization()
    {
        Yii::$app->setComponents([
            'authManager' => [
                'class' => 'yii\rbac\DbManager',
                'itemTable' => '{{%podium_auth_item}}',
                'itemChildTable' => '{{%podium_auth_item_child}}',
                'assignmentTable' => '{{%podium_auth_assignment}}',
                'ruleTable' => '{{%podium_auth_rule}}',
                'cache' => $this->cache->engine
            ],
        ]);
    }

    /**
     * Registers formatter with default timezone.
     */
    public function registerFormatter()
    {
        Yii::$app->setComponents([
            'formatter' => [
                'class' => 'yii\i18n\Formatter',
                'timeZone' => 'UTC',
            ],
        ]);
    }

    /**
     * Registers user identity.
     * @see User
     */
    public function registerIdentity()
    {
        Yii::$app->setComponents([
            'user' => [
                'class' => 'yii\web\User',
                'identityClass' => 'bizley\podium\models\User',
                'enableAutoLogin' => true,
                'loginUrl' => $this->loginUrl,
                'identityCookie' => [
                    'name' => 'podium', 
                    'httpOnly' => true,
                    'secure' => $this->secureIdentityCookie,
                ],
                'idParam' => '__id_podium',
            ],
        ]);
    }

    /**
     * Registers translations.
     */
    public function registerTranslations()
    {
        Yii::$app->i18n->translations['podium/*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => '@podium/messages',
        ];
    }
}
