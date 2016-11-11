<?php

namespace bizley\podium;

use bizley\podium\components\Cache;
use bizley\podium\components\Config;
use bizley\podium\log\DbTarget;
use bizley\podium\maintenance\Installation;
use bizley\podium\models\Activity;
use Yii;
use yii\base\Action;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Module;
use yii\console\Application as ConsoleApplication;
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
 * @property Cache $cache
 * @property Config $config
 * @property Formatter $formatter
 * @property DbManager $rbac
 * @property User $user
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
     * Registers user identity, authorization, translations, and formatter.
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
        
        $this->setAliases(['@podium' => '@vendor/bizley/podium/src']);
        
        if ($this->loginUrl !== false) {
            $this->loginUrl = [$this->prepareRoute(self::ROUTE_LOGIN)];
        }
        if ($this->registerUrl !== false) {
            $this->registerUrl = [$this->prepareRoute(self::ROUTE_REGISTER)];
        }

        $this->registerAuthorization();
        
        if (Yii::$app instanceof WebApplication) {
            $this->registerIdentity();
            $this->registerFormatter();
            $this->registerTranslations();

            $this->layout = self::MAIN_LAYOUT;
            $this->_installed = Installation::check();
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
        $componentId = 'podium_' . $name;
        $configurationName = $name . 'Component';
        if (is_string($this->$configurationName)) {
            $componentId = $this->$configurationName;
        }
        return $this->get($componentId);
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
            'cache' => $this->cache->engine
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
}
