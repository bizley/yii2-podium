<?php

namespace bizley\podium;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\base\Module;

/**
 * Podium component service.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.6
 */
class PodiumComponent extends Component
{
    /**
     * @var Module
     */
    public $module;


    /**
     * Allows direct reference to parent module.
     * @param Module $module
     * @param array $config
     */
    public function __construct($module, $config = [])
    {
        parent::__construct($config);
        $this->module = $module;
    }

    /**
     * Registers required components.
     */
    public function registerComponents()
    {
        $this->registerDbConnection();
        $this->registerIdentity();
        $this->registerCache();
        $this->registerAuthorization();
        $this->registerFormatter();
        $this->registerTranslations();
    }

    /**
     * Registers required console components.
     */
    public function registerConsoleComponents()
    {
        $this->registerDbConnection();
        $this->registerCache();
        $this->registerAuthorization();
    }

    /**
     * Returns instance of component of given name.
     * @param string $name
     * @return Component
     * @throws InvalidConfigException
     */
    public function getComponent($name)
    {
        $configurationName = $name . 'Component';
        if (is_string($this->module->$configurationName)) {
            return Yii::$app->get($this->module->$configurationName);
        }
        return $this->module->get('podium_' . $name);
    }

    /**
     * Registers user authorization.
     */
    public function registerAuthorization()
    {
        if ($this->module->rbacComponent !== true
                && !is_string($this->module->rbacComponent)
                && !is_array($this->module->rbacComponent)) {
            throw InvalidConfigException('Invalid value for the rbacComponent parameter.');
        }
        if (is_string($this->module->rbacComponent)) {
            return;
        }
        $this->module->set('podium_rbac', is_array($this->module->rbacComponent)
                ? $this->module->rbacComponent
                : [
                    'class' => 'yii\rbac\DbManager',
                    'db' => $this->module->db,
                    'itemTable' => '{{%podium_auth_item}}',
                    'itemChildTable' => '{{%podium_auth_item_child}}',
                    'assignmentTable' => '{{%podium_auth_assignment}}',
                    'ruleTable' => '{{%podium_auth_rule}}',
                    'cache' => $this->module->cache
                ]);
    }

    /**
     * Registers formatter with default time zone.
     */
    public function registerFormatter()
    {
        if ($this->module->formatterComponent !== true
                && !is_string($this->module->formatterComponent)
                && !is_array($this->module->formatterComponent)) {
            throw InvalidConfigException('Invalid value for the formatterComponent parameter.');
        }
        if (is_string($this->module->formatterComponent)) {
            return;
        }
        $this->module->set('podium_formatter', is_array($this->module->formatterComponent)
                ? $this->module->formatterComponent
                : [
                    'class' => 'yii\i18n\Formatter',
                    'timeZone' => 'UTC',
                ]);
    }

    /**
     * Registers user identity.
     */
    public function registerIdentity()
    {
        if ($this->module->userComponent !== true
                && !is_string($this->module->userComponent)
                && !is_array($this->module->userComponent)) {
            throw InvalidConfigException('Invalid value for the userComponent parameter.');
        }
        if (is_string($this->module->userComponent)) {
            return;
        }
        $this->module->set('podium_user', is_array($this->module->userComponent)
                ? $this->module->userComponent
                : [
                    'class' => 'bizley\podium\web\User',
                    'identityClass' => 'bizley\podium\models\User',
                    'enableAutoLogin' => true,
                    'loginUrl' => $this->module->loginUrl,
                    'identityCookie' => [
                        'name' => 'podium',
                        'httpOnly' => true,
                        'secure' => $this->module->secureIdentityCookie,
                    ],
                    'idParam' => '__id_podium',
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

    /**
     * Registers DB connection.
     */
    public function registerDbConnection()
    {
        if (!is_string($this->module->dbComponent)
                && !is_array($this->module->dbComponent)) {
            throw InvalidConfigException('Invalid value for the dbComponent parameter.');
        }
        if (is_array($this->module->dbComponent)) {
            $this->module->set('podium_db', $this->module->dbComponent);
        }
    }

    /**
     * Registers cache.
     */
    public function registerCache()
    {
        if ($this->module->cacheComponent !== false
                && !is_string($this->module->cacheComponent)
                && !is_array($this->module->cacheComponent)) {
            throw InvalidConfigException('Invalid value for the cacheComponent parameter.');
        }
        if (is_string($this->module->cacheComponent)) {
            return;
        }
        $this->module->set('podium_cache', is_array($this->module->cacheComponent)
            ? $this->module->cacheComponent
            : ['class' => 'yii\caching\DummyCache']);
    }
}
