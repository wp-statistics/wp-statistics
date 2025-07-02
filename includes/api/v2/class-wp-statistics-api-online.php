<?php

namespace WP_STATISTICS\Api\v2;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;

class CheckUserOnline extends \WP_STATISTICS\RestAPI
{
    /**
     * REST API Address for Checking Online Users
     *
     * @var string
     */
    public static $endpoint = 'online';

    /**
     * CheckUserOnline constructor.
     */
    public function __construct()
    {

        # Create REST API to Check Online User
        add_action('rest_api_init', array($this, 'register_online_user_rest_api'));
    }

    // Create REST API to Check Online Users
    public function register_online_user_rest_api()
    {
        register_rest_route(self::$namespace, '/' . self::$endpoint, array(
            'methods'             => \WP_REST_Server::CREATABLE,
            'callback'            => [$this, 'onlineUserUpdateCallback'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return $this->checkSignature($request);
            }
        ));
    }

    public function onlineUserUpdateCallback()
    {
        $statusCode = false;

        try {
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
         * Set the status code
         */
        if ($statusCode) {
            $response->set_status($statusCode);
        }

        /**
         * Set headers for the response
         *
         * @since 13.0.8
         */
        $response->set_headers(array(
            /**
             * Cache-Control for Cloudflare caching compatibility
             *
             * @link https://wordpress.org/support/topic/request-for-cloudflare-html-caching-compatibility/
             */
            'Cache-Control' => 'no-cache',
        ));

        return $response;
    }
}

new CheckUserOnline();
