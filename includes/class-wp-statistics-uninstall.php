<?php

namespace WP_STATISTICS;

class Uninstall
{

    public function __construct()
    {
        global $wpdb;

        if (is_multisite()) {

            $blog_ids = $wpdb->get_col("SELECT `blog_id` FROM $wpdb->blogs");
            foreach ($blog_ids as $blog_id) {
                switch_to_blog($blog_id);
                $this->wp_statistics_site_removal();
                restore_current_blog();
            }

        } else {
            $this->wp_statistics_site_removal();
        }
    }

    /**
     * Removes database options, user meta keys & tables
     */
    public function wp_statistics_site_removal()
    {
        global $wpdb;

        // Delete the options from the WordPress options table.
        delete_option('wp_statistics');
        delete_option('wp_statistics_plugin_version');
        delete_option('wp_statistics_referrals_detail');
        delete_option('wp_statistics_overview_page_ads');
        delete_option('wp_statistics_users_city');

        // Delete the transients.
        delete_transient('wps_top_referring');
        delete_transient('wps_excluded_hostname_to_ip_cache');

        // Delete the user options.
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE `meta_key` LIKE 'wp_statistics%'");

        // Drop the tables
        foreach (DB::table() as $tbl) {
            $wpdb->query("DROP TABLE IF EXISTS {$tbl}");
        }
    }
}
