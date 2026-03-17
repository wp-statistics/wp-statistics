<?php

namespace WP_Statistics\Abstracts;

/**
 * Abstract base class for WP Statistics tracking implementations.
 * Defines the structure for tracking endpoint registration and routing.
 *
 * @since 15.0.0
 */
abstract class BaseTrackerController
{
    /**
     * REST API endpoint slug for recording page hits.
     *
     * @var string
     */
    protected const ENDPOINT_HIT = 'hit';

    /**
     * Namespace for tracking endpoints.
     *
     * @since 15.0.0
     * @var string
     */
    protected $namespace = 'wp-statistics/v2';

    /**
     * Register tracking endpoints.
     *
     * @since 15.0.0
     */
    abstract public function register();

    /**
     * Get tracking endpoint route.
     *
     * @return string|null Tracking endpoint route
     * @since 15.0.0
     */
    abstract public function getRoute();
}
