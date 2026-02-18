<?php

namespace WP_Statistics\Service\Admin\SiteHealth;

use WP_Statistics\Components\CachePlugin;
use WP_Statistics\Components\Singleton;
use WP_Statistics\Components\Option;
use WP_Statistics\Utils\PostType;
use WP_Statistics\Utils\User;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\CloudflareGeolocationProvider;

/**
 * Class SiteHealthInfo
 *
 * Provides WP Statistics debug information for the WordPress Site Health page.
 *
 * @package WP_Statistics\Service\Admin\SiteHealth
 */
class SiteHealthInfo extends Singleton
{
    /**
     * Slug for the WP Statistics debug information section.
     */
    const DEBUG_INFO_SLUG = 'wp_statistics';

    /**
     * Whether hooks have been registered.
     *
     * @var bool
     */
    private static $registered = false;

    /**
     * Get the singleton instance and auto-register hooks.
     *
     * @return self
     */
    public static function instance(): self
    {
        $instance = parent::instance();

        if (!self::$registered) {
            self::$registered = true;
            add_filter('debug_information', [$instance, 'addStatisticsInfo']);
        }

        return $instance;
    }

    /**
     * Add WP Statistics debug information to the Site Health Info page.
     *
     * @param array $info
     *
     * @return array
     */
    public function addStatisticsInfo($info)
    {
        $allSettings = array_merge(
            $this->getPluginSettings(),
            $this->getAddOnsSettings()
        );

        $info[self::DEBUG_INFO_SLUG] = [
            'label'       => esc_html__('WP Statistics', 'wp-statistics'),
            'description' => esc_html__('This section contains debug information about your WP Statistics settings to help you troubleshoot issues.', 'wp-statistics'),
            'fields'      => $allSettings,
        ];

        return $info;
    }

    /**
     * Get plugin settings.
     *
     * @return array
     */
    public function getPluginSettings()
    {
        $userRoleExclusions    = $this->getUserRoleExclusions(false);
        $geoIpProvider         = GeolocationFactory::getProviderInstance();
        $geoIpProviderValidity = $geoIpProvider->validateDatabaseFile();
        $locationMethodInfo    = $this->getLocationDetectionMethodInfo();
        $databaseSchemaStatus  = $this->getDatabaseSchemaStatus();
        $requiredHeaderExists  = CloudflareGeolocationProvider::isAvailable();

        $settings = [
            /**
             * General settings.
             */
            'version'                        => [
                'label' => esc_html__('Version', 'wp-statistics'),
                'value' => WP_STATISTICS_VERSION,
                'debug' => WP_STATISTICS_VERSION,
            ],
            'database_version'               => [
                'label' => esc_html__('Database Version', 'wp-statistics'),
                'value' => Option::getGroupValue('db', 'version', '0.0.0'),
                'debug' => Option::getGroupValue('db', 'version', '0.0.0'),
            ],
            'database_schema'                => [
                'label' => esc_html__('Database Schema', 'wp-statistics'),
                'value' => $databaseSchemaStatus['label'],
                'debug' => $databaseSchemaStatus['debug'],
            ],
            'detectActiveCachePlugin'        => [
                'label' => esc_html__('Detect Active Cache Plugin', 'wp-statistics'),
                'value' => CachePlugin::isActive() ? sprintf(__('Enabled (%s)', 'wp-statistics'), CachePlugin::getLabel()) : __('Disabled', 'wp-statistics'),
                'debug' => CachePlugin::isActive() ? 'Enabled ' . CachePlugin::getAll()['debug'] : 'Disabled',
            ],
            'activePostTypes'                => [
                'label' => esc_html__('Active Post Types', 'wp-statistics'),
                'value' => implode(', ', PostType::getAllTypes()),
                'debug' => implode(', ', PostType::getAllTypes()),
            ],
            'dailySaltDate'                  => [
                'label' => esc_html__('Daily Salt Date', 'wp-statistics'),
                'value' => is_array(get_option('wp_statistics_daily_salt')) ? get_option('wp_statistics_daily_salt')['date'] : '',
                'debug' => is_array(get_option('wp_statistics_daily_salt')) ? get_option('wp_statistics_daily_salt')['date'] : '',
            ],

            /**
             * Geolocation database settings.
             */
            'geoipLocationDetectionMethod'   => [
                'label' => esc_html__('Location Detection Method', 'wp-statistics'),
                'value' => $locationMethodInfo['title'],
                'debug' => $locationMethodInfo['debug'],
            ],
            'geoIpDatabaseUpdateSource'      => [
                'label' => esc_html__('Geolocation Database Update Source', 'wp-statistics'),
                'value' => Option::getValue('geoip_license_type') ? Option::getValue('geoip_license_type') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('geoip_license_type') ? Option::getValue('geoip_license_type') : 'Not Set',
            ],
            'cloudflareRequiredHeaderExists' => [
                'label' => esc_html__('Cloudflare Required Headers Exists', 'wp-statistics'),
                'value' => $requiredHeaderExists ? __('Yes', 'wp-statistics') : __('No', 'wp-statistics'),
                'debug' => $requiredHeaderExists ? 'Yes' : 'No',
            ],
            'geoIpDatabaseExists'            => [
                'label' => esc_html__('GeoIP Database Exists', 'wp-statistics'),
                'value' => $geoIpProvider->isDatabaseExist() ? __('Yes', 'wp-statistics') : __('No', 'wp-statistics'),
                'debug' => $geoIpProvider->isDatabaseExist() ? 'Yes' : 'No',
            ],
            'geoIpDatabaseLastUpdated'       => [
                'label' => esc_html__('GeoIP Database Last Updated', 'wp-statistics'),
                'value' => $geoIpProvider->getLastDatabaseFileUpdated(),
                'debug' => $geoIpProvider->getLastDatabaseFileUpdated(),
            ],
            'geoIpDatabaseSize'              => [
                'label' => esc_html__('GeoIP Database Size', 'wp-statistics'),
                'value' => $geoIpProvider->getDatabaseSize(),
                'debug' => $geoIpProvider->getDatabaseSize(),
            ],
            'geoIpDatabaseType'              => [
                'label' => esc_html__('GeoIP Database Type', 'wp-statistics'),
                'value' => $geoIpProvider->getDatabaseType(),
                'debug' => $geoIpProvider->getDatabaseType(),
            ],
            'geoIpDatabaseValidation'        => [
                'label' => esc_html__('GeoIP Database Validation', 'wp-statistics'),
                'value' => is_wp_error($geoIpProviderValidity) ? esc_html__('No', 'wp-statistics') : esc_html__('Yes', 'wp-statistics'),
                'debug' => is_wp_error($geoIpProviderValidity) ? $geoIpProviderValidity->get_error_message() : 'Yes',
            ],

            /**
             * Plugin configuration settings.
             */
            'monitorOnlineVisitors'          => [
                'label' => esc_html__('Monitor Online Visitors', 'wp-statistics'),
                'value' => Option::getValue('useronline') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('useronline') ? 'Enabled' : 'Disabled',
            ],
            'trackLoggedInUserActivity'      => [
                'label' => esc_html__('Track Logged-In User Activity', 'wp-statistics'),
                'value' => Option::getValue('visitors_log') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('visitors_log') ? 'Enabled' : 'Disabled',
            ],
            'trackingMethod'                 => [
                'label' => esc_html__('Tracking Method', 'wp-statistics'),
                'value' => __('Client Side Tracking', 'wp-statistics'),
                'debug' => 'Client Side Tracking',
            ],
            'bypassAdBlockers'               => [
                'label' => esc_html__('Bypass Ad Blockers', 'wp-statistics'),
                'value' => Option::getValue('bypass_ad_blockers') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('bypass_ad_blockers') ? 'Enabled' : 'Disabled',
            ],
            'storeIpAddresses'               => [
                'label' => esc_html__('Store IP Addresses', 'wp-statistics'),
                'value' => Option::getValue('store_ip') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('store_ip') ? 'Enabled' : 'Disabled',
            ],
            'wpConsentLevelIntegration'      => [
                'label' => esc_html__('WP Consent Level Integration', 'wp-statistics'),
                'value' => Option::getValue('consent_level_integration') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('consent_level_integration') ? 'Enabled' : 'Disabled',
            ],
            'anonymousTracking'              => [
                'label' => esc_html__('Anonymous Tracking', 'wp-statistics'),
                'value' => Option::getValue('anonymous_tracking') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('anonymous_tracking') ? 'Enabled' : 'Disabled',
            ],
            'viewStatsInEditor'              => [
                'label' => esc_html__('View Stats in Editor', 'wp-statistics'),
                'value' => Option::getValue('disable_editor') ? __('Disabled', 'wp-statistics') : __('Enabled', 'wp-statistics'),
                'debug' => Option::getValue('disable_editor') ? 'Disabled' : 'Enabled',
            ],
            'viewsColumnInContentList'       => [
                'label' => esc_html__('Stats Column in Content List', 'wp-statistics'),
                'value' => Option::getValue('disable_column') ? __('Disabled', 'wp-statistics') : __('Enabled', 'wp-statistics'),
                'debug' => Option::getValue('disable_column') ? 'Disabled' : 'Enabled',
            ],
            'viewsColumnInUserList'          => [
                'label' => esc_html__('Views Column in User List', 'wp-statistics'),
                'value' => Option::getValue('enable_user_column') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('enable_user_column') ? 'Enabled' : 'Disabled',
            ],
            'wpStatisticsNotifications'      => [
                'label' => esc_html__('WP Statistics Notifications', 'wp-statistics'),
                'value' => Option::getValue('display_notifications') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('display_notifications') ? 'Enabled' : 'Disabled',
            ],
            'disableInactiveFeatureNotices'  => [
                'label' => esc_html__('Disable Admin Notices', 'wp-statistics'),
                'value' => Option::getValue('hide_notices') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('hide_notices') ? 'Enabled' : 'Disabled',
            ],
            'viewsInSingleContents'          => [
                'label' => esc_html__('Views in Single Contents', 'wp-statistics'),
                'value' => Option::getValue('show_hits') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('show_hits') ? 'Enabled' : 'Disabled',
            ],
            'userRoleExclusions'             => [
                'label' => esc_html__('User Role Exclusions', 'wp-statistics'),
                'value' => $userRoleExclusions ? implode(', ', $userRoleExclusions) : __('Not Set', 'wp-statistics'),
                'debug' => $userRoleExclusions ? implode(', ', $userRoleExclusions) : 'Not Set',
            ],
            'ipExclusions'                   => [
                'label' => esc_html__('IP Exclusions', 'wp-statistics'),
                'value' => Option::getValue('exclude_ip') ? __('Set', 'wp-statistics') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('exclude_ip') ? 'Set' : 'Not Set',
            ],
            'excludedLoginPage'              => [
                'label' => esc_html__('Excluded Login Page', 'wp-statistics'),
                'value' => Option::getValue('exclude_loginpage') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('exclude_loginpage') ? 'Enabled' : 'Disabled',
            ],
            'excludedRssFeeds'               => [
                'label' => esc_html__('Excluded RSS Feeds', 'wp-statistics'),
                'value' => Option::getValue('exclude_feeds') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('exclude_feeds') ? 'Enabled' : 'Disabled',
            ],
            'excluded404Page'                => [
                'label' => esc_html__('Excluded 404 Pages', 'wp-statistics'),
                'value' => Option::getValue('exclude_404s') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('exclude_404s') ? 'Enabled' : 'Disabled',
            ],
            'excludedURLs'                   => [
                'label' => esc_html__('Excluded URLs', 'wp-statistics'),
                'value' => Option::getValue('excluded_urls') ? __('Set', 'wp-statistics') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('excluded_urls') ? 'Set' : 'Not Set',
            ],
            'logRecordExclusions'            => [
                'label' => esc_html__('Log Record Exclusions', 'wp-statistics'),
                'value' => Option::getValue('record_exclusions') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('record_exclusions') ? 'Enabled' : 'Disabled',
            ],
            'minRoleToViewStats'             => [
                'label' => esc_html__('Minimum Role to View Statistics', 'wp-statistics'),
                'value' => Option::getValue('read_capability') ? Option::getValue('read_capability') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('read_capability') ? Option::getValue('read_capability') : 'Not Set',
            ],
            'minRoleToManageSettings'        => [
                'label' => esc_html__('Minimum Role to Manage Settings', 'wp-statistics'),
                'value' => Option::getValue('manage_capability') ? Option::getValue('manage_capability') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('manage_capability') ? Option::getValue('manage_capability') : 'Not Set',
            ],
            'ipDetectionMethod'              => [
                'label' => esc_html__('IP Detection Method', 'wp-statistics'),
                'value' => Option::getValue('ip_method') ? Option::getValue('ip_method') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('ip_method') ? Option::getValue('ip_method') : 'Not Set',
            ],
            'automaticCleanup'               => [
                'label' => esc_html__('Automatic Cleanup', 'wp-statistics'),
                'value' => Option::getValue('schedule_dbmaint') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('schedule_dbmaint') ? 'Enabled' : 'Disabled',
            ],
            'purgeDataOlderThan'             => [
                'label' => esc_html__('Purge Data Older Than', 'wp-statistics'),
                'value' => Option::getValue('schedule_dbmaint_days') ? Option::getValue('schedule_dbmaint_days') : __('Not Set', 'wp-statistics'),
                'debug' => Option::getValue('schedule_dbmaint_days') ? Option::getValue('schedule_dbmaint_days') : 'Not Set',
            ],
            'shareAnonymousData'             => [
                'label' => esc_html__('Share Anonymous Data', 'wp-statistics'),
                'value' => Option::getValue('share_anonymous_data') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getValue('share_anonymous_data') ? 'Enabled' : 'Disabled',
            ],
            'phpGmpExtension'                => [
                'label' => esc_html__('PHP Extension (GMP)', 'wp-statistics'),
                'value' => extension_loaded('gmp') ? __('Installed', 'wp-statistics') : __('Not Installed', 'wp-statistics'),
                'debug' => extension_loaded('gmp') ? 'Installed' : 'Not Installed',
            ],
            'phpBcmathExtension'             => [
                'label' => esc_html__('PHP Extension (BCMath)', 'wp-statistics'),
                'value' => extension_loaded('bcmath') ? __('Installed', 'wp-statistics') : __('Not Installed', 'wp-statistics'),
                'debug' => extension_loaded('bcmath') ? 'Installed' : 'Not Installed',
            ],
            'phpGzopenFunction'              => [
                'label' => esc_html__('PHP Function (gzopen)', 'wp-statistics'),
                'value' => function_exists('gzopen') ? __('Installed', 'wp-statistics') : __('Not Installed', 'wp-statistics'),
                'debug' => function_exists('gzopen') ? 'Installed' : 'Not Installed',
            ],
            'phpPharDataClass'               => [
                'label' => esc_html__('PHP Class (PharData)', 'wp-statistics'),
                'value' => class_exists('PharData') ? __('Installed', 'wp-statistics') : __('Not Installed', 'wp-statistics'),
                'debug' => class_exists('PharData') ? 'Installed' : 'Not Installed',
            ],
        ];

        return $settings;
    }

    /**
     * Get settings for active add-ons.
     *
     * @deprecated Add-on settings are now managed by the premium plugin.
     *
     * @return array
     */
    public function getAddOnsSettings()
    {
        return [];
    }

    /**
     * Get database schema migration status for Site Health display.
     *
     * @return array{label: string, debug: string}
     */
    private function getDatabaseSchemaStatus()
    {
        $isMigrated    = Option::getGroupValue('db', 'migrated', false);
        $statusDetail  = Option::getGroupValue('db', 'migration_status_detail', []);

        if (!empty($statusDetail['status']) && $statusDetail['status'] === 'failed') {
            return [
                'label' => __('Migration Failed', 'wp-statistics'),
                'debug' => 'Migration Failed: ' . ($statusDetail['message'] ?? 'Unknown error'),
            ];
        }

        if ($isMigrated) {
            return [
                'label' => __('Up to date', 'wp-statistics'),
                'debug' => 'Up to date',
            ];
        }

        return [
            'label' => __('Pending migrations', 'wp-statistics'),
            'debug' => 'Pending migrations',
        ];
    }

    /**
     * Get the location detection method info for Site Health display.
     *
     * @return array{title: string, debug: string, method: string, is_maxmind: bool}
     */
    private function getLocationDetectionMethodInfo()
    {
        $method = Option::getValue('geoip_location_detection_method', 'maxmind');

        switch ($method) {
            case 'cloudflare':
                return [
                    'title'     => __('Cloudflare IP Geolocation', 'wp-statistics'),
                    'debug'     => 'Cloudflare IP Geolocation',
                    'method'    => $method,
                    'is_maxmind' => false,
                ];

            case 'dbip':
                return [
                    'title'     => __('DB-IP Geolocation', 'wp-statistics'),
                    'debug'     => 'DB-IP Geolocation',
                    'method'    => $method,
                    'is_maxmind' => false,
                ];

            case 'maxmind':
            default:
                return [
                    'title'     => __('MaxMind GeoIP', 'wp-statistics'),
                    'debug'     => 'MaxMind GeoIP',
                    'method'    => $method,
                    'is_maxmind' => true,
                ];
        }
    }

    /**
     * Get the user role exclusions.
     *
     * @param bool $translate
     *
     * @return array
     */
    public function getUserRoleExclusions($translate = true)
    {
        $excludeRoles = [];
        foreach (User::getRoles() as $role) {
            $optionName = 'exclude_' . str_replace(" ", "_", strtolower($role));

            if (Option::getValue($optionName)) {
                if ($translate) {
                    $translatedRoleName = ($role === 'Anonymous Users') ? __('Anonymous Users', 'wp-statistics') : translate_user_role($role);
                } else {
                    $translatedRoleName = ($role === 'Anonymous Users') ? 'Anonymous Users' : $role;
                }
                $excludeRoles[] = $translatedRoleName;
            }
        }

        return $excludeRoles;
    }
}
