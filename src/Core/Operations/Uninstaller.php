<?php

namespace WP_Statistics\Core\Operations;

use WP_Statistics\Components\AssetNameObfuscator;
use WP_Statistics\Core\AbstractCore;
use WP_STATISTICS\DB;
use WP_STATISTICS\Option;

/**
 * Handles uninstall-time cleanup.
 *
 * On uninstall (and per site on multisite), this class removes plugin data
 * when the "delete_data_on_uninstall" option is enabled: options, transients,
 * scheduled hooks, user/post meta, and plugin-created tables.
 *
 * @see register_uninstall_hook()
 * @package WP_Statistics\Core\Operations
 */
class Uninstaller extends AbstractCore
{
    /**
     * Uninstaller constructor.
     *
     * @param bool $networkWide
     * @return void
     */
    public function __construct($networkWide = false)
    {
        parent::__construct($networkWide);
        $this->execute();
    }

    /**
     * Execute the core function.
     *
     * @return void
     */
    public function execute()
    {
        $this->loadRequiredFiles();


        if (is_multisite()) {
            $blog_ids = $this->wpdb->get_col("SELECT `blog_id` FROM {$this->wpdb->blogs}");

            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);

                if (Option::get('delete_data_on_uninstall')) {
                    $this->cleanupSiteData();
                }

                restore_current_blog();
            }
        } else {
            if (Option::get('delete_data_on_uninstall')) {
                $this->cleanupSiteData();
            }
        }
    }

    /**
     * Removes database options, user meta keys & tables
     */
    public function cleanupSiteData()
    {

        // Delete the options from the WordPress options table.
        delete_option('wp_statistics');
        delete_option('wp_statistics_privacy_status');
        delete_option('wp_statistics_plugin_version');
        delete_option('wp_statistics_referrals_detail');
        delete_option('wp_statistics_overview_page_ads');
        delete_option('wp_statistics_users_city');
        delete_option('wp_statistics_activate_addons');
        delete_option('wp_statistics_disable_addons');
        delete_option('wp_statistics_disable_addons_notice');
        delete_option('wp_statistics_check_user_online');
        delete_option('wp_statistics_daily_salt');
        delete_option('wp_statistics_dismissed_notices');
        delete_option('wp_statistics_dismissed_widgets');
        delete_option('wp_statistics_jobs');
        delete_option('wp_statistics_user_modals');
        delete_option('wp_statistics_closed_widgets');
        delete_option('wp_statistics_licenses');
        delete_option('wp_statistics_tracker_js_errors');
        delete_option('wp_statistics_db');
        delete_option('wp_statistics_installation_time');
        delete_option('wps_robotlist');
        delete_option('wp_statistics_cipher_key');

        // Delete the transients.
        delete_transient('wps_top_referring');
        delete_transient('wps_excluded_hostname_to_ip_cache');
        delete_transient('wps_check_rest_api');

        // Remove All Scheduled
        if (function_exists('wp_clear_scheduled_hook')) {
            wp_clear_scheduled_hook('wp_statistics_geoip_hook');
            wp_clear_scheduled_hook('wp_statistics_report_hook');
            wp_clear_scheduled_hook('wp_statistics_referrerspam_hook');
            wp_clear_scheduled_hook('wp_statistics_dbmaint_hook');
            wp_clear_scheduled_hook('wp_statistics_dbmaint_visitor_hook');
            wp_clear_scheduled_hook('wp_statistics_add_visit_hook');
            wp_clear_scheduled_hook('wp_statistics_optimize_table');
            wp_clear_scheduled_hook('wp_statistics_daily_cron_hook');
        }

        // Delete all hashed files and their options
        $assetNameObfuscator = new AssetNameObfuscator();
        $assetNameObfuscator->deleteAllHashedFiles();
        $assetNameObfuscator->deleteDatabaseOption();

        // Delete the user options.
        $this->wpdb->query("DELETE FROM {$this->wpdb->usermeta} WHERE `meta_key` LIKE 'wp_statistics%'");
        $this->wpdb->query("DELETE FROM {$this->wpdb->postmeta} WHERE `meta_key` LIKE 'wp_statistics%'");

        // Drop the tables
        foreach (DB::table() as $tbl) {
            $this->wpdb->query("DROP TABLE IF EXISTS {$tbl}");
        }
    }

    /**
     * Load core classes needed during uninstall.
     *
     * @return void
     * @todo Remove after PSR-4 autoloading is in place.
     */
    private function loadRequiredFiles()
    {
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-option.php';
        require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-db.php';
    }
}