<?php

namespace WP_STATISTICS\Service\Tracking\API;

use Exception;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Tracking\TrackingFactory;
use WP_STATISTICS\Abstracts\BaseRestAPI;

/**
 * REST endpoint for tracking online users.
 *
 * This endpoint is typically triggered by front-end pings to indicate
 * the user is currently active.
 */
class UserOnline extends BaseRestAPI
{
    /**
     * UserOnline constructor.
     *
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     */
    public function __construct()
    {
        $this->endpoint = 'online';

        parent::__construct();
    }

    /**
     * This endpoint does not require any additional parameters.
     *
     * @return array
     */
    protected function getArgs()
    {
        return [];
    }

    /**
     * Handle the request to update online user status.
     *
     * @return \WP_REST_Response
     */
    public function handle()
    {
        $statusCode = false;

        try {
            Helper::validateHitRequest();
            TrackingFactory::userOnline()->recordIfAllowed();

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

new UserOnline();
