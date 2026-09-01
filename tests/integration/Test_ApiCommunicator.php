<?php

use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;

class Test_ApiCommunicator extends WP_UnitTestCase
{
    private $licenseKey = '12345678901234567890123456789012';
    private $pluginSlug = 'wp-statistics-advanced-reporting';

    public function tearDown(): void
    {
        delete_transient($this->getProductInfoCacheKey());
        delete_site_transient($this->getNegativeCacheKey());
        delete_site_transient($this->getNegativeCacheBackoffKey());
        remove_all_filters('pre_http_request');

        parent::tearDown();
    }

    public function test_authoritative_refusal_uses_network_wide_long_negative_cache()
    {
        $requestCount = 0;

        add_filter('pre_http_request', function () use (&$requestCount) {
            $requestCount++;

            return [
                'response' => ['code' => 403],
                'body'     => wp_json_encode(['status' => 'suspended']),
            ];
        });

        $communicator = new ApiCommunicator();

        try {
            $communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug);
            $this->fail('Expected the authoritative refusal to throw an exception.');
        } catch (Exception $e) {
            $this->assertSame(1, $requestCount);
        }

        $cached = get_site_transient($this->getNegativeCacheKey());
        $this->assertIsObject($cached);
        $this->assertTrue(isset($cached->_negative_cache));
        $this->assertGreaterThanOrEqual(time() + 11 * HOUR_IN_SECONDS, $this->getNegativeCacheTimeout());

        $this->assertNull($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(1, $requestCount);
    }

    public function test_transport_failure_retains_short_negative_cache()
    {
        add_filter('pre_http_request', function () {
            return new WP_Error('http_request_failed', 'Connection timed out');
        });

        $communicator = new ApiCommunicator();

        try {
            $communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug);
            $this->fail('Expected the transport failure to throw an exception.');
        } catch (Exception $e) {
            $timeout = $this->getNegativeCacheTimeout();

            $this->assertGreaterThanOrEqual(time() + 4 * MINUTE_IN_SECONDS, $timeout);
            $this->assertLessThanOrEqual(time() + 6 * MINUTE_IN_SECONDS, $timeout);
            $this->assertFalse(get_site_transient($this->getNegativeCacheBackoffKey()));
        }
    }

    public function test_repeated_authoritative_refusals_use_bounded_backoff()
    {
        $requestCount = 0;

        add_filter('pre_http_request', function () use (&$requestCount) {
            $requestCount++;

            return [
                'response' => ['code' => 403],
                'body'     => wp_json_encode(['status' => 'suspended']),
            ];
        });

        $communicator      = new ApiCommunicator();
        $expectedDurations = [
            12 * HOUR_IN_SECONDS,
            DAY_IN_SECONDS,
            2 * DAY_IN_SECONDS,
            2 * DAY_IN_SECONDS,
        ];

        foreach ($expectedDurations as $expectedDuration) {
            $this->assertDownloadRequestFails($communicator);
            $this->assertCacheDuration($expectedDuration);
            $this->assertSame($expectedDuration, get_site_transient($this->getNegativeCacheBackoffKey()));

            // Simulate the negative marker expiring while preserving the backoff state.
            delete_site_transient($this->getNegativeCacheKey());
        }

        $this->assertSame(4, $requestCount);
    }

    public function test_successful_request_clears_authoritative_refusal_backoff()
    {
        add_filter('pre_http_request', function () {
            return [
                'response' => ['code' => 403],
                'body'     => wp_json_encode(['status' => 'suspended']),
            ];
        });

        $communicator = new ApiCommunicator();
        $this->assertDownloadRequestFails($communicator);

        delete_site_transient($this->getNegativeCacheKey());
        remove_all_filters('pre_http_request');
        add_filter('pre_http_request', function () {
            return [
                'response' => ['code' => 200],
                'body'     => wp_json_encode(['download_url' => 'https://example.com/add-on.zip']),
            ];
        });

        $this->assertIsObject($communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertFalse(get_site_transient($this->getNegativeCacheKey()));
        $this->assertFalse(get_site_transient($this->getNegativeCacheBackoffKey()));
    }

    public function test_network_refusal_does_not_override_existing_site_specific_success()
    {
        $requestCount = 0;
        $productInfo  = (object)['download_url' => 'https://example.com/add-on.zip'];

        set_transient($this->getProductInfoCacheKey(), $productInfo, DAY_IN_SECONDS);
        set_site_transient($this->getNegativeCacheKey(), (object)['_negative_cache' => true], DAY_IN_SECONDS);
        add_filter('pre_http_request', function () use (&$requestCount) {
            $requestCount++;

            return new WP_Error('unexpected_request', 'The cached response should be used.');
        });

        $communicator = new ApiCommunicator();

        $this->assertEquals($productInfo, $communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug));
        $this->assertSame(0, $requestCount);
    }

    private function assertDownloadRequestFails($communicator)
    {
        try {
            $communicator->getDownloadUrl($this->licenseKey, $this->pluginSlug);
            $this->fail('Expected the authoritative refusal to throw an exception.');
        } catch (Exception $e) {
            $this->assertNotEmpty($e->getMessage());
        }
    }

    private function assertCacheDuration($expectedDuration)
    {
        $timeout = $this->getNegativeCacheTimeout();

        $this->assertGreaterThanOrEqual(time() + $expectedDuration - MINUTE_IN_SECONDS, $timeout);
        $this->assertLessThanOrEqual(time() + $expectedDuration + MINUTE_IN_SECONDS, $timeout);
    }

    private function getProductInfoCacheKey()
    {
        return 'wp_statistics_product_info_' . md5($this->pluginSlug . '_' . $this->licenseKey . '_' . get_current_blog_id());
    }

    private function getNegativeCacheKey()
    {
        return 'wp_statistics_product_info_negative_' . md5($this->pluginSlug . '_' . $this->licenseKey);
    }

    private function getNegativeCacheBackoffKey()
    {
        return $this->getNegativeCacheKey() . '_backoff';
    }

    private function getNegativeCacheTimeout()
    {
        $optionName = '_site_transient_timeout_' . $this->getNegativeCacheKey();

        return (int) (is_multisite() ? get_site_option($optionName) : get_option($optionName));
    }
}
