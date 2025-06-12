<?php

namespace WP_STATISTICS\Service\Tracking\Controllers;

use Exception;
use WP_Statistics\Abstracts\BaseTrackerController;
use WP_STATISTICS\Option;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_Statistics\Service\Tracking\TrackingFactory;

/**
 * REST API-based Tracking Controller
 *
 * Handles visitor tracking through WordPress REST API endpoints when client-side tracking
 * is enabled but ad blocker bypass is disabled. This controller integrates with WordPress
 * REST API for secure tracking requests, providing endpoints for recording page hits (/hit)
 * and tracking online users (/online). Includes signature validation, client-side configuration,
 * and compatibility with cache plugins while following privacy settings.
 *
 * @since 15.0.0
 */
class RestApiTracking extends BaseTrackerController
{
    /**
     * Initialize the REST API tracking controller.
     * Calls the register method to set up REST API endpoints for hit and online tracking.
     *
     * @since 15.0.0
     */
    public function __construct()
    {
        $this->register();
    }

    /**
     * Register REST API endpoints and filters if conditions are met.
     * Only registers endpoints when client-side tracking is enabled and ad blocker bypass is disabled.
     *
     * @return void
     * @since 15.0.0
     */
    public function register()
    {
        if (
            !Option::get('use_cache_plugin') ||
            Option::get('bypass_ad_blockers', false)
        ) {
            return;
        }

        add_action('rest_api_init', [$this, 'registerRoutes']);
        add_filter('wp_statistics_js_localized_arguments', [$this, 'addLocalizedArguments']);
    }

    /**
     * Add tracking-related arguments to the localized JavaScript object.
     * Provides necessary configuration for client-side tracking, including endpoints and parameters.
     *
     * @param array $args Existing localized arguments
     * @return array Modified arguments with tracking configuration
     * @since 15.0.0
     */
    public function addLocalizedArguments($args)
    {
        $args['requestUrl']   = get_rest_url(null, $this->namespace);
        $args['hitParams']    = array_merge($args, ['endpoint' => self::ENDPOINT_HIT]);
        $args['onlineParams'] = array_merge($args, ['endpoint' => self::ENDPOINT_ONLINE]);

        return $args;
    }

    /**
     * Register REST API routes for hit and online tracking.
     * Sets up endpoints with appropriate HTTP methods, callbacks, and argument validation.
     *
     * @return void
     * @since 15.0.0
     */
    public function registerRoutes()
    {
        register_rest_route($this->namespace, '/' . self::ENDPOINT_HIT, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'recordHit'],
            'permission_callback' => '__return_true',
            'args'                => $this->getArgs(),
        ]);

        register_rest_route($this->namespace, '/' . self::ENDPOINT_ONLINE, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'markOnline'],
            'permission_callback' => '__return_true',
            'args'                => $this->getArgs(),
        ]);
    }

    /**
     * Define accepted arguments for REST API endpoints.
     * Specifies required parameters and their types for request validation.
     *
     * @return array Array of argument definitions
     * @since 15.0.0
     */
    protected function getArgs()
    {
        return [
            'page_uri'    => [
                'required' => true,
                'type'     => 'string',
            ],
            'source_type' => [
                'required' => true,
                'type'     => 'string',
            ],
            'source_id'   => [
                'required' => true,
                'type'     => 'integer',
            ],
            'signature'   => [
                'required' => false,
                'type'     => 'string',
            ],
        ];
    }

    /**
     * Handle requests to record a hit.
     * Validates the request, records the hit, and returns appropriate response.
     *
     * @param WP_REST_Request $request The REST API request object
     * @return WP_REST_Response The REST API response
     * @since 15.0.0
     */
    public function recordHit(WP_REST_Request $request)
    {
        $statusCode = false;

        try {
            $this->checkSignature();
            Helper::validateHitRequest();
            TrackingFactory::hits()->record();

            $responseData['status'] = true;
        } catch (Exception $e) {
            $responseData['status'] = false;
            $responseData['data']   = $e->getMessage();
            $statusCode             = $e->getCode();
        }

        $response = rest_ensure_response($responseData);

        /**
         * Set the status code.
         */
        if ($statusCode) {
            $response->set_status($statusCode);
        }

        /**
         * Set headers to avoid caching.
         *
         * @since 13.0.8
         * @link https://wordpress.org/support/topic/request-for-cloudflare-html-caching-compatibility/
         */
        $response->set_headers([
            'Cache-Control' => 'no-cache',
        ]);

        return $response;
    }

    /**
     * Handle requests to mark a user as online.
     * Validates the request, updates online status, and returns appropriate response.
     *
     * @param WP_REST_Request $request The REST API request object
     * @return WP_REST_Response The REST API response
     * @since 15.0.0
     */
    public function markOnline(WP_REST_Request $request)
    {
        $statusCode = false;

        try {
            $this->checkSignature();
            Helper::validateHitRequest();
            Hits::recordOnline();

            $responseData['status'] = true;
        } catch (Exception $e) {
            $responseData['status'] = false;
            $responseData['data']   = $e->getMessage();
            $statusCode             = $e->getCode();
        }

        $response = rest_ensure_response($responseData);

        /**
         * Set the status code.
         */
        if ($statusCode) {
            $response->set_status($statusCode);
        }

        /**
         * Set headers to avoid caching.
         *
         * @since 13.0.8
         * @link https://wordpress.org/support/topic/request-for-cloudflare-html-caching-compatibility/
         */
        $response->set_headers([
            'Cache-Control' => 'no-cache',
        ]);

        return $response;
    }

    /**
     * Get the base route for this tracking controller.
     * Returns the namespace used for REST API endpoints.
     *
     * @return string The REST API namespace
     * @since 15.0.0
     */
    public function getRoute()
    {
        return $this->namespace;
    }
}