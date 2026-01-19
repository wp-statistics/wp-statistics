<?php
/**
 * WP Statistics Constants
 *
 * Defines core plugin path and URL constants.
 * Must be loaded before any other plugin files.
 *
 * @package WP_Statistics
 * @since 15.0.0
 */

// Ensure get_plugin_data function exists
if (!function_exists('get_plugin_data')) {
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Plugin root directory (parent of /src)
$pluginDir = dirname(__DIR__);

// Set plugin path and URL constants
define('WP_STATISTICS_URL', plugin_dir_url($pluginDir . '/wp-statistics.php'));
define('WP_STATISTICS_DIR', $pluginDir . '/');
define('WP_STATISTICS_MAIN_FILE', WP_STATISTICS_DIR . 'wp-statistics.php');
define('WP_STATISTICS_UPLOADS_DIR', 'wp-statistics');
define('WP_STATISTICS_SITE_URL', 'https://wp-statistics.com');

// Plugin version (also defined in wp-statistics.php for redundancy)
// This ensures version is available early in load sequence
if (!defined('WP_STATISTICS_VERSION')) {
    define('WP_STATISTICS_VERSION', '15.0');
}
