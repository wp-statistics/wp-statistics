<?php

namespace WP_Statistics\Tests\Tools;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Tools\OptionInspectionService;

/**
 * Tests for OptionInspectionService.
 */
class Test_OptionInspectionService extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        remove_all_filters('wp_statistics_option_inspection_groups');
        remove_all_filters('wp_statistics_user_meta_inspection_keys');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_option_inspection_groups');
        remove_all_filters('wp_statistics_user_meta_inspection_keys');
        parent::tearDown();
    }

    public function test_returns_options()
    {
        $service = new OptionInspectionService();
        $options = $service->getOptions();

        $this->assertIsArray($options);
    }

    public function test_options_have_required_fields()
    {
        $service = new OptionInspectionService();
        $options = $service->getOptions();

        foreach ($options as $option) {
            $this->assertArrayHasKey('key', $option);
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('group', $option);
        }
    }

    public function test_includes_version_group()
    {
        $service = new OptionInspectionService();
        $options = $service->getOptions();
        $groups  = array_column($options, 'group');

        $this->assertContains('version', $groups);
    }

    public function test_returns_transients()
    {
        set_transient('wp_statistics_test_transient', 'test_value', 3600);

        $service    = new OptionInspectionService();
        $transients = $service->getTransients();

        $this->assertIsArray($transients);

        delete_transient('wp_statistics_test_transient');
    }

    public function test_transients_have_required_fields()
    {
        set_transient('wp_statistics_test_format', ['key' => 'val'], 3600);

        $service    = new OptionInspectionService();
        $transients = $service->getTransients();

        foreach ($transients as $transient) {
            $this->assertArrayHasKey('name', $transient);
            $this->assertArrayHasKey('value', $transient);
        }

        delete_transient('wp_statistics_test_format');
    }

    public function test_returns_user_meta()
    {
        $userId   = get_current_user_id() ?: 1;
        $service  = new OptionInspectionService();
        $userMeta = $service->getUserMeta($userId);

        $this->assertIsArray($userMeta);
        $this->assertNotEmpty($userMeta);

        $keys = array_column($userMeta, 'key');
        $this->assertContains('wp_statistics_dashboard_preferences', $keys);
    }

    public function test_user_meta_has_required_fields()
    {
        $service  = new OptionInspectionService();
        $userMeta = $service->getUserMeta(1);

        foreach ($userMeta as $meta) {
            $this->assertArrayHasKey('key', $meta);
            $this->assertArrayHasKey('value', $meta);
            $this->assertArrayHasKey('exists', $meta);
            $this->assertArrayHasKey('isLegacy', $meta);
        }
    }

    public function test_marks_legacy_keys()
    {
        $service  = new OptionInspectionService();
        $userMeta = $service->getUserMeta(1);

        $byKey = [];
        foreach ($userMeta as $meta) {
            $byKey[$meta['key']] = $meta;
        }

        $this->assertFalse($byKey['wp_statistics_dashboard_preferences']['isLegacy']);

        if (isset($byKey['wp_statistics'])) {
            $this->assertTrue($byKey['wp_statistics']['isLegacy']);
        }
    }

    public function test_groups_filter()
    {
        add_filter('wp_statistics_option_inspection_groups', function ($groups) {
            $groups['wp_statistics_premium'] = 'premium';
            return $groups;
        });

        update_option('wp_statistics_premium', ['license_key' => 'test']);

        $service = new OptionInspectionService();
        $options = $service->getOptions();
        $groups  = array_column($options, 'group');

        $this->assertContains('premium', $groups);

        delete_option('wp_statistics_premium');
    }

    public function test_user_meta_keys_filter()
    {
        add_filter('wp_statistics_user_meta_inspection_keys', function ($keys) {
            $keys['wp_statistics_premium_pref'] = false;
            return $keys;
        });

        $service  = new OptionInspectionService();
        $userMeta = $service->getUserMeta(1);
        $keys     = array_column($userMeta, 'key');

        $this->assertContains('wp_statistics_premium_pref', $keys);
    }

    public function test_user_meta_filter_preserves_core_keys()
    {
        add_filter('wp_statistics_user_meta_inspection_keys', function ($keys) {
            $keys['wp_statistics_premium_pref'] = false;
            return $keys;
        });

        $service  = new OptionInspectionService();
        $userMeta = $service->getUserMeta(1);
        $keys     = array_column($userMeta, 'key');

        $this->assertContains('wp_statistics_dashboard_preferences', $keys);
        $this->assertContains('wp_statistics_premium_pref', $keys);
    }
}
