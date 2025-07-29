<?php
namespace WP_Statistics\Components;

use WP_Statistics\Utils\Query;
use WP_Statistics\Service\Admin\LicenseManagement\ApiEndpoints;

class SystemCleaner
{
    /**
     * Clears a specific cache.
     *
     * @param string $cacheId
     */
    public static function clearTransientById($cacheId)
    {
        return delete_transient("wp_statistics_cache_$cacheId");
    }

    /**
     * Clears all caches.
     */
    public static function clearAllTransients()
    {
        return Query::delete('options')
            ->where('option_name', 'LIKE', '%wp_statistics_cache%')
            ->execute();
    }

    /**
     * Clears remote add-ons list cache.
     */
    public static function clearAddonsListCache()
    {
        $request = new RemoteRequest(ApiEndpoints::PRODUCT_LIST, 'GET');
        $request->clearCache();
    }
}