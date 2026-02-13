<?php

namespace WP_Statistics\Service\Admin\Settings;

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
     * Build and return the full settings config.
     *
     * @return array{tabs: array, cards: array, fields: array}
     */
    public function getConfig(): array
    {
        $tabs = apply_filters('wp_statistics_settings_tabs', $this->getCoreTabs());

        $config = ['tabs' => $tabs, 'cards' => [], 'fields' => []];

        foreach ($tabs as $tabId => $tab) {
            // Component-based tabs render entirely in React — no cards/fields from PHP.
            if (!empty($tab['component'])) {
                continue;
            }

            $cards = apply_filters('wp_statistics_settings_cards', $this->getCoreCards($tabId), $tabId);
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
                $config['fields']["{$tabId}/{$cardId}"] = $fields;
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

    // ------------------------------------------------------------------
    // Core tab definitions
    // ------------------------------------------------------------------

    private function getCoreTabs(): array
    {
        return [
            // ── Settings tabs ────────────────────────────────────────
            'general' => [
                'area'             => 'settings',
                'label'            => __('General', 'wp-statistics'),
                'icon'             => 'settings',
                'order'            => 10,
                'save_description' => __('General settings have been updated.', 'wp-statistics'),
            ],
            'display' => [
                'area'             => 'settings',
                'label'            => __('Display', 'wp-statistics'),
                'icon'             => 'monitor',
                'order'            => 20,
                'save_description' => __('Display settings have been updated.', 'wp-statistics'),
            ],
            'privacy' => [
                'area'             => 'settings',
                'label'            => __('Privacy', 'wp-statistics'),
                'icon'             => 'shield',
                'order'            => 30,
                'save_description' => __('Privacy settings have been updated.', 'wp-statistics'),
            ],
            'notifications' => [
                'area'             => 'settings',
                'label'            => __('Notifications', 'wp-statistics'),
                'icon'             => 'bell',
                'order'            => 40,
                'save_description' => __('Notification settings have been updated.', 'wp-statistics'),
                'component'        => 'NotificationSettings',
            ],
            'exclusions' => [
                'area'             => 'settings',
                'label'            => __('Exclusions', 'wp-statistics'),
                'icon'             => 'ban',
                'order'            => 50,
                'save_description' => __('Exclusion settings have been updated.', 'wp-statistics'),
                'component'        => 'ExclusionSettings',
            ],
            'access' => [
                'area'             => 'settings',
                'label'            => __('Access', 'wp-statistics'),
                'icon'             => 'users',
                'order'            => 60,
                'save_description' => __('Access settings have been updated.', 'wp-statistics'),
                'component'        => 'AccessSettings',
            ],
            'data-management' => [
                'area'             => 'settings',
                'label'            => __('Data Management', 'wp-statistics'),
                'icon'             => 'database',
                'order'            => 70,
                'save_description' => __('Data management settings have been updated.', 'wp-statistics'),
                'tab_key'          => 'data',
                'component'        => 'DataManagementSettings',
            ],
            'advanced' => [
                'area'             => 'settings',
                'label'            => __('Advanced', 'wp-statistics'),
                'icon'             => 'wrench',
                'order'            => 80,
                'save_description' => __('Advanced settings have been updated.', 'wp-statistics'),
                'component'        => 'AdvancedSettings',
            ],

            // ── Tools tabs ───────────────────────────────────────────
            'system-info' => [
                'area'      => 'tools',
                'label'     => __('System Info', 'wp-statistics'),
                'icon'      => 'info',
                'order'     => 10,
                'component' => 'SystemInfoPage',
            ],
            'diagnostics' => [
                'area'      => 'tools',
                'label'     => __('Diagnostics', 'wp-statistics'),
                'icon'      => 'stethoscope',
                'order'     => 20,
                'component' => 'DiagnosticsPage',
            ],
            'scheduled-tasks' => [
                'area'      => 'tools',
                'label'     => __('Scheduled Tasks', 'wp-statistics'),
                'icon'      => 'clock',
                'order'     => 30,
                'component' => 'ScheduledTasksPage',
            ],
            'background-jobs' => [
                'area'      => 'tools',
                'label'     => __('Background Jobs', 'wp-statistics'),
                'icon'      => 'activity',
                'order'     => 40,
                'component' => 'BackgroundJobsPage',
            ],
            'import-export' => [
                'area'      => 'tools',
                'label'     => __('Import / Export', 'wp-statistics'),
                'icon'      => 'upload',
                'order'     => 50,
                'component' => 'ImportExportPage',
            ],
            'backups' => [
                'area'      => 'tools',
                'label'     => __('Backups', 'wp-statistics'),
                'icon'      => 'database',
                'order'     => 60,
                'component' => 'BackupsPage',
            ],
        ];
    }

    // ------------------------------------------------------------------
    // Core card definitions (per tab)
    // ------------------------------------------------------------------

    private function getCoreCards(string $tabId): array
    {
        switch ($tabId) {
            case 'general':
                return [
                    'tracking' => [
                        'title'       => __('Tracking Options', 'wp-statistics'),
                        'description' => __('Configure what data WP Statistics collects from your visitors.', 'wp-statistics'),
                        'order'       => 10,
                    ],
                    'tracker-config' => [
                        'title'       => __('Tracker Configuration', 'wp-statistics'),
                        'description' => __('Configure how the tracking script works on your site.', 'wp-statistics'),
                        'order'       => 20,
                    ],
                ];

            case 'display':
                return [
                    'admin-interface' => [
                        'title'       => __('Admin Interface', 'wp-statistics'),
                        'description' => __('Configure how WP Statistics appears in the WordPress admin area.', 'wp-statistics'),
                        'order'       => 10,
                    ],
                    'frontend-display' => [
                        'title'       => __('Frontend Display', 'wp-statistics'),
                        'description' => __('Configure how statistics appear on your website frontend.', 'wp-statistics'),
                        'order'       => 20,
                    ],
                ];

            case 'privacy':
                return [
                    'data-protection' => [
                        'title'       => __('Data Protection', 'wp-statistics'),
                        'description' => __('Configure how visitor IP addresses are stored and processed.', 'wp-statistics'),
                        'order'       => 10,
                    ],
                    'user-preferences' => [
                        'title'       => __('User Preferences', 'wp-statistics'),
                        'description' => __('Configure consent integration and respect user privacy preferences.', 'wp-statistics'),
                        'order'       => 20,
                    ],
                    'privacy-audit' => [
                        'title'       => __('Privacy Audit', 'wp-statistics'),
                        'description' => __('Enable privacy monitoring and compliance tools.', 'wp-statistics'),
                        'order'       => 30,
                    ],
                ];

            default:
                return [];
        }
    }

    // ------------------------------------------------------------------
    // Core field definitions (per tab + card)
    // ------------------------------------------------------------------

    private function getCoreFields(string $tabId, string $cardId): array
    {
        $key    = "{$tabId}/{$cardId}";
        $fields = $this->getAllCoreFields();

        return $fields[$key] ?? [];
    }

    /**
     * All declarative field definitions, keyed by "tabId/cardId".
     */
    private function getAllCoreFields(): array
    {
        return [
            // ── General / Tracking ───────────────────────────────────
            'general/tracking' => [
                'visitors_log' => [
                    'type'        => 'toggle',
                    'setting_key' => 'visitors_log',
                    'label'       => __('Track Logged-In User Activity', 'wp-statistics'),
                    'description' => __('Tracks activities of logged-in users with their WordPress User IDs. If disabled, logged-in users are tracked anonymously.', 'wp-statistics'),
                    'default'     => false,
                    'order'       => 10,
                ],
            ],

            // ── General / Tracker Configuration ──────────────────────
            'general/tracker-config' => [
                'bypass_ad_blockers' => [
                    'type'        => 'toggle',
                    'setting_key' => 'bypass_ad_blockers',
                    'label'       => __('Bypass Ad Blockers', 'wp-statistics'),
                    'description' => __('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics'),
                    'default'     => false,
                    'order'       => 10,
                ],
            ],

            // ── Display / Admin Interface ────────────────────────────
            'display/admin-interface' => [
                'disable_editor' => [
                    'type'        => 'toggle',
                    'setting_key' => 'disable_editor',
                    'label'       => __('View Stats in Editor', 'wp-statistics'),
                    'description' => __('Show a summary of content view statistics in the post editor.', 'wp-statistics'),
                    'default'     => false,
                    'inverted'    => true,
                    'order'       => 10,
                ],
                'disable_column' => [
                    'type'        => 'toggle',
                    'setting_key' => 'disable_column',
                    'label'       => __('Stats Column in Content List', 'wp-statistics'),
                    'description' => __('Display the statistics column in the content list menus, showing page view or visitor counts.', 'wp-statistics'),
                    'default'     => false,
                    'inverted'    => true,
                    'order'       => 20,
                ],
                'enable_user_column' => [
                    'type'        => 'toggle',
                    'setting_key' => 'enable_user_column',
                    'label'       => __('Views Column in User List', 'wp-statistics'),
                    'description' => __('Display the "Views" column in the admin user list. Requires "Track Logged-In User Activity" to be enabled.', 'wp-statistics'),
                    'default'     => false,
                    'order'       => 30,
                ],
                'display_notifications' => [
                    'type'        => 'toggle',
                    'setting_key' => 'display_notifications',
                    'label'       => __('WP Statistics Notifications', 'wp-statistics'),
                    'description' => __('Display important notifications such as new version releases, feature updates, and news.', 'wp-statistics'),
                    'default'     => true,
                    'order'       => 40,
                ],
                'hide_notices' => [
                    'type'        => 'toggle',
                    'setting_key' => 'hide_notices',
                    'label'       => __('Disable Admin Notices', 'wp-statistics'),
                    'description' => __('Hides configuration and optimization notices in the admin area. Critical database notices will still be shown.', 'wp-statistics'),
                    'default'     => false,
                    'order'       => 50,
                ],
                'menu_bar' => [
                    'type'        => 'toggle',
                    'setting_key' => 'menu_bar',
                    'label'       => __('Show Stats in Admin Bar', 'wp-statistics'),
                    'description' => __('Display a quick statistics summary in the WordPress admin bar.', 'wp-statistics'),
                    'default'     => true,
                    'order'       => 60,
                ],
            ],

            // ── Display / Frontend Display ───────────────────────────
            'display/frontend-display' => [
                'show_hits' => [
                    'type'        => 'toggle',
                    'setting_key' => 'show_hits',
                    'label'       => __('Views in Single Contents', 'wp-statistics'),
                    'description' => __("Shows the view count on the content's page for visitor insight.", 'wp-statistics'),
                    'default'     => false,
                    'order'       => 10,
                ],
                'display_hits_position' => [
                    'type'         => 'select',
                    'setting_key'  => 'display_hits_position',
                    'label'        => __('Display Position', 'wp-statistics'),
                    'description'  => __('Choose the position to show views on your content pages.', 'wp-statistics'),
                    'default'      => 'none',
                    'layout'       => 'stacked',
                    'nested'       => true,
                    'visible_when' => [
                        'show_hits' => true,
                    ],
                    'options' => [
                        ['value' => 'none', 'label' => __('Please select', 'wp-statistics')],
                        ['value' => 'before_content', 'label' => __('Before Content', 'wp-statistics')],
                        ['value' => 'after_content', 'label' => __('After Content', 'wp-statistics')],
                    ],
                    'order' => 20,
                ],
            ],

            // ── Privacy / Data Protection ────────────────────────────
            'privacy/data-protection' => [
                'store_ip' => [
                    'type'        => 'toggle',
                    'setting_key' => 'store_ip',
                    'label'       => __('Store IP Addresses', 'wp-statistics'),
                    'description' => __('Record full visitor IP addresses in the database. When disabled, only anonymous hashes are stored.', 'wp-statistics'),
                    'default'     => false,
                    'order'       => 10,
                ],
                'hash_rotation_interval' => [
                    'type'        => 'select',
                    'setting_key' => 'hash_rotation_interval',
                    'label'       => __('Hash Rotation Interval', 'wp-statistics'),
                    'description' => __('How often the salt used for visitor hashing rotates. Shorter intervals improve privacy but reduce returning-visitor detection accuracy.', 'wp-statistics'),
                    'default'     => 'daily',
                    'options'     => [
                        ['value' => 'daily', 'label' => __('Daily', 'wp-statistics')],
                        ['value' => 'weekly', 'label' => __('Weekly', 'wp-statistics')],
                        ['value' => 'monthly', 'label' => __('Monthly', 'wp-statistics')],
                        ['value' => 'disabled', 'label' => __('Disabled', 'wp-statistics')],
                    ],
                    'order' => 20,
                ],
                'hash_rotation_warning' => [
                    'type'         => 'notice',
                    'notice_type'  => 'warning',
                    'message'      => __('Disabling hash rotation means the same visitor will always produce the same hash. This improves returning-visitor detection but reduces privacy protection.', 'wp-statistics'),
                    'visible_when' => [
                        'hash_rotation_interval' => 'disabled',
                    ],
                    'order' => 25,
                ],
            ],

            // ── Privacy / User Preferences ───────────────────────────
            'privacy/user-preferences' => [
                'consent_integration' => [
                    'type'        => 'select',
                    'setting_key' => 'consent_integration',
                    'label'       => __('Consent Plugin Integration', 'wp-statistics'),
                    'description' => __('Integrate with supported consent management plugins.', 'wp-statistics'),
                    'default'     => 'none',
                    'options'     => [
                        ['value' => 'none', 'label' => __('None', 'wp-statistics')],
                        ['value' => 'wp_consent_api', 'label' => __('Via WP Consent API', 'wp-statistics')],
                        ['value' => 'complianz', 'label' => __('Complianz', 'wp-statistics')],
                        ['value' => 'cookieyes', 'label' => __('CookieYes', 'wp-statistics')],
                        ['value' => 'real_cookie_banner', 'label' => __('Real Cookie Banner', 'wp-statistics')],
                        ['value' => 'borlabs_cookie', 'label' => __('Borlabs Cookie', 'wp-statistics')],
                    ],
                    'order' => 10,
                ],
                'consent_level_integration' => [
                    'type'         => 'select',
                    'setting_key'  => 'consent_level_integration',
                    'label'        => __('Consent Category', 'wp-statistics'),
                    'description'  => __('Select the consent category WP Statistics should track.', 'wp-statistics'),
                    'default'      => 'functional',
                    'nested'       => true,
                    'visible_when' => [
                        'consent_integration' => 'wp_consent_api',
                    ],
                    'options' => [
                        ['value' => 'functional', 'label' => __('Functional', 'wp-statistics')],
                        ['value' => 'statistics-anonymous', 'label' => __('Statistics-Anonymous', 'wp-statistics')],
                        ['value' => 'statistics', 'label' => __('Statistics', 'wp-statistics')],
                        ['value' => 'marketing', 'label' => __('Marketing', 'wp-statistics')],
                    ],
                    'order' => 20,
                ],
                'anonymous_tracking' => [
                    'type'         => 'toggle',
                    'setting_key'  => 'anonymous_tracking',
                    'label'        => __('Anonymous Tracking', 'wp-statistics'),
                    'description'  => __('Track all users anonymously without PII by default.', 'wp-statistics'),
                    'default'      => false,
                    'nested'       => true,
                    'visible_when' => [
                        'consent_integration' => ['!=', 'none'],
                    ],
                    'order' => 30,
                ],
            ],

            // ── Privacy / Privacy Audit ──────────────────────────────
            'privacy/privacy-audit' => [
                'privacy_audit' => [
                    'type'        => 'toggle',
                    'setting_key' => 'privacy_audit',
                    'label'       => __('Enable Privacy Audit', 'wp-statistics'),
                    'description' => __('Show privacy indicators on settings that affect user privacy.', 'wp-statistics'),
                    'default'     => true,
                    'order'       => 10,
                ],
            ],
        ];
    }
}
