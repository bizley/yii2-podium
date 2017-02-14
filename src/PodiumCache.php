<?php

namespace bizley\podium;

use bizley\podium\models\User;
use yii\base\InvalidConfigException;
use yii\base\Object;
use yii\base\View;
use yii\caching\Cache;
use yii\di\Instance;
use yii\widgets\FragmentCache;

/**
 * Podium Cache helper
 *
 * Handles the cache component. If cache component is not set in configuration
 * \yii\caching\DummyCache is used instead.
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
 * user.friends       => list of users friends
 * user.newmessages   => list of users new messages count
 * user.postscount    => list of users posts count
 * user.subscriptions => list of users subscribed threads with new posts count
 * user.threadscount  => list of users threads count
 * user.votes.ID      => user's votes per hour
 *
 * @author Paweł Bizley Brzozowski <pawel@positive.codes>
 * @since 0.5
 *
 * @property Cache $engine
 */
class PodiumCache extends Object
{
    /**
     * @var string Podium cache element prefix.
     * This prefix is automatically added to every element.
     */
    protected $_cachePrefix = 'podium.';

    /**
     * Returns cache engine.
     * @return Cache
     * @throws InvalidConfigException
     * @since 0.2
     */
    public function getEngine()
    {
        return Instance::ensure(Podium::getInstance()->cache, Cache::className());
    }

    /**
     * Begins FragmentCache widget.
     * Usage:
     * if (Podium::getInstance()->cache->beginCache('key', $this)) {
     *      $this->endCache();
     * }
     * @param string $key the key identifying the content to be cached.
     * @param View $view view object
     * @param int $duration number of seconds that the data can remain valid in cache.
     * Use 0 to indicate that the cached data will never expire.
     * @return bool
     * @since 0.2
     */
    public function begin($key, $view, $duration = 60)
    {
        $properties['id'] = $this->_cachePrefix . $key;
        $properties['view'] = $view;
        $properties['duration'] = $duration;

        $cache = FragmentCache::begin($properties);
        if ($cache->getCachedContent() !== false) {
            $this->end();
            return false;
        }
        return true;
    }

    /**
     * Clears several elements at once.
     * @param string $what action identifier
     * @since 0.2
     */
    public static function clearAfter($what)
    {
        $cache = new static;

        switch ($what) {
            case 'userDelete':
                $cache->delete('forum.latestposts');
                // no break
            case 'activate':
                $cache->delete('members.fieldlist');
                $cache->delete('forum.memberscount');
                break;
            case 'categoryDelete':
            case 'forumDelete':
            case 'threadDelete':
            case 'postDelete':
                $cache->delete('forum.threadscount');
                $cache->delete('forum.postscount');
                $cache->delete('user.threadscount');
                $cache->delete('user.postscount');
                $cache->delete('forum.latestposts');
                break;
            case 'threadMove':
            case 'postMove':
                $cache->delete('forum.threadscount');
                $cache->delete('forum.postscount');
                $cache->delete('forum.latestposts');
                break;
            case 'newThread':
                $cache->delete('forum.threadscount');
                $cache->deleteElement('user.threadscount', User::loggedId());
                // no break
            case 'newPost':
                $cache->delete('forum.postscount');
                $cache->delete('forum.latestposts');
                $cache->deleteElement('user.postscount', User::loggedId());
                break;
        }
    }

    /**
     * Deletes the value with the specified key from cache
     * @param string $key the key identifying the value to be deleted from cache.
     * @return bool
     */
    public function delete($key)
    {
        return $this->engine->delete($this->_cachePrefix . $key);
    }

    /**
     * Deletes the value of element with the specified key from cache array.
     * @param string $key a key identifying the value to be deleted from cache.
     * @param string $element a key of the element.
     * @return bool
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
     * Ends FragmentCache widget.
     */
    public function end()
    {
        return FragmentCache::end();
    }

    /**
     * Flushes all cache.
     */
    public function flush()
    {
        return $this->engine->flush();
    }

    /**
     * Retrieves the value from cache with the specified key.
     * @param string $key the key identifying the cached value.
     * @return mixed the value stored in cache, false if the value is not in the cache, expired,
     * or the dependency associated with the cached data has changed.
     */
    public function get($key)
    {
        return $this->engine->get($this->_cachePrefix . $key);
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
     * Stores the value identified by the key into cache.
     * @param string $key the key identifying the value to be cached.
     * @param mixed $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool
     */
    public function set($key, $value, $duration = 0)
    {
        return $this->engine->set($this->_cachePrefix . $key, $value, $duration);
    }

    /**
     * Stores the value for the element into cache array identified by the key.
     * @param string $key the key identifying the value to be cached.
     * @param string $element the key of the element.
     * @param mixed $value the value to be cached
     * @param int $duration the number of seconds in which the cached value will expire. 0 means never expire.
     * @return bool
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
