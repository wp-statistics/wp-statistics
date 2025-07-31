<?php

namespace WP_Statistics\Abstracts;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use Exception;

/**
 * Abstract base class for WP Statistics REST API endpoints.
 *
 * Provides reusable logic for route registration, request validation,
 * and signature checking. Subclasses must define the endpoint slug,
 * request arguments, and handler logic.
 */
abstract class BaseRestAPI
{
    /**
     * REST API namespace used for all WP Statistics endpoints.
     *
     * @var string
     */
    protected $namespace = 'wp-statistics/v2';

    /**
     * Endpoint slug to be defined by subclass (e.g., 'hit', 'online').
     *
     * @var string
     */
    protected $endpoint;

    /**
     * HTTP method to register the route with.
     *
     * @var string
     */
    protected $method = WP_REST_Server::CREATABLE;

    /**
     * BaseRestAPI constructor.
     *
     * Initializes database access, loads options, and hooks route registration.
     */
    public function __construct()
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * Register the REST route with WordPress.
     *
     * Subclasses must set $endpoint before this runs.
     *
     * @return void
     * @see https://developer.wordpress.org/reference/functions/register_rest_route/
     */
    public function registerRoutes()
    {
        if (!$this->endpoint) {
            return;
        }

        register_rest_route($this->namespace, '/' . $this->endpoint, [
            [
                'methods'             => $this->method,
                'callback'            => [$this, 'handle'],
                'args'                => $this->getArgs(),
                'permission_callback' => [$this, 'permissionCallback'],
            ],
        ]);
    }

    /**
     * Permission callback for the REST API endpoint.
     *
     * @param WP_REST_Request $request The REST API request object.
     * @return true|WP_Error Returns true if the request is authorized, or WP_Error if not.
     */
    public function permissionCallback(WP_REST_Request $request)
    {
        return true;
    }

    /**
     * Define expected request parameters for this endpoint.
     *
     * Should be overridden by child classes to validate input.
     *
     * @return array List of REST parameter definitions.
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/#arguments
     */
    protected function getArgs()
    {
        return [];
    }

    /**
     * Get the REST API endpoint slug.
     *
     * @return string
     */
    public function getEndpoint()
    {
        return $this->endpoint;
    }

    /**
     * Handle the REST API request.
     *
     * Subclasses must implement this to provide the actual
     * endpoint behavior.
     *
     * @param WP_REST_Request $request The incoming REST request.
     *
     * @return WP_REST_Response
     * @throws Exception
     */
    abstract public function handle(WP_REST_Request $request);
}