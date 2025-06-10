<?php

namespace WP_Statistics\Abstracts;

use WP_REST_Request;
use WP_REST_Server;
use WP_REST_Response;
use WP_Error;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;

;

use WP_Statistics\Utils\Signature;
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
     * Plugin options from WP Statistics.
     *
     * @var array
     */
    protected $option;

    /**
     * WordPress database instance.
     *
     * @var \wpdb
     */
    protected $db;

    /**
     * BaseRestAPI constructor.
     *
     * Initializes database access, loads options, and hooks route registration.
     */
    public function __construct()
    {
        global $wpdb;

        $this->db     = $wpdb;
        $this->option = Option::getOptions();

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
        return $this->checkSignature($request);
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
     * Check the request signature for validation.
     *
     * If signature validation is enabled in plugin settings, this method
     * verifies that the request matches the expected hash signature.
     *
     * @param WP_REST_Request $request Incoming REST request.
     * @return true|WP_Error True if valid, or WP_Error if invalid.
     * @doc https://wp-statistics.com/resources/managing-request-signatures/
     */
    protected function checkSignature(WP_REST_Request $request)
    {
        if (Helper::isRequestSignatureEnabled()) {
            $signature = $request->get_param('signature');
            $payload   = [
                $request->get_param('source_type'),
                (int)$request->get_param('source_id'),
            ];

            if (!Signature::check($payload, $signature)) {
                return new WP_Error(
                    'rest_forbidden',
                    __('Invalid signature', 'wp-statistics'),
                    ['status' => 403]
                );
            }
        }

        return true;
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