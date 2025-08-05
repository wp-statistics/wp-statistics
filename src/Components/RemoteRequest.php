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
     * Response body from the request.
     * @var string|null
     */
    private $responseCode;

    /**
     * Response body from the request.
     * @var string|null
     */
    private $responseBody;

    /**
     * Complete WordPress HTTP API response object
     * @var array|null
     */
    private $response;

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
        return wp_json_encode(array_merge(['url' => $this->requestUrl], $this->parsedArgs));
    }

    /**
     * Clears the cache for the current request.
     *
     * This method is useful when you want to make sure that the next request is not served from the cache.
     */
    public function clearCache()
    {
        $this->clearCachedResult($this->generateCacheKey());
    }

    /**
     * Checks if the given HTTP response code indicates a successful request.
     *
     * @return bool True if the response code indicates a successful request, false otherwise.
     */
    public function isRequestSuccessful()
    {
        return in_array($this->responseCode, [200, 201, 202]);
    }

    /**
     * Checks if the request is cached.
     *
     * @return bool True if the request is cached, false otherwise.
     */
    public function isCached()
    {
        return $this->getCachedResult($this->generateCacheKey()) !== false;
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

        $this->response = $response;

        if (is_wp_error($response)) {
            if (empty($throwFailedHttpCodeResponse)) {
                return false;
            }

            throw new Exception(esc_html($response->get_error_message()));
        }

        $this->responseCode = wp_remote_retrieve_response_code($response);
        $this->responseBody = wp_remote_retrieve_body($response);

        if ($throwFailedHttpCodeResponse && !$this->isRequestSuccessful()) {
            throw new Exception(sprintf(
                esc_html__('Failed to get success response. URL: %s, Method: %s, Status Code: %s', 'wp-statistics'),
                esc_html($this->requestUrl),
                esc_html($this->parsedArgs['method'] ?? 'GET'),
                esc_html($this->responseCode)
            ));
        }

        $responseJson = json_decode($this->responseBody);

        // Cache the result if caching is enabled
        $resultToCache = ($responseJson === null) ? $this->responseBody : $responseJson;
        if ($useCache) {
            if ($this->isRequestSuccessful() && (is_object($resultToCache) || is_array($resultToCache))) {
                $this->setCachedResult($cacheKey, $resultToCache, $cacheExpiration);
            }
        }

        return $resultToCache;
    }

    /**
     * Returns the response body from the executed request
     *
     * @return string|null The response body or null if no request has been executed
     */
    public function getResponseBody()
    {
        return $this->responseBody;
    }

    /**
     * Returns the HTTP response code from the last executed request
     *
     * @return int|null The HTTP response code or null if no request has been executed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Retrieves the complete WordPress HTTP API response object
     *
     * @return array|null Complete response array or null if no request executed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Validate if response is a valid JSON array
     *
     * @return bool Returns true if valid JSON array, false otherwise
     */
    public function isValidJsonResponse()
    {
        if (
            !empty($this->responseBody) &&
            is_string($this->responseBody) &&
            is_array(json_decode($this->responseBody, true)) &&
            json_last_error() == 0
        ) {
            return true;
        }

        return false;
    }
}
