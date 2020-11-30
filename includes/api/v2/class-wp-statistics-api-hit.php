<?php

namespace WP_STATISTICS\Api\v2;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Hits;
use WP_STATISTICS\Option;

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
            'browser',
            'platform',
            'version',
            'ip',
            'track_all',
            'timestamp',
            'page_uri',
            'user_id',
        );
    }

    /**
     * Register routes
     *
     * @see https://developer.wordpress.org/reference/classes/wp_rest_server/
     */
    public function register_routes()
    {

        // Create Require Params
        $params = array();
        foreach (self::require_params_hit() as $p) {
            $params[$p] = array('required' => true);
        }

        // Record WP-Statistics when Cache is enable
        register_rest_route(self::$namespace, '/' . self::$endpoint, array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'hit_callback'),
                'permission_callback' => function () {
                    return Option::get('use_cache_plugin') == 1;
                },
                'args'                => array_merge(
                    array('_wpnonce' => array(
                        'required'          => true,
                        'validate_callback' => function ($value) {
                            return wp_verify_nonce($value, 'wp_rest');
                        }
                    )), $params)
            )
        ));

        // Check WP-Statistics Rest API Not disabled
        register_rest_route(self::$namespace, '/enable', array(
            array(
                'methods'  => \WP_REST_Server::READABLE,
                'callback' => array($this, 'check_enable_callback'),
                'permission_callback' => function () {
                    return true;
                },
                'args'     => array(
                    'connect' => array(
                        'required' => true
                    ),
                )
            )
        ));
    }

    /**
     * Record WP-Statistics when Cache is enable
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws \Exception
     */
    public function hit_callback(\WP_REST_Request $request)
    {

        // Start Record
        Hits::record();

        // Return
        return self::response(__('Visitor Hit was recorded successfully.', 'wp-statistics'));
    }

    /**
     * Check WP-Statistics Rest API Not disabled
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function check_enable_callback(\WP_REST_Request $request)
    {
        if ($request->get_param('connect') == "wp-statistics") {
            return self::response('enable');
        }

        return self::response('Missing connect parameter.', 400);
    }
}

new Hit();