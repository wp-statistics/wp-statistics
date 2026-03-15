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
// These use if-checks to allow Premium plugin to define them first
if (!defined('WP_STATISTICS_URL')) {
    define('WP_STATISTICS_URL', plugin_dir_url($pluginDir . '/wp-statistics.php'));
}
if (!defined('WP_STATISTICS_DIR')) {
    define('WP_STATISTICS_DIR', $pluginDir . '/');
}
if (!defined('WP_STATISTICS_MAIN_FILE')) {
    define('WP_STATISTICS_MAIN_FILE', WP_STATISTICS_DIR . 'wp-statistics.php');
}
if (!defined('WP_STATISTICS_UPLOADS_DIR')) {
    define('WP_STATISTICS_UPLOADS_DIR', 'wp-statistics');
}
if (!defined('WP_STATISTICS_SITE_URL')) {
    define('WP_STATISTICS_SITE_URL', 'https://wp-statistics.com');
}

// Plugin version
if (!defined('WP_STATISTICS_VERSION')) {
    define('WP_STATISTICS_VERSION', '15.0');
}
