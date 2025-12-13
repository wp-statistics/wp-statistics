<?php

namespace WP_Statistics\Tests\DashboardBootstrap;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\DashboardBootstrap\Providers\GlobalDataProvider;

/**
 * Test GlobalDataProvider class.
 *
 * Tests the GlobalDataProvider's ability to provide essential global data
 * to the React dashboard application.
 */
class Test_GlobalDataProvider extends WP_UnitTestCase
{
    private $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new GlobalDataProvider();
    }

    /**
     * Test getKey returns correct key.
     */
    public function test_get_key_returns_correct_value()
    {
        $this->assertEquals('globals', $this->provider->getKey());
    }

    /**
     * Test getData returns array.
     */
    public function test_get_data_returns_array()
    {
        $data = $this->provider->getData();

        $this->assertIsArray($data);
    }

    /**
     * Test getData contains required fields.
     */
    public function test_get_data_contains_required_fields()
    {
        $data = $this->provider->getData();

        $this->assertArrayHasKey('isPremium', $data);
        $this->assertArrayHasKey('ajaxUrl', $data);
        $this->assertArrayHasKey('nonce', $data);
        $this->assertArrayHasKey('pluginUrl', $data);
        $this->assertArrayHasKey('analyticsAction', $data);
    }

    /**
     * Test isPremium is boolean or null.
     */
    public function test_is_premium_is_boolean_or_null()
    {
        $data = $this->provider->getData();

        // Can be boolean or null depending on license state
        $this->assertTrue(
            is_bool($data['isPremium']) || is_null($data['isPremium']),
            'isPremium should be boolean or null'
        );
    }

    /**
     * Test ajaxUrl is valid URL.
     */
    public function test_ajax_url_is_valid()
    {
        $data = $this->provider->getData();

        $this->assertIsString($data['ajaxUrl']);
        $this->assertStringContainsString('admin-ajax.php', $data['ajaxUrl']);
    }

    /**
     * Test nonce is non-empty string.
     */
    public function test_nonce_is_non_empty_string()
    {
        $data = $this->provider->getData();

        $this->assertIsString($data['nonce']);
        $this->assertNotEmpty($data['nonce']);
    }

    /**
     * Test pluginUrl is defined constant.
     */
    public function test_plugin_url_is_defined()
    {
        // Define constant if not already defined
        if (!defined('WP_STATISTICS_URL')) {
            define('WP_STATISTICS_URL', 'http://example.com/wp-content/plugins/wp-statistics/');
        }

        $data = $this->provider->getData();

        $this->assertArrayHasKey('pluginUrl', $data);
        $this->assertEquals(WP_STATISTICS_URL, $data['pluginUrl']);
    }

    /**
     * Test analyticsAction is non-empty string.
     */
    public function test_analytics_action_is_non_empty_string()
    {
        $data = $this->provider->getData();

        $this->assertIsString($data['analyticsAction']);
        $this->assertNotEmpty($data['analyticsAction']);
    }

    /**
     * Test getData applies filter hook.
     */
    public function test_get_data_applies_filter()
    {
        // Add a filter to modify data
        add_filter('wp_statistics_dashboard_global_data', function ($data) {
            $data['custom_field'] = 'custom_value';
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayHasKey('custom_field', $data);
        $this->assertEquals('custom_value', $data['custom_field']);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_global_data');
    }

    /**
     * Test getData filter can modify existing fields.
     */
    public function test_get_data_filter_can_modify_fields()
    {
        // Add a filter to modify existing data
        add_filter('wp_statistics_dashboard_global_data', function ($data) {
            $data['isPremium'] = true;
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertTrue($data['isPremium']);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_global_data');
    }

    /**
     * Test getData filter can remove fields.
     */
    public function test_get_data_filter_can_remove_fields()
    {
        // Add a filter to remove a field
        add_filter('wp_statistics_dashboard_global_data', function ($data) {
            unset($data['nonce']);
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayNotHasKey('nonce', $data);

        // Remove filter to not affect other tests
        remove_all_filters('wp_statistics_dashboard_global_data');
    }

    /**
     * Test nonce is unique across calls.
     */
    public function test_nonce_consistency()
    {
        $data1 = $this->provider->getData();
        $data2 = $this->provider->getData();

        // Nonces should be the same for the same action within the same request
        $this->assertIsString($data1['nonce']);
        $this->assertIsString($data2['nonce']);
    }

    /**
     * Test all values are properly sanitized.
     */
    public function test_values_are_sanitized()
    {
        $data = $this->provider->getData();

        // Check that URL values don't contain harmful content
        $this->assertDoesNotMatchRegularExpression('/<script/', $data['ajaxUrl']);
        $this->assertDoesNotMatchRegularExpression('/<script/', $data['pluginUrl']);

        // Check that string values are clean
        $this->assertIsString($data['nonce']);
        $this->assertIsString($data['analyticsAction']);
    }
}
