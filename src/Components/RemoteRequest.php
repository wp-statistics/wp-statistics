<?php

namespace WP_Statistics\Components;

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
     * @throws \Exception
     */
    public function execute($throwFailedHttpCodeResponse = true)
    {
        $response = wp_remote_request(
            $this->requestUrl,
            $this->parsedArgs
        );

        if (is_wp_error($response)) {
            throw new \Exception(esc_html($response->get_error_message()));
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $responseBody = wp_remote_retrieve_body($response);

        if ($throwFailedHttpCodeResponse) {
            if (in_array($responseCode, [200, 201, 202]) === false) {
                if (Helper::isJson($responseBody)) {
                    $responseBody = json_decode($responseBody, true);
                }

                // translators: %s: Response message.
                throw new \Exception(sprintf(esc_html__('Failed to get success response, %s', 'wp-statistics'), esc_html(var_export($responseBody, true))));
            }
        }

        $responseJson = json_decode($responseBody);

        return ($responseJson == null) ? $responseBody : $responseJson;
    }

    /**
     * Downloads a URL to a local path using the WordPress HTTP API.
     *
     * @param string $destination Destination to move the downloaded file to.
     *
     * @return string|\WP_Error The path to the file or an error if invalid.
     */
    public function downloadToSite($destination)
    {
        if (empty($this->requestUrl) || empty($destination)) {
            return new \WP_Error('wp_statistics_download_url_error', __('Download URL and destination are required!', 'wp-statistics'));
        }

        // Ensure the necessary function is available
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        // Download the file to a temporary location
        $tempFile = download_url(esc_url_raw($this->requestUrl));
        if (is_wp_error($tempFile)) {
            return new \WP_Error('wp_statistics_download_url_error', $tempFile->get_error_data());
        }

        // Move the temporary file to the final destination
        $destination = path_join($destination, basename($this->requestUrl));
        copy($tempFile, $destination);
        unlink($tempFile);

        // Verify the file exists at the destination
        if (file_exists($destination)) {
            return $destination;
        }

        return new \WP_Error('wp_statistics_download_url_error', __('Error downloading the file!', 'wp-statistics'));
    }
}
