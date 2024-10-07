<?php

namespace WP_Statistics\Tests\Components;

use Exception;
use WP_Statistics\Components\RemoteRequest;
use WP_UnitTestCase;
use WP_Error;

class Test_RemoteRequest extends WP_UnitTestCase
{
    /**
     * Set up mock functions and filters.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Mock the 'wp_statistics_remote_request_params' filter
        add_filter('wp_statistics_remote_request_params', function ($params) {
            return $params;
        });

        // Mock the 'wp_statistics_remote_request_args' filter
        add_filter('wp_statistics_remote_request_args', function ($args) {
            return $args;
        });
    }

    /**
     * Test the constructor and request URL generation.
     */
    public function test_constructor_and_requestUrl_generation()
    {
        $url = 'https://example.com/api';
        $params = ['key' => 'value'];
        $method = 'GET';
        $args = ['timeout' => 5];

        $remoteRequest = new RemoteRequest($url, $method, $params, $args);

        // Assert the request URL is generated correctly
        $expectedUrl = add_query_arg($params, $url);
        $this->assertEquals($expectedUrl, $remoteRequest->getRequestUrl());

        // Assert parsed args are set correctly
        $parsedArgs = wp_parse_args($args, ['method' => $method, 'timeout' => 10]);
        $this->assertEquals($parsedArgs, $remoteRequest->getParsedArgs());
    }

    /**
     * Test the generateCacheKey method.
     */
    public function test_generateCacheKey()
    {
        $url = 'https://example.com/api';
        $params = ['key' => 'value'];
        $method = 'GET';
        $args = ['timeout' => 5];

        $remoteRequest = $this->getMockBuilder(RemoteRequest::class)
            ->setConstructorArgs([$url, $method, $params, $args])
            ->onlyMethods(['getCacheKey'])
            ->getMock();

        // Mock the getCacheKey method
        $remoteRequest->expects($this->once())
            ->method('getCacheKey')
            ->with($this->isType('string'))
            ->willReturn('mock_cache_key');

        // Test the cache key generation
        $cacheKey = $remoteRequest->generateCacheKey();
        $this->assertEquals('mock_cache_key', $cacheKey);
    }

    /**
     * Test the execute method with caching enabled.
     */
    public function test_execute_with_cache()
    {
        $url = 'https://example.com/api';
        $params = [];
        $method = 'GET';
        $args = [];

        $remoteRequest = $this->getMockBuilder(RemoteRequest::class)
            ->setConstructorArgs([$url, $method, $params, $args])
            ->onlyMethods(['getCachedResult', 'setCachedResult', 'generateCacheKey'])
            ->getMock();

        // Mock cache key generation
        $remoteRequest->expects($this->once())
            ->method('generateCacheKey')
            ->willReturn('mock_cache_key');

        // Mock cached result retrieval
        $remoteRequest->expects($this->once())
            ->method('getCachedResult')
            ->with('mock_cache_key')
            ->willReturn('mock_cached_response');

        // Test the execution with cache
        $result = $remoteRequest->execute(true, true);
        $this->assertEquals('mock_cached_response', $result);
    }

    /**
     * Test the execute method when no cached result exists.
     */
    public function test_execute_with_no_cache()
    {
        $url = 'https://example.com/api';
        $params = [];
        $method = 'GET';
        $args = [];

        // Mock wp_remote_request response
        $mockResponse = [
            'response' => ['code' => 200],
            'body' => json_encode(['success' => true])
        ];

        // Mock wp_remote_request to return the mocked response
        add_filter('pre_http_request', function ($preempt, $args, $url) use ($mockResponse) {
            return $mockResponse;
        }, 10, 3);

        $remoteRequest = $this->getMockBuilder(RemoteRequest::class)
            ->setConstructorArgs([$url, $method, $params, $args])
            ->onlyMethods(['generateCacheKey', 'getCachedResult', 'setCachedResult'])
            ->getMock();

        // Mock cache key generation
        $remoteRequest->expects($this->once())
            ->method('generateCacheKey')
            ->willReturn('mock_cache_key');

        // Mock cached result retrieval (no cache hit)
        $remoteRequest->expects($this->once())
            ->method('getCachedResult')
            ->with('mock_cache_key')
            ->willReturn(false);

        // Mock setting cached result
        $remoteRequest->expects($this->once())
            ->method('setCachedResult')
            ->with('mock_cache_key', json_decode($mockResponse['body']), HOUR_IN_SECONDS);

        // Test the execution without cache
        $result = $remoteRequest->execute(true, true);
        $this->assertEquals(json_decode($mockResponse['body']), $result);
    }

    /**
     * Test the execute method throws an exception on WP_Error.
     */
    public function test_execute_throws_exception_on_wp_error()
    {
        $this->expectException(Exception::class);

        $url = 'https://example.com/api';
        $params = [];
        $method = 'GET';
        $args = [];

        // Mock wp_remote_request to return a WP_Error
        add_filter('pre_http_request', function () {
            return new WP_Error('http_error', 'An error occurred');
        });

        $remoteRequest = new RemoteRequest($url, $method, $params, $args);

        // Execute and expect an exception to be thrown
        $remoteRequest->execute(true, false);
    }

    /**
     * Test the execute method throws an exception on non-200 status code.
     */
    public function test_execute_throws_exception_on_failed_status_code()
    {
        $this->expectException(Exception::class);

        $url = 'https://example.com/api';
        $params = [];
        $method = 'GET';
        $args = [];

        // Mock wp_remote_request to return a response with a failed status code
        add_filter('pre_http_request', function () {
            return [
                'response' => ['code' => 500],
                'body' => 'Internal Server Error'
            ];
        });

        $remoteRequest = new RemoteRequest($url, $method, $params, $args);

        // Execute and expect an exception due to failed HTTP code
        $remoteRequest->execute(true, false);
    }
}
