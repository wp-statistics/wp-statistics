<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Abstracts\BaseTrackerController;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Controllers\AjaxBasedTracking;
use WP_Statistics\Service\Tracking\Controllers\BatchTracking;
use WP_Statistics\Service\Tracking\Controllers\RestApiTracking;
use WP_Statistics\Service\Tracking\DirectEndpoint\DirectEndpointManager;

/**
 * Central manager for the three tracking delivery methods:
 *
 *  1. REST API    — default, uses /wp-json/wp-statistics/v2/hit
 *  2. AJAX        — when tracking_method is 'ajax'
 *  3. Direct File — SHORTINIT mu-plugin endpoint (highest performance)
 *
 * Owns controller registration, direct-endpoint lifecycle,
 * and URL resolution for the frontend tracker script.
 *
 * @since 15.0.0
 */
class TrackingManager
{
    public const METHOD_REST        = 'rest';
    public const METHOD_AJAX        = 'ajax';
    public const METHOD_DIRECT_FILE = 'direct_file';

    /**
     * @var BaseTrackerController
     */
    private $hitController;

    /**
     * @var DirectEndpointManager
     */
    private $directEndpoint;

    public function __construct()
    {
        $this->directEndpoint = new DirectEndpointManager();
    }

    /**
     * Register all tracking endpoints.
     *
     * Called once during plugin boot.
     */
    public function register(): void
    {
        // 1. Hit endpoint: REST or AJAX based on settings
        $this->hitController = $this->createHitController();

        // 2. Batch endpoint: always active
        new BatchTracking();

        // 3. Direct file endpoint: install/update if enabled
        $this->directEndpoint->register();
    }

    /**
     * Get the URL the JS tracker should POST hits to.
     *
     * Priority: direct file endpoint > controller route.
     *
     * @return string
     */
    public function getHitUrl(): string
    {
        if ($this->isDirectEndpointActive()) {
            return $this->directEndpoint->getEndpointUrl();
        }

        return $this->hitController ? $this->hitController->getRoute() : '';
    }

    /**
     * Get the URL for batch/engagement events.
     *
     * When direct endpoint is active, batch goes through it too.
     * Otherwise follows the same REST/AJAX split as hits.
     *
     * @return string
     */
    public function getBatchUrl(): string
    {
        if ($this->isDirectEndpointActive()) {
            return $this->directEndpoint->getEndpointUrl();
        }

        if ($this->getTrackingMethod() === self::METHOD_AJAX) {
            return '';  // AJAX batch URL is built client-side from ajaxUrl
        }

        return rest_url('wp-statistics/v2/batch');
    }

    /**
     * Get the tracking route from the active hit controller.
     *
     * Used by diagnostic checks.
     *
     * @return string|null
     */
    public function getTrackingRoute(): ?string
    {
        if ($this->hitController) {
            return $this->hitController->getRoute();
        }

        return null;
    }

    /**
     * Get the direct endpoint manager instance.
     *
     * @return DirectEndpointManager
     */
    public function getDirectEndpointManager(): DirectEndpointManager
    {
        return $this->directEndpoint;
    }

    /**
     * Get the active tracking method.
     *
     * @return string One of 'rest', 'ajax', 'direct_file'.
     */
    public function getTrackingMethod(): string
    {
        return Option::getValue('tracking_method', self::METHOD_REST);
    }

    /**
     * Check if the direct file endpoint is active and installed.
     *
     * @return bool
     */
    public function isDirectEndpointActive(): bool
    {
        return $this->getTrackingMethod() === self::METHOD_DIRECT_FILE
            && $this->directEndpoint->isInstalled();
    }

    /**
     * Create the appropriate hit controller based on settings.
     *
     * Preserves the wp_statistics_tracker_controller filter for
     * third-party extensions that override the hit controller.
     *
     * @return BaseTrackerController
     */
    private function createHitController(): BaseTrackerController
    {
        if ($this->getTrackingMethod() === self::METHOD_AJAX) {
            $controller = new AjaxBasedTracking();
        } else {
            $controller = new RestApiTracking();
        }

        /**
         * Filter the tracking controller instance.
         *
         * @param BaseTrackerController $controller The default tracking controller instance.
         * @return BaseTrackerController The filtered tracking controller instance.
         * @since 15.0.0
         */
        $controller = apply_filters('wp_statistics_tracker_controller', $controller);

        if (!($controller instanceof BaseTrackerController)) {
            throw new \Exception('Custom tracker controller must extend BaseTrackerController');
        }

        return $controller;
    }

}
