<?php

namespace bizley\podium\components;

use Exception;
use yii\caching\Cache as DefaultCache;
use yii\caching\DummyCache;
use yii\di\Instance;
use yii\widgets\FragmentCache;

/**
 * Cache helper
 * If cache component is not set in configuration it uses \yii\caching\DummyCache
 * 
 * List of keys:
 * config => Podium configuration
 * members.fieldlist => list of active users w/pages
 * user.newmessages => list of users' new messages count
 * forum.lastactive => number of last active users
 * forum.memberscount => number of activated and banned users
 * forum.threadscount => number of forum threads
 * forum.postscount => number of forum posts
 * user.votes.ID => user's votes per hour
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
    
    /**
     * Retrieves a value from cache with a specified key.
     * @param string $key a key identifying the cached value.
     * @return mixed the value stored in cache, false if the value is not in the cache, expired,
     * or the dependency associated with the cached data has changed.
     */
    public function get($key)
    {
        return $this->cache->get($this->_cachePrefix . $key);
    }
    
    /**
     * Stores a value identified by a key into cache.
     * @param string $key a key identifying the value to be cached.
     * @param mixed $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean whether the value is successfully stored into cache
     */
    public function set($key, $value, $duration = 0)
    {
        return $this->cache->set($this->_cachePrefix . $key, $value, $duration);
    }
    
    /**
     * Deletes a value with the specified key from cache
     * @param string $key a key identifying the value to be deleted from cache.
     * @return boolean if no error happens during deletion
     */
    public function delete($key)
    {
        return $this->cache->delete($this->_cachePrefix . $key);
    }
    
    /**
     * Retrieves a value of element from array cache with a specified key.
     * @param string $key a key identifying the cached value.
     * @param string $element a key of the element.
     * @return mixed the value of element stored in cache array, false if the value is not in the cache, expired,
     * array key does not exist or the dependency associated with the cached data has changed.
     */
    public function getElement($key, $element)
    {
        $cache = $this->get($key);
        if ($cache !== false && isset($cache[$element])) {
            return $cache[$element];
        }
        return false;
    }
    
    /**
     * Stores a value for the element into cache array identified by a key.
     * @param string $key a key identifying the value to be cached.
     * @param string $element a key of the element.
     * @param mixed $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean whether the value is successfully stored into cache
     */
    public function setElement($key, $element, $value, $duration = 0)
    {
        $cache = $this->get($key);
        if ($cache === false) {
            $cache = [];
        }
        $cache[$element] = $value;
        return $this->set($key, $cache, $duration);
    }
    
    /**
     * Deletes a value of element with the specified key from cache array.
     * @param string $key a key identifying the value to be deleted from cache.
     * @param string $element a key of the element.
     * @return boolean if no error happens during deletion
     */
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