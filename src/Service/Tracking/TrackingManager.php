<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Service\Tracking\Methods\BaseTracking;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Methods\AjaxTracking;
use WP_Statistics\Service\Tracking\Methods\DirectFile\DirectFileTracking;
use WP_Statistics\Service\Tracking\Methods\RestTracking;

/**
 * Central manager for the three tracking methods:
 *
 *  1. REST API    — default, uses /wp-json/wp-statistics/v2/hit
 *  2. AJAX        — routes through admin-ajax.php (bypasses ad blockers)
 *  3. Direct File — SHORTINIT mu-plugin endpoint (highest performance)
 *
 * Each method implements BaseTracking. The manager creates
 * the active one and delegates — no method-specific branching here.
 *
 * @since 15.0.0
 */
class TrackingManager
{
    /**
     * Option value → tracking method class.
     */
    private const METHODS = [
        'rest'        => RestTracking::class,
        'ajax'        => AjaxTracking::class,
        'direct_file' => DirectFileTracking::class,
    ];

    private const DEFAULT_METHOD = 'rest';

    /**
     * @var BaseTracking
     */
    private $trackingMethod;

    /**
     * @var string
     */
    private $trackingOption;

    public function __construct()
    {
        $option               = Option::getValue('tracking_method', self::DEFAULT_METHOD);
        $this->trackingOption = isset(self::METHODS[$option]) ? $option : self::DEFAULT_METHOD;
    }

    /**
     * Register the active tracking method.
     *
     * Called once during plugin boot.
     */
    public function register(): void
    {
        $this->trackingMethod = $this->getActiveMethod();
        $this->trackingMethod->register();

        add_action('wp_statistics_settings_saved', [$this, 'onSettingsSaved'], 10, 2);
    }

    /**
     * Get the full JS tracker configuration from the active method.
     *
     * @return array
     */
    public function getTrackerConfig(): array
    {
        return $this->trackingMethod->getTrackerConfig();
    }

    /**
     * Get the active tracking method key.
     *
     * @return string
     */
    public function getTrackingMethod(): string
    {
        return $this->trackingOption;
    }

    /**
     * Diagnostic route string for health checks.
     */
    public function getTrackingRoute(): ?string
    {
        return $this->trackingMethod->getRoute();
    }

    // ── Settings lifecycle ─────────────────────────────────────────

    public function onSettingsSaved(string $tab, array $settings): void
    {
        if (!array_key_exists('tracking_method', $settings)) {
            return;
        }

        $newKey = $settings['tracking_method'];
        $newKey = isset(self::METHODS[$newKey]) ? $newKey : self::DEFAULT_METHOD;

        // Deactivate the current method, activate the new one.
        $this->trackingMethod->deactivate();

        $this->trackingOption = $newKey;
        $this->trackingMethod = $this->getActiveMethod();
        $this->trackingMethod->activate();
    }

    // ── Internal ───────────────────────────────────────────────────

    private function getActiveMethod(): BaseTracking
    {
        $class          = self::METHODS[$this->trackingOption];
        $trackingMethod = new $class();

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
