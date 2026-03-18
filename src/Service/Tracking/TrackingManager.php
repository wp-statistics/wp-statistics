<?php

namespace WP_Statistics\Service\Tracking;

use WP_Statistics\Abstracts\BaseDeliveryMethod;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\Tracking\Delivery\AjaxDelivery;
use WP_Statistics\Service\Tracking\Delivery\DirectFileDelivery;
use WP_Statistics\Service\Tracking\Delivery\RestDelivery;
use WP_Statistics\Service\Tracking\DirectEndpoint\DirectEndpointManager;

/**
 * Central manager for the three tracking delivery methods:
 *
 *  1. REST API    — default, uses /wp-json/wp-statistics/v2/hit
 *  2. AJAX        — routes through admin-ajax.php (bypasses ad blockers)
 *  3. Direct File — SHORTINIT mu-plugin endpoint (highest performance)
 *
 * Each method implements BaseDeliveryMethod. The manager creates
 * the active one and delegates — no method-specific branching here.
 *
 * @since 15.0.0
 */
class TrackingManager
{
    /**
     * Method key → controller class.
     */
    private const METHODS = [
        'rest'        => RestDelivery::class,
        'ajax'        => AjaxDelivery::class,
        'direct_file' => DirectFileDelivery::class,
    ];

    private const DEFAULT_METHOD = 'rest';

    /**
     * The active delivery method.
     *
     * @var BaseDeliveryMethod
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
     * Register the active tracking delivery method.
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

    private function createActiveMethod(): BaseDeliveryMethod
    {
        $class      = self::METHODS[$this->method];
        $controller = new $class();

        /**
         * Filter the tracking controller instance.
         *
         * @param BaseDeliveryMethod $controller The default tracking controller.
         * @return BaseDeliveryMethod The filtered tracking controller.
         * @since 15.0.0
         */
        $controller = apply_filters('wp_statistics_tracker_controller', $controller);

        if (!($controller instanceof BaseDeliveryMethod)) {
            throw new \Exception('Custom tracker controller must extend BaseDeliveryMethod');
        }

        return $controller;
    }
}
