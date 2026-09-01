<?php

use WP_Statistics\Service\Admin\LicenseManagement\ApiCommunicator;

class Test_ApiCommunicator extends WP_UnitTestCase
{
    private $licenseKey = '12345678901234567890123456789012';
    private $pluginSlug = 'wp-statistics-advanced-reporting';

    public function tearDown(): void
    {
        delete_site_transient($this->getNegativeCacheKey());
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
        }
    }

    private function getNegativeCacheKey()
    {
        return 'wp_statistics_product_info_negative_' . md5($this->pluginSlug . '_' . $this->licenseKey);
    }

    private function getNegativeCacheTimeout()
    {
        $optionName = '_site_transient_timeout_' . $this->getNegativeCacheKey();

        return (int) (is_multisite() ? get_site_option($optionName) : get_option($optionName));
    }
}
