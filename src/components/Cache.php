<?php

/**
 * Podium Module
 * Yii 2 Forum Module
 */
namespace bizley\podium\components;

use Exception;
use yii\caching\Cache as DefaultCache;
use yii\caching\DummyCache;
use yii\di\Instance;
use yii\widgets\FragmentCache;

/**
 * Cache helper
 * Handles the cache component. If cache component is not set in configuration 
 * [[\yii\caching\DummyCache|DummyCache]] is used instead.
 * Every Podium cache element is prefixed with 'podium.' automatically.
 * This helper also allows to operate on single array key of cached elements and views content.
 * 
 * List of keys:
 * config             => Podium configuration
 * forum.lastactive   => number of last active users
 * forum.latestposts  => 5 latest forum posts
 * forum.memberscount => number of activated and banned users
 * forum.moderators   => moderators for each forum
 * forum.threadscount => number of forum threads
 * forum.postscount   => number of forum posts
 * members.fieldlist  => list of active users w/pages
 * user.newmessages   => list of users' new messages count
 * user.postscount    => list of users' posts count
 * user.subscriptions => list of users' subscribed threads with new posts count
 * user.votes.ID      => user's votes per hour
 * 
 * @author Paweł Bizley Brzozowski <pb@human-device.com>
 * @since 0.1
 */
class Cache
{
    /**
     * @var string name of the cache component
     */
    public $cache = 'cache';
    
    /**
     * @var string Podium cache element prefix.
     * This prefix is automatically added to every element.
     */
    protected $_cachePrefix = 'podium.';
    
    /**
     * @var boolean|Cache instance of Cache object
     */
    protected static $_instance = false;
    
    /**
     * Singleton construct.
     */
    protected function __construct()
    {
        $this->init();
    }
    
    /**
     * Begins [[FragmentCache]] widget.
     * Usage:
     * if (Cache::getInstance()->beginCache('key', $this)) {
     *      $this->endCache();
     * }
     * @param string $key the key identifying the content to be cached.
     * @param \yii\base\View $view view object
     * @param integer $duration number of seconds that the data can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @return boolean
     */
    public function beginCache($key, $view, $duration = 60)
    {
        $properties['id']       = $this->_cachePrefix . $key;
        $properties['view']     = $view;
        $properties['duration'] = $duration;

        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->endCache();
            return false;
        } else {
            return true;
        }
    }
    
    public static function clearAfterActivate()
    {
        $cache = static::getInstance();
        $cache->delete('members.fieldlist');
        $cache->delete('forum.memberscount');
    }
    
    /**
     * Deletes the value with the specified key from cache
     * @param string $key the key identifying the value to be deleted from cache.
     * @return boolean
     */
    public function delete($key)
    {
        return $this->cache->delete($this->_cachePrefix . $key);
    }
    
    /**
     * Deletes the value of element with the specified key from cache array.
     * @param string $key a key identifying the value to be deleted from cache.
     * @param string $element a key of the element.
     * @return boolean
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
    
    /**
     * Ends [[FragmentCache]] widget.
     */
    public function endCache()
    {
        FragmentCache::end();
    }
    
    /**
     * Flushes all cache.
     */
    public function flush()
    {
        return $this->cache->flush();
    }
    
    /**
     * Retrieves the value from cache with the specified key.
     * @param string $key the key identifying the cached value.
     * @return mixed the value stored in cache, false if the value is not in the cache, expired,
     * or the dependency associated with the cached data has changed.
     */
    public function get($key)
    {
        return $this->cache->get($this->_cachePrefix . $key);
    }
    
    /**
     * Retrieves the value of element from array cache with the specified key.
     * @param string $key the key identifying the cached value.
     * @param string $element the key of the element.
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
     * Initialises component.
     * If cache is not set in configuration [[DummyCache]] is used instead.
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
     * Stores the value identified by the key into cache.
     * @param string $key the key identifying the value to be cached.
     * @param mixed $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean
     */
    public function set($key, $value, $duration = 0)
    {
        return $this->cache->set($this->_cachePrefix . $key, $value, $duration);
    }
    
    /**
     * Stores the value for the element into cache array identified by the key.
     * @param string $key the key identifying the value to be cached.
     * @param string $element the key of the element.
     * @param mixed $value the value to be cached
     * @param integer $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return boolean
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
}