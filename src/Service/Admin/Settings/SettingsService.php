<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Components\Option;
use InvalidArgumentException;

/**
 * Reusable service for reading and writing WP Statistics settings.
 *
 * Provides tab-scoped reads/writes with key allowlisting and sanitization.
 * Can be called from AJAX endpoints, WP-CLI, or other contexts.
 *
 * @since 15.0.0
 */
class SettingsService
{
    /**
     * Cached allowed keys per tab.
     *
     * @var array<string, string[]>|null
     */
    private $allowedKeysByTab = null;

    /**
     * Cached defaults per tab (core + filtered extension field defaults).
     *
     * @var array<string, array<string, mixed>>|null
     */
    private $defaultsByTab = null;

    /**
     * Get settings for a specific tab.
     *
     * @param string $tab Tab key (e.g. 'general', 'privacy').
     * @return array Key => value map.
     */
    public function getTabSettings(string $tab): array
    {
        $keys      = $this->getAllowedKeysForTab($tab);
        $defaults  = $this->getDefaultsForTab($tab);
        $options   = Option::get(); // Single load — avoids per-key get_option() overhead
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = $this->resolveSettingValue($key, $options, $defaults);
        }

        // Include available roles so the UI can render them dynamically
        if ($tab === 'access' || $tab === 'exclusions') {
            $settings['_roles'] = self::getAvailableRoles();
        }

        return $settings;
    }

    /**
     * Get all settings for all tabs.
     *
     * @return array<string, array> Tab key => settings map.
     */
    public function getAllSettings(): array
    {
        $provider = new SettingsConfigProvider();
        $tabs     = $provider->getSettingsAreaTabKeys();
        $options  = Option::get(); // Single load for all tabs

        $settings = [];
        foreach ($tabs as $tab) {
            $keys        = $this->getAllowedKeysForTab($tab);
            $tabDefaults = $this->getDefaultsForTab($tab);
            $tabData     = [];

            foreach ($keys as $key) {
                $tabData[$key] = $this->resolveSettingValue($key, $options, $tabDefaults);
            }

            if ($tab === 'access' || $tab === 'exclusions') {
                $tabData['_roles'] = self::getAvailableRoles();
            }

            $settings[$tab] = $tabData;
        }

        return $settings;
    }

    /**
     * Save settings for a specific tab with key validation.
     *
     * Only keys that are allowed for the tab are persisted.
     *
     * @param string $tab      Tab key.
     * @param array  $settings Key => value map (raw values — will be sanitized).
     * @return void
     * @throws InvalidArgumentException If settings are empty.
     */
    public function saveTabSettings(string $tab, array $settings): void
    {
        if (empty($settings)) {
            throw new InvalidArgumentException(__('No settings provided.', 'wp-statistics'));
        }

        $allowedKeys = $this->getAllowedKeysForTab($tab);

        foreach ($settings as $key => $value) {
            $sanitizedKey = sanitize_key($key);

            if (!in_array($sanitizedKey, $allowedKeys, true)) {
                continue;
            }

            $sanitizedValue = SettingsSanitizer::sanitize($sanitizedKey, $value);
            Option::updateValue($sanitizedKey, $sanitizedValue);
        }

        /**
         * Fires after settings are saved for a specific tab.
         *
         * @since 15.3.0
         *
         * @param string $tab      Tab key (e.g. 'general', 'privacy').
         * @param array  $settings The raw settings that were submitted.
         */
        do_action('wp_statistics_settings_saved', $tab, $settings);
    }

    /**
     * Save arbitrary settings (no tab scope).
     *
     * @param array $settings Key => value map (raw values — will be sanitized).
     * @return void
     * @throws InvalidArgumentException If settings are empty.
     */
    public function saveSettings(array $settings): void
    {
        if (empty($settings)) {
            throw new InvalidArgumentException(__('No settings provided.', 'wp-statistics'));
        }

        foreach ($settings as $key => $value) {
            $sanitizedKey   = sanitize_key($key);
            $sanitizedValue = SettingsSanitizer::sanitize($sanitizedKey, $value);
            Option::updateValue($sanitizedKey, $sanitizedValue);
        }

        /** This action is documented in SettingsService::saveTabSettings() */
        do_action('wp_statistics_settings_saved', 'all', $settings);
    }

    /**
     * Get available WordPress roles with translated names.
     *
     * @return array<int, array{slug: string, name: string}>
     */
    public static function getAvailableRoles(): array
    {
        global $wp_roles;

        if (!is_object($wp_roles) || !is_array($wp_roles->roles)) {
            return [];
        }

        $roles = [];
        foreach ($wp_roles->get_names() as $slug => $name) {
            $roles[] = [
                'slug' => $slug,
                'name' => translate_user_role($name),
            ];
        }

        return $roles;
    }

    /**
     * Get allowed setting keys for a tab.
     *
     * @param string $tab AJAX tab key.
     * @return string[]
     */
    public function getAllowedKeysForTab(string $tab): array
    {
        if ($this->allowedKeysByTab === null) {
            $provider               = new SettingsConfigProvider();
            $this->allowedKeysByTab = $provider->getAllowedKeysByTab();
        }

        return $this->allowedKeysByTab[$tab] ?? [];
    }

    /**
     * Get resolved defaults for a specific tab.
     *
     * Combines:
     * - Core defaults (Option::getDefaults), constrained by allowed keys.
     * - Field-level defaults from filtered settings config (extensions/premium).
     *
     * @param string $tab AJAX tab key.
     * @return array<string, mixed>
     */
    private function getDefaultsForTab(string $tab): array
    {
        if ($this->defaultsByTab === null) {
            $this->defaultsByTab = $this->buildDefaultsByTab();
        }

        return $this->defaultsByTab[$tab] ?? [];
    }

    /**
     * Build defaults map for all settings tabs.
     *
     * @return array<string, array<string, mixed>>
     */
    private function buildDefaultsByTab(): array
    {
        $provider         = new SettingsConfigProvider();
        $config           = $provider->getConfig();
        $coreDefaults     = Option::getDefaults();
        $allowedKeysByTab = $this->getAllowedKeysByTabMap();
        $defaultsByTab    = [];

        // Seed each settings tab with core defaults for allowed keys.
        foreach ($allowedKeysByTab as $tabKey => $keys) {
            if (!isset($defaultsByTab[$tabKey])) {
                $defaultsByTab[$tabKey] = [];
            }

            foreach ($keys as $key) {
                if (array_key_exists($key, $coreDefaults)) {
                    $defaultsByTab[$tabKey][$key] = $coreDefaults[$key];
                }
            }
        }

        // Overlay field-level defaults from filtered config (includes premium/third-party).
        foreach ($config['fields'] ?? [] as $path => $fields) {
            $parts = explode('/', (string) $path, 2);
            $tabId = $parts[0] ?? '';
            $tab   = $config['tabs'][$tabId] ?? null;

            if (!$tab || ($tab['area'] ?? null) !== 'settings') {
                continue;
            }

            $tabKey = $tab['tab_key'] ?? $tabId;
            if (!isset($defaultsByTab[$tabKey])) {
                $defaultsByTab[$tabKey] = [];
            }

            foreach ($fields as $field) {
                if (empty($field['setting_key']) || !array_key_exists('default', $field)) {
                    continue;
                }

                $defaultsByTab[$tabKey][$field['setting_key']] = $field['default'];
            }
        }

        return $defaultsByTab;
    }

    /**
     * Resolve a settings value using stored options and tab defaults.
     *
     * - Missing keys fallback to default if available, else false (legacy behavior).
     * - Legacy normalization: if stored value is false but default is non-boolean,
     *   return the typed default to avoid leaking sentinel false into text/array fields.
     *
     * @param string               $key      Setting key.
     * @param array<string, mixed> $options  Stored options.
     * @param array<string, mixed> $defaults Tab defaults.
     * @return mixed
     */
    private function resolveSettingValue(string $key, array $options, array $defaults)
    {
        $hasDefault = array_key_exists($key, $defaults);
        $default    = $hasDefault ? $defaults[$key] : null;

        if (array_key_exists($key, $options)) {
            $rawValue = $options[$key];
            $value    = apply_filters("wp_statistics_option_{$key}", $rawValue);

            if ($rawValue === false && $value === false && $hasDefault && !is_bool($default)) {
                return $default;
            }

            return $value;
        }

        return $hasDefault ? $default : false;
    }

    /**
     * Get or build the allowed keys map for all tabs.
     *
     * @return array<string, string[]>
     */
    private function getAllowedKeysByTabMap(): array
    {
        if ($this->allowedKeysByTab === null) {
            $provider               = new SettingsConfigProvider();
            $this->allowedKeysByTab = $provider->getAllowedKeysByTab();
        }

        return $this->allowedKeysByTab;
    }
}
