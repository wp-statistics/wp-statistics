<?php

namespace WP_Statistics\Components;

use Exception;
use WP_STATISTICS\Helper;

class RemoteRequest
{
    public $requestUrl;
    private $parsedArgs = [];

    /**
     * @param string $url
     * @param string $method
     * @param array $params URL parameters.
     * @param array $args Request arguments.
     */
    public function __construct($url, $method = 'GET', $params = [], $args = [])
    {
        // Filter to modify URL parameters
        $params = apply_filters('wp_statistics_remote_request_params', $params);

        // Build request URL
        $this->requestUrl = add_query_arg($params, $url);

        // Filter to modify arguments
        $args = apply_filters('wp_statistics_remote_request_args', $args);

        // Prepare the arguments
        $this->parsedArgs = wp_parse_args($args, [
            'method'  => $method,
            'timeout' => 10,
        ]);
    }

    /**
     * Returns request URL.
     *
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    /**
     * Returns parsed request arguments.
     *
     * @return array
     */
    public function getParsedArgs()
    {
        return $this->parsedArgs;
    }

    /**
     * Executes the request.
     *
     * @param bool $throwFailedHttpCodeResponse Whether or not to throw an exception if the request returns a failed HTTP code.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function execute($throwFailedHttpCodeResponse = true)
    {
        $response = wp_remote_request(
            $this->requestUrl,
            $this->parsedArgs
        );

        if (is_wp_error($response)) {
            throw new Exception(esc_html($response->get_error_message()));
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $responseBody = wp_remote_retrieve_body($response);

        if ($throwFailedHttpCodeResponse) {
            if (in_array($responseCode, [200, 201, 202]) === false) {
                if (Helper::isJson($responseBody)) {
                    $responseBody = json_decode($responseBody, true);
                }

                // translators: %s: Response message.
                throw new Exception(sprintf(esc_html__('Failed to get success response, %s', 'wp-statistics'), esc_html(var_export($responseBody, true))));
            }
        }

        $responseJson = json_decode($responseBody);

        return ($responseJson == null) ? $responseBody : $responseJson;
    }
}
