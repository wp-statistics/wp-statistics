<?php
namespace WP_Statistics\Components;

use WP_Statistics\Utils\Query;
use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;
use WP_Statistics\Service\Admin\LicenseManagement\ApiEndpoints;

class CacheCleanup
{
    /**
     * Clears a specific cache.
     *
     * @param string $cacheId
     */
    public static function clear($cacheId)
    {
        return delete_transient("wp_statistics_cache_$cacheId");
    }

    /**
     * Clears all caches.
     */
    public static function clearAll()
    {
        return Query::delete('options')
            ->where('option_name', 'LIKE', '%wp_statistics_cache%')
            ->execute();
    }

    /**
     * Clears remote add-ons list cache.
     */
    public static function clearAddonsList()
    {
        $request = new RemoteRequest(ApiEndpoints::PRODUCT_LIST, 'GET');
        $request->clearCache();
    }
}