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
 * @version 0.7 (beta)
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
 * @property Formatter $formatter
 * @property DbManager $rbac
 * @property User $user
 * @property Connection $db
 * @property Cache $cache
 * @property boolean $installed
 * @property string $version
 * @property PodiumComponent $podiumComponent
 * @property array $loginUrl
 * @property array $registerUrl
 */
class Podium extends Module implements BootstrapInterface
{
    /**
     * @var string Module version.
     */
    protected $_version = '0.7';

    /**
     * @var int Admin account ID if $userComponent is not set to true.
     */
    public $adminId;

    /**
     * @var array the list of IPs that are allowed to access installation mode
     * of this module. Each array element represents a single IP filter which
     * can be either an IP address or an address with wildcard
     * (e.g. `192.168.0.*`) to represent a network segment.
     * The default value is `['127.0.0.1', '::1']`, which means the module can
     * only be accessed by localhost.
     */
    public $allowedIPs = ['127.0.0.1', '::1'];

    /**
     * @var bool|string|array Module user component.
     * Since version 0.5 it can be:
     * - true for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at registerIdentity() to see
     *   what Podium uses).
     */
    public $userComponent = true;

    /**
     * @var bool|string|array Module RBAC component.
     * Since version 0.5 it can be:
     * - true for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at registerAuthorization() to
     *   see what Podium uses).
     */
    public $rbacComponent = true;

    /**
     * @var bool|string|array Module formatter component.
     * It can be:
     * - true for own Podium component configuration,
     * - string with inherited component ID,
     * - array with custom configuration (look at registerFormatter() to
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
     * - false for not using cache,
     * - string with inherited component ID,
     * - array with custom configuration.
     * @since 0.5
     */
    public $cacheComponent = false;

    /**
     * @var string Module inherited user password_hash field.
     * This will be used for profile updating confirmation.
     * Default value is 'password_hash'.
     */
    public $userPasswordField = 'password_hash';

    /**
     * @var string Default route for Podium.
     * @since 0.2
     */
    public $defaultRoute = 'forum';

    /**
     * @var bool Value of identity Cookie 'secure' parameter.
     * @since 0.2
     */
    public $secureIdentityCookie = false;

    /**
     * @var callable Callback that will be called to determine the type of
     * Podium access for user.
     * The signature of the callback should be as follows:
     *      function ($user)
     * where $user is the user component.
     * The callback should return an integer value indicating access type.
     *  1 => member access
     *  0 => guest access
     * -1 => no access
     * @since 0.6
     */
    public $accessChecker;

    /**
     * @var callable Callback that will be called in case Podium access has been
     * denied for user.
     * The signature of the callback should be as follows:
     *      function ($user)
     * where $user is the user component.
     * @since 0.6
     */
    public $denyCallback;


    /**
     * Initializes the module for Web application.
     * Sets Podium alias (@podium) and layout.
     * Registers user identity, authorization, translations, formatter, db, and cache.
     */
    public function init()
    {
        parent::init();
        $this->setAliases(['@podium' => '@vendor/bizley/podium/src']);
        if (Yii::$app instanceof WebApplication) {
            $this->podiumComponent->registerComponents();
            $this->layout = 'main';
        } else {
            $this->podiumComponent->registerConsoleComponents();
        }
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
     * Adds UrlManager rules.
     * @param Application $app the application currently running
     * @since 0.2
     */
    protected function addUrlManagerRules($app)
    {
        $app->urlManager->addRules([new GroupUrlRule([
                'prefix' => $this->id,
                'rules' => require(__DIR__ . '/url-rules.php'),
            ])], true);
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

    private $_cache;

    /**
     * Returns Podium cache instance.
     * @return PodiumCache
     * @since 0.5
     */
    public function getPodiumCache()
    {
        if ($this->_cache === null) {
            $this->_cache = new PodiumCache();
        }
        return $this->_cache;
    }

    private $_config;

    /**
     * Returns Podium configuration instance.
     * @return PodiumConfig
     * @since 0.5
     */
    public function getPodiumConfig()
    {
        if ($this->_config === null) {
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
        return '/' . $this->id . (substr($route, 0, 1) === '/' ? '' : '/') . $route;
    }

    /**
     * Redirects to Podium main controller's action.
     * @return Response
     */
    public function goPodium()
    {
        return Yii::$app->response->redirect([$this->prepareRoute('forum/index')]);
    }

    /**
     * Returns login URL.
     * @return array|null
     * @since 0.6
     */
    public function getLoginUrl()
    {
        if ($this->userComponent !== true) {
            return null;
        }
        return [$this->prepareRoute('account/login')];
    }

    /**
     * Returns registration URL.
     * @return array|null
     * @since 0.6
     */
    public function getRegisterUrl()
    {
        if ($this->userComponent !== true) {
            return null;
        }
        return [$this->prepareRoute('account/register')];
    }

    private $_component;

    /**
     * Returns Podium component service.
     * @return PodiumComponent
     * @since 0.6
     */
    public function getPodiumComponent()
    {
        if ($this->_component === null) {
            $this->_component = new PodiumComponent($this);
        }
        return $this->_component;
    }

    /**
     * Returns instance of RBAC component.
     * @return DbManager
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getRbac()
    {
        return $this->podiumComponent->getComponent('rbac');
    }

    /**
     * Returns instance of formatter component.
     * @return Formatter
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getFormatter()
    {
        return $this->podiumComponent->getComponent('formatter');
    }

    /**
     * Returns instance of user component.
     * @return User
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getUser()
    {
        return $this->podiumComponent->getComponent('user');
    }

    /**
     * Returns instance of db component.
     * @return Connection
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getDb()
    {
        return $this->podiumComponent->getComponent('db');
    }

    /**
     * Returns instance of cache component.
     * @return Cache
     * @throws InvalidConfigException
     * @since 0.5
     */
    public function getCache()
    {
        return $this->podiumComponent->getComponent('cache');
    }
}
