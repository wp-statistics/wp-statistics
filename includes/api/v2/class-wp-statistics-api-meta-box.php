<?php

namespace WP_STATISTICS\Api\v2;

use WP_STATISTICS\User;

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
                        'required'          => true,
                        'validate_callback' => function ($value, $request, $key) {
                            return (in_array($value, array_keys(\WP_STATISTICS\Meta_Box::getList())) and \WP_STATISTICS\Meta_Box::IsExistMetaBoxClass($value));
                        }
                    )
                ),
                'permission_callback' => function () {
                    return User::Access('read');
                }
            )
        ));
    }

    /**
     * Admin Meta Box WP-Statistics
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     * @throws \Exception
     */
    public function meta_box_callback(\WP_REST_Request $request)
    {
        $class = \WP_STATISTICS\Meta_Box::getMetaBoxClass($request->get_param('name'));
        return $class::get($request->get_params());
    }

}

new Meta_Box;