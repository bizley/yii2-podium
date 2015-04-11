<?php

namespace bizley\podium\components;

use Exception;
use Yii;
use yii\db\Query;

class Config
{
    public $cache;
    protected static $_instance = false;
    protected $_defaults = [
        'name'    => 'Podium',
        'version' => '1.0'
    ];
    
    protected function __construct()
    {
        $this->cache = Cache::getInstance();
    }
    
    public static function getInstance()
    {
        if (self::$_instance === false) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }

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
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
    }
    
    public function all()
    {
        return $this->fromCache();
    }
    
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
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
        
        return $config;
    }
    
    public function get($name)
    {
        $config = $this->fromCache();
        return isset($config[$name]) ? $config[$name] : null;
    }
    
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
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
        
        return false;
    }
}