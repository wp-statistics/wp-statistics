<?php

namespace WP_STATISTICS\Api\v2;

use Exception;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;

class Hit extends \WP_STATISTICS\RestAPI
{
    /**
     * Hit Endpoint
     *
     * @var string
     */
    public static $endpoint = 'hit';

    /**
     * Hit constructor.
     *
     * @see https://developer.wordpress.org/rest-api/extending-the-rest-api/adding-custom-endpoints/
     */
    public function __construct()
    {
        // Use Parent Construct
        parent::__construct();

        // Register routes
        add_action('rest_api_init', array($this, 'register_routes'));
    }

    /**
     * List Of Required Params
     *
     * @return array
     */
    public static function require_params_hit()
    {
        return array(
            'page_uri' => array('required' => true, 'type' => 'string')
        );
    }

    /**
     * Register routes
     *
     * @see https://developer.wordpress.org/reference/classes/wp_rest_server/
     */
    public function register_routes()
    {
        $GLOBALS['wp_statistics_user_id'] = get_current_user_id();

        // Record WP Statistics when Cache is enable
        register_rest_route(self::$namespace, '/' . self::$endpoint, array(
            array(
                'methods'             => \WP_REST_Server::CREATABLE,
                'callback'            => array($this, 'hit_callback'),
                'args'                => self::require_params_hit(),
                'permission_callback' => function (\WP_REST_Request $request) {
                    return $this->checkSignature($request);
                }
            )
        ));
    }

    /**
     * Record WP Statistics when Cache is enable
     *
     * @return \WP_REST_Response
     * @throws Exception
     */
    public function hit_callback()
    {
        $statusCode = false;

        try {
            Helper::validateHitRequest();
            Hits::record();

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

new Hit();
