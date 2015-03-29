<?php

namespace bizley\podium\components;

use Exception;
use Yii;
use yii\base\Component;
use yii\caching\Cache;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\di\Instance;

class Config extends Component
{
    
    public $cache = 'cache';
    protected $_cacheKey = 'podium.config';
    protected static $_instance = false;
    
    protected function __construct($config = [])
    {
        parent::__construct($config);
    }
    
    public static function getInstance()
    {
        if (self::$_instance === false) {
            self::$_instance = new Config();
        }
        return self::$_instance;
    }

    /**
     * Initialise component.
     */
    public function init()
    {
        parent::init();

        $this->cache = Instance::ensure($this->cache, Cache::className());
    }
    
    public function fromCache()
    {
        try {
            $cache = $this->cache->get($this->_cacheKey);

            if ($cache === false) {
                $cache = $this->getFromDb();
                $this->cache->set($this->_cacheKey, $cache);
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
        return (new Query)->from('{{%podium_config}}')->all();
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

                $queryBuilder = new QueryBuilder(Yii::$app->db);

                if ((new Query)->from('{{%podium_config}}')->where(['name' => $name])->exists()) {
                    Yii::$app->db->createCommand($queryBuilder->update('{{%podium_config}}', ['value' => $value], ['name' => $name]))->execute();
                }
                else {
                    Yii::$app->db->createCommand($queryBuilder->insert('{{%podium_config}}', ['name' => $name, 'value' => $value]))->execute();
                }
                
                $this->cache->set($this->_cacheKey, $this->getFromDb());
                
                return true;
            }      
        }
        catch (Exception $e) {
            Yii::trace([$e->getName(), $e->getMessage()], __METHOD__);
        }
        
        return false;
    }
}