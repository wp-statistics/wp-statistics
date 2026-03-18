<?php

namespace WP_Statistics\Service\Tracking\Methods;

/**
 * Abstract base for tracking methods.
 *
 * Every tracking method (REST, AJAX, Direct File) extends this so that
 * TrackingManager can delegate without special-casing any method.
 *
 * @since 15.0.0
 */
abstract class BaseTracking
{
    /**
     * Register the method's endpoints with WordPress.
     */
    abstract public function register(): void;

    /**
     * Configuration the JS tracker needs to send hits via this method.
     *
     * @return array{baseUrl: string, hitEndpoint: string, batchEndpoint: string}
     */
    abstract public function getTrackerConfig(): array;

    /**
     * Diagnostic route string for health checks.
     */
    abstract public function getRoute(): ?string;
}
