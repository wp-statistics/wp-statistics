<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\ReactApp\Controllers\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;

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
            'isPremium'             => LicenseHelper::isPremiumLicenseAvailable(),
            'ajaxUrl'               => admin_url('admin-ajax.php'),
            'nonce'                 => wp_create_nonce('wp_statistics_dashboard_nonce'),
            'pluginUrl'             => WP_STATISTICS_URL,
            'siteUrl'               => home_url(),
            'analyticsAction'       => AnalyticsQuery::getActionName(),
            'userPreferencesAction' => UserPreferences::getActionName(),
            'filterAction'          => FilterOptions::getActionName(),
            'hashIps'               => (bool) Option::getValue('hash_ips', true),
            'trackLoggedInUsers'    => (bool) Option::getValue('visitors_log', false),
            'userPreferences'       => [
                'globalFilters' => $this->getGlobalFiltersPreferences(),
            ],
            'currentPage'           => isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '',
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

    /**
     * Get global filters preferences for the current user.
     *
     * @return array|null Global filters preferences or null if not set
     */
    private function getGlobalFiltersPreferences()
    {
        $manager = new UserPreferencesManager();
        return $manager->get('global_filters');
    }
}

