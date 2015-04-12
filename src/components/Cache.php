<?php

namespace bizley\podium\components;

use Exception;
use yii\caching\Cache as DefaultCache;
use yii\caching\DummyCache;
use yii\di\Instance;
use yii\widgets\FragmentCache;

/**
 * Cache helper
 * 
 * List of keys:
 * config => Podium configuration
 * members.fieldlist => list of active users w/pages
 * user.newmessages => list of users' new messages count
 * forum.lastactive => number of last active users
 * forum.memberscount => number of activated and banned users
 * forum.threadscount => number of forum threads
 * forum.postscount => number of forum posts
 */
class Cache
{
    public $cache = 'cache';
    protected $_cachePrefix = 'podium.';
    protected static $_instance = false;
    
    protected function __construct()
    {
        $this->init();
    }
    
    public static function getInstance()
    {
        if (self::$_instance === false) {
            self::$_instance = new Cache();
        }
        return self::$_instance;
    }

    /**
     * Initialise component.
     */
    public function init()
    {
        try {
            $this->cache = Instance::ensure($this->cache, DefaultCache::className());
        }
        catch (Exception $e) {
            $this->cache = new DummyCache();
        }
    }
    
    public function get($key)
    {
        return $this->cache->get($this->_cachePrefix . $key);
    }
    
    public function set($key, $value, $duration = 0)
    {
        return $this->cache->set($this->_cachePrefix . $key, $value, $duration);
    }
    
    public function delete($key)
    {
        return $this->cache->delete($this->_cachePrefix . $key);
    }
    
    public function getElement($key, $element)
    {
        $cache = $this->get($key);
        if ($cache !== false && isset($cache[$element])) {
            return $cache[$element];
        }
        return false;
    }
    
    public function setElement($key, $element, $value, $duration = 0)
    {
        $cache = $this->get($key);
        if ($cache === false) {
            $cache = [];
        }
        $cache[$element] = $value;
        return $this->set($key, $cache, $duration);
    }
    
    public function deleteElement($key, $element)
    {
        $cache = $this->get($key);
        if ($cache !== false && isset($cache[$element])) {
            unset($cache[$element]);
            return $this->set($key, $cache);
        }
        return true;
    }
    
    public function beginCache($key, $view, $duration = 60)
    {
        $properties['id'] = $this->_cachePrefix . $key;
        $properties['view'] = $view;
        $properties['duration'] = $duration;

        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->endCache();
            return false;
        } else {
            return true;
        }
    }

    public function endCache()
    {
        FragmentCache::end();
    }
}