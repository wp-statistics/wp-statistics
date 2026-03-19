<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\Core\Tracker;
use Exception;
use WP_REST_Server;
use WP_REST_Request;

/**
 * REST API tracking method.
 *
 * Registers /wp-json/wp-statistics/v2/hit endpoint.
 *
 * @since 15.0.0
 */
class RestTracker extends BaseTracker
{
    private const API_NAMESPACE = 'wp-statistics/v2';
    private const ENDPOINT_HIT  = 'hit';

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        add_action('rest_api_init', [$this, 'registerRoutes']);
    }

    /**
     * {@inheritDoc}
     */
    public function getTrackerConfig(): array
    {
        return [
            'baseUrl'      => get_rest_url(null, self::API_NAMESPACE),
            'hitEndpoint'  => '/' . self::ENDPOINT_HIT,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getMethodType(): string
    {
        return 'rest';
    }

    /**
     * {@inheritDoc}
     */
    public function getRoute(): ?string
    {
        return self::API_NAMESPACE;
    }

    /**
     * Register REST API route for hit tracking.
     */
    public function registerRoutes(): void
    {
        register_rest_route(self::API_NAMESPACE, '/' . self::ENDPOINT_HIT, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'recordHit'],
            'permission_callback' => '__return_true',
            'args'                => [
                'resource_uri_id' => ['required' => true, 'type' => 'string'],
                'signature'       => ['required' => false, 'type' => 'string'],
            ],
        ]);
    }

    /**
     * Handle hit recording.
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function recordHit(WP_REST_Request $request)
    {
        $responseData = [];
        $statusCode   = false;

        try {
            (new Tracker())->record();
            $responseData['status'] = true;
        } catch (Exception $e) {
            $responseData['status'] = false;
            $responseData['data']   = $e->getMessage();
            $statusCode             = $e->getCode();
        }

        $response = rest_ensure_response($responseData);

        if ($statusCode) {
            $response->set_status($statusCode);
        }

        $response->set_headers(['Cache-Control' => 'no-cache']);

        return $response;
    }
}
