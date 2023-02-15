<?php

namespace WP_STATISTICS\Api\v2;

use WP_STATISTICS\Option;

class Meta_Box extends \WP_STATISTICS\RestAPI
{
    /**
     * Meta Box constructor.
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
     * Register routes
     *
     * @see https://developer.wordpress.org/reference/classes/wp_rest_server/
     */
    public function register_routes()
    {

        // Get Admin Meta Box
        register_rest_route(self::$namespace, '/metabox', array(
            array(
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => array($this, 'meta_box_callback'),
                'args'                => array(
                    'name' => array(
                        'required' => true
                    )
                ),
                'permission_callback' => function (\WP_REST_Request $request) {

                    // Check User Auth
                    $user = wp_get_current_user();
                    if ($user->ID == 0) {
                        return false;
                    }

                    return current_user_can(Option::get('read_capability', 'manage_options'));
                }
            )
        ));
    }

    /**
     * Admin Meta Box WP Statistics
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws \Exception
     */
    public function meta_box_callback(\WP_REST_Request $request)
    {
        // Check Exist MetaBox Name
        if (in_array($request->get_param('name'), array_keys(\WP_STATISTICS\Meta_Box::getList())) and \WP_STATISTICS\Meta_Box::metaBoxClassExist($request->get_param('name'))) {
            $class = \WP_STATISTICS\Meta_Box::getMetaBoxClass($request->get_param('name'));
            return $class::get($request->get_params());
        }

        // Not Define MetaBox
        return new \WP_REST_Response(array('code' => 'not_found_meta_box', 'message' => __('The name of MetaBox is invalid on request.', 'wp-statistics')), 400);
    }

}

new Meta_Box;