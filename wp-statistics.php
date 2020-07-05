<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * Description: Complete WordPress Analytics and Statistics for your site!
 * Version: 12.7
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages
 */

# Exit if accessed directly
if (!defined('ABSPATH')) exit;

# Load Plugin Defines
require_once 'includes/defines.php';

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