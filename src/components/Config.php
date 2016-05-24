<?php

namespace bizley\podium\components;

use bizley\podium\log\Log;
use Exception;
use Yii;
use yii\base\Object;
use yii\db\Query;

/**
 * Config Podium component.
 * Handles the module configuration.
 * Every default configuration value is saved in database first time when 
 * administrator saves Podium settings.
 * 
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.1
 * 
 * @property array $defaults
 * @property array $fromDb
 */
class Config extends Object
{
    const CURRENT_VERSION = '0.2';
    const DEFAULT_FROM_EMAIL = 'no-reply@change.me';
    const DEFAULT_FROM_NAME = 'Podium';
    const FLAG_USE_CAPTCHA = 1;
    const FLAG_MEMBERS_VISIBLE = 1;
    const HOT_MINIMUM = 20;
    const MAINTENANCE_MODE = 0;
    const MAX_SEND_ATTEMPTS = 5;
    const META_DESCRIPTION = 'Podium - Yii 2 Forum Module';
    const META_KEYWORDS = 'yii2, forum, podium';
    const PODIUM_NAME = 'Podium';
    const SECONDS_ACTIVATION_TOKEN_EXPIRE = 259200;
    const SECONDS_EMAIL_TOKEN_EXPIRE = 86400;
    const SECONDS_PASSWORD_RESET_TOKEN_EXPIRE = 86400;
    
    /**
     * @var Cache cache object instance
     */
    public $cache;
    
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
            'name'                        => self::PODIUM_NAME,
            'version'                     => self::CURRENT_VERSION,
            'hot_minimum'                 => self::HOT_MINIMUM,
            'members_visible'             => self::FLAG_MEMBERS_VISIBLE,
            'from_email'                  => self::DEFAULT_FROM_EMAIL,
            'from_name'                   => self::DEFAULT_FROM_NAME,
            'maintenance_mode'            => self::MAINTENANCE_MODE,
            'max_attempts'                => self::MAX_SEND_ATTEMPTS,
            'use_captcha'                 => self::FLAG_USE_CAPTCHA,
            'recaptcha_sitekey'           => '',
            'recaptcha_secretkey'         => '',
            'password_reset_token_expire' => self::SECONDS_PASSWORD_RESET_TOKEN_EXPIRE,
            'email_token_expire'          => self::SECONDS_EMAIL_TOKEN_EXPIRE,
            'activation_token_expire'     => self::SECONDS_ACTIVATION_TOKEN_EXPIRE,
            'meta_keywords'               => self::META_KEYWORDS,
            'meta_description'            => self::META_DESCRIPTION,
        ];
    }
    
    /**
     * Alias for fromCache().
     * @return array
     */
    public function all()
    {
        return $this->fromCache();
    }
    
    /**
     * Returns all configuration values from cache.
     * If cache is empty this merges default values with the ones stored in 
     * database and saves it to cache.
     * @return array
     */
    public function fromCache()
    {
        try {
            $cache = $this->cache->get('config');
            if ($cache === false) {
                $cache = array_merge($this->defaults, $this->fromDb);
                $this->cache->set('config', $cache);
            }
            return $cache;
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
    
    /**
     * Returns configuration value of the given name from cache.
     * @param string $name configuration name
     * @return string|null
     */
    public function get($name)
    {
        $config = $this->fromCache();
        return isset($config[$name]) ? $config[$name] : null;
    }
    
    /**
     * Returns all configuration values from database.
     * @return array
     */
    public function getFromDb()
    {
        $config = [];
        try {
            $query = (new Query)->from(static::tableName())->all();
            foreach ($query as $setting) {
                $config[$setting['name']] = $setting['value'];
            }
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return $config;
    }
    
    /**
     * Sets configuration value of the given name.
     * Every change automatically updates the cache.
     * @param string $name configuration name
     * @param string $value configuration value
     * @return boolean
     */
    public function set($name, $value)
    {
        try {
            if (is_string($name) && is_string($value)) {
                if ($value == '') {
                    if (array_key_exists($name, $this->defaults)) {
                        $value = $this->defaults[$name];
                    }
                }
                if ((new Query)->from(static::tableName())->where(['name' => $name])->exists()) {
                    Yii::$app
                        ->db
                        ->createCommand()
                        ->update(
                            static::tableName(), 
                            ['value' => $value], 
                            'name = :name', 
                            [':name' => $name]
                        )
                        ->execute();
                } else {
                    Yii::$app
                        ->db
                        ->createCommand()
                        ->insert(
                            static::tableName(), 
                            ['name' => $name, 'value' => $value]
                        )
                        ->execute();
                }
                $this->cache->set(
                                'config', 
                                array_merge($this->defaults, $this->fromDb)
                            );
                return true;
            }      
        } catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        return false;
    }
}
