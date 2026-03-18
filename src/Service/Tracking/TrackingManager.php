<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Service\Tracking\Methods\BaseTracking;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Methods\AjaxTracking;
use WP_Statistics\Service\Tracking\Methods\DirectFileTracking;
use WP_Statistics\Service\Tracking\Methods\RestTracking;
use WP_Statistics\Service\Tracking\DirectEndpoint\DirectEndpointManager;

/**
 * Central manager for the three tracking tracking methods:
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
     * Method key → tracking method class.
     */
    private const METHODS = [
        'rest'        => RestTracking::class,
        'ajax'        => AjaxTracking::class,
        'direct_file' => DirectFileTracking::class,
    ];

    private const DEFAULT_METHOD = 'rest';

    /**
     * The active tracking method.
     *
     * @var BaseTracking
     */
    private $activeMethod;

    /**
     * Cached tracking method key.
     *
     * @var string
     */
    private $method;

    public function __construct()
    {
        $method       = Option::getValue('tracking_method', self::DEFAULT_METHOD);
        $this->method = isset(self::METHODS[$method]) ? $method : self::DEFAULT_METHOD;
    }

    /**
     * Register the active tracking tracking method.
     *
     * Called once during plugin boot.
     */
    public function register(): void
    {
        $this->activeMethod = $this->createActiveMethod();
        $this->activeMethod->register();

        // Always listen for tracking method changes so direct endpoint
        // can be installed/uninstalled when settings are saved.
        add_action('wp_statistics_settings_saved', [$this, 'onSettingsSaved'], 10, 2);
    }

    // ── Delegated to active method ─────────────────────────────────

    public function getHitUrl(): string
    {
        return $this->activeMethod->getHitUrl();
    }

    public function getBatchUrl(): string
    {
        return $this->activeMethod->getBatchUrl();
    }

    public function getTrackingRoute(): ?string
    {
        return $this->activeMethod->getRoute();
    }

    // ── Method info ────────────────────────────────────────────────

    public function getTrackingMethod(): string
    {
        return $this->method;
    }

    public function isAjax(): bool
    {
        return $this->method === 'ajax';
    }

    /**
     * Direct endpoint URL, or empty string if not the active method.
     */
    public function getDirectEndpointUrl(): string
    {
        if ($this->method !== 'direct_file') {
            return '';
        }

        return $this->activeMethod->getHitUrl();
    }

    // ── Settings lifecycle ─────────────────────────────────────────

    public function onSettingsSaved(string $tab, array $settings): void
    {
        if (!array_key_exists('tracking_method', $settings)) {
            return;
        }

        $endpointManager = new DirectEndpointManager();

        if ($settings['tracking_method'] === 'direct_file') {
            $endpointManager->reinstall();
        } else {
            $endpointManager->uninstall();
        }
    }

    // ── Internal ───────────────────────────────────────────────────

    private function createActiveMethod(): BaseTracking
    {
        $class          = self::METHODS[$this->method];
        $method = new $class();

        /**
         * Filter the active tracking method instance.
         *
         * @param BaseTracking $method The default tracking method.
         * @return BaseTracking The filtered tracking method.
         * @since 15.0.0
         */
        $method = apply_filters('wp_statistics_tracker_controller', $method);

        if (!($method instanceof BaseTracking)) {
            throw new \Exception('Custom tracking method must extend BaseTracking');
        }

        return $method;
    }
}
