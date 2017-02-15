<?php

namespace bizley\podium;

use bizley\podium\db\Query;
use bizley\podium\log\Log;
use bizley\podium\Podium;
use Exception;
use yii\base\Component;
use yii\caching\Cache;

/**
 * Podium configuration component.
 * Handles the module configuration.
 * Every default configuration value is saved in database first time when
 * administrator saves Podium settings.
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 *
 * @property Cache $cache
 * @property array $defaults
 * @property array $all
 * @property array $cached
 * @property array $notCached
 * @property array $stored
 */
class PodiumConfig extends Component
{
    const DEFAULT_FROM_EMAIL = 'no-reply@change.me';
    const DEFAULT_FROM_NAME = 'Podium';
    const FLAG_ALLOW_POLLS = 1;
    const FLAG_MEMBERS_VISIBLE = 1;
    const FLAG_MERGE_POSTS = 1;
    const FLAG_USE_CAPTCHA = 1;
    const FLAG_USE_WYSIWYG = 1;
    const HOT_MINIMUM = 20;
    const MAINTENANCE_MODE = 0;
    const MAX_SEND_ATTEMPTS = 5;
    const META_DESCRIPTION = 'Podium - Yii 2 Forum Module';
    const META_KEYWORDS = 'yii2, forum, podium';
    const PODIUM_NAME = 'Podium';
    const REGISTRATION_OFF = 0;
    const SECONDS_ACTIVATION_TOKEN_EXPIRE = 259200;
    const SECONDS_EMAIL_TOKEN_EXPIRE = 86400;
    const SECONDS_PASSWORD_RESET_TOKEN_EXPIRE = 86400;

    /**
     * Returns configuration table name.
     * @return string
     * @since 0.2
     */
    public static function tableName()
    {
        return '{{%podium_config}}';
    }

    /**
     * Returns list of default configuration values.
     * These values are stored in cached configuration but saved only when
     * administrator saves Podium settings.
     * @return array
     * @since 0.2
     */
    public function getDefaults()
    {
        return [
            'activation_token_expire' => self::SECONDS_ACTIVATION_TOKEN_EXPIRE,
            'allow_polls' => self::FLAG_ALLOW_POLLS,
            'email_token_expire' => self::SECONDS_EMAIL_TOKEN_EXPIRE,
            'from_email' => self::DEFAULT_FROM_EMAIL,
            'from_name' => self::DEFAULT_FROM_NAME,
            'hot_minimum' => self::HOT_MINIMUM,
            'maintenance_mode' => self::MAINTENANCE_MODE,
            'max_attempts' => self::MAX_SEND_ATTEMPTS,
            'members_visible' => self::FLAG_MEMBERS_VISIBLE,
            'merge_posts' => self::FLAG_MERGE_POSTS,
            'meta_description' => self::META_DESCRIPTION,
            'meta_keywords' => self::META_KEYWORDS,
            'name' => self::PODIUM_NAME,
            'password_reset_token_expire' => self::SECONDS_PASSWORD_RESET_TOKEN_EXPIRE,
            'recaptcha_secretkey' => '',
            'recaptcha_sitekey' => '',
            'registration_off' => self::REGISTRATION_OFF,
            'use_captcha' => self::FLAG_USE_CAPTCHA,
            'use_wysiwyg' => self::FLAG_USE_WYSIWYG,
            'version' => Podium::getInstance()->version,
        ];
    }

    /**
     * Returns Podium cache instance.
     * @return Cache
     * @since 0.5
     */
    public function getCache()
    {
        return Podium::getInstance()->podiumCache;
    }

    private $_config;

    /**
     * Returns configuration values.
     * @return array
     * @since 0.6
     */
    public function getAll()
    {
        if ($this->_config !== null) {
            return $this->_config;
        }
        try {
            $this->_config = $this->cached;
        } catch (Exception $exc) {
            Log::warning($exc->getMessage(), null, __METHOD__);
            $this->_config = $this->stored;
        }
        return $this->_config;
    }

    /**
     * Returns cached configuration values.
     * @return array
     * @throws Exception
     * @since 0.6
     */
    public function getCached()
    {
        $cache = $this->cache->get('config');
        if ($cache === false) {
            $cache = $this->notCached;
            $this->cache->set('config', $cache);
        }
        return $cache;
    }

    /**
     * Returns not cached configuration values.
     * If stored configuration is empty default values are returned.
     * @return array
     * @since 0.6
     */
    public function getNotCached()
    {
        return array_merge($this->defaults, $this->stored);
    }

    /**
     * Returns stored configuration values.
     * These can be empty if configuration has not been modified.
     * @return array
     * @since 0.6
     */
    public function getStored()
    {
        $stored = [];
        try {
            $query = (new Query())->from(static::tableName())->all();
            if (!empty($query)) {
                foreach ($query as $setting) {
                    $stored[$setting['name']] = $setting['value'];
                }
            }
        } catch (Exception $e) {
            if (Podium::getInstance()->getInstalled()) {
                Log::error($e->getMessage(), null, __METHOD__);
            }
        }
        return $stored;
    }

    /**
     * Returns configuration value of the given name.
     * @param string $name configuration key
     * @return string|null
     */
    public function get($name)
    {
        $config = $this->all;
        return isset($config[$name]) ? $config[$name] : null;
    }

    /**
     * Sets configuration value of the given name.
     * Every change automatically updates the cache.
     * Set value to null to restore default one.
     * @param string $name configuration key
     * @param string $value configuration value
     * @return bool
     */
    public function set($name, $value)
    {
        try {
            if (is_string($name) && (is_string($value) || $value === null)) {
                if ($value === null) {
                    if (!array_key_exists($name, $this->defaults)) {
                        return false;
                    }
                    $value = $this->defaults[$name];
                }
                if ((new Query())->from(static::tableName())->where(['name' => $name])->exists()) {
                    Podium::getInstance()->db->createCommand()->update(
                        static::tableName(), ['value' => $value], ['name' => $name]
                    )->execute();
                } else {
                    Podium::getInstance()->db->createCommand()->insert(
                        static::tableName(), ['name' => $name, 'value' => $value]
                    )->execute();
                }
                $this->cache->set('config', $this->notCached);
                $this->_config = null;
                return true;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }

    /**
     * Alias for getAll().
     * @return array
     */
    public function all()
    {
        return $this->all;
    }
}
