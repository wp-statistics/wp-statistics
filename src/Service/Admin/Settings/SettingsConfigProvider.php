<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Service\Admin\Settings\Definitions\SettingsAreaDefinitions;
use WP_Statistics\Service\Admin\Tools\Definitions\ToolsAreaDefinitions;

/**
 * Provides the settings/tools page configuration (tabs, cards, fields) via WordPress filters.
 *
 * This config is NOT included in wps_react (which loads on every page). Instead, it is
 * served via a dedicated AJAX endpoint so it only loads when the user visits a settings
 * or tools page.
 *
 * Premium and third-party plugins extend via:
 *   - wp_statistics_settings_tabs   — add/modify tabs
 *   - wp_statistics_settings_cards  — add/modify cards per tab
 *   - wp_statistics_settings_fields — add/modify fields per tab+card
 *
 * @since 15.3.0
 */
class SettingsConfigProvider
{
    /**
     * @var SettingsAreaDefinitions
     */
    private $settingsDefinitions;

    /**
     * @var ToolsAreaDefinitions
     */
    private $toolsDefinitions;

    public function __construct()
    {
        $this->settingsDefinitions = new SettingsAreaDefinitions();
        $this->toolsDefinitions    = new ToolsAreaDefinitions();
    }

    /**
     * Build and return the full settings config.
     *
     * @return array{tabs: array, cards: array, fields: array}
     */
    public function getConfig(): array
    {
        $tabs = apply_filters('wp_statistics_settings_tabs', $this->getCoreTabs());

        $config = ['tabs' => $tabs, 'cards' => [], 'fields' => []];

        foreach ($tabs as $tabId => $tab) {
            $cards = apply_filters('wp_statistics_settings_cards', $this->getCoreCards($tabId), $tabId);

            if (empty($cards)) {
                continue;
            }

            $config['cards'][$tabId] = $cards;

            foreach ($cards as $cardId => $card) {
                if (!empty($card['type']) && $card['type'] === 'component') {
                    continue;
                }

                $fields = apply_filters(
                    'wp_statistics_settings_fields',
                    $this->getCoreFields($tabId, $cardId),
                    $tabId,
                    $cardId
                );

                if (!empty($fields)) {
                    $config['fields']["{$tabId}/{$cardId}"] = $fields;
                }
            }
        }

        return $config;
    }

    /**
     * Extract all setting_key values from a built config, grouped by AJAX tab key.
     *
     * Used by SettingsEndpoints::getAllowedKeysForTab() to dynamically whitelist
     * premium-added fields.
     *
     * @return array<string, string[]>  AJAX tab key => list of setting keys
     */
    public function getSettingKeysByTab(): array
    {
        $config = $this->getConfig();
        $result = [];

        foreach ($config['tabs'] as $tabId => $tab) {
            $tabKey = $tab['tab_key'] ?? $tabId;

            if (!isset($result[$tabKey])) {
                $result[$tabKey] = [];
            }
        }

        foreach ($config['fields'] as $path => $fields) {
            $tabId  = explode('/', $path, 2)[0];
            $tabKey = $config['tabs'][$tabId]['tab_key'] ?? $tabId;

            foreach ($fields as $field) {
                if (!empty($field['setting_key'])) {
                    $result[$tabKey][] = $field['setting_key'];
                }
            }
        }

        return $result;
    }

    /**
     * Get AJAX tab keys for the settings area only.
     *
     * Returns the tab_key (or tabId) for every settings-area tab,
     * so callers don't need to hardcode a list of tab names.
     *
     * @return string[]
     */
    public function getSettingsAreaTabKeys(): array
    {
        $keys = [];

        foreach ($this->settingsDefinitions->getDefinitions() as $tabId => $tab) {
            $keys[] = $tab['tab_key'] ?? $tabId;
        }

        return $keys;
    }

    /**
     * Get all allowed setting keys per AJAX tab key.
     *
     * Merges:
     *  - `defaults` keys from tab definitions (hidden settings with defaults)
     *  - `allowed_keys` from tab definitions (whitelisted keys without defaults)
     *  - `setting_key` from declarative field definitions
     *  - Keys added via wp_statistics_settings_fields filter (premium plugins)
     *  - Dynamic role-based exclusion keys
     *
     * @return array<string, string[]>  AJAX tab key => list of allowed setting keys
     */
    public function getAllowedKeysByTab(): array
    {
        $config      = $this->getConfig();
        $definitions = $this->settingsDefinitions->getDefinitions();
        $result      = [];

        // Seed with defaults keys + allowed_keys from tab definitions
        foreach ($config['tabs'] as $tabId => $tab) {
            if ($tab['area'] !== 'settings') {
                continue;
            }

            $tabKey          = $tab['tab_key'] ?? $tabId;
            $def             = $definitions[$tabId] ?? [];
            $defaultKeys     = array_keys($def['defaults'] ?? []);
            $allowedKeys     = $def['allowed_keys'] ?? [];
            $result[$tabKey] = array_merge($defaultKeys, $allowedKeys);
        }

        // Add setting_key values from declarative fields
        foreach ($config['fields'] as $path => $fields) {
            $tabId = explode('/', $path, 2)[0];
            $tab   = $config['tabs'][$tabId] ?? null;

            if (!$tab || $tab['area'] !== 'settings') {
                continue;
            }

            $tabKey = $tab['tab_key'] ?? $tabId;

            foreach ($fields as $field) {
                if (!empty($field['setting_key'])) {
                    $result[$tabKey][] = $field['setting_key'];
                }
            }
        }

        // Add dynamic role-based exclusion keys
        if (isset($result['exclusions'])) {
            $result['exclusions'] = array_merge(
                $this->getRoleExclusionKeys(),
                $result['exclusions']
            );
        }

        // Deduplicate
        foreach ($result as $tabKey => $keys) {
            $result[$tabKey] = array_values(array_unique($keys));
        }

        return $result;
    }

    /**
     * Generate exclude_{role} keys for all registered WordPress roles.
     *
     * @return string[]
     */
    private function getRoleExclusionKeys(): array
    {
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return [];
        }

        return array_map(function ($slug) {
            return 'exclude_' . $slug;
        }, array_keys($wp_roles->roles));
    }

    // ------------------------------------------------------------------
    // Delegate to definition classes
    // ------------------------------------------------------------------

    private function getCoreTabs(): array
    {
        $settingsTabs = $this->extractTabs($this->settingsDefinitions->getDefinitions(), 'settings');
        $toolsTabs    = $this->injectArea($this->toolsDefinitions->getTabs(), 'tools');

        return array_merge($settingsTabs, $toolsTabs);
    }

    /**
     * Extract tab-level properties from nested definitions and inject `area`.
     *
     * Strips `cards`, `defaults`, and `allowed_keys` from the tab output
     * (cards/fields are handled separately; defaults/allowed_keys are used
     * internally by getAllowedKeysByTab).
     *
     * @param array  $definitions Nested definitions array.
     * @param string $area        Area name ('settings').
     * @return array Flat tab definitions with `area` injected.
     */
    private function extractTabs(array $definitions, string $area): array
    {
        $tabs = [];

        foreach ($definitions as $tabId => $def) {
            $tab = $def;
            unset($tab['cards'], $tab['defaults'], $tab['allowed_keys']);
            $tab['area']   = $area;
            $tabs[$tabId]  = $tab;
        }

        return $tabs;
    }

    /**
     * Inject the `area` field into each tab definition.
     *
     * Used for tools tabs which still use the flat getTabs() format.
     *
     * @param array  $tabs Tab definitions.
     * @param string $area Area name ('settings' or 'tools').
     * @return array Tabs with `area` injected.
     */
    private function injectArea(array $tabs, string $area): array
    {
        foreach ($tabs as &$tab) {
            $tab['area'] = $area;
        }

        return $tabs;
    }

    /**
     * Get cards for a tab from nested definitions.
     *
     * Strips the `fields` key from each card (fields are fetched separately).
     *
     * @param string $tabId Tab identifier.
     * @return array Card definitions without fields.
     */
    private function getCoreCards(string $tabId): array
    {
        $definitions = $this->settingsDefinitions->getDefinitions();

        if (!isset($definitions[$tabId]['cards'])) {
            return [];
        }

        $cards = [];

        foreach ($definitions[$tabId]['cards'] as $cardId => $card) {
            $stripped = $card;
            unset($stripped['fields']);
            $cards[$cardId] = $stripped;
        }

        return $cards;
    }

    /**
     * Get fields for a specific tab + card from nested definitions.
     *
     * @param string $tabId  Tab identifier.
     * @param string $cardId Card identifier.
     * @return array Field definitions.
     */
    private function getCoreFields(string $tabId, string $cardId): array
    {
        $definitions = $this->settingsDefinitions->getDefinitions();

        return $definitions[$tabId]['cards'][$cardId]['fields'] ?? [];
    }
}
