<?php

namespace WP_Statistics\Service\Tracking\Methods;

/**
 * Abstract base for tracking methods.
 *
 * Every tracking method (REST, AJAX, Hybrid Mode) extends this so that
 * TrackerManager can delegate without special-casing any method.
 *
 * @since 15.0.0
 */
abstract class BaseTracker
{
    /**
     * Register the method's endpoints with WordPress.
     */
    abstract public function register(): void;

    /**
     * Configuration the JS tracker needs to send hits via this method.
     *
     * @return array{baseUrl: string, hitEndpoint: string}
     */
    abstract public function getTrackerConfig(): array;

    /**
     * Tracker method type identifier for the `wp_statistics_tracker_enabled` filter.
     *
     * @return string e.g. 'ajax', 'rest', 'hybrid'
     */
    abstract public function getMethodType(): string;

    /**
     * Diagnostic route string for health checks.
     */
    abstract public function getRoute(): ?string;

    /**
     * Called when this method becomes the active tracking method.
     */
    public function activate(): void {}

    /**
     * Called when this method is no longer the active tracking method.
     */
    public function deactivate(): void {}
}
