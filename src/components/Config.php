<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use Exception;
use Yii;
use yii\db\Query;

/**
 * Config helper
 * Handles the module configuration.
 * Every default configuration value is saved in database first time when 
 * administrator saves Podium settings.
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Config
{
    /**
     * @var Cache cache object instance
     */
    public $cache;
    
    /**
     * @var array configuration defaults.
     * Those values are stored in cached configuration but saved only when 
     * administrator saves Podium settings.
     */
    protected $_defaults = [
        'name'                        => 'Podium',
        'version'                     => '0.1',
        'hot_minimum'                 => 20,
        'members_visible'             => 1,
        'from_email'                  => 'no-reply@change.me',
        'from_name'                   => 'Podium',
        'max_attempts'                => 5,
        'use_captcha'                 => 1,
        'recaptcha_sitekey'           => '',
        'recaptcha_secretkey'         => '',
        'password_reset_token_expire' => 86400,
        'email_token_expire'          => 86400,
        'activation_token_expire'     => 259200,
        'meta_keywords'               => 'yii2, forum, podium',
        'meta_description'            => 'Podium - Yii 2 Forum Module',
    ];
    
    /**
     * @var boolean|Config configuration object instance
     */
    protected static $_instance = false;
    
    /**
     * Singleton construct.
     */
    protected function __construct()
    {
        $this->cache = Cache::getInstance();
    }
    
    /**
     * Alias for [[fromCache()]].
     * @return array
     */
    public function all()
    {
        return $this->fromCache();
    }
    
    /**
     * Gets all configuration values from cache.
     * If cache is empty this merges default values with the ones stored in database 
     * and saves it to cache.
     * @return array
     */
    public function fromCache()
    {
        try {
            $cache = $this->cache->get('config');

            if ($cache === false) {
                $cache = array_merge($this->_defaults, $this->getFromDb());
                $this->cache->set('config', $cache);
            }

            return $cache;
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
    }
    
    /**
     * Gets configuration value of the given name from cache.
     * @param string $name configuration name
     * @return string|null
     */
    public function get($name)
    {
        $config = $this->fromCache();
        return isset($config[$name]) ? $config[$name] : null;
    }
    
    /**
     * Gets all configuration values from database.
     * @return array
     */
    public function getFromDb()
    {
        $config = [];
        try {
            $query = (new Query)->from('{{%podium_config}}')->all();
            foreach ($query as $setting) {
                $config[$setting['name']] = $setting['value'];
            }
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        
        return $config;
    }
    
    /**
     * Calls for Cache instance.
     * @return Cache
     */
    public static function getInstance()
    {
        if (self::$_instance === false) {
            self::$_instance = new self;
        }
        return self::$_instance;
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
                    if (array_key_exists($name, $this->_defaults)) {
                        $value = $this->_defaults[$name];
                    }
                }
                
                if ((new Query)->from('{{%podium_config}}')->where(['name' => $name])->exists()) {
                    Yii::$app->db->createCommand()->update('{{%podium_config}}', ['value' => $value], 'name = :name', [':name' => $name])->execute();
                }
                else {
                    Yii::$app->db->createCommand()->insert('{{%podium_config}}', ['name' => $name, 'value' => $value])->execute();
                }
                
                $this->cache->set('config', array_merge($this->_defaults, $this->getFromDb()));
                
                return true;
            }      
        }
        catch (Exception $e) {
            Log::error($e->getMessage(), null, __METHOD__);
        }
        
        return false;
    }
}