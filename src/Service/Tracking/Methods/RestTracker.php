<?php

namespace WP_Statistics\Service\Tracking\Methods;

use WP_Statistics\Service\Tracking\Core\RateLimiter;
use WP_Statistics\Service\Tracking\Core\Tracker;
use Exception;
use WP_REST_Server;
use WP_REST_Request;

/**
 * REST API tracking method.
 *
 * Registers /wp-json/wp-statistics/v2/hit and /batch endpoints.
 *
 * @since 15.0.0
 */
class RestTracker extends BaseTracker
{
    public const API_NAMESPACE  = 'wp-statistics/v2';
    public const ENDPOINT_HIT   = 'hit';
    public const ENDPOINT_BATCH = 'batch';

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
            'hitEndpoint'   => '/' . self::ENDPOINT_HIT,
            'batchEndpoint' => '/' . self::ENDPOINT_BATCH,
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
     * Register REST API routes for hit and batch tracking.
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

        register_rest_route(self::API_NAMESPACE, '/' . self::ENDPOINT_BATCH, [
            'methods'             => WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'recordBatch'],
            'permission_callback' => '__return_true',
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

        $headers = ['Cache-Control' => 'no-cache'];
        if ($statusCode === 429) {
            $headers['Retry-After'] = (string) RateLimiter::getTimeWindow();
        }
        $response->set_headers($headers);

        return $response;
    }

    /**
     * Handle batch tracking via REST.
     *
     * @param WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function recordBatch(WP_REST_Request $request)
    {
        try {
            $bodyParams = $request->get_body_params();
            $result     = $this->processBatch($bodyParams['batch_data'] ?? null);

            $response = rest_ensure_response([
                'status'    => true,
                'processed' => $result['processed'],
                'errors'    => $result['errors'],
            ]);
        } catch (Exception $e) {
            $response = rest_ensure_response([
                'status' => false,
                'data'   => $e->getMessage(),
            ]);
            $response->set_status($e->getCode() ?: 400);
        }

        $response->set_headers(['Cache-Control' => 'no-cache']);

        return $response;
    }
}
