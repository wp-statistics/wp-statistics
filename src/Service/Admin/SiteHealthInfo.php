<?php

namespace WP_Statistics\Service\Admin;

use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\CloudflareGeolocationProvider;

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
        $userRoleExclusions      = $this->getUserRoleExclusions(false);
        $geoIpProvider           = GeolocationFactory::getProviderInstance();
        $geoIpProviderValidity   = $geoIpProvider->validateDatabaseFile();
        $locationDetectionMethod = Option::get('geoip_location_detection_method', 'maxmind');
        $isMaxmindLocationMethod = 'maxmind' === $locationDetectionMethod;
        $requiredHeaderExists    = CloudflareGeolocationProvider::isAvailable();

        $currentMethod = [
            'title' => __('Cloudflare IP Geolocation', 'wp-statistics'),
            'debug' => 'Cloudflare IP Geolocation',
        ];

        if ($locationDetectionMethod) {
            $currentMethod = [
                'title' => __('DB-IP Geolocation', 'wp-statistics'),
                'debug' => 'DB-IP Geolocation',
            ];
        }

        $settings = [
            /**
             * General settings.
             */
            'version'                        => [
                'label' => esc_html__('Version', 'wp-statistics'),
                'value' => WP_STATISTICS_VERSION,
            ],
            'database_version'               => [
                'label' => esc_html__('Database Version', 'wp-statistics'),
                'value' => Option::getOptionGroup('db', 'version', '0.0.0'),
            ],
            'detectActiveCachePlugin'        => [
                'label' => esc_html__('Detect Active Cache Plugin', 'wp-statistics'),
                'value' => Helper::checkActiveCachePlugin()['status'] === true ? sprintf(__('Enabled (%s)', 'wp-statistics'), Helper::checkActiveCachePlugin()['plugin']) : __('Disabled', 'wp-statistics'),
                'debug' => Helper::checkActiveCachePlugin()['status'] === true ? sprintf(__('Enabled (%s)', 'wp-statistics'), Helper::checkActiveCachePlugin()['plugin']) : 'Disabled',
            ],
            'activePostTypes'                => [
                'label' => esc_html__('Active Post Types', 'wp-statistics'),
                'value' => implode(', ', Helper::getPostTypes()),
            ],
            'dailySaltDate'                  => [
                'label' => esc_html__('Daily Salt Date', 'wp-statistics'),
                'value' => is_array(get_option('wp_statistics_daily_salt')) ? get_option('wp_statistics_daily_salt')['date'] : '',
            ],

            /**
             * Geolocation database settings.
             */
            'geoipLocationDetectionMethod'   => [
                'label' => esc_html__('Location Detection Method', 'wp-statistics'),
                'value' => $isMaxmindLocationMethod ? __('MaxMind GeoIP', 'wp-statistics') : $currentMethod['title'],
                'debug' => $isMaxmindLocationMethod ? 'MaxMind GeoIP' : $currentMethod['debug'],
            ],
            'geoIpDatabaseUpdateSource'      => [
                'label' => esc_html__('Geolocation Database Update Source', 'wp-statistics'),
                'value' => Option::get('geoip_license_type') ? Option::get('geoip_license_type') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('geoip_license_type') ? Option::get('geoip_license_type') : 'Not Set',
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
            ],
            'geoIpDatabaseSize'              => [
                'label' => esc_html__('GeoIP Database Size', 'wp-statistics'),
                'value' => $geoIpProvider->getDatabaseSize(),
            ],
            'geoIpDatabaseType'              => [
                'label' => esc_html__('GeoIP Database Type', 'wp-statistics'),
                'value' => $geoIpProvider->getDatabaseType(),
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
                'value' => Option::get('useronline') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('useronline') ? 'Enabled' : 'Disabled',
            ],
            'trackLoggedInUserActivity'      => [
                'label' => esc_html__('Track Logged-In User Activity', 'wp-statistics'),
                'value' => Option::get('visitors_log') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('visitors_log') ? 'Enabled' : 'Disabled',
            ],
            'storeEntireUserAgentString'     => [
                'label' => esc_html__('Store Entire User Agent String', 'wp-statistics'),
                'value' => Option::get('store_ua') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('store_ua') ? 'Enabled' : 'Disabled',
            ],
            'attributionModel'               => [
                'label' => esc_html__('Attribution Model', 'wp-statistics'),
                'value' => Option::get('attribution_model', 'first-touch'),
                'debug' => Option::get('attribution_model', 'first-touch'),
            ],
            'trackingMethod'                 => [
                'label' => esc_html__('Tracking Method', 'wp-statistics'),
                'value' => Option::get('use_cache_plugin') ? __('Client Side Tracking', 'wp-statistics') : __('Server Side Tracking', 'wp-statistics'),
                'debug' => Option::get('use_cache_plugin') ? 'Client Side Tracking' : 'Server Side Tracking',
            ],
            'bypassAdBlockers'               => [
                'label' => esc_html__('Bypass Ad Blockers', 'wp-statistics'),
                'value' => Option::get('bypass_ad_blockers') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('bypass_ad_blockers') ? 'Enabled' : 'Disabled',
            ],
            'anonymizeIpAddresses'           => [
                'label' => esc_html__('Anonymize IP Addresses', 'wp-statistics'),
                'value' => Option::get('anonymize_ips') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('anonymize_ips') ? 'Enabled' : 'Disabled',
            ],
            'hashIpAddresses'                => [
                'label' => esc_html__('Hash IP Addresses', 'wp-statistics'),
                'value' => Option::get('hash_ips') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('hash_ips') ? 'Enabled' : 'Disabled',
            ],
            'wpConsentLevelIntegration'      => [
                'label' => esc_html__('WP Consent Level Integration', 'wp-statistics'),
                'value' => Option::get('consent_level_integration') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('consent_level_integration') ? 'Enabled' : 'Disabled',
            ],
            'anonymousTracking'              => [
                'label' => esc_html__('Anonymous Tracking', 'wp-statistics'),
                'value' => Option::get('anonymous_tracking') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('anonymous_tracking') ? 'Enabled' : 'Disabled',
            ],
            'doNotTrack'                     => [
                'label' => esc_html__('Do Not Track (DNT)', 'wp-statistics'),
                'value' => Option::get('do_not_track') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('do_not_track') ? 'Enabled' : 'Disabled',
            ],
            'viewStatsInEditor'              => [
                'label' => esc_html__('View Stats in Editor', 'wp-statistics'),
                'value' => Option::get('disable_editor') ? __('Disabled', 'wp-statistics') : __('Enabled', 'wp-statistics'),
                'debug' => Option::get('disable_editor') ? 'Disabled' : 'Enabled',
            ],
            'viewsColumnInContentList'       => [
                'label' => esc_html__('Views Column in Content List', 'wp-statistics'),
                'value' => Option::get('disable_column') ? __('Disabled', 'wp-statistics') : __('Enabled', 'wp-statistics'),
                'debug' => Option::get('disable_column') ? 'Disabled' : 'Enabled',
            ],
            'viewsColumnInUserList'          => [
                'label' => esc_html__('Views Column in User List', 'wp-statistics'),
                'value' => Option::get('enable_user_column') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('enable_user_column') ? 'Enabled' : 'Disabled',
            ],
            'showStatsInAdminMenuBar'        => [
                'label' => esc_html__('Show Stats in Admin Menu Bar', 'wp-statistics'),
                'value' => Option::get('menu_bar') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('menu_bar') ? 'Enabled' : 'Disabled',
            ],
            'wpStatisticsChartsPrevPeriod'   => [
                'label' => esc_html__('Previous Period in Charts', 'wp-statistics'),
                'value' => Option::get('charts_previous_period') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('charts_previous_period') ? 'Enabled' : 'Disabled',
            ],
            'wpStatisticsWidgets'            => [
                'label' => esc_html__('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'),
                'value' => Option::get('disable_dashboard') ? __('Disabled', 'wp-statistics') : __('Enabled', 'wp-statistics'),
                'debug' => Option::get('disable_dashboard') ? 'Disabled' : 'Enabled',
            ],
            'wpStatisticsNotifications'      => [
                'label' => esc_html__('WP Statistics Notifications', 'wp-statistics'),
                'value' => Option::get('display_notifications') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('display_notifications') ? 'Enabled' : 'Disabled',
            ],
            'disableInactiveFeatureNotices'  => [
                'label' => esc_html__('Disable Inactive Essential Feature Notices', 'wp-statistics'),
                'value' => Option::get('hide_notices') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('hide_notices') ? 'Enabled' : 'Disabled',
            ],
            'viewsInSingleContents'          => [
                'label' => esc_html__('Views in Single Contents', 'wp-statistics'),
                'value' => Option::get('show_hits') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('show_hits') ? 'Enabled' : 'Disabled',
            ],
            'reportFrequency'                => [
                'label' => esc_html__('Report Frequency', 'wp-statistics'),
                'value' => Option::get('time_report') ? Option::get('time_report') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('time_report') ? Option::get('time_report') : 'Disabled',
            ],
            'userRoleExclusions'             => [
                'label' => esc_html__('User Role Exclusions', 'wp-statistics'),
                'value' => $userRoleExclusions ? implode(', ', $userRoleExclusions) : __('Not Set', 'wp-statistics'),
                'debug' => $userRoleExclusions ? implode(', ', $userRoleExclusions) : 'Not Set',
            ],
            'ipExclusions'                   => [
                'label' => esc_html__('IP Exclusions', 'wp-statistics'),
                'value' => Option::get('exclude_ip') ? __('Set', 'wp-statistics') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('exclude_ip') ? 'Set' : 'Not Set',
            ],
            'excludedLoginPage'              => [
                'label' => esc_html__('Excluded Login Page', 'wp-statistics'),
                'value' => Option::get('exclude_loginpage') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('exclude_loginpage') ? 'Enabled' : 'Disabled',
            ],
            'excludedRssFeeds'               => [
                'label' => esc_html__('Excluded RSS Feeds', 'wp-statistics'),
                'value' => Option::get('exclude_feeds') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('exclude_feeds') ? 'Enabled' : 'Disabled',
            ],
            'excluded404Page'                => [
                'label' => esc_html__('Excluded 404 Pages', 'wp-statistics'),
                'value' => Option::get('exclude_404s') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('exclude_404s') ? 'Enabled' : 'Disabled',
            ],
            'excludedURLs'                   => [
                'label' => esc_html__('Excluded URLs', 'wp-statistics'),
                'value' => Option::get('excluded_urls') ? __('Set', 'wp-statistics') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('excluded_urls') ? 'Set' : 'Not Set',
            ],
            'matomoReferrerSpamBlacklist'    => [
                'label' => esc_html__('Matomo Referrer Spam Blacklist', 'wp-statistics'),
                'value' => Option::get('referrerspam') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('referrerspam') ? 'Enabled' : 'Disabled',
            ],
            'logRecordExclusions'            => [
                'label' => esc_html__('Log Record Exclusions', 'wp-statistics'),
                'value' => Option::get('record_exclusions') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('record_exclusions') ? 'Enabled' : 'Disabled',
            ],
            'minRoleToViewStats'             => [
                'label' => esc_html__('Minimum Role to View Statistics', 'wp-statistics'),
                'value' => Option::get('read_capability') ? Option::get('read_capability') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('read_capability') ? Option::get('read_capability') : 'Not Set',
            ],
            'minRoleToManageSettings'        => [
                'label' => esc_html__('Minimum Role to Manage Settings', 'wp-statistics'),
                'value' => Option::get('manage_capability') ? Option::get('manage_capability') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('manage_capability') ? Option::get('manage_capability') : 'Not Set',
            ],
            'ipDetectionMethod'              => [
                'label' => esc_html__('IP Detection Method', 'wp-statistics'),
                'value' => Option::get('ip_method') ? Option::get('ip_method') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('ip_method') ? Option::get('ip_method') : 'Not Set',
            ],
            'automaticCleanup'               => [
                'label' => esc_html__('Automatic Cleanup', 'wp-statistics'),
                'value' => Option::get('schedule_dbmaint') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('schedule_dbmaint') ? 'Enabled' : 'Disabled',
            ],
            'purgeDataOlderThan'             => [
                'label' => esc_html__('Purge Data Older Than', 'wp-statistics'),
                'value' => Option::get('schedule_dbmaint_days') ? Option::get('schedule_dbmaint_days') : __('Not Set', 'wp-statistics'),
                'debug' => Option::get('schedule_dbmaint_days') ? Option::get('schedule_dbmaint_days') : 'Not Set',
            ],
            'shareAnonymousData'             => [
                'label' => esc_html__('Share Anonymous Data', 'wp-statistics'),
                'value' => Option::get('share_anonymous_data') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::get('share_anonymous_data') ? 'Enabled' : 'Disabled',
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
     * @return array
     */
    public function getAddOnsSettings()
    {
        $settings = [];

        /**
         * REST API
         */
        if (Helper::isAddOnActive('rest-api')) {
            $settings['apiServiceStatus'] = [
                'label' => esc_html__('API Service Status', 'wp-statistics'),
                'value' => Option::getByAddon('status', 'rest_api') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('status', 'rest_api') ? 'Enabled' : 'Disabled',
            ];
        }

        /**
         * Advanced Reporting
         */
        if (Helper::isAddOnActive('advanced-reporting')) {
            $settings['chooseYourReportTiming']    = [
                'label' => esc_html__('Choose Your Report Timing', 'wp-statistics'),
                'value' => Option::getByAddon('report_time_frame_type', 'advanced_reporting'),
            ];
            $settings['topMetrics']                = [
                'label' => esc_html__('Top Metrics', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_metrics', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_metrics', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['visitorsSummary']           = [
                'label' => esc_html__('Visitors Summary', 'wp-statistics'),
                'value' => Option::getByAddon('email_summary_stats', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_summary_stats', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['viewsChart']                = [
                'label' => esc_html__('Views Chart', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_hits_visits', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_hits_visits', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['searchEngineReferrals']     = [
                'label' => esc_html__('Search Engine Referrals', 'wp-statistics'),
                'value' => Option::getByAddon('email_search_engine', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_search_engine', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['searchEngineChart']         = [
                'label' => esc_html__('Search Engine Chart', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_search_engines', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_search_engines', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['topReferringDomains']       = [
                'label' => esc_html__('Top Referring Domains', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_referring', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_referring', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['topPages']                  = [
                'label' => esc_html__('Top Pages', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_ten_pages', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_ten_pages', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['topCountries']              = [
                'label' => esc_html__('Top Countries', 'wp-statistics'),
                'value' => Option::getByAddon('email_top_ten_countries', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_top_ten_countries', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['topBrowsers']               = [
                'label' => esc_html__('Top Browsers', 'wp-statistics'),
                'value' => Option::getByAddon('email_chart_top_browsers', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_chart_top_browsers', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['moreInformationButton']     = [
                'label' => esc_html__('More Information Button', 'wp-statistics'),
                'value' => Option::getByAddon('email_more_info_button', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_more_info_button', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['auto-GeneratedNotice']      = [
                'label' => esc_html__('Auto-Generated Notice', 'wp-statistics'),
                'value' => Option::getByAddon('email_disable_copyright', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('email_disable_copyright', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['emailPDFReportAttachments'] = [
                'label' => esc_html__('Email PDF Report Attachments', 'wp-statistics'),
                'value' => Option::getByAddon('pdf_report_status', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('pdf_report_status', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
            $settings['recordEmailLogs']           = [
                'label' => esc_html__('Record Email Logs', 'wp-statistics'),
                'value' => Option::getByAddon('record_email_logs', 'advanced_reporting') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('record_email_logs', 'advanced_reporting') ? 'Enabled' : 'Disabled',
            ];
        }

        /**
         * Real-time Stats
         */
        if (Helper::isAddOnActive('realtime-stats')) {
            $settings['chartMapRefreshRate'] = [
                'label' => esc_html__('Chart & Map Refresh Rate (seconds)', 'wp-statistics'),
                'value' => Option::getByAddon('interval_time', 'realtime_stats'),
            ];
        }

        /**
         * Advanced Widgets
         */
        if (Helper::isAddOnActive('widgets')) {
            $settings['refreshEvery']            = [
                'label' => esc_html__('Refresh Every', 'wp-statistics'),
                'value' => Option::getByAddon('cache_life', 'widgets'),
            ];
            $settings['useDefaultWidgetStyling'] = [
                'label' => esc_html__('Use Default Widget Styling', 'wp-statistics'),
                'value' => Option::getByAddon('disable_styles', 'widgets') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('disable_styles', 'widgets') ? 'Enabled' : 'Disabled',
            ];
        }

        /**
         * Customization
         */
        if (Helper::isAddOnActive('customization')) {
            $settings['whiteLabel']           = [
                'label' => esc_html__('White Label', 'wp-statistics'),
                'value' => Option::getByAddon('wps_white_label', 'customization') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('wps_white_label', 'customization') ? 'Enabled' : 'Disabled',
            ];
            $settings['enableOverviewWidget'] = [
                'label' => esc_html__('Enable Overview Widget', 'wp-statistics'),
                'value' => Option::getByAddon('show_wps_about_widget_overview', 'customization'),
            ];
        }

        /**
         * Data Plus
         */
        if (Helper::isAddOnActive('data-plus')) {
            $settings['linkTracker']            = [
                'label' => esc_html__('Link Tracker', 'wp-statistics'),
                'value' => Option::getByAddon('link_tracker', 'data_plus', '1') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('link_tracker', 'data_plus', '1') ? 'Enabled' : 'Disabled',
            ];
            $settings['downloadTracker']        = [
                'label' => esc_html__('Download Tracker', 'wp-statistics'),
                'value' => Option::getByAddon('download_tracker', 'data_plus', '1') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('download_tracker', 'data_plus', '1') ? 'Enabled' : 'Disabled',
            ];
            $settings['latestVisitorsInEditor'] = [
                'label' => esc_html__('Latest Visitors In Editor', 'wp-statistics'),
                'value' => Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') ? __('Enabled', 'wp-statistics') : __('Disabled', 'wp-statistics'),
                'debug' => Option::getByAddon('latest_visitors_metabox', 'data_plus', '1') ? 'Enabled' : 'Disabled',
            ];
        }

        /**
         * Mini Chart
         */
        if (Helper::isAddOnActive('mini-chart')) {
            $settings['chartMetric']    = [
                'label' => esc_html__('Chart Metric', 'wp-statistics'),
                'value' => Option::getByAddon('metric', 'mini_chart', 'views'),
            ];
            $settings['chartDateRange'] = [
                'label' => esc_html__('Chart Date Range', 'wp-statistics'),
                'value' => Option::getByAddon('date_range', 'mini_chart', '14'),
            ];
            $settings['countDisplay']   = [
                'label' => esc_html__('Count Display', 'wp-statistics'),
                'value' => Option::getByAddon('count_display', 'mini_chart', 'total'),
            ];
        }

        return $settings;
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
        foreach (\WP_STATISTICS\User::get_role_list() as $role) {
            $optionName = 'exclude_' . str_replace(" ", "_", strtolower($role));

            if (Option::get($optionName)) {
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
