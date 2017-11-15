<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * Description: Complete statistics for your WordPress site.
 * Version: 12.1.3
 * Author: Verona Labs
 * Author URI: http://veronalabs.com/
 *
 * Text Domain: wp-statistics
 * Domain Path: /languages/
 */

define('WP_STATISTICS_MAIN_FILE', __FILE__);
include_once plugin_dir_path(__FILE__) . 'includes/classes/class-wp-statistics.php';
$WP_Statistics = new WP_Statistics();
/* Silence is golden! */
