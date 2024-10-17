<?php

namespace WP_Statistics\Components;

use Exception;
use WP_Statistics\Traits\TransientCacheTrait;

class RemoteRequest
{
    use TransientCacheTrait;

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
     * Generates a cache key based on the request URL and arguments.
     *
     * @return string
     */
    public function generateCacheKey()
    {
        return $this->getCacheKey($this->requestUrl . serialize($this->parsedArgs));
    }


    /**
     * Checks if the given HTTP response code indicates a successful request.
     *
     * @param int $responseCode The HTTP response code.
     * @return bool True if the response code indicates a successful request, false otherwise.
     */
    public function isRequestSuccessful($responseCode)
    {
        return in_array($responseCode, [200, 201, 202]);
    }

    /**
     * Executes the request with optional caching.
     *
     * @param bool $throwFailedHttpCodeResponse Whether or not to throw an exception if the request returns a failed HTTP code.
     * @param bool $useCache Whether or not to use caching.
     * @param int $cacheExpiration Cache expiration time in seconds.
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function execute($throwFailedHttpCodeResponse = true, $useCache = true, $cacheExpiration = HOUR_IN_SECONDS)
    {
        // Generate the cache key
        $cacheKey = $this->generateCacheKey();

        // Check if cached result exists if caching is enabled
        if ($useCache) {
            $cachedResponse = $this->getCachedResult($cacheKey);
            if ($cachedResponse !== false) {
                return $cachedResponse;
            }
        }

        // Execute the request if no cached result exists or caching is disabled
        $response = wp_remote_request(
            $this->requestUrl,
            $this->parsedArgs
        );

        if (is_wp_error($response)) {
            throw new Exception(esc_html($response->get_error_message()));
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        $responseBody = wp_remote_retrieve_body($response);

        if ($throwFailedHttpCodeResponse && !$this->isRequestSuccessful($responseCode)) {
            throw new Exception(sprintf(
                esc_html__('Failed to get success response. URL: %s, Method: %s, Status Code: %s', 'wp-statistics'),
                esc_html($this->requestUrl),
                esc_html($this->parsedArgs['method'] ?? 'GET'),
                esc_html($responseCode)
            ));
        }

        $responseJson = json_decode($responseBody);

        // Cache the result if caching is enabled
        $resultToCache = ($responseJson === null) ? $responseBody : $responseJson;
        if ($useCache) {
            if ($this->isRequestSuccessful($responseCode) && (is_object($resultToCache) || is_array($resultToCache))) {
                $this->setCachedResult($cacheKey, $resultToCache, $cacheExpiration);
            }
        }

        return $resultToCache;
    }
}
