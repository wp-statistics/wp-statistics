<?php

namespace WP_Statistics\Traits;

/**
 * Trait to handle caching logic.
 * @doc https://github.com/wp-statistics/wp-statistics/wiki/ObjectCacheTrait.md
 */
trait ObjectCacheTrait
{
    /**
     * Cached data to prevent duplicate queries.
     *
     * @var array
     */
    private $cache = [];

    /**
     * Sets a value in the cache.
     *
     * @param string $key The cache key.
     * @param mixed $value The value to cache.
     *
     * @return void
     */
    public function setCache($key, $value)
    {
        $this->cache[$key] = $value;
    }

    /**
     * Gets a value from the cache.
     *
     * @param string $key The cache key.
     *
     * @return mixed|null The cached value or null if not set.
     */
    public function getCache($key, $default = null)
    {
        return $this->isCacheSet($key) ? $this->cache[$key] : $default;
    }

    /**
     * Checks if a cache key is set.
     *
     * @param string $key The cache key.
     *
     * @return bool True if the cache key is set, false otherwise.
     */
    public function isCacheSet($key)
    {
        return isset($this->cache[$key]);
    }

    /**
     * Resets the cache.
     *
     * @return void
     */
    public function resetCache()
    {
        $this->cache = [];
    }

    /**
     * Fetches data from the model with caching.
     *
     * @param string $key Cache key.
     * @param callable $callback Function to fetch data if not cached.
     *
     * @return mixed Cached or fetched data.
     */
    public function getCachedData($key, callable $callback)
    {
        if (!$this->isCacheSet($key)) {
            $this->setCache($key, $callback());
        }

        return $this->getCache($key);
    }
}
