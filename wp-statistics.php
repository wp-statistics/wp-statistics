<?php
/**
 * Plugin Name: WP Statistics
 * Plugin URI: https://wp-statistics.com/
 * GitHub Plugin URI: https://github.com/wp-statistics/wp-statistics
 * Description: Get website traffic insights with GDPR/CCPA compliant, privacy-friendly analytics. Includes visitor data, stunning graphs, and no data sharing.
 * Version: 15.0
 * Author: VeronaLabs
 * Author URI: https://veronalabs.com/
 * Text Domain: wp-statistics
 * Domain Path: /resources/languages
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * License: GPL-2.0+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

# Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Mutual Exclusivity Check
 *
 * WP Statistics Pro includes all Free features. When Pro is active,
 * the Free version should not load to prevent conflicts.
 * ACF-style: Users install Free OR Pro, not both.
 */
register_activation_hook(__FILE__, function () {
    // Include plugin.php for is_plugin_active()
    if (!function_exists('is_plugin_active')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    // Check if Pro is already active
    if (is_plugin_active('wp-statistics-pro/wp-statistics-pro.php')) {
        wp_die(
            __('WP Statistics Pro is already active and includes all free features. Please deactivate Pro first if you want to use the free version.', 'wp-statistics'),
            __('Plugin Activation Error', 'wp-statistics'),
            ['back_link' => true]
        );
    }
});

/**
 * Check if Pro is active and skip loading Free
 *
 * Pro defines WP_STATISTICS_PRO_FILE constant. If it's defined,
 * Pro is handling everything and Free should stay dormant.
 */
if (defined('WP_STATISTICS_PRO_FILE')) {
    // Pro is active - show notice and don't load Free
    add_action('admin_notices', function () {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p>
                <strong><?php esc_html_e('WP Statistics', 'wp-statistics'); ?>:</strong>
                <?php esc_html_e('WP Statistics Pro is active and includes all free features. Please deactivate the free version to avoid conflicts.', 'wp-statistics'); ?>
            </p>
        </div>
        <?php
    });
    return; // Stop loading Free
}

# Load Plugin Constants
require_once __DIR__ . '/src/constants.php';

# Set another useful plugin define.
define('WP_STATISTICS_VERSION', '15.0');

# Load Composer autoloader
require_once WP_STATISTICS_DIR . 'vendor/autoload.php';

# Load global functions
require_once WP_STATISTICS_DIR . 'src/functions.php';

# Initialize plugin
WP_Statistics\Bootstrap::init();
