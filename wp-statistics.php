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
 * Check if Pro is active and skip loading Free
 *
 * Pro defines WP_STATISTICS_PRO_FILE constant. If it's defined,
 * Pro is handling everything and Free should stay dormant.
 */
if (defined('WP_STATISTICS_PRO_FILE')) {
    // Pro is active - show persistent notice asking user to deactivate Free
    add_action('admin_notices', function () {
        ?>
        <div class="notice notice-warning">
            <p>
                <strong><?php esc_html_e('WP Statistics', 'wp-statistics'); ?>:</strong>
                <?php esc_html_e('WP Statistics Pro is active and includes all free features. Please deactivate the free version to avoid conflicts.', 'wp-statistics'); ?>
            </p>
        </div>
        <?php
    });
    return; // Stop loading Free
}

# Load Plugin Constants (includes WP_STATISTICS_VERSION)
require_once __DIR__ . '/src/constants.php';

# Load Composer autoloader
require_once WP_STATISTICS_DIR . 'vendor/autoload.php';

# Load global functions
require_once WP_STATISTICS_DIR . 'src/functions.php';

# Initialize plugin
WP_Statistics\Bootstrap::init();
