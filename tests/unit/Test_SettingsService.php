<?php

namespace WP_Statistics\Tests\Settings;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Settings\SettingsService;
use WP_Statistics\Service\Admin\Settings\SettingsConfigProvider;
use WP_Statistics\Components\Option;

/**
 * Tests for SettingsService — tab reads, writes, roles, batch reads, and saved hook.
 */
class Test_SettingsService extends WP_UnitTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        remove_all_filters('wp_statistics_settings_tabs');
        remove_all_filters('wp_statistics_settings_cards');
        remove_all_filters('wp_statistics_settings_fields');
        remove_all_filters('wp_statistics_settings_saved');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_settings_tabs');
        remove_all_filters('wp_statistics_settings_cards');
        remove_all_filters('wp_statistics_settings_fields');
        remove_all_filters('wp_statistics_settings_saved');
        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Tab reads
    // ------------------------------------------------------------------

    public function test_get_tab_settings_returns_array()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        $this->assertIsArray($settings);
    }

    public function test_get_tab_settings_returns_empty_for_unknown_tab()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('nonexistent_tab');

        $this->assertIsArray($settings);
        $this->assertEmpty($settings);
    }

    public function test_general_tab_has_expected_keys()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        $this->assertArrayHasKey('visitors_log', $settings);
        $this->assertArrayHasKey('bypass_ad_blockers', $settings);
    }

    public function test_display_tab_has_expected_keys()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('display');

        $this->assertArrayHasKey('disable_editor', $settings);
        $this->assertArrayHasKey('disable_column', $settings);
        $this->assertArrayHasKey('menu_bar', $settings);
    }

    public function test_privacy_tab_has_expected_keys()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('privacy');

        $this->assertArrayHasKey('store_ip', $settings);
        $this->assertArrayHasKey('consent_integration', $settings);
    }

    public function test_get_all_settings_returns_keyed_by_tab()
    {
        $service  = new SettingsService();
        $settings = $service->getAllSettings();

        $this->assertIsArray($settings);
        $this->assertArrayHasKey('general', $settings);
        $this->assertArrayHasKey('display', $settings);
        $this->assertArrayHasKey('privacy', $settings);
    }

    // ------------------------------------------------------------------
    // Saves
    // ------------------------------------------------------------------

    public function test_save_tab_settings_filters_by_allowed_keys()
    {
        $service = new SettingsService();

        $service->saveTabSettings('general', [
            'visitors_log' => true,
        ]);

        $this->assertTrue(Option::getValue('visitors_log'));
    }

    public function test_save_rejects_disallowed_keys()
    {
        $service = new SettingsService();

        Option::updateValue('some_random_key_that_is_not_allowed', 'original');

        $service->saveTabSettings('general', [
            'visitors_log'                        => true,
            'some_random_key_that_is_not_allowed' => 'hacked',
        ]);

        $this->assertEquals('original', Option::getValue('some_random_key_that_is_not_allowed'));
    }

    public function test_save_throws_for_empty_settings()
    {
        $this->expectException(\InvalidArgumentException::class);

        $service = new SettingsService();
        $service->saveTabSettings('general', []);
    }

    public function test_save_settings_no_tab_scope()
    {
        $service = new SettingsService();

        $service->saveSettings([
            'visitors_log' => true,
        ]);

        $this->assertTrue(Option::getValue('visitors_log'));
    }

    // ------------------------------------------------------------------
    // Roles
    // ------------------------------------------------------------------

    public function test_get_available_roles()
    {
        $roles = SettingsService::getAvailableRoles();

        $this->assertIsArray($roles);
        $this->assertNotEmpty($roles);

        $slugs = array_column($roles, 'slug');
        $this->assertContains('administrator', $slugs);
    }

    public function test_roles_have_slug_and_name()
    {
        $roles = SettingsService::getAvailableRoles();

        foreach ($roles as $role) {
            $this->assertArrayHasKey('slug', $role);
            $this->assertArrayHasKey('name', $role);
            $this->assertNotEmpty($role['slug']);
            $this->assertNotEmpty($role['name']);
        }
    }

    public function test_get_tab_includes_roles_for_access_tab()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('access');

        $this->assertArrayHasKey('_roles', $settings);
        $this->assertNotEmpty($settings['_roles']);
    }

    public function test_get_tab_includes_roles_for_exclusions_tab()
    {
        $service  = new SettingsService();
        $settings = $service->getTabSettings('exclusions');

        $this->assertArrayHasKey('_roles', $settings);
    }

    // ------------------------------------------------------------------
    // Allowed keys
    // ------------------------------------------------------------------

    public function test_get_allowed_keys_for_tab()
    {
        $service = new SettingsService();
        $keys    = $service->getAllowedKeysForTab('general');

        $this->assertIsArray($keys);
        $this->assertContains('visitors_log', $keys);
        $this->assertContains('bypass_ad_blockers', $keys);
    }

    public function test_allowed_keys_include_filter_added_fields()
    {
        add_filter('wp_statistics_settings_fields', function ($fields, $tabId, $cardId) {
            if ($tabId === 'general' && $cardId === 'tracking') {
                $fields['premium_tracking'] = [
                    'type'        => 'toggle',
                    'setting_key' => 'premium_tracking_enabled',
                    'label'       => 'Premium Tracking',
                    'order'       => 100,
                ];
            }
            return $fields;
        }, 10, 3);

        $service = new SettingsService();
        $keys    = $service->getAllowedKeysForTab('general');

        $this->assertContains('premium_tracking_enabled', $keys);
    }

    public function test_caches_allowed_keys()
    {
        $service = new SettingsService();

        $keys1 = $service->getAllowedKeysForTab('general');
        $keys2 = $service->getAllowedKeysForTab('general');

        $this->assertEquals($keys1, $keys2);
    }

    // ------------------------------------------------------------------
    // SettingsConfigProvider (area injection)
    // ------------------------------------------------------------------

    public function test_config_provider_injects_area_into_settings_tabs()
    {
        $provider = new SettingsConfigProvider();
        $config   = $provider->getConfig();

        $settingsTabIds = ['general', 'display', 'privacy', 'notifications', 'exclusions', 'access', 'data-management', 'advanced'];
        foreach ($settingsTabIds as $tabId) {
            $this->assertArrayHasKey($tabId, $config['tabs'], "Tab '{$tabId}' should exist");
            $this->assertEquals('settings', $config['tabs'][$tabId]['area'], "Tab '{$tabId}' should have area='settings'");
        }
    }

    public function test_config_provider_injects_area_into_tools_tabs()
    {
        $provider = new SettingsConfigProvider();
        $config   = $provider->getConfig();

        $toolsTabIds = ['system-info', 'diagnostics', 'scheduled-tasks', 'background-jobs', 'import-export', 'backups'];
        foreach ($toolsTabIds as $tabId) {
            $this->assertArrayHasKey($tabId, $config['tabs'], "Tab '{$tabId}' should exist");
            $this->assertEquals('tools', $config['tabs'][$tabId]['area'], "Tab '{$tabId}' should have area='tools'");
        }
    }

    public function test_config_provider_settings_area_tab_keys()
    {
        $provider = new SettingsConfigProvider();
        $tabKeys  = $provider->getSettingsAreaTabKeys();

        $this->assertContains('general', $tabKeys);
        $this->assertContains('display', $tabKeys);
        $this->assertContains('privacy', $tabKeys);
        $this->assertContains('data', $tabKeys);
        $this->assertNotContains('system-info', $tabKeys);
        $this->assertNotContains('diagnostics', $tabKeys);
    }

    public function test_config_provider_allowed_keys_excludes_tools_tabs()
    {
        $provider    = new SettingsConfigProvider();
        $allowedKeys = $provider->getAllowedKeysByTab();

        $this->assertArrayHasKey('general', $allowedKeys);
        $this->assertArrayNotHasKey('system-info', $allowedKeys);
        $this->assertArrayNotHasKey('diagnostics', $allowedKeys);
    }

    // ------------------------------------------------------------------
    // Batch option reads (suggestion #4)
    // ------------------------------------------------------------------

    public function test_batch_read_applies_option_filter()
    {
        Option::updateValue('visitors_log', false);

        add_filter('wp_statistics_option_visitors_log', function () {
            return true;
        });

        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        $this->assertTrue($settings['visitors_log']);

        remove_all_filters('wp_statistics_option_visitors_log');
    }

    public function test_batch_read_returns_default_for_missing_key()
    {
        // Ensure key does not exist in stored options
        $options = Option::get();
        unset($options['bypass_ad_blockers']);
        update_option('wp_statistics', $options);

        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        // bypass_ad_blockers has no entry in Option::getDefaults(), so defaults to false
        $this->assertFalse($settings['bypass_ad_blockers']);
    }

    public function test_batch_read_uses_filter_added_field_default_for_missing_value()
    {
        add_filter('wp_statistics_settings_fields', function ($fields, $tabId, $cardId) {
            if ($tabId === 'general' && $cardId === 'tracking') {
                $fields['premium_text_setting_field'] = [
                    'type'        => 'input',
                    'setting_key' => 'premium_text_setting',
                    'label'       => 'Premium Text',
                    'default'     => '',
                    'order'       => 999,
                ];
            }

            return $fields;
        }, 10, 3);

        $options = Option::get();
        unset($options['premium_text_setting']);
        update_option('wp_statistics', $options);

        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        $this->assertArrayHasKey('premium_text_setting', $settings);
        $this->assertSame('', $settings['premium_text_setting']);
    }

    public function test_batch_read_normalizes_legacy_false_to_filter_added_non_boolean_default()
    {
        add_filter('wp_statistics_settings_fields', function ($fields, $tabId, $cardId) {
            if ($tabId === 'general' && $cardId === 'tracking') {
                $fields['premium_text_setting_field'] = [
                    'type'        => 'input',
                    'setting_key' => 'premium_text_setting',
                    'label'       => 'Premium Text',
                    'default'     => '',
                    'order'       => 999,
                ];
            }

            return $fields;
        }, 10, 3);

        Option::updateValue('premium_text_setting', false);

        $service  = new SettingsService();
        $settings = $service->getTabSettings('general');

        $this->assertArrayHasKey('premium_text_setting', $settings);
        $this->assertSame('', $settings['premium_text_setting']);
    }

    public function test_get_all_settings_batch_returns_same_as_per_tab()
    {
        $service = new SettingsService();

        $allSettings = $service->getAllSettings();
        $generalTab  = $service->getTabSettings('general');

        // Both paths should produce the same result for the general tab
        $this->assertEquals($generalTab, $allSettings['general']);
    }

    // ------------------------------------------------------------------
    // Settings saved hook (suggestion #5)
    // ------------------------------------------------------------------

    public function test_save_tab_fires_settings_saved_hook()
    {
        $service = new SettingsService();

        $service->saveTabSettings('general', [
            'visitors_log' => true,
        ]);

        $this->assertSame(1, did_action('wp_statistics_settings_saved'));
    }

    public function test_save_settings_fires_settings_saved_hook_with_all()
    {
        $captured = [];
        add_action('wp_statistics_settings_saved', function ($tab, $settings) use (&$captured) {
            $captured = ['tab' => $tab, 'settings' => $settings];
        }, 10, 2);

        $service = new SettingsService();
        $service->saveSettings(['visitors_log' => true]);

        $this->assertEquals('all', $captured['tab']);
        $this->assertArrayHasKey('visitors_log', $captured['settings']);
    }

    public function test_save_tab_hook_receives_correct_tab_name()
    {
        $captured = [];
        add_action('wp_statistics_settings_saved', function ($tab) use (&$captured) {
            $captured[] = $tab;
        });

        $service = new SettingsService();
        $service->saveTabSettings('privacy', ['store_ip' => false]);

        $this->assertContains('privacy', $captured);
    }
}
