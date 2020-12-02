<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * Description: This plugin gives you the complete information on your website's visitors.
 * Version: 13.0.3
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages
 */

# Exit if accessed directly
if (!defined('ABSPATH')) exit;

# Load Plugin Defines
require_once 'includes/defines.php';

# Include some empty class to make sure they are exist while upgrading plugin.
if (!class_exists('WP_Statistics_Welcome')) {
    class WP_Statistics_Welcome
    {
        public static function init(){}
        public static function menu(){}
        public static function page_callback(){}
        public static function do_welcome($upgrader_object, $options){}
        public static function show_change_log(){}
    }
}
if (!class_exists('WP_Statistics_Updates')) {
    class WP_Statistics_Updates
    {
        public static $geoip = array();
        static function do_upgrade(){}
        static function download_geoip($pack, $type = "enable"){}
        static function download_referrerspam(){}
        static function populate_geoip_info(){}
    }
}
add_filter('wp_statistics_show_welcome_page', 'wp_statistics_disable_show_welcome', 999);
function wp_statistics_disable_show_welcome(){
    update_option('wp_statistics_test', 'filter', 'no');
    return false;
}
remove_action( 'upgrader_process_complete', 'WP_Statistics_Welcome::do_welcome' );

# Load Plugin
if (!class_exists('WP_Statistics')) {
    require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
}

# Returns the main instance of WP-Statistics.
function WP_Statistics()
{
    return WP_Statistics::instance();
}

# Global for backwards compatibility.
$GLOBALS['WP_Statistics'] = WP_Statistics();