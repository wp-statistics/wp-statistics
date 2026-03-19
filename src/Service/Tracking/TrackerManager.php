<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Service\Tracking\Methods\BaseTracker;
use WP_Statistics\Service\Tracking\Methods\BatchEndpoint;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Methods\AjaxTracker;
use WP_Statistics\Service\Tracking\Methods\HybridMode\HybridModeTracker;
use WP_Statistics\Service\Tracking\Methods\RestTracker;

/**
 * Central manager for the tracking layer.
 *
 * Transport layer (what the JS tracker sends hits to):
 *   - Default: AJAX (admin-ajax.php) — works everywhere
 *   - Optional: Hybrid Mode (mu-plugin endpoint) — highest performance
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
class TrackerManager
{
    /**
     * @var BaseTracker
     */
    private $trackerMethod;

    /**
     * @var BatchEndpoint
     */
    private $batchTracker;

    /**
     * Register tracking endpoints.
     *
     * Called once during plugin boot.
     */
    public function register(): void
    {
        // Always register REST routes (for headless/API consumers).
        if (apply_filters('wp_statistics_tracker_enabled', true, 'rest')) {
            (new RestTracker())->register();
        }

        // Always register batch tracking (independent of hit transport).
        if (apply_filters('wp_statistics_tracker_enabled', true, 'batch')) {
            $this->batchTracker = new BatchEndpoint();
            $this->batchTracker->register();
        }

        // Register the active transport method.
        $this->trackerMethod = $this->getTrackerMethod();
        if (apply_filters('wp_statistics_tracker_enabled', true, $this->trackerMethod->getMethodType())) {
            $this->trackerMethod->register();
        }

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
        $config = $this->trackerMethod ? $this->trackerMethod->getTrackerConfig() : [];

        if ($this->batchTracker) {
            $config['batchEndpoint'] = $this->batchTracker->getBatchEndpoint();
        }

        return $config;
    }

    /**
     * Diagnostic route string for health checks.
     */
    public function getTrackerRoute(): ?string
    {
        return $this->trackerMethod ? $this->trackerMethod->getRoute() : null;
    }

    // ── Settings lifecycle ─────────────────────────────────────────

    public function onSettingsSaved(string $tab, array $settings): void
    {
        if (!array_key_exists('direct_file_tracking', $settings)) {
            return;
        }

        if (!$this->trackerMethod) {
            return;
        }

        // Deactivate the current method, activate the new one.
        $this->trackerMethod->deactivate();

        $this->trackerMethod = $this->getTrackerMethod();
        $this->trackerMethod->activate();
    }

    // ── Internal ───────────────────────────────────────────────────

    private function getTrackerMethod(): BaseTracker
    {
        $trackerMethod = Option::getValue('direct_file_tracking')
            ? new HybridModeTracker()
            : new AjaxTracker();

        /**
         * Filter the active tracking method instance.
         *
         * @param BaseTracker $trackerMethod The default tracking method.
         * @return BaseTracker The filtered tracking method.
         * @since 15.0.0
         */
        $trackerMethod = apply_filters('wp_statistics_tracker_controller', $trackerMethod);

        if (!($trackerMethod instanceof BaseTracker)) {
            throw new \Exception('Custom tracking method must extend BaseTracker');
        }

        return $trackerMethod;
    }
}
