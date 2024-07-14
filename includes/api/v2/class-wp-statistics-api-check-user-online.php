<?php

namespace WP_STATISTICS\Api\v2;

use WP_STATISTICS\UserOnline;

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
            'methods'             => \WP_REST_Server::READABLE,
            'callback'            => [$this, 'onlineUserUpdateCallback'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return $this->checkSignature($request);
            }
        ));
    }

    public function onlineUserUpdateCallback()
    {
        UserOnline::record();

        $response = rest_ensure_response([
            'status' => true
        ]);

        /**
         * Set headers for the response
         *
         * @since 14.9
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
