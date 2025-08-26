<?php

namespace WP_Statistics\Service\Tracking;

use Exception;
use WP_Statistics\Abstracts\BaseTrackerController;
use WP_Statistics\Globals\Option;
use WP_STATISTICS\Service\Tracking\Controllers\AjaxBasedTracking;
use WP_STATISTICS\Service\Tracking\Controllers\RestApiTracking;
use WP_STATISTICS\Service\Tracking\Controllers\ServerSideTracking;

/**
 * Factory class responsible for creating and managing tracking controller instances.
 *
 * This factory determines which tracking implementation to use based on plugin settings
 * and allows for custom tracking controllers through WordPress filters.
 *
 * @since 15.0.0
 */
class TrackerControllerFactory
{
    /**
     * Creates and returns the appropriate tracking controller based on settings.
     *
     * @return BaseTrackerController The configured tracking controller instance
     * @throws Exception If custom controller validation fails
     * @since 15.0.0
     */
    public static function createController()
    {
        $useClientSide   = Option::getValue('use_cache_plugin', true);
        $bypassAdblocker = Option::getValue('bypass_ad_blockers', false);

        /**
         * TODO: Add performance optimization setting, and remained controller.
         */

        if ($useClientSide) {
            if ($bypassAdblocker) {
                $controller = new AjaxBasedTracking();
            } else {
                $controller = new RestApiTracking();
            }
        } else {
            $controller = new ServerSideTracking();
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

        return $controller;
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
}