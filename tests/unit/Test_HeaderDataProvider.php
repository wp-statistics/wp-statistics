<?php

namespace WP_Statistics\Tests\ReactApp;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\ReactApp\Providers\HeaderDataProvider;

/**
 * Test HeaderDataProvider class.
 *
 * Tests the HeaderDataProvider's ability to provide dashboard header data
 * including premium badge information.
 */
class Test_HeaderDataProvider extends WP_UnitTestCase
{
    private $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new HeaderDataProvider();
    }

    /**
     * Test getKey returns correct key.
     */
    public function test_get_key_returns_correct_value()
    {
        $this->assertEquals('header', $this->provider->getKey());
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
     * Test getData contains required sections.
     */
    public function test_get_data_contains_required_sections()
    {
        $data = $this->provider->getData();

        $this->assertArrayHasKey('premiumBadge', $data);
    }

    /**
     * Test premiumBadge section structure.
     */
    public function test_premium_badge_section_structure()
    {
        $data = $this->provider->getData();

        $this->assertIsArray($data['premiumBadge']);
        $this->assertArrayHasKey('isActive', $data['premiumBadge']);
        $this->assertArrayHasKey('url', $data['premiumBadge']);
        $this->assertArrayHasKey('icon', $data['premiumBadge']);
        $this->assertArrayHasKey('label', $data['premiumBadge']);
    }

    /**
     * Test premiumBadge isActive is boolean.
     */
    public function test_premium_badge_is_active_is_boolean()
    {
        $data = $this->provider->getData();

        $this->assertIsBool($data['premiumBadge']['isActive']);
    }

    /**
     * Test premiumBadge url is valid.
     */
    public function test_premium_badge_url_is_valid()
    {
        if (!defined('WP_STATISTICS_SITE_URL')) {
            define('WP_STATISTICS_SITE_URL', 'https://wp-statistics.com');
        }

        $data = $this->provider->getData();

        $this->assertIsString($data['premiumBadge']['url']);
        $this->assertStringContainsString('pricing', $data['premiumBadge']['url']);
        $this->assertStringContainsString('utm_source=wp-statistics', $data['premiumBadge']['url']);
    }

    /**
     * Test premiumBadge icon is string.
     */
    public function test_premium_badge_icon_is_string()
    {
        $data = $this->provider->getData();

        $this->assertIsString($data['premiumBadge']['icon']);
        $this->assertEquals('Crown', $data['premiumBadge']['icon']);
    }

    /**
     * Test premiumBadge label is translatable string.
     */
    public function test_premium_badge_label_is_translatable()
    {
        $data = $this->provider->getData();

        $this->assertIsString($data['premiumBadge']['label']);
        $this->assertNotEmpty($data['premiumBadge']['label']);
    }

    /**
     * Test getData applies filter hook.
     */
    public function test_get_data_applies_filter()
    {
        add_filter('wp_statistics_dashboard_header_data', function ($data) {
            $data['customSection'] = [
                'isActive' => true,
                'label'    => 'Custom Section',
            ];
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayHasKey('customSection', $data);
        $this->assertTrue($data['customSection']['isActive']);

        remove_all_filters('wp_statistics_dashboard_header_data');
    }

    /**
     * Test getData filter can remove sections.
     */
    public function test_get_data_filter_can_remove_sections()
    {
        add_filter('wp_statistics_dashboard_header_data', function ($data) {
            unset($data['premiumBadge']);
            return $data;
        });

        $data = $this->provider->getData();

        $this->assertArrayNotHasKey('premiumBadge', $data);

        remove_all_filters('wp_statistics_dashboard_header_data');
    }

    /**
     * Test all labels are properly escaped.
     */
    public function test_labels_are_escaped()
    {
        $data = $this->provider->getData();

        $this->assertDoesNotMatchRegularExpression('/<script/', $data['premiumBadge']['label']);
    }

    /**
     * Test all URLs are properly escaped.
     */
    public function test_urls_are_escaped()
    {
        if (!defined('WP_STATISTICS_SITE_URL')) {
            define('WP_STATISTICS_SITE_URL', 'https://wp-statistics.com');
        }

        $data = $this->provider->getData();

        $this->assertIsString($data['premiumBadge']['url']);
        $this->assertDoesNotMatchRegularExpression('/<script/', $data['premiumBadge']['url']);
    }
}
