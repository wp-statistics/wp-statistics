<?php

namespace WP_Statistics\Service\Admin;

use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

/**
 * Class SiteHealthInfo
 *
 * @package WP_Statistics\Service\Admin
 */
class SiteHealthInfo
{
    /**
     * Slug for the WP Statistics debug information section.
     */
    const DEBUG_INFO_SLUG = 'wp_statistics';

    public function register()
    {
        add_filter('debug_information', [$this, 'addStatisticsInfo']);
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
        $userRoleExclusions = $this->getUserRoleExclusions();

        $info[self::DEBUG_INFO_SLUG] = [
            'label'       => esc_html__('WP Statistics', 'wp-statistics'),
            'description' => esc_html__('This section contains debug information about your WP Statistics settings to help you troubleshoot issues.', 'wp-statistics'),
            'fields'      => [
                /**
                 * General settings.
                 */
                'version'                       => [
                    'label' => esc_html__('Version', 'wp-statistics'),
                    'value' => WP_STATISTICS_VERSION,
                ],
                'detectActiveCachePlugin'       => [
                    'label' => esc_html__('Detect Active Cache Plugin', 'wp-statistics'),
                    'value' => Helper::checkActiveCachePlugin()['status'] === true ? sprintf('Enabled (%s)', Helper::checkActiveCachePlugin()['plugin']) : 'Disabled',
                ],
                'activePostTypes'               => [
                    'label' => esc_html__('Active Post Types', 'wp-statistics'),
                    'value' => implode(', ', Helper::getPostTypes()),
                ],
                'dailySaltDate'                 => [
                    'label' => esc_html__('Daily Salt Date', 'wp-statistics'),
                    'value' => get_option('wp_statistics_daily_salt')['date'],
                ],

                /**
                 * Geolocation database settings.
                 */
                'geoIpDatabaseExists'           => [
                    'label' => esc_html__('GeoIP Database Exists', 'wp-statistics'),
                    'value' => GeoIP::isExist() ? 'Yes' : 'No',
                ],
                'geoIpDatabaseLastUpdated'      => [
                    'label' => esc_html__('GeoIP Database Last Updated', 'wp-statistics'),
                    'value' => GeoIP::getLastUpdate(),
                ],
                'geoIpDatabaseSize'             => [
                    'label' => esc_html__('GeoIP Database Size', 'wp-statistics'),
                    'value' => GeoIP::getDatabaseSize(),
                ],
                'geoIpDatabaseType'             => [
                    'label' => esc_html__('GeoIP Database Type', 'wp-statistics'),
                    'value' => GeoIP::getDatabaseType(),
                ],

                /**
                 * Plugin configuration settings.
                 */
                'monitorOnlineVisitors'         => [
                    'label' => esc_html__('Monitor Online Visitors', 'wp-statistics'),
                    'value' => Option::get('useronline') ? 'Enabled' : 'Disabled',
                ],
                'trackLoggedInUserActivity'     => [
                    'label' => esc_html__('Track Logged-In User Activity', 'wp-statistics'),
                    'value' => Option::get('visitors_log') ? 'Enabled' : 'Disabled',
                ],
                'storeEntireUserAgentString'    => [
                    'label' => esc_html__('Store Entire User Agent String', 'wp-statistics'),
                    'value' => Option::get('store_ua') ? 'Enabled' : 'Disabled',
                ],
                'trackingMethod'                => [
                    'label' => esc_html__('Tracking Method', 'wp-statistics'),
                    'value' => Option::get('use_cache_plugin') ? 'Client Side Tracking' : 'Server Side Tracking',
                ],
                'bypassAdBlockers'              => [
                    'label' => esc_html__('Bypass Ad Blockers', 'wp-statistics'),
                    'value' => Option::get('bypass_ad_blockers') ? 'Enabled' : 'Disabled',
                ],
                'anonymizeIpAddresses'          => [
                    'label' => esc_html__('Anonymize IP Addresses', 'wp-statistics'),
                    'value' => Option::get('anonymize_ips') ? 'Enabled' : 'Disabled',
                ],
                'hashIpAddresses'               => [
                    'label' => esc_html__('Hash IP Addresses', 'wp-statistics'),
                    'value' => Option::get('hash_ips') ? 'Enabled' : 'Disabled',
                ],
                'wpConsentLevelIntegration'     => [
                    'label' => esc_html__('WP Consent Level Integration', 'wp-statistics'),
                    'value' => Option::get('consent_level_integration') ? 'Enabled' : 'Disabled',
                ],
                'anonymousTracking'             => [
                    'label' => esc_html__('Anonymous Tracking', 'wp-statistics'),
                    'value' => Option::get('anonymous_tracking') ? 'Enabled' : 'Disabled',
                ],
                'doNotTrack'                    => [
                    'label' => esc_html__('Do Not Track (DNT)', 'wp-statistics'),
                    'value' => Option::get('do_not_track') ? 'Enabled' : 'Disabled',
                ],
                'viewStatsInEditor'             => [
                    'label' => esc_html__('View Stats in Editor', 'wp-statistics'),
                    'value' => Option::get('disable_editor') ? 'Disabled' : 'Enable',
                ],
                'viewsColumnInContentList'      => [
                    'label' => esc_html__('Views Column in Content List', 'wp-statistics'),
                    'value' => Option::get('disable_column') ? 'Disable' : 'Enable',
                ],
                'viewsColumnInUserList'         => [
                    'label' => esc_html__('Views Column in User List', 'wp-statistics'),
                    'value' => Option::get('enable_user_column') ? 'Enabled' : 'Disabled',
                ],
                'showStatsInAdminMenuBar'       => [
                    'label' => esc_html__('Show Stats in Admin Menu Bar', 'wp-statistics'),
                    'value' => Option::get('menu_bar') ? 'Enabled' : 'Disabled',
                ],
                'wpStatisticsWidgets'           => [
                    'label' => esc_html__('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'),
                    'value' => Option::get('disable_dashboard') ? 'Disable' : 'Enable',
                ],
                'disableInactiveFeatureNotices' => [
                    'label' => esc_html__('Disable Inactive Essential Feature Notices', 'wp-statistics'),
                    'value' => Option::get('hide_notices') ? 'Enabled' : 'Disabled',
                ],
                'viewsInSingleContents'         => [
                    'label' => esc_html__('Views in Single Contents', 'wp-statistics'),
                    'value' => Option::get('show_hits') ? 'Enabled' : 'Disabled',
                ],
                'reportFrequency'               => [
                    'label' => esc_html__('Report Frequency', 'wp-statistics'),
                    'value' => Option::get('time_report') ? Option::get('time_report') : 'Disabled',
                ],
                'userRoleExclusions'            => [
                    'label' => esc_html__('User Role Exclusions', 'wp-statistics'),
                    'value' => $userRoleExclusions ? implode(', ', $userRoleExclusions) : 'Not Set',
                ],
                'ipExclusions'                  => [
                    'label' => esc_html__('IP Exclusions', 'wp-statistics'),
                    'value' => Option::get('exclude_ip') ? 'Set' : 'Not Set',
                ],
                'excludedLoginPage'             => [
                    'label' => esc_html__('Excluded Login Page', 'wp-statistics'),
                    'value' => Option::get('exclude_loginpage') ? 'Enabled' : 'Disabled',
                ],
                'excludedRssFeeds'              => [
                    'label' => esc_html__('Excluded RSS Feeds', 'wp-statistics'),
                    'value' => Option::get('exclude_feeds') ? 'Enabled' : 'Disabled',
                ],
                'excluded404Page'               => [
                    'label' => esc_html__('Excluded 404 Pages', 'wp-statistics'),
                    'value' => Option::get('exclude_404s') ? 'Enabled' : 'Disabled',
                ],
                'excludedURLs'                  => [
                    'label' => esc_html__('Excluded URLs', 'wp-statistics'),
                    'value' => Option::get('excluded_urls') ? 'Set' : 'Not Set',
                ],
                'matomoReferrerSpamBlacklist'   => [
                    'label' => esc_html__('Matomo Referrer Spam Blacklist', 'wp-statistics'),
                    'value' => Option::get('referrerspam') ? 'Enabled' : 'Disabled',
                ],
                'logRecordExclusions'           => [
                    'label' => esc_html__('Log Record Exclusions', 'wp-statistics'),
                    'value' => Option::get('record_exclusions') ? 'Enabled' : 'Disabled',
                ],
                'minRoleToViewStats'            => [
                    'label' => esc_html__('Minimum Role to View Statistics', 'wp-statistics'),
                    'value' => Option::get('read_capability') ? Option::get('read_capability') : 'Not Set',
                ],
                'minRoleToManageSettings'       => [
                    'label' => esc_html__('Minimum Role to Manage Settings', 'wp-statistics'),
                    'value' => Option::get('manage_capability') ? Option::get('manage_capability') : 'Not Set',
                ],
                'ipDetectionMethod'             => [
                    'label' => esc_html__('IP Detection Method', 'wp-statistics'),
                    'value' => Option::get('ip_method') ? Option::get('ip_method') : 'Not Set',
                ],
                'geoIpDatabaseUpdateSource'     => [
                    'label' => esc_html__('GeoIP Database Update Source', 'wp-statistics'),
                    'value' => Option::get('geoip_license_type') ? Option::get('geoip_license_type') : 'Not Set',
                ],
                'automaticCleanup'              => [
                    'label' => esc_html__('Automatic Cleanup', 'wp-statistics'),
                    'value' => Option::get('schedule_dbmaint') ? 'Enabled' : 'Disabled',
                ],
                'purgeDataOlderThan'            => [
                    'label' => esc_html__('Purge Data Older Than', 'wp-statistics'),
                    'value' => Option::get('schedule_dbmaint_days') ? Option::get('schedule_dbmaint_days') : 'Not Set',
                ],
                'phpGmpExtension'               => [
                    'label' => esc_html__('PHP Extension (GMP)', 'wp-statistics'),
                    'value' => extension_loaded('gmp') ? 'Installed' : 'Not Installed',
                ],
                'phpBcmathExtension'            => [
                    'label' => esc_html__('PHP Extension (BCMath)', 'wp-statistics'),
                    'value' => extension_loaded('bcmath') ? 'Installed' : 'Not Installed',
                ],
                'phpGzopenFunction'             => [
                    'label' => esc_html__('PHP Function (gzopen)', 'wp-statistics'),
                    'value' => function_exists('gzopen') ? 'Installed' : 'Not Installed',
                ],
                'phpPharDataClass'              => [
                    'label' => esc_html__('PHP Class (PharData)', 'wp-statistics'),
                    'value' => class_exists('PharData') ? 'Installed' : 'Not Installed',
                ]
            ]
        ];

        return $info;
    }

    /**
     * Get the user role exclusions.
     *
     * @return array
     */
    public function getUserRoleExclusions()
    {
        $excludeRoles = [];
        foreach (\WP_STATISTICS\User::get_role_list() as $role) {
            $optionName = 'exclude_' . str_replace(" ", "_", strtolower($role));

            $translatedRoleName = ($role === 'Anonymous Users') ? __('Anonymous Users', 'wp-statistics') : translate_user_role($role);

            if (Option::get($optionName)) {
                $excludeRoles[] = $translatedRoleName;
            }
        }

        return $excludeRoles;
    }
}
