<?php

namespace WP_Statistics\Service\Admin;

use WP_STATISTICS\GeoIP;
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

                /**
                 * Geolocation database settings.
                 */
                'geoIpDatabaseExists'           => [
                    'label' => esc_html__('GeoIP Database Exists', 'wp-statistics'),
                    'value' => GeoIP::isExist() ? __('Yes', 'wp-statistics') : __('No', 'wp-statistics'),
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
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'trackLoggedInUserActivity'     => [
                    'label' => esc_html__('Track Logged-In User Activity', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'trackingMethod'                => [
                    'label' => esc_html__('Tracking Method', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'bypassAdBlockers'              => [
                    'label' => esc_html__('Bypass Ad Blockers', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'anonymizeIpAddresses'          => [
                    'label' => esc_html__('Anonymize IP Addresses', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'hashIpAddresses'               => [
                    'label' => esc_html__('Hash IP Addresses', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'wpConsentLevelIntegration'     => [
                    'label' => esc_html__('WP Consent Level Integration', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'anonymousTracking'             => [
                    'label' => esc_html__('Anonymous Tracking', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'doNotTrack'                    => [
                    'label' => esc_html__('Do Not Track (DNT)', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'viewStatsInEditor'             => [
                    'label' => esc_html__('View Stats in Editor', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'viewsColumnInContentList'      => [
                    'label' => esc_html__('Views Column in Content List', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'showStatsInAdminMenuBar'       => [
                    'label' => esc_html__('Show Stats in Admin Menu Bar', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'wpStatisticsWidgets'           => [
                    'label' => esc_html__('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'disableInactiveFeatureNotices' => [
                    'label' => esc_html__('Disable Inactive Essential Feature Notices', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'viewsInSingleContents'         => [
                    'label' => esc_html__('Views in Single Contents', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'reportFrequency'               => [
                    'label' => esc_html__('Report Frequency', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'userRoleExclusions'            => [
                    'label' => esc_html__('User Role Exclusions', 'wp-statistics'),
                    'value' => implode(', ', Option::get('key') ?: [__('None', 'wp-statistics')]),
                ],
                'minRoleToViewStats'            => [
                    'label' => esc_html__('Minimum Role to View Statistics', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'minRoleToManageSettings'       => [
                    'label' => esc_html__('Minimum Role to Manage Settings', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'ipDetectionMethod'             => [
                    'label' => esc_html__('IP Detection Method', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'geoIpDatabaseUpdateSource'     => [
                    'label' => esc_html__('GeoIP Database Update Source', 'wp-statistics'),
                    'value' => Option::get('key') ?: __('Not Set', 'wp-statistics'),
                ],
                'automaticCleanup'              => [
                    'label' => esc_html__('Automatic Cleanup', 'wp-statistics'),
                    'value' => Option::get('key') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                ],
                'purgeDataOlderThan'            => [
                    'label' => esc_html__('Purge Data Older Than', 'wp-statistics'),
                    'value' => Option::get('key') ? Option::get('key') . ' days' : __('Not Set', 'wp-statistics'),
                ],

                /**
                 * Add-ons configuration settings.
                 */
                // todo

                /**
                 * Active cron jobs.
                 */
                // todo
            ],
        ];

        return $info;
    }
}
