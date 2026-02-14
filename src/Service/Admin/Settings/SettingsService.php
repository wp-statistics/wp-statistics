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
     * Get settings for a specific tab.
     *
     * @param string $tab Tab key (e.g. 'general', 'privacy').
     * @return array Key => value map.
     */
    public function getTabSettings(string $tab): array
    {
        $keys     = $this->getAllowedKeysForTab($tab);
        $defaults = Option::getDefaults();
        $options  = Option::get(); // Single load — avoids per-key get_option() overhead
        $settings = [];

        foreach ($keys as $key) {
            $default = array_key_exists($key, $defaults) ? $defaults[$key] : null;

            if (array_key_exists($key, $options)) {
                $settings[$key] = apply_filters("wp_statistics_option_{$key}", $options[$key]);
            } else {
                $settings[$key] = $default !== null ? $default : false;
            }
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
        $defaults = Option::getDefaults();
        $options  = Option::get(); // Single load for all tabs

        $settings = [];
        foreach ($tabs as $tab) {
            $keys    = $this->getAllowedKeysForTab($tab);
            $tabData = [];

            foreach ($keys as $key) {
                $default = array_key_exists($key, $defaults) ? $defaults[$key] : null;

                if (array_key_exists($key, $options)) {
                    $tabData[$key] = apply_filters("wp_statistics_option_{$key}", $options[$key]);
                } else {
                    $tabData[$key] = $default !== null ? $default : false;
                }
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
}
