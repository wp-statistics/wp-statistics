<?php

namespace WP_Statistics\Tests\Settings;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Settings\SettingsConfigProvider;
use WP_Statistics\Service\Admin\Settings\Definitions\SettingsAreaDefinitions;
use WP_Statistics\Service\Admin\Tools\Definitions\ToolsAreaDefinitions;

/**
 * Test SettingsConfigProvider class and definition subclasses.
 */
class Test_SettingsConfigProvider extends WP_UnitTestCase
{
    private $provider;

    public function setUp(): void
    {
        parent::setUp();
        $this->provider = new SettingsConfigProvider();

        // Clean up any filters from previous tests
        remove_all_filters('wp_statistics_settings_tabs');
        remove_all_filters('wp_statistics_settings_cards');
        remove_all_filters('wp_statistics_settings_fields');
    }

    public function tearDown(): void
    {
        remove_all_filters('wp_statistics_settings_tabs');
        remove_all_filters('wp_statistics_settings_cards');
        remove_all_filters('wp_statistics_settings_fields');
        parent::tearDown();
    }

    /**
     * Test getConfig returns correct top-level structure.
     */
    public function test_get_config_returns_correct_structure()
    {
        $config = $this->provider->getConfig();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('tabs', $config);
        $this->assertArrayHasKey('cards', $config);
        $this->assertArrayHasKey('fields', $config);
    }

    /**
     * Test every tab has required fields.
     */
    public function test_get_config_tabs_have_required_fields()
    {
        $config = $this->provider->getConfig();

        foreach ($config['tabs'] as $tabId => $tab) {
            $this->assertArrayHasKey('area', $tab, "Tab '{$tabId}' missing 'area'");
            $this->assertArrayHasKey('label', $tab, "Tab '{$tabId}' missing 'label'");
            $this->assertArrayHasKey('icon', $tab, "Tab '{$tabId}' missing 'icon'");
            $this->assertArrayHasKey('order', $tab, "Tab '{$tabId}' missing 'order'");
        }
    }

    /**
     * Test both 'settings' and 'tools' areas exist.
     */
    public function test_get_config_tabs_contain_all_areas()
    {
        $config = $this->provider->getConfig();
        $areas  = array_unique(array_column($config['tabs'], 'area'));

        $this->assertContains('settings', $areas);
        $this->assertContains('tools', $areas);
    }

    /**
     * Test declarative tabs (no component) have cards and fields populated.
     */
    public function test_get_config_declarative_tabs_have_cards_and_fields()
    {
        $config = $this->provider->getConfig();

        $declarativeTabs = array_filter($config['tabs'], function ($tab) {
            return empty($tab['component']);
        });

        $this->assertNotEmpty($declarativeTabs, 'There should be at least one declarative tab');

        foreach ($declarativeTabs as $tabId => $tab) {
            $this->assertArrayHasKey($tabId, $config['cards'], "Declarative tab '{$tabId}' should have cards");
            $this->assertNotEmpty($config['cards'][$tabId], "Declarative tab '{$tabId}' cards should not be empty");
        }
    }

    /**
     * Test all settings tabs are now fully declarative and have cards.
     */
    public function test_get_config_all_settings_tabs_have_cards()
    {
        $config = $this->provider->getConfig();

        $settingsTabs = array_filter($config['tabs'], function ($tab) {
            return $tab['area'] === 'settings';
        });

        foreach ($settingsTabs as $tabId => $tab) {
            $this->assertArrayHasKey($tabId, $config['cards'], "Settings tab '{$tabId}' should have cards");
            $this->assertNotEmpty($config['cards'][$tabId], "Settings tab '{$tabId}' cards should not be empty");
        }
    }

    /**
     * Test no settings tabs have the `component` key (all are declarative).
     */
    public function test_get_config_no_settings_tabs_have_component()
    {
        $config = $this->provider->getConfig();

        $settingsTabs = array_filter($config['tabs'], function ($tab) {
            return $tab['area'] === 'settings';
        });

        foreach ($settingsTabs as $tabId => $tab) {
            $this->assertArrayNotHasKey('component', $tab, "Settings tab '{$tabId}' should not have 'component' — all are declarative");
        }
    }

    /**
     * Test every field has 'type' and 'order'; toggle/select/input fields have 'setting_key'.
     */
    public function test_get_config_fields_have_required_properties()
    {
        $config         = $this->provider->getConfig();
        $keyedTypes     = ['toggle', 'select', 'input', 'textarea', 'number'];

        foreach ($config['fields'] as $path => $fields) {
            foreach ($fields as $fieldId => $field) {
                $this->assertArrayHasKey('type', $field, "Field '{$path}/{$fieldId}' missing 'type'");
                $this->assertArrayHasKey('order', $field, "Field '{$path}/{$fieldId}' missing 'order'");

                if (in_array($field['type'], $keyedTypes, true)) {
                    $this->assertArrayHasKey(
                        'setting_key',
                        $field,
                        "Field '{$path}/{$fieldId}' of type '{$field['type']}' should have 'setting_key'"
                    );
                }
            }
        }
    }

    /**
     * Test tabs filter can add a new tab.
     */
    public function test_tabs_filter_can_add_tab()
    {
        add_filter('wp_statistics_settings_tabs', function ($tabs) {
            $tabs['custom-tab'] = [
                'area'  => 'settings',
                'label' => 'Custom Tab',
                'icon'  => 'star',
                'order' => 100,
            ];
            return $tabs;
        });

        $config = $this->provider->getConfig();

        $this->assertArrayHasKey('custom-tab', $config['tabs']);
        $this->assertEquals('Custom Tab', $config['tabs']['custom-tab']['label']);
    }

    /**
     * Test cards filter can add a card to a tab.
     */
    public function test_cards_filter_can_add_card()
    {
        add_filter('wp_statistics_settings_cards', function ($cards, $tabId) {
            if ($tabId === 'general') {
                $cards['custom-card'] = [
                    'title' => 'Custom Card',
                    'order' => 100,
                ];
            }
            return $cards;
        }, 10, 2);

        $config = $this->provider->getConfig();

        $this->assertArrayHasKey('custom-card', $config['cards']['general']);
        $this->assertEquals('Custom Card', $config['cards']['general']['custom-card']['title']);
    }

    /**
     * Test fields filter can add a field to a card.
     */
    public function test_fields_filter_can_add_field()
    {
        add_filter('wp_statistics_settings_fields', function ($fields, $tabId, $cardId) {
            if ($tabId === 'general' && $cardId === 'tracking') {
                $fields['custom-field'] = [
                    'type'        => 'toggle',
                    'setting_key' => 'custom_option',
                    'label'       => 'Custom Option',
                    'order'       => 100,
                ];
            }
            return $fields;
        }, 10, 3);

        $config = $this->provider->getConfig();

        $this->assertArrayHasKey('custom-field', $config['fields']['general/tracking']);
        $this->assertEquals('custom_option', $config['fields']['general/tracking']['custom-field']['setting_key']);
    }

    /**
     * Test getSettingKeysByTab returns correct keys.
     */
    public function test_get_setting_keys_by_tab_returns_correct_keys()
    {
        $result = $this->provider->getSettingKeysByTab();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('general', $result);
        $this->assertContains('visitors_log', $result['general']);
        $this->assertContains('bypass_ad_blockers', $result['general']);
    }

    /**
     * Test getSettingKeysByTab respects tab_key mapping.
     */
    public function test_get_setting_keys_by_tab_respects_tab_key_mapping()
    {
        $result = $this->provider->getSettingKeysByTab();

        // 'data-management' tab has tab_key='data', so keys should be under 'data'
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayNotHasKey('data-management', $result);
    }

    /**
     * Test fields added via filter appear in getSettingKeysByTab output.
     */
    public function test_get_setting_keys_by_tab_includes_filter_added_fields()
    {
        add_filter('wp_statistics_settings_fields', function ($fields, $tabId, $cardId) {
            if ($tabId === 'general' && $cardId === 'tracking') {
                $fields['premium-field'] = [
                    'type'        => 'toggle',
                    'setting_key' => 'premium_tracking_option',
                    'order'       => 200,
                ];
            }
            return $fields;
        }, 10, 3);

        $result = $this->provider->getSettingKeysByTab();

        $this->assertContains('premium_tracking_option', $result['general']);
    }

    // ------------------------------------------------------------------
    // Definition subclass tests
    // ------------------------------------------------------------------

    /**
     * Test SettingsAreaDefinitions returns tabs without 'area' via getDefinitions().
     */
    public function test_settings_definitions_returns_tabs_without_area()
    {
        $definitions = new SettingsAreaDefinitions();
        $all         = $definitions->getDefinitions();

        $this->assertNotEmpty($all);

        foreach ($all as $tabId => $tab) {
            $this->assertArrayNotHasKey('area', $tab, "Tab '{$tabId}' should NOT have 'area' — it's injected by the provider");
            $this->assertArrayHasKey('label', $tab, "Tab '{$tabId}' should have 'label'");
        }
    }

    /**
     * Test ToolsAreaDefinitions returns tabs without 'area' (injected by provider).
     */
    public function test_tools_definitions_returns_tabs_without_area()
    {
        $definitions = new ToolsAreaDefinitions();
        $tabs        = $definitions->getTabs();

        $this->assertNotEmpty($tabs);

        foreach ($tabs as $tabId => $tab) {
            $this->assertArrayNotHasKey('area', $tab, "Tab '{$tabId}' should NOT have 'area' — it's injected by the provider");
            $this->assertArrayHasKey('label', $tab, "Tab '{$tabId}' should have 'label'");
        }
    }

    /**
     * Test provider injects correct 'area' into settings tabs.
     */
    public function test_provider_injects_settings_area()
    {
        $config = $this->provider->getConfig();

        $settingsDefs = new SettingsAreaDefinitions();
        $settingsKeys = array_keys($settingsDefs->getDefinitions());

        foreach ($settingsKeys as $tabId) {
            $this->assertEquals('settings', $config['tabs'][$tabId]['area'], "Tab '{$tabId}' should have area='settings' after injection");
        }
    }

    /**
     * Test provider injects correct 'area' into tools tabs.
     */
    public function test_provider_injects_tools_area()
    {
        $config = $this->provider->getConfig();

        $toolsDefs = new ToolsAreaDefinitions();
        $toolsKeys = array_keys($toolsDefs->getTabs());

        foreach ($toolsKeys as $tabId) {
            $this->assertEquals('tools', $config['tabs'][$tabId]['area'], "Tab '{$tabId}' should have area='tools' after injection");
        }
    }

    /**
     * Test SettingsAreaDefinitions provides cards for all settings tabs.
     */
    public function test_settings_definitions_provides_cards()
    {
        $definitions = new SettingsAreaDefinitions();
        $all         = $definitions->getDefinitions();

        $expectedTabs = ['general', 'display', 'privacy', 'notifications', 'exclusions', 'access', 'data-management', 'advanced'];

        foreach ($expectedTabs as $tabId) {
            $this->assertArrayHasKey($tabId, $all, "Missing tab '{$tabId}'");
            $this->assertArrayHasKey('cards', $all[$tabId], "Tab '{$tabId}' should have 'cards'");
            $this->assertNotEmpty($all[$tabId]['cards'], "Tab '{$tabId}' cards should not be empty");
        }
    }

    /**
     * Test SettingsAreaDefinitions provides fields for known tab+card combos.
     */
    public function test_settings_definitions_provides_fields()
    {
        $definitions = new SettingsAreaDefinitions();
        $all         = $definitions->getDefinitions();

        // General tab
        $fields = $all['general']['cards']['tracking']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('visitors_log', $fields);

        // Privacy tab
        $fields = $all['privacy']['cards']['data-protection']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('store_ip', $fields);

        // Notifications tab
        $fields = $all['notifications']['cards']['email-reports']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('time_report', $fields);
        $this->assertArrayHasKey('email_list', $fields);

        $fields = $all['notifications']['cards']['email-content']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('content_report', $fields);

        // Exclusions tab
        $fields = $all['exclusions']['cards']['page-exclusions']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('exclude_loginpage', $fields);

        $fields = $all['exclusions']['cards']['role-exclusions']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('role_exclusions', $fields);
        $this->assertEquals('component', $fields['role_exclusions']['type']);

        // Access tab
        $fields = $all['access']['cards']['roles-permissions']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('access_level_table', $fields);
        $this->assertEquals('component', $fields['access_level_table']['type']);

        // Data Management tab
        $fields = $all['data-management']['cards']['data-retention']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('retention_mode_selector', $fields);

        // Advanced tab
        $fields = $all['advanced']['cards']['geoip-settings']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('geoip_location_detection_method', $fields);
        $this->assertArrayHasKey('geoip_license_key', $fields);

        $fields = $all['advanced']['cards']['data-sharing']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('share_anonymous_data', $fields);

        $fields = $all['advanced']['cards']['advanced-danger-zone']['fields'];
        $this->assertNotEmpty($fields);
        $this->assertArrayHasKey('delete_data_on_uninstall', $fields);
        $this->assertArrayHasKey('restore_defaults_action', $fields);
    }

    /**
     * Test SettingsAreaDefinitions returns empty cards for unknown tab.
     */
    public function test_settings_definitions_returns_empty_for_unknown()
    {
        $definitions = new SettingsAreaDefinitions();
        $all         = $definitions->getDefinitions();

        $this->assertArrayNotHasKey('nonexistent', $all);
    }

    /**
     * Test combined tabs from both definitions match provider output.
     */
    public function test_definitions_combine_to_match_provider_tabs()
    {
        $settingsDefs = new SettingsAreaDefinitions();
        $toolsDefs    = new ToolsAreaDefinitions();

        $combined = array_merge(array_keys($settingsDefs->getDefinitions()), array_keys($toolsDefs->getTabs()));
        $config   = $this->provider->getConfig();

        $this->assertEquals($combined, array_keys($config['tabs']));
    }

    /**
     * Test getDefaults extracts defaults from definitions.
     */
    public function test_settings_definitions_get_defaults()
    {
        $definitions = new SettingsAreaDefinitions();
        $defaults    = $definitions->getDefaults();

        $this->assertIsArray($defaults);

        // Tab-level defaults
        $this->assertArrayHasKey('useronline', $defaults);
        $this->assertTrue($defaults['useronline']);
        $this->assertArrayHasKey('pages', $defaults);
        $this->assertTrue($defaults['pages']);
        $this->assertArrayHasKey('ip_method', $defaults);
        $this->assertEquals('sequential', $defaults['ip_method']);
        $this->assertArrayHasKey('schedule_dbmaint', $defaults);
        $this->assertTrue($defaults['schedule_dbmaint']);

        // Field-level defaults
        $this->assertArrayHasKey('visitors_log', $defaults);
        $this->assertFalse($defaults['visitors_log']);
        $this->assertArrayHasKey('store_ip', $defaults);
        $this->assertFalse($defaults['store_ip']);
        $this->assertArrayHasKey('privacy_audit', $defaults);
        $this->assertTrue($defaults['privacy_audit']);
        $this->assertArrayHasKey('geoip_location_detection_method', $defaults);
        $this->assertEquals('maxmind', $defaults['geoip_location_detection_method']);
        $this->assertArrayHasKey('display_notifications', $defaults);
        $this->assertTrue($defaults['display_notifications']);

        // Dynamic defaults
        $this->assertArrayHasKey('email_list', $defaults);

        // Component fields should NOT have defaults (no setting_key)
        $this->assertArrayNotHasKey('role_exclusions', $defaults);
        $this->assertArrayNotHasKey('access_level_table', $defaults);
    }

    /**
     * Test provider output does not leak internal keys (cards, defaults, allowed_keys) into tabs.
     */
    public function test_provider_tabs_do_not_contain_internal_keys()
    {
        $config = $this->provider->getConfig();

        foreach ($config['tabs'] as $tabId => $tab) {
            $this->assertArrayNotHasKey('cards', $tab, "Tab '{$tabId}' should not contain 'cards' — they are separated by the provider");
            $this->assertArrayNotHasKey('defaults', $tab, "Tab '{$tabId}' should not contain 'defaults' — they are internal");
            $this->assertArrayNotHasKey('allowed_keys', $tab, "Tab '{$tabId}' should not contain 'allowed_keys' — they are internal");
        }
    }
}
