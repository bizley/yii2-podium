<?php

namespace bizley\podium;

use bizley\podium\log\DbTarget;
use bizley\podium\maintenance\Maintenance;
use bizley\podium\models\Activity;
use bizley\podium\PodiumCache;
use bizley\podium\PodiumConfig;
use Yii;
use yii\base\Action;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\console\Application as ConsoleApplication;
use yii\db\Connection;
use yii\i18n\Formatter;
use yii\rbac\DbManager;
use yii\web\Application as WebApplication;
use yii\web\GroupUrlRule;
use yii\web\Response;
use yii\web\User;

/**
 * Podium Module
 * Yii 2 Forum Module
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @version 0.5-dev (beta)
 * @license Apache License 2.0
 * 
 * https://github.com/bizley/yii2-podium
 * Please report all issues at GitHub
 * https://github.com/bizley/yii2-podium/issues
 * 
 * Podium requires Yii 2
 * http://www.yiiframework.com
 * https://github.com/yiisoft/yii2
 * 
 * For Podium documentation go to
 * https://github.com/bizley/yii2-podium/wiki
 * 
 * @property PodiumCache $podiumCache
 * @property PodiumConfig $podiumConfig
 * 
 * @property Formatter $formatter
 * @property DbManager $rbac
 * @property User $user
 * @property Connection $db
 * @property Cache $cache
 * 
 * @property boolean $installed
 * @property string $version
 */
class Podium extends Module implements BootstrapInterface
{
    const ROUTE_DEFAULT  = '/forum/index';
    const ROUTE_LOGIN    = '/account/login';
    const ROUTE_REGISTER = '/account/register';
    
    const MAIN_LAYOUT = 'main';
    
    const FIELD_PASSWORD = 'password_hash';
    
    /**
     * @var string Module version.
     */
    protected $_version = '0.4';

    /**
     * @var null|int Admin account ID if `$userComponent` is not set to `true`.
     */
    public $adminId;

    /**
     * @var array the list of IPs that are allowed to access installation mode 
     * of this module.
     * Each array element represents a single IP filter which can be either an 
     * IP address or an address with wildcard (e.g. `192.168.0.*`) to represent 
     * a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can 
     * only be accessed by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];
    
    /**
     * @var string Controller namespace.
     */
    public $controllerNamespace = 'bizley\podium\controllers';

    /**
     * @var bool|string|array Module user component.
     * Since version 0.5 it can be:
     * - `true` for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at `registerIdentity()` to see
     *   what Podium uses).
     */
    public $userComponent = true;
    
    /**
     * @var bool|string|array Module RBAC component.
     * Since version 0.5 it can be:
     * - `true` for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at `registerAuthorization()` to 
     *   see what Podium uses).
     */
    public $rbacComponent = true;
    
    /**
     * @var bool|string|array Module formatter component.
     * It can be:
     * - `true` for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at `registerFormatter()` to 
     *   see what Podium uses).
     * @since 0.5
     */
    public $formatterComponent = true;
    
    /**
     * @var string|array Module db component.
     * It can be:
     * - string with inherited component ID,
     * - array with custom configuration.
     * @since 0.5
     */
    public $dbComponent = 'db';
    
    /**
     * @var bool|string|array Module cache component.
     * It can be:
     * - `false` for not using cache,
     * - string with inherited component ID,
     * - array with custom configuration.
     * @since 0.5
     */
    public $cacheComponent = false;
    
    /**
     * @var bool|string URL for user login.
     * If this property is `false` login link is not provided in menu.
     * Login link is not provided for inherited user component.
     */
    public $loginUrl;
    
    /**
     * @var bool|string URL for new user registering.
     * If this property is `false` registration link is not provided in menu.
     * Registration link is not provided for inherited user component.
     */
    public $registerUrl;
    
    /**
     * @var string Module inherited user password_hash field.
     * This will be used for profile updating confirmation.
     * Default value is `'password_hash'`.
     */
    public $userPasswordField = self::FIELD_PASSWORD;
    
    /**
     * @var string Default route for Podium.
     * @since 0.2
     */
    public $defaultRoute = 'forum';
    
    /**
     * @var bool Value of identity Cookie `'secure'` parameter.
     * @since 0.2
     */
    public $secureIdentityCookie = false;

    /**
     * @var Cache Module cache instance.
     * @since 0.2
     */
    protected $_cache;
    
    /**
     * @var Config Module configuration instance.
     */
    protected $_config;

    /**
     * @var bool Installation flag.
     */
    protected $_installed;
    

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
                    'prefix' => $this->id,
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
        $dbTarget = new DbTarget();
        $dbTarget->logTable = '{{%podium_log}}';
        $dbTarget->categories = ['bizley\podium\*'];
        $dbTarget->logVars = [];

        $app->log->targets['podium'] = $dbTarget;
    }

    /**
     * Returns Podium cache instance.
     * @return PodiumCache
     * @since 0.5
     */
    public function getPodiumCache()
    {
        if (!$this->_cache) {
            $this->_cache = new PodiumCache();
        }
        return $this->_cache;
    }
    
    /**
     * Returns Podium configuration instance.
     * @return PodiumConfig
     * @since 0.5
     */
    public function getPodiumConfig()
    {
        if (!$this->_config) {
            $this->_config = new PodiumConfig();
        }
        return $this->_config;
    }

    /**
     * Returns Podium installation flag.
     * @return bool
     */
    public function getInstalled()
    {
        return Maintenance::check();
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
     * Appends module ID to the route.
     * @param string $route
     * @return string
     * @since 0.5
     */
    public function prepareRoute($route)
    {
        return '/' . $this->id . $route;
    }
    
    /**
     * Redirects to Podium main controller's action.
     * @return Response
     */
    public function goPodium()
    {
        return Yii::$app->response->redirect([$this->prepareRoute(self::ROUTE_DEFAULT)]);
    }

    /**
     * Initializes the module for Web application.
     * Sets Podium alias (@podium) and layout.
     * Registers user identity, authorization, translations, formatter, db, and cache.
     * Verifies the installation.
     */
    public function init()
    {
        parent::init();
        
        if ($this->userComponent !== true && !is_string($this->userComponent) && !is_array($this->userComponent)) {
            throw InvalidConfigException('Invalid value for the userComponent parameter.');
        }
        if ($this->rbacComponent !== true && !is_string($this->rbacComponent) && !is_array($this->rbacComponent)) {
            throw InvalidConfigException('Invalid value for the rbacComponent parameter.');
        }
        if ($this->formatterComponent !== true && !is_string($this->formatterComponent) && !is_array($this->formatterComponent)) {
            throw InvalidConfigException('Invalid value for the formatterComponent parameter.');
        }
        if (!is_string($this->dbComponent) && !is_array($this->dbComponent)) {
            throw InvalidConfigException('Invalid value for the dbComponent parameter.');
        }
        if ($this->cacheComponent !== false && !is_string($this->cacheComponent) && !is_array($this->cacheComponent)) {
            throw InvalidConfigException('Invalid value for the cacheComponent parameter.');
        }
        
        $this->setAliases(['@podium' => '@vendor/bizley/podium/src']);
        
        if ($this->loginUrl !== false) {
            $this->loginUrl = [$this->prepareRoute(self::ROUTE_LOGIN)];
        }
        if ($this->registerUrl !== false) {
            $this->registerUrl = [$this->prepareRoute(self::ROUTE_REGISTER)];
        }

        if (Yii::$app instanceof WebApplication) {
            $this->registerDbConnection();
            $this->registerIdentity();
            $this->registerCache();
            $this->registerAuthorization();
            $this->registerFormatter();
            $this->registerTranslations();

            $this->layout = self::MAIN_LAYOUT;
        }
    }

    /**
     * Returns instance of component of given name.
     * @param string $name
     * @return Component
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getComponent($name)
    {
        $configurationName = $name . 'Component';
        if (is_string($this->$configurationName)) {
            return Yii::$app->get($this->$configurationName);
        }
        return $this->get('podium_' . $name);
    }
    
    /**
     * Returns instance of RBAC component.
     * @return DbManager
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getRbac()
    {
        return $this->getComponent('rbac');
    }
    
    /**
     * Registers user authorization.
     */
    public function registerAuthorization()
    {
        if (is_string($this->rbacComponent)) {
            return;
        }
        
        $configuration = [
            'class' => 'yii\rbac\DbManager',
            'itemTable' => '{{%podium_auth_item}}',
            'itemChildTable' => '{{%podium_auth_item_child}}',
            'assignmentTable' => '{{%podium_auth_assignment}}',
            'ruleTable' => '{{%podium_auth_rule}}',
            'cache' => $this->cache
        ];
        if (is_array($this->rbacComponent)) {
            $configuration = $this->rbacComponent;
        }
        
        $this->set('podium_rbac', $configuration);
    }

    /**
     * Returns instance of formatter component.
     * @return Formatter
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getFormatter()
    {
        return $this->getComponent('formatter');
    }
    
    /**
     * Registers formatter with default time zone.
     */
    public function registerFormatter()
    {
        if (is_string($this->formatterComponent)) {
            return;
        }
        
        $configuration = [
            'class' => 'yii\i18n\Formatter',
            'timeZone' => 'UTC',
        ];
        if (is_array($this->formatterComponent)) {
            $configuration = $this->formatterComponent;
        }
        
        $this->set('podium_formatter', $configuration);
    }

    /**
     * Returns instance of user component.
     * @return User
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getUser()
    {
        return $this->getComponent('user');
    }
    
    /**
     * Registers user identity.
     */
    public function registerIdentity()
    {
        if (is_string($this->userComponent)) {
            return;
        }
        
        $configuration = [
            'class' => 'bizley\podium\web\User',
            'identityClass' => 'bizley\podium\models\User',
            'enableAutoLogin' => true,
            'loginUrl' => $this->loginUrl,
            'identityCookie' => [
                'name' => 'podium', 
                'httpOnly' => true,
                'secure' => $this->secureIdentityCookie,
            ],
            'idParam' => '__id_podium',
        ];
        if (is_array($this->userComponent)) {
            $configuration = $this->userComponent;
        }
        
        $this->set('podium_user', $configuration);
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
    
    /**
     * Returns instance of db component.
     * @return Connection
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getDb()
    {
        return $this->getComponent('db');
    }
    
    /**
     * Registers DB connection.
     * @since 0.5
     */
    public function registerDbConnection()
    {
        if (is_array($this->dbComponent)) {
            $this->set('podium_db', $this->dbComponent);
        }
    }
    
    /**
     * Returns instance of cache component.
     * @return Cache
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getCache()
    {
        return $this->getComponent('cache');
    }
    
    /**
     * Registers cache.
     * @since 0.5
     */
    public function registerCache()
    {
        if (is_string($this->cacheComponent)) {
            return;
        }
        
        $configuration = [
            'class' => 'yii\caching\DummyCache',
        ];
        if (is_array($this->cacheComponent)) {
            $configuration = $this->cacheComponent;
        }
        
        $this->set('podium_cache', $configuration);
    }
}
