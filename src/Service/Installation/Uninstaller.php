<?php

namespace WP_Statistics\Service\Installation;

/**
 * Handles plugin uninstallation and cleanup operations.
 *
 * This class manages the cleanup of all plugin data when the plugin is:
 * - Deactivated (clears cron events and temporary files)
 * - Uninstalled/deleted (optionally removes all data based on user settings)
 *
 * @package WP_Statistics\Service\Installation
 * @since 15.0.0
 */
class Uninstaller
{
    /**
     * Plugin option WP_Statistics_names to delete on full uninstall.
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
     * Plugin table WP_Statistics_names (without prefix).
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
        // Current v15 hooks
        'wp_statistics_dbmaint_hook',
        'wp_statistics_referrerspam_hook',
        'wp_statistics_geoip_hook',
        'wp_statistics_email_report',
        'wp_statistics_queue_daily_summary',
        'wp_statistics_licenses_hook',
        'wp_statistics_check_licenses_status',
        'wp_statistics_referrals_db_hook',
        'wp_statistics_daily_cron_hook',

        // Optional hooks (self-managed but cleanup on uninstall)
        'wp_statistics_anonymized_share_data_hook',

        // Legacy hooks (v14 cleanup)
        'wp_statistics_report_hook',
        'wp_statistics_notification_hook',
        'wp_statistics_dbmaint_visitor_hook',
        'wp_statistics_marketing_campaign_hook',
        'wp_statistics_add_visit_hook',
    ];

    /**
     * Run deactivation cleanup.
     *
     * Called when the plugin is deactivated. Clears temporary data
     * but preserves user data and settings.
     *
     * @return void
     */
    public static function deactivate(): void
    {
        self::clearCronEvents();
        self::deleteObfuscatedAssets();
        self::deleteTransients();
    }

    /**
     * Run full uninstall cleanup.
     *
     * Called when the plugin is deleted. Always runs deactivation cleanup,
     * then removes all data if user enabled delete_data_on_uninstall option.
     *
     * @return void
     */
    public static function uninstall(): void
    {
        global $wpdb;

        // Always run deactivation cleanup first
        self::deactivate();

        // Check if we should delete data on uninstall
        $options = get_option('wp_statistics');
        $deleteOnUninstall = isset($options['delete_data_on_uninstall']) && $options['delete_data_on_uninstall'];

        if (!$deleteOnUninstall) {
            return;
        }

        if (is_multisite()) {
            $blogIds = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}");

            foreach ($blogIds as $blogId) {
                switch_to_blog($blogId);
                self::uninstallSingleSite();
                restore_current_blog();
            }

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
    private static function uninstallSingleSite(): void
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
    private static function dropTables(): void
    {
        global $wpdb;

        foreach (self::$tables as $table) {
            $tableName = $wpdb->prefix . $table;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query("DROP TABLE IF EXISTS `{$tableName}`");
        }
    }

    /**
     * Delete all plugin options.
     *
     * @return void
     */
    private static function deleteOptions(): void
    {
        global $wpdb;

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
    private static function deleteNetworkOptions(): void
    {
        global $wpdb;

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
    private static function deleteTransients(): void
    {
        global $wpdb;

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
    private static function deleteUserMeta(): void
    {
        global $wpdb;

        $wpdb->query(
            "DELETE FROM {$wpdb->usermeta}
             WHERE meta_key = 'wp_statistics'
             OR meta_key LIKE 'wp_statistics_%'
             OR meta_key LIKE 'wps_%'"
        );

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
    public static function clearCronEvents(): void
    {
        foreach (self::$cronHooks as $hook) {
            wp_clear_scheduled_hook($hook);
        }
    }

    /**
     * Delete obfuscated asset files (bypass ad blockers feature).
     *
     * @return void
     */
    private static function deleteObfuscatedAssets(): void
    {
        if (!class_exists('WP_Statistics\Components\AssetNameObfuscator')) {
            return;
        }

        $obfuscator = new \WP_Statistics\Components\AssetNameObfuscator();
        $obfuscator->deleteAllHashedFiles();
    }

    /**
     * Delete plugin-created files from uploads directory.
     *
     * @return void
     */
    private static function deleteUploadedFiles(): void
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
    private static function recursiveDelete(string $dir): bool
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
