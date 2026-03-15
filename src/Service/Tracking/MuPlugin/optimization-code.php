<?php
/**
 * Plugin Name: WP Statistics Optimizer
 * Description: Reduces plugin load on WP Statistics tracking requests for better performance.
 * Version: 1.0.0
 * Author: VeronaLabs
 *
 * This mu-plugin intercepts WP Statistics tracking requests and excludes
 * all non-essential plugins from loading, significantly reducing response time.
 *
 * @since 15.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if the current request is a WP Statistics tracking request.
 */
function wp_statistics_is_tracking_request()
{
    if (!isset($_SERVER['REQUEST_URI'])) {
        return false;
    }

    $uri = $_SERVER['REQUEST_URI'];

    // REST API tracking endpoints
    if (strpos($uri, '/wp-statistics/v2/hit') !== false || strpos($uri, '/wp-statistics/v2/batch') !== false) {
        return true;
    }

    // AJAX tracking endpoints
    if (strpos($uri, 'admin-ajax.php') !== false && isset($_REQUEST['action'])) {
        $trackingActions = [
            'wp_statistics_hit_record',
            'wp_statistics_batch',
        ];
        if (in_array($_REQUEST['action'], $trackingActions, true)) {
            return true;
        }
    }

    // Direct endpoint
    if (strpos($uri, 'wp-statistics-endpoint.php') !== false) {
        return true;
    }

    return false;
}

if (wp_statistics_is_tracking_request()) {
    add_filter('option_active_plugins', function ($plugins) {
        if (!is_array($plugins)) {
            return $plugins;
        }

        // Always keep WP Statistics
        $keep = array_filter($plugins, function ($plugin) {
            return strpos($plugin, 'wp-statistics') !== false;
        });

        /**
         * Filter the list of plugins to keep active during tracking requests.
         *
         * @param array $keep    Plugins to keep active.
         * @param array $plugins All active plugins.
         * @since 15.0.0
         */
        $keep = apply_filters('wp_statistics_mu_plugin_keep_plugins', $keep, $plugins);

        return array_values($keep);
    });
}
