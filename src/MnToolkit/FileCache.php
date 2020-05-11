<?php
declare(strict_types=1);

namespace MnToolkit;

use Phpfastcache\CacheManager;
use Phpfastcache\Config\ConfigurationOption;
use Phpfastcache\Exceptions\PhpfastcacheSimpleCacheException;
use Phpfastcache\Helper\Psr16Adapter;

class FileCache
{
    private static $instance = null;

    private $cache = null;

    /**
     * FileCache singleton accessor.  Returns the single instance of FileCache
     */
    public static function getInstance(): FileCache
    {
        if (self::$instance == null) {
            self::$instance = new FileCache();
        }

        return self::$instance;
    }

    /**
     * Either return the cached value associated with $key if available,
     * or load it using $loadFunction and add it to the cache.
     * @param  string  $key  the unique cache key for this object
     * @param  int  $secondsToExpiry  number of seconds after which this object will be removed from cache
     * @param  callable  $loadFunction  the function to call to load the value for this object
     * @return mixed|null
     */
    public function fetch(string $key, int $secondsToExpiry, callable $loadFunction)
    {
        $value = null;
        try {
            if ($this->cache->has($key)) {
                // if it's in the cache, get it
                $value = $this->cache->get($key);
            } else {
                $value = $loadFunction();
                if (!is_null($value)) {
                    // don't put null values in the cache
                    $this->cache->set($key, $value, $secondsToExpiry);
                }
            }
        } catch (PhpfastcacheSimpleCacheException $e) {
            GlobalLogger::getInstance()->getLogger()->error(e);
        }
        return $value;
    }

  /**
     * Set a cached value associated with $key,
     * @param  string  $key  the unique cache key for this object
     * @param  string  $value  the value to set the key to
     * @param  int  $secondsToExpiry  number of seconds after which this object will be removed from cache
     * @return null
     */
    public function set(string $key, string $value, int $secondsToExpiry)
    {
        try {
            $this->cache->set($key, $value, $secondsToExpiry);
        } catch (PhpfastcacheSimpleCacheException $e) {
            GlobalLogger::getInstance()->getLogger()->error(e);
        }
    }

  /**
     * Get the cached value associated with $key if available,
     * @param  string  $key  the unique cache key for this object
     * @return mixed|null
     */
    public function get(string $key)
    {
        $value = null;
        try {
            if ($this->cache->has($key)) {
                $value = $this->cache->get($key);
            }
        } catch (PhpfastcacheSimpleCacheException $e) {
            GlobalLogger::getInstance()->getLogger()->error(e);
        }
        return $value;
    }

    /**
     * Private constructor.  Only called from getInstance.
     */
    private function __construct()
    {
        $this->cache = MnToolkitBase::checkCache();
    }
}
