<?php

namespace WP_STATISTICS;

use WP_Statistics\Utils\Signature;

class RestAPI
{
    /**
     * WP Statistics Rest API namespace
     *
     * @var string
     */
    public static $namespace = 'wp-statistics/v2';

    /**
     * Get WP Statistics Options
     *
     * @var array
     */
    public $option;

    /**
     * Use WordPress DB Class
     *
     * @var \wpdb
     */
    protected $db;

    /**
     * RestAPI constructor.
     */
    public function __construct()
    {
        global $wpdb;

        $this->option = Option::getOptions();
        $this->db     = $wpdb;
    }

    /**
     * Handle Response
     *
     * @param $message
     * @param int $status
     * @return \WP_REST_Response
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Status
     */
    public static function response($message, $status = 200)
    {
        if ($status == 200) {
            $output = array(
                'data' => $message
            );
        } else {
            $output = array(
                'error' => array(
                    'status'  => $status,
                    'message' => $message,
                )
            );
        }
        return new \WP_REST_Response($output, $status);
    }

    /**
     * Internal Request WP REST API
     *
     * @param array $args
     * @return mixed
     */
    public static function request($args = array())
    {

        // Define the array of defaults
        $defaults = array(
            'type'      => 'GET',
            'namespace' => self::$namespace,
            'route'     => '',
            'params'    => array()
        );
        $args     = wp_parse_args($args, $defaults);

        // Send Request
        $request = new \WP_REST_Request($args['type'], '/' . ltrim($args['namespace'], "/") . '/' . $args['route']);
        $request->set_query_params($args['params']);

        $response = rest_do_request($request);
        $server   = rest_get_server();

        return $server->response_to_data($response, false);
    }

    /**
     * @param $request
     * @return true|\WP_Error
     * @doc https://wp-statistics.com/resources/managing-request-signatures/
     */
    protected function checkSignature($request)
    {
        if (Helper::isRequestSignatureEnabled()) {
            $signature = $request->get_param('signature');
            $payload   = [
                $request->get_param('source_type'),
                (int)$request->get_param('source_id'),
            ];

            if (!Signature::check($payload, $signature)) {
                return new \WP_Error('rest_forbidden', __('Invalid signature', 'wp-statistics'), array('status' => 403));
            }
        }

        return true;
    }
}

new RestAPI;
