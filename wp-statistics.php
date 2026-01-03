<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * GitHub Plugin URI: https://github.com/wp-statistics/wp-statistics
 * Description: Get website traffic insights with GDPR/CCPA compliant, privacy-friendly analytics. Includes visitor data, stunning graphs, and no data sharing.
 * Version: 14.15.1
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages
 * Requires at least: 5.3
 * Requires PHP: 7.2
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

# Exit if accessed directly
if (!defined('ABSPATH')) exit;

# Load Plugin Defines
require_once __DIR__ . '/includes/defines.php';

# Set another useful plugin define.
define('WP_STATISTICS_VERSION', '14.15.1');

# Load Composer autoloader
require_once WP_STATISTICS_DIR . 'vendor/autoload.php';

# Load global functions
require_once WP_STATISTICS_DIR . 'src/functions.php';

# Initialize plugin
WP_Statistics\Bootstrap::init();