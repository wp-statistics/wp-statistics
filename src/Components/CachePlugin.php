<?php

namespace WP_Statistics\Components;

/**
 * Detects and identifies active WordPress caching mechanisms.
 *
 * This component checks for known WordPress caching plugins and the core object cache,
 * caching the result per request to minimize overhead. It supports plugins like WP Rocket,
 * WP Super Cache, Comet Cache, WP Fastest Cache, Cache Enabler, W3 Total Cache, WP-Optimize,
 * and the built-in object cache (via WP_CACHE).
 *
 * Developers can extend detection using the {@see 'wp_statistics_cache_status'} filter.
 *
 * @package WP_Statistics\Components
 * @since 15.0.0
 */
class CachePlugin
{
    /**
     * Cached detection result for the current request.
     *
     * Holds an associative array with keys `status` (bool), `label` (string),
     * and `debug` (string) after detection runs, or `null` before first use.
     *
     * @var array{status:bool,label:string,debug:string}|null
     */
    private static $pluginInfo = null;

    /**
     * Check whether any supported cache layer is active.
     *
     * Runs detection once per request via {@see self::getAll()} and returns
     * the cached boolean state.
     *
     * @return bool True when a cache plugin—or core object cache—is detected.
     */
    public static function isActive()
    {
        $pluginInfo = self::getAll();

        return !empty($pluginInfo['status']);
    }

    /**
     * Get the human‑readable label of the detected cache plugin.
     *
     * Returns an empty string when no supported cache layer is active.
     *
     * @return string Cache‑plugin label or an empty string.
     */
    public static function getLabel()
    {
        $pluginInfo = self::getAll();

        return isset($pluginInfo['label']) ? $pluginInfo['label'] : '';
    }

    /**
     * Detect the active cache plugin and cache the result.
     *
     * The method iterates through known cache implementations, caching the
     * first match. Subsequent calls return the stored result without running
     * detection again.
     *
     * @return array{status:bool,label:string,debug:string} Detection result.
     */
    public static function getAll()
    {
        if (self::$pluginInfo !== null) {
            return self::$pluginInfo;
        }

        if (defined('WP_CACHE') && WP_CACHE) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('WordPress Object Cache', 'wp-statistics'),
                'debug'  => 'WordPress Object Cache',
            ];

            return self::$pluginInfo;
        }

        if (function_exists('get_rocket_cdn_url')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('WP Rocket', 'wp-statistics'),
                'debug'  => 'WP Rocket',
            ];

            return self::$pluginInfo;
        }

        if (function_exists('wpsc_init')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('WP Super Cache', 'wp-statistics'),
                'debug'  => 'WP Super Cache',
            ];

            return self::$pluginInfo;
        }

        if (function_exists('___wp_php_rv_initialize')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('Comet Cache', 'wp-statistics'),
                'debug'  => 'Comet Cache',
            ];

            return self::$pluginInfo;
        }

        if (class_exists('WpFastestCache')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('WP Fastest Cache', 'wp-statistics'),
                'debug'  => 'WP Fastest Cache',
            ];

            return self::$pluginInfo;
        }

        if (defined('CE_MIN_WP')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('Cache Enabler', 'wp-statistics'),
                'debug'  => 'Cache Enabler',
            ];

            return self::$pluginInfo;
        }

        if (defined('W3TC')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('W3 Total Cache', 'wp-statistics'),
                'debug'  => 'W3 Total Cache',
            ];

            return self::$pluginInfo;
        }

        if (class_exists('WP_Optimize')) {
            self::$pluginInfo = [
                'status' => true,
                'label'  => __('WP-Optimize', 'wp-statistics'),
                'debug'  => 'WP-Optimize',
            ];

            return self::$pluginInfo;
        }

        self::$pluginInfo = apply_filters(
            'wp_statistics_cache_status',
            [
                'status' => false,
                'label'  => '',
                'debug'  => '',
            ]
        );

        return self::$pluginInfo;
    }
}