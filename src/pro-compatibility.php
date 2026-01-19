<?php
/**
 * Pro Compatibility Check
 *
 * Handles the case when WP Statistics Pro is active alongside Free.
 * Pro includes all Free features, so Free should stay dormant.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if Pro is active and handle gracefully
 *
 * @return bool True if Pro is active (Free should stop loading), false otherwise
 */
function wp_statistics_is_pro_active(): bool
{
    return defined('WP_STATISTICS_PRO_FILE');
}

/**
 * Display admin notice when Pro is active
 *
 * @return void
 */
function wp_statistics_pro_active_notice(): void
{
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e('WP Statistics', 'wp-statistics'); ?>:</strong>
            <?php esc_html_e('WP Statistics Pro is active and includes all free features. Please deactivate the free version to avoid conflicts.', 'wp-statistics'); ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize Pro compatibility mode
 *
 * Loads textdomain and registers admin notices when Pro is active.
 *
 * @param string $plugin_file Main plugin file path
 * @return void
 */
function wp_statistics_init_pro_compatibility(string $plugin_file): void
{
    // Load textdomain early so notice can be translated
    add_action('init', function () use ($plugin_file) {
        load_plugin_textdomain(
            'wp-statistics',
            false,
            dirname(plugin_basename($plugin_file)) . '/resources/languages'
        );
    }, 1);

    // Register notice for regular admin
    add_action('admin_notices', 'wp_statistics_pro_active_notice');

    // Register notice for network admin on multisite
    if (is_multisite()) {
        add_action('network_admin_notices', 'wp_statistics_pro_active_notice');
    }
}
