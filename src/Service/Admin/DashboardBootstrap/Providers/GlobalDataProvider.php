<?php

namespace WP_Statistics\Service\Admin\DashboardBootstrap\Providers;

use WP_Statistics\Service\Admin\DashboardBootstrap\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;

/**
 * Provider for global application data.
 *
 * This provider is responsible for delivering essential global
 * data to the React dashboard application, such as license
 * status, user permissions, and other application-wide settings.
 *
 * @since 15.0.0
 */
class GlobalDataProvider implements LocalizeDataProviderInterface
{
    /**
     * Get global application data.
     *
     * @return array Array of global data
     */
    public function getData()
    {
        $data = [
            'isPremium' => LicenseHelper::isPremiumLicenseAvailable(),
        ];

        /**
         * Filter global data before sending to React.
         *
         * Allows other components to add, remove, or modify global data.
         *
         * @param array $data Array of global data
         * @since 15.0.0
         */
        return apply_filters('wp_statistics_dashboard_global_data', $data);
    }

    /**
     * Get the localize data key.
     *
     * @return string The key 'global' for global data
     */
    public function getKey()
    {
        return 'globals';
    }
}

