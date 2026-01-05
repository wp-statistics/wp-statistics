<?php

namespace WP_Statistics\Service\Tracking;

use Exception;
use WP_Statistics\Abstracts\BaseTrackerController;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Controllers\AjaxBasedTracking;
use WP_Statistics\Service\Tracking\Controllers\BatchTracking;
use WP_Statistics\Service\Tracking\Controllers\RestApiTracking;

/**
 * Factory class responsible for creating and managing tracking controller instances.
 *
 * This factory determines which tracking implementation to use based on plugin settings
 * and allows for custom tracking controllers through WordPress filters.
 *
 * Uses lazy loading - the controller is only created once and cached for subsequent calls.
 *
 * @since 15.0.0
 */
class TrackerControllerFactory
{
    /**
     * Cached controller instance (lazy loaded).
     *
     * @var BaseTrackerController|null
     */
    private static $controller = null;

    /**
     * Whether batch tracking has been initialized.
     *
     * @var bool
     */
    private static $batchInitialized = false;

    /**
     * Creates and returns the appropriate tracking controller based on settings.
     *
     * Uses caching - the controller is created once and reused for subsequent calls.
     *
     * @return BaseTrackerController The configured tracking controller instance
     * @throws Exception If custom controller validation fails
     * @since 15.0.0
     */
    public static function createController()
    {
        // Return cached instance if available
        if (self::$controller !== null) {
            return self::$controller;
        }

        $bypassAdblocker = Option::getValue('bypass_ad_blockers', false);

        if ($bypassAdblocker) {
            $controller = new AjaxBasedTracking();
        } else {
            $controller = new RestApiTracking();
        }

        // Initialize batch tracking only once
        if (!self::$batchInitialized) {
            new BatchTracking();
            self::$batchInitialized = true;
        }

        /**
         * Filter the tracking controller instance.
         *
         * @param BaseTrackerController $controller The default tracking controller instance.
         * @return BaseTrackerController The filtered tracking controller instance.
         * @since 15.0.0
         */
        $controller = apply_filters('wp_statistics_tracker_controller', $controller);

        // Validate custom controller
        if (!($controller instanceof BaseTrackerController)) {
            throw new Exception('Custom tracker controller must extend BaseTrackerController');
        }

        // Cache the controller
        self::$controller = $controller;

        return self::$controller;
    }

    /**
     * Retrieves the tracking endpoint route from the active controller.
     *
     * @return string|null The tracking endpoint route or null if an error occurs.
     * @since 15.0.0
     */
    public static function getTrackingRoute()
    {
        try {
            $controller = self::createController();
            return $controller->getRoute();
        } catch (Exception $e) {
            error_log('WP Statistics: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Reset the cached controller.
     *
     * Useful for testing or when settings change.
     *
     * @return void
     * @since 15.0.0
     */
    public static function reset()
    {
        self::$controller = null;
        // Note: We don't reset $batchInitialized since hooks are already registered
    }
}