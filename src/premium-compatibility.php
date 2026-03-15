<?php
/**
 * Premium Compatibility Check
 *
 * Handles the case when WP Statistics Premium is active alongside Free.
 * Premium includes all Free features, so Free should stay dormant.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if Premium is active and handle gracefully
 *
 * @return bool True if Premium is active (Free should stop loading), false otherwise
 */
function wp_statistics_is_premium_active(): bool
{
    return defined('WP_STATISTICS_PREMIUM_FILE');
}

/**
 * Display admin notice when Premium is active
 *
 * @return void
 */
function wp_statistics_premium_active_notice(): void
{
    ?>
    <div class="notice notice-warning">
        <p>
            <strong><?php esc_html_e('WP Statistics', 'wp-statistics'); ?>:</strong>
            <?php esc_html_e('WP Statistics Premium is active and includes all free features. Please deactivate the free version to avoid conflicts.', 'wp-statistics'); ?>
        </p>
    </div>
    <?php
}

/**
 * Initialize Premium compatibility mode
 *
 * Loads textdomain and registers admin notices when Premium is active.
 *
 * @param string $plugin_file Main plugin file path
 * @return void
 */
function wp_statistics_init_premium_compatibility(string $plugin_file): void
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
    add_action('admin_notices', 'wp_statistics_premium_active_notice');

    // Register notice for network admin on multisite
    if (is_multisite()) {
        add_action('network_admin_notices', 'wp_statistics_premium_active_notice');
    }
}
