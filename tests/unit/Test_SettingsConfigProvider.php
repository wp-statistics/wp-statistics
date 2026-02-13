<?php

namespace WP_Statistics\Tests\Settings;

use WP_UnitTestCase;
use WP_Statistics\Service\Admin\Settings\SettingsConfigProvider;

/**
 * Test SettingsConfigProvider class.
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
     * Test component-based tabs have no cards or fields.
     */
    public function test_get_config_component_tabs_have_no_cards()
    {
        $config = $this->provider->getConfig();

        $componentTabs = array_filter($config['tabs'], function ($tab) {
            return !empty($tab['component']);
        });

        $this->assertNotEmpty($componentTabs, 'There should be at least one component tab');

        foreach ($componentTabs as $tabId => $tab) {
            $this->assertArrayNotHasKey($tabId, $config['cards'], "Component tab '{$tabId}' should have no cards");
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
}
