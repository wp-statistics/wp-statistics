<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Service\Tracking\Methods\BaseTracking;
use WP_Statistics\Service\Tracking\Methods\BatchTracking;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Methods\AjaxTracking;
use WP_Statistics\Service\Tracking\Methods\DirectFile\DirectFileTracking;
use WP_Statistics\Service\Tracking\Methods\RestTracking;

/**
 * Central manager for the tracking layer.
 *
 * Transport layer (what the JS tracker sends hits to):
 *   - Default: AJAX (admin-ajax.php) — works everywhere
 *   - Optional: Direct File (mu-plugin endpoint) — highest performance
 *
 * REST routes are always registered for headless/API consumers.
 * Batch tracking is always registered independently of the hit transport.
 *
 * Independent toggles:
 *   - `bypass_ad_blockers` — obfuscates tracker.js filename/URL
 *   - `direct_file_tracking` — switches transport to mu-plugin endpoint
 *
 * @since 15.0.0
 */
class TrackingManager
{
    /**
     * @var BaseTracking
     */
    private $trackingMethod;

    /**
     * @var BatchTracking
     */
    private $batchTracking;

    /**
     * Register tracking endpoints.
     *
     * Called once during plugin boot.
     */
    public function register(): void
    {
        // Always register REST routes (for headless/API consumers).
        (new RestTracking())->register();

        // Always register batch tracking (independent of hit transport).
        $this->batchTracking = new BatchTracking();
        $this->batchTracking->register();

        // Register the active transport method.
        $this->trackingMethod = $this->getTrackingMethod();
        $this->trackingMethod->register();

        add_action('wp_statistics_settings_saved', [$this, 'onSettingsSaved'], 10, 2);
    }

    /**
     * Get the full JS tracker configuration from the active transport method,
     * merged with the batch endpoint.
     *
     * @return array
     */
    public function getTrackerConfig(): array
    {
        $config = $this->trackingMethod ? $this->trackingMethod->getTrackerConfig() : [];

        $config['batchEndpoint'] = $this->batchTracking->getBatchEndpoint();

        return $config;
    }

    /**
     * Diagnostic route string for health checks.
     */
    public function getTrackingRoute(): ?string
    {
        return $this->trackingMethod ? $this->trackingMethod->getRoute() : null;
    }

    // ── Settings lifecycle ─────────────────────────────────────────

    public function onSettingsSaved(string $tab, array $settings): void
    {
        if (!array_key_exists('direct_file_tracking', $settings)) {
            return;
        }

        // Deactivate the current method, activate the new one.
        $this->trackingMethod->deactivate();

        $this->trackingMethod = $this->getTrackingMethod();
        $this->trackingMethod->activate();
    }

    // ── Internal ───────────────────────────────────────────────────

    private function getTrackingMethod(): BaseTracking
    {
        $trackingMethod = Option::getValue('direct_file_tracking')
            ? new DirectFileTracking()
            : new AjaxTracking();

        /**
         * Filter the active tracking method instance.
         *
         * @param BaseTracking $trackingMethod The default tracking method.
         * @return BaseTracking The filtered tracking method.
         * @since 15.0.0
         */
        $trackingMethod = apply_filters('wp_statistics_tracker_controller', $trackingMethod);

        if (!($trackingMethod instanceof BaseTracking)) {
            throw new \Exception('Custom tracking method must extend BaseTracking');
        }

        return $trackingMethod;
    }
}
