<?php

namespace WP_Statistics\Service\Admin\ReactApp\Providers;

use WP_Statistics\Components\Country;
use WP_Statistics\Components\Ip;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\ReactApp\Contracts\LocalizeDataProviderInterface;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\AnalyticsQuery;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\FilterOptions;
use WP_Statistics\Service\Admin\Dashboard\Endpoints\UserPreferences;
use WP_Statistics\Service\Admin\LicenseManagement\LicenseHelper;
use WP_Statistics\Service\Admin\UserPreferences\UserPreferencesManager;
use WP_Statistics\Utils\Taxonomy;
use WP_Statistics\Utils\User;

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
            'storeIp'               => (bool) Option::getValue('store_ip', false),
            'userIp'                => Ip::getCurrent(),
            'trackLoggedInUsers'    => (bool) Option::getValue('visitors_log', false),
            'userPreferences'       => [
                'globalFilters' => $this->getGlobalFiltersPreferences(),
            ],
            'currentPage'           => isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '',
            'dateFormat'            => get_option('date_format', 'Y-m-d'),
            'startOfWeek'           => (int) get_option('start_of_week', 0),
            'taxonomies'            => $this->getTaxonomyList(),
            'userCountry'           => $this->getUserCountryCode(),
            'userCountryName'       => $this->getUserCountryName(),
            'accessLevel'           => User::getAccessLevel(),
            'userId'                => get_current_user_id(),
            'timezone'              => [
                'string'    => wp_timezone_string(),
                'gmtOffset' => (float) get_option('gmt_offset', 0),
            ],
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

    /**
     * Get list of public taxonomies for the frontend.
     *
     * @return array Array of taxonomy objects with value and label
     */
    private function getTaxonomyList()
    {
        $taxonomies = Taxonomy::getAll();
        $result     = [];

        foreach ($taxonomies as $slug => $label) {
            $result[] = [
                'value' => $slug,
                'label' => $label,
            ];
        }

        return $result;
    }

    /**
     * Get user's country code based on WordPress timezone.
     *
     * @return string Country code or empty string if not detected
     */
    private function getUserCountryCode()
    {
        static $countryCode = null;

        if ($countryCode === null) {
            $countryCode = Country::getByTimeZone();
        }

        return $countryCode;
    }

    /**
     * Get user's country name based on WordPress timezone.
     *
     * @return string Country name or empty string if not detected
     */
    private function getUserCountryName()
    {
        $countryCode = $this->getUserCountryCode();

        if (empty($countryCode)) {
            return '';
        }

        return Country::getName($countryCode);
    }
}

