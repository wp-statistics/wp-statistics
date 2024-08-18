<?php

namespace WP_Statistics\Traits;

trait Cacheable
{
    protected function getCacheKey($query)
    {
        $hash = substr(md5($query), 0, 10);
        return sprintf('wp_statistics_cache_%s', $hash);
    }

    protected function getCachedResult($query)
    {
        $cacheKey = $this->getCacheKey($query);
        return get_transient($cacheKey);
    }

    protected function setCachedResult($query, $result)
    {
        $cacheKey = $this->getCacheKey($query);
        return set_transient($cacheKey, $result, HOUR_IN_SECONDS);
    }
}
