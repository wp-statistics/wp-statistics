<?php

namespace WP_Statistics\Traits;

trait Cacheable
{
    protected function getCacheKey($query)
    {
        return md5($query);
    }

    protected function getCachedResult($query)
    {
        $cacheKey = $this->getCacheKey($query);
        return wp_cache_get($cacheKey, 'wp-statistics');
    }

    protected function setCachedResult($query, $result)
    {
        $cacheKey = $this->getCacheKey($query);
        wp_cache_set($cacheKey, $result, 'wp-statistics', HOUR_IN_SECONDS);
    }
}
