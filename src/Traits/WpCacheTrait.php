<?php

namespace WP_Statistics\Traits;

/**
 * Trait to handle caching logic using WordPress Object Cache.
 */
trait WpCacheTrait
{
    /**
     * Cache group name to avoid collisions.
     *
     * @var string
     */
    protected $cacheGroup = 'wp_statistics';

    /**
     * Sets a value in the cache.
     *
     * @param string $key   Cache key.
     * @param mixed  $value Value to cache.
     * @param mixed  $expire Expiration time in seconds.
     *
     * @return bool True on success, false on failure.
     */
    public function setCache($key, $value, $expire = 0)
    {
        return wp_cache_set($key, $value, $this->cacheGroup, $expire);
    }

    /**
     * Gets a value from the cache.
     *
     * @param string $key     Cache key.
     * @param mixed  $default Default value if not found.
     *
     * @return mixed
     */
    public function getCache($key, $default = null)
    {
        $value = wp_cache_get($key, $this->cacheGroup);

        return ($value === false) ? $default : $value;
    }

    /**
     * Checks if a cache key is set.
     *
     * @param string $key Cache key.
     *
     * @return bool
     */
    public function isCacheSet($key)
    {
        return wp_cache_get($key, $this->cacheGroup) !== false;
    }

    /**
     * Deletes a value from the cache.
     *
     * @param string $key Cache key.
     *
     * @return bool True on success, false on failure.
     */
    public function deleteCache($key)
    {
        return wp_cache_delete($key, $this->cacheGroup);
    }

    /**
     * Fetches data with caching.
     *
     * @param string   $key      Cache key.
     * @param string   $expire   Expiration in seconds.
     * @param callable $callback Function to fetch data if not cached.
     *
     * @return mixed
     */
    public function getCachedData($key, callable $callback, $expire = 0)
    {
        $value = wp_cache_get($key, $this->cacheGroup);

        if ($value === false) {
            $value = $callback();
            $this->setCache($key, $value, $expire);
        }

        return $value;
    }
}
