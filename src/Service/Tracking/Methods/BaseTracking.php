<?php

namespace WP_Statistics\Service\Tracking\Methods;

/**
 * Abstract base for tracking delivery methods.
 *
 * Every delivery method (REST, AJAX, Direct File) extends this so that
 * TrackingManager can delegate without special-casing any method.
 *
 * @since 15.0.0
 */
abstract class BaseTracking
{
    /**
     * Register the delivery method's endpoints with WordPress.
     */
    abstract public function register(): void;

    /**
     * URL the JS tracker should POST hits to.
     */
    abstract public function getHitUrl(): string;

    /**
     * URL for batch/engagement events.
     */
    abstract public function getBatchUrl(): string;

    /**
     * Diagnostic route string for health checks.
     *
     * Returns a REST namespace or URL that TrackingCheck can probe.
     * Delivery methods without a probeable route return null.
     */
    abstract public function getRoute(): ?string;
}
