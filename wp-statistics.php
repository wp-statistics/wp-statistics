<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * GitHub Plugin URI: https://github.com/wp-statistics/wp-statistics
 * Description: Get complete website traffic insights with privacy-friendly, GDPR/CCPA compliant analytics, including visitor data, stunning graphs, and no third-party data sharing.
 * Version: 14.7
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

# Exit if accessed directly
if (!defined('ABSPATH')) exit;

# Load Plugin Defines
require_once __DIR__ . '/includes/defines.php';

# Include some empty class to make sure they are existed while upgrading plugin.
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-updates.php';
require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics-welcome.php';

# Load Plugin
if (!class_exists('WP_Statistics')) {
    require_once WP_STATISTICS_DIR . 'includes/class-wp-statistics.php';
}

# Returns the main instance of WP Statistics.
function WP_Statistics()
{
    return WP_Statistics::instance();
}

# Global for backwards compatibility.
$GLOBALS['WP_Statistics'] = WP_Statistics();