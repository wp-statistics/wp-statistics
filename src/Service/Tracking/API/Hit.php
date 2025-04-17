<?php

namespace WP_STATISTICS\Service\Tracking\API;

use Exception;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Tracking\TrackingFactory;
use WP_STATISTICS\Abstracts\BaseRestAPI;

/**
 * REST endpoint for recording page hits.
 *
 * Handles requests to track visits, used when page caching is active.
 */
class Hit extends BaseRestAPI
{
    /**
     * Hit constructor.
     *
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     */
    public function __construct()
    {
        $this->endpoint = 'hit';

        parent::__construct();
    }

    /**
     * Required parameters for the hit endpoint.
     *
     * @return array
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/#arguments
     */
    protected function getArgs()
    {
        return [
            'page_uri' => [
                'required' => true,
                'type'     => 'string',
            ],
        ];
    }

    /**
     * Handle the request to record a hit.
     *
     * @return \WP_REST_Response
     * @throws Exception
     */
    public function handle()
    {
        $statusCode = false;

        try {
            Helper::validateHitRequest();
            TrackingFactory::hits()->record();

            $responseData = ['status' => true];

        } catch (Exception $e) {
            $responseData = [
                'status' => false,
                'data'   => $e->getMessage(),
            ];
            $statusCode = $e->getCode();
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
}

new Hit();
