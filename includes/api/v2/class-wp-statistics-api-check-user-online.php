<?php

namespace WP_STATISTICS\Api\v2;

use WP_Statistics\Service\Analytics\VisitorProfile;

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
            'methods'             => 'GET',
            'callback'            => [$this, 'onlineUserUpdateCallback'],
            'permission_callback' => function (\WP_REST_Request $request) {
                return true;
            }
        ));
    }

    public function onlineUserUpdateCallback()
    {
        $visitorProfile = new VisitorProfile();

        \WP_STATISTICS\UserOnline::record($visitorProfile);

        $response = [
            'status'  => true,
            'message' => 'User is online, the data is updated successfully.',
        ];
        return rest_ensure_response($response);
    }
}

new CheckUserOnline();
