<?php

namespace WP_Statistics\Service\Admin\Settings\Endpoints;

use WP_Statistics\Abstracts\BaseEndpoint;
use WP_Statistics\Service\Admin\Settings\SettingsConfigProvider;
use WP_Statistics\Service\Admin\Settings\SettingsService;
use WP_Statistics\Utils\Request;
use Exception;

/**
 * Settings AJAX Endpoints for the React SPA.
 *
 * Thin routing layer — all business logic delegated to:
 * - SettingsService (read/write settings)
 * - SettingsConfigProvider (page config)
 *
 * Uses a single `wp_statistics_settings` action with `sub_action` parameter.
 *
 * @since 15.0.0
 */
class SettingsEndpoints extends BaseEndpoint
{
    protected function getActionName(): string
    {
        return 'settings';
    }

    protected function getSubActions(): array
    {
        return [
            'get_config'    => 'getSettingsConfig',
            'get'           => 'getSettings',
            'save'          => 'saveSettings',
            'get_tab'       => 'getTabSettings',
            'save_tab'      => 'saveTabSettings',
        ];
    }

    protected function getErrorCode(): string
    {
        return 'settings_error';
    }

    /**
     * Return the full settings/tools page configuration (tabs, cards, fields).
     *
     * Includes all settings-area tab values so the React SPA never needs
     * a separate get_tab request for settings tabs. Tools tabs continue
     * to lazy-load their own data via the tools endpoint.
     */
    protected function getSettingsConfig(): void
    {
        $provider = new SettingsConfigProvider();
        $config   = $provider->getConfig();

        $service = new SettingsService();

        wp_send_json_success([
            'tabs'         => $config['tabs'],
            'cards'        => $config['cards'],
            'fields'       => $config['fields'],
            'all_settings' => $service->getAllSettings(),
        ]);
    }

    /**
     * Get all settings.
     */
    protected function getSettings(): void
    {
        $service = new SettingsService();

        wp_send_json_success([
            'settings' => $service->getAllSettings(),
        ]);
    }

    /**
     * Save settings (no tab scope).
     */
    protected function saveSettings(): void
    {
        $settings = $this->decodeSettingsFromRequest();
        $service  = new SettingsService();
        $service->saveSettings($settings);

        wp_send_json_success([
            'message' => __('Settings saved successfully.', 'wp-statistics'),
        ]);
    }

    /**
     * Get settings for a specific tab.
     */
    protected function getTabSettings(): void
    {
        $tab     = sanitize_key(Request::get('tab', 'general'));
        $service = new SettingsService();

        wp_send_json_success([
            'tab'      => $tab,
            'settings' => $service->getTabSettings($tab),
        ]);
    }

    /**
     * Save settings for a specific tab.
     */
    protected function saveTabSettings(): void
    {
        $tab      = sanitize_key(Request::get('tab', 'general'));
        $settings = $this->decodeSettingsFromRequest();
        $service  = new SettingsService();

        $service->saveTabSettings($tab, $settings);

        wp_send_json_success([
            'message' => __('Settings saved successfully.', 'wp-statistics'),
            'tab'     => $tab,
        ]);
    }

    /**
     * Decode JSON settings from the request body.
     *
     * @return array
     * @throws Exception If settings are empty or invalid.
     */
    protected function decodeSettingsFromRequest(): array
    {
        $rawSettings = isset($_REQUEST['settings']) ? wp_unslash($_REQUEST['settings']) : '';
        $settings    = is_string($rawSettings) ? json_decode($rawSettings, true) : $rawSettings;

        if (empty($settings) || !is_array($settings)) {
            throw new Exception(__('No settings provided.', 'wp-statistics'));
        }

        return $settings;
    }
}
