<?php
/**
 * WP Statistics Uninstall Handler
 *
 * Fired when the plugin is uninstalled (deleted) from the WordPress admin.
 * Cleans up all plugin data including:
 * - Database tables
 * - Options
 * - Transients
 * - User meta
 * - Cron events
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Exit if accessed directly or not being uninstalled
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

/**
 * Main uninstall class.
 *
 * Using a class to prevent variable/function name collisions.
 */
class WP_Statistics_Uninstaller
{
    /**
     * Plugin option names to delete.
     *
     * @var array
     */
    private static $options = [
        // Main options
        'wp_statistics',
        'wp_statistics_plugin_version',
        'wp_statistics_is_fresh',
        'wp_statistics_installation_time',
        'wp_statistics_dismissed_notices',

        // Group options
        'wp_statistics_db',
        'wp_statistics_jobs',
        'wp_statistics_cache',

        // Legacy options (v14)
        'wps_main',
        'wp_statistics_admin_notices',
    ];

    /**
     * Plugin table names (without prefix).
     *
     * @var array
     */
    private static $tables = [
        'statistics_visitor',
        'statistics_visitor_relationships',
        'statistics_pages',
        'statistics_visit',
        'statistics_historical',
        'statistics_exclusions',
        'statistics_useronline',
        'statistics_events',
    ];

    /**
     * Cron hooks to clear.
     *
     * @var array
     */
    private static $cronHooks = [
        'wp_statistics_dbmaint_hook',
        'wp_statistics_referrerspam_hook',
        'wp_statistics_report_hook',
        'wp_statistics_geoip_hook',
        'wp_statistics_queue_daily_summary',
        'wp_statistics_check_licenses_status',
        'wp_statistics_referrals_db_hook',
        'wp_statistics_notification_hook',
        'wp_statistics_dbmaint_visitor_hook',
        'wp_statistics_marketing_campaign_hook',
        'wp_statistics_add_visit_hook',
    ];

    /**
     * Run the uninstaller.
     *
     * @return void
     */
    public static function run()
    {
        global $wpdb;

        // Check if we should delete data on uninstall
        $options = get_option('wp_statistics');
        $deleteOnUninstall = isset($options['delete_data_on_uninstall']) && $options['delete_data_on_uninstall'];

        if (!$deleteOnUninstall) {
            // User opted to keep data, only clear cron events
            self::clearCronEvents();
            return;
        }

        if (is_multisite()) {
            // Get all blog IDs
            $blogIds = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                self::uninstallSingleSite();
                restore_current_blog();
            }

            // Delete network-wide options if any
            self::deleteNetworkOptions();
        } else {
            self::uninstallSingleSite();
        }
    }

    /**
     * Uninstall for a single site.
     *
     * @return void
     */
    private static function uninstallSingleSite()
    {
        self::dropTables();
        self::deleteOptions();
        self::deleteTransients();
        self::deleteUserMeta();
        self::clearCronEvents();
        self::deleteUploadedFiles();
    }

    /**
     * Drop all plugin database tables.
     *
     * @return void
     */
    private static function dropTables()
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $tableName = $wpdb->prefix . $table;
            $wpdb->query("DROP TABLE IF EXISTS `{$tableName}`"); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        }
    }

    /**
     * Delete all plugin options.
     *
     * @return void
     */
    private static function deleteOptions()
    {
        global $wpdb;

        // Delete known options
        foreach (self::$options as $option) {
            delete_option($option);
        }

        // Delete any options with wp_statistics_ or wps_ prefix
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE 'wp_statistics_%'
             OR option_name LIKE 'wps_%'"
        );
    }

    /**
     * Delete network-wide options for multisite.
     *
     * @return void
     */
    private static function deleteNetworkOptions()
    {
        global $wpdb;

        // Delete from sitemeta for network-activated plugins
        $wpdb->query(
            "DELETE FROM {$wpdb->sitemeta}
             WHERE meta_key LIKE 'wp_statistics_%'
             OR meta_key LIKE 'wps_%'"
        );
    }

    /**
     * Delete all plugin transients.
     *
     * @return void
     */
    private static function deleteTransients()
    {
        global $wpdb;

        // Delete transients from options table
        $wpdb->query(
            "DELETE FROM {$wpdb->options}
             WHERE option_name LIKE '%_transient_wp_statistics%'
             OR option_name LIKE '%_transient_wps_%'"
        );
    }

    /**
     * Delete plugin-related user meta.
     *
     * @return void
     */
    private static function deleteUserMeta()
    {
        global $wpdb;

        // Delete wp_statistics user meta
        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta}
             WHERE meta_key = 'wp_statistics'
             OR meta_key LIKE 'wp_statistics_%'
             OR meta_key LIKE 'wps_%'"
        );

        // Delete metabox preferences for WP Statistics pages
        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta}
             WHERE meta_key LIKE 'metaboxhidden_toplevel_page_wp-statistics%'
             OR meta_key LIKE 'meta-box-order_toplevel_page_wp-statistics%'
             OR meta_key LIKE 'screen_layout_toplevel_page_wp-statistics%'"
        );
    }

    /**
     * Clear all scheduled cron events.
     *
     * @return void
     */
    private static function clearCronEvents()
    {
        foreach (self::$cronHooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Delete plugin-created files from uploads directory.
     *
     * @return void
     */
    private static function deleteUploadedFiles()
    {
        $uploadDir = wp_upload_dir();
        $pluginUploadPath = $uploadDir['basedir'] . '/wp-statistics';

        if (is_dir($pluginUploadPath)) {
            self::recursiveDelete($pluginUploadPath);
        }
    }

    /**
     * Recursively delete a directory and its contents.
     *
     * @param string $dir Directory path.
     * @return bool
     */
    private static function recursiveDelete($dir)
    {
        if (!is_dir($dir)) {
            return false;
        }

        $files = array_diff(scandir($dir), ['.', '..']);

        foreach ($files as $file) {
            $path = $dir . '/' . $file;

            if (is_dir($path)) {
                self::recursiveDelete($path);
            } else {
                unlink($path);
            }
        }

        return rmdir($dir);
    }
}

// Run the uninstaller
WP_Statistics_Uninstaller::run();
