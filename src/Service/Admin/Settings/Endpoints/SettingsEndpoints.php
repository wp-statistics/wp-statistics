<?php

namespace WP_Statistics\Service\Admin\Settings\Endpoints;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Service\Admin\AccessControl\AccessLevel;
use WP_Statistics\Utils\Request;
use WP_Statistics\Components\Option;
use WP_Statistics\Utils\User;
use Exception;

/**
 * Settings AJAX Endpoints for the React SPA.
 *
 * Provides endpoints for:
 * - Getting settings values
 * - Saving settings values
 * - Email preview generation
 * - Test email sending
 *
 * Registered globally in ReactAppManager::initSettingsAjax().
 *
 * @since 15.0.0
 */
class SettingsEndpoints
{
    /**
     * Register AJAX handlers.
     *
     * @return void
     */
    public function register()
    {
        // Settings operations (admin only, not public)
        Ajax::register('settings_get', [$this, 'getSettings'], false);
        Ajax::register('settings_save', [$this, 'saveSettings'], false);
        Ajax::register('settings_get_tab', [$this, 'getTabSettings'], false);
        Ajax::register('settings_save_tab', [$this, 'saveTabSettings'], false);

        // Email operations (admin only, not public)
        Ajax::register('email_preview', [$this, 'generateEmailPreview'], false);
        Ajax::register('email_send_test', [$this, 'sendTestEmail'], false);
    }

    /**
     * Get all settings.
     *
     * @return void
     */
    public function getSettings()
    {
        try {
            $this->verifyRequest();

            $settings = $this->getAllSettings();

            wp_send_json_success([
                'settings' => $settings,
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'settings_error',
            ]);
        }
    }

    /**
     * Save settings.
     *
     * @return void
     */
    public function saveSettings()
    {
        try {
            $this->verifyRequest();

            // Get raw settings value (don't sanitize - it's JSON that we'll decode and sanitize per-key)
            $rawSettings = isset($_REQUEST['settings']) ? wp_unslash($_REQUEST['settings']) : '';

            // Decode JSON string (frontend sends JSON-encoded settings)
            $settings = is_string($rawSettings) ? json_decode($rawSettings, true) : $rawSettings;

            if (empty($settings) || !is_array($settings)) {
                throw new Exception(__('No settings provided.', 'wp-statistics'));
            }

            foreach ($settings as $key => $value) {
                $sanitizedKey = sanitize_key($key);

                // Handle different value types
                if (is_array($value)) {
                    $sanitizedValue = array_map('sanitize_text_field', $value);
                } elseif ($value === 'true' || $value === true) {
                    $sanitizedValue = true;
                } elseif ($value === 'false' || $value === false) {
                    $sanitizedValue = false;
                } elseif (is_numeric($value)) {
                    $sanitizedValue = intval($value);
                } else {
                    $sanitizedValue = sanitize_text_field($value);
                }

                Option::updateValue($sanitizedKey, $sanitizedValue);
            }

            wp_send_json_success([
                'message' => __('Settings saved successfully.', 'wp-statistics'),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'save_error',
            ]);
        }
    }

    /**
     * Get settings for a specific tab.
     *
     * @return void
     */
    public function getTabSettings()
    {
        try {
            $this->verifyRequest();

            $tab = sanitize_key(Request::get('tab', 'general'));

            $settings = $this->getSettingsForTab($tab);

            wp_send_json_success([
                'tab'      => $tab,
                'settings' => $settings,
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'settings_error',
            ]);
        }
    }

    /**
     * Save settings for a specific tab.
     *
     * @return void
     */
    public function saveTabSettings()
    {
        try {
            $this->verifyRequest();

            $tab = sanitize_key(Request::get('tab', 'general'));

            // Get raw settings value (don't sanitize - it's JSON that we'll decode and sanitize per-key)
            $rawSettings = isset($_REQUEST['settings']) ? wp_unslash($_REQUEST['settings']) : '';

            // Decode JSON string (frontend sends JSON-encoded settings)
            $settings = is_string($rawSettings) ? json_decode($rawSettings, true) : $rawSettings;

            if (empty($settings) || !is_array($settings)) {
                throw new Exception(__('No settings provided.', 'wp-statistics'));
            }

            // Get allowed keys for this tab
            $allowedKeys = $this->getAllowedKeysForTab($tab);

            foreach ($settings as $key => $value) {
                $sanitizedKey = sanitize_key($key);

                // Only save keys that are allowed for this tab
                if (!in_array($sanitizedKey, $allowedKeys, true)) {
                    continue;
                }

                // Validate access_levels specifically
                if ($sanitizedKey === 'access_levels' && is_array($value)) {
                    $sanitizedValue = $this->sanitizeAccessLevels($value);
                } elseif (is_array($value)) {
                    // Handle different value types
                    $sanitizedValue = array_map('sanitize_text_field', $value);
                } elseif ($value === 'true' || $value === true) {
                    $sanitizedValue = true;
                } elseif ($value === 'false' || $value === false) {
                    $sanitizedValue = false;
                } elseif (is_numeric($value)) {
                    $sanitizedValue = intval($value);
                } else {
                    $sanitizedValue = sanitize_text_field($value);
                }

                Option::updateValue($sanitizedKey, $sanitizedValue);
            }

            wp_send_json_success([
                'message' => __('Settings saved successfully.', 'wp-statistics'),
                'tab'     => $tab,
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'save_error',
            ]);
        }
    }

    /**
     * Generate email preview HTML.
     *
     * @return void
     */
    public function generateEmailPreview()
    {
        try {
            $this->verifyRequest();

            $template = Request::get('template', []);

            // TODO: Implement EmailReportRenderer to generate preview
            // For now, return a placeholder
            $html = $this->buildEmailPreviewHtml($template);

            wp_send_json_success([
                'html' => $html,
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'preview_error',
            ]);
        }
    }

    /**
     * Send a test email.
     *
     * @return void
     */
    public function sendTestEmail()
    {
        try {
            $this->verifyRequest();

            $email    = sanitize_email(Request::get('email', ''));
            $template = Request::get('template', []);

            if (empty($email) || !is_email($email)) {
                throw new Exception(__('Please provide a valid email address.', 'wp-statistics'));
            }

            // Generate email HTML
            $html    = $this->buildEmailPreviewHtml($template);
            $subject = sprintf(
                /* translators: %s: Site name */
                __('[%s] WP Statistics Test Report', 'wp-statistics'),
                get_bloginfo('name')
            );

            // Send email
            $sent = wp_mail($email, $subject, $html, [
                'Content-Type: text/html; charset=UTF-8',
            ]);

            if (!$sent) {
                throw new Exception(__('Failed to send test email. Please check your email configuration.', 'wp-statistics'));
            }

            wp_send_json_success([
                'message' => sprintf(
                    /* translators: %s: Email address */
                    __('Test email sent to %s', 'wp-statistics'),
                    $email
                ),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'email_error',
            ]);
        }
    }

    /**
     * Verify the AJAX request.
     *
     * @throws Exception If verification fails.
     * @return void
     */
    private function verifyRequest()
    {
        if (!Request::isFrom('ajax')) {
            throw new Exception(__('Invalid request.', 'wp-statistics'));
        }

        if (!User::hasAccess('manage')) {
            throw new Exception(__('You do not have permission to perform this action.', 'wp-statistics'));
        }

        if (!check_ajax_referer('wp_statistics_dashboard_nonce', 'wps_nonce', false)) {
            throw new Exception(__('Security check failed. Please refresh the page and try again.', 'wp-statistics'));
        }
    }

    /**
     * Get all settings values.
     *
     * @return array
     */
    private function getAllSettings()
    {
        $tabs = ['general', 'privacy', 'notifications', 'exclusions', 'advanced', 'display', 'access'];

        $settings = [];
        foreach ($tabs as $tab) {
            $settings[$tab] = $this->getSettingsForTab($tab);
        }

        return $settings;
    }

    /**
     * Get settings for a specific tab.
     *
     * @param string $tab Tab name.
     * @return array
     */
    private function getSettingsForTab($tab)
    {
        $keys     = $this->getAllowedKeysForTab($tab);
        $defaults = Option::getDefaults();
        $settings = [];

        foreach ($keys as $key) {
            $default        = array_key_exists($key, $defaults) ? $defaults[$key] : null;
            $settings[$key] = Option::getValue($key, $default);
        }

        // Include available roles so the UI can render them dynamically
        if ($tab === 'access' || $tab === 'exclusions') {
            $settings['_roles'] = $this->getAvailableRoles();
        }

        return $settings;
    }

    /**
     * Get available WordPress roles for access level assignment.
     *
     * @return array<int, array{slug: string, name: string}>
     */
    private function getAvailableRoles()
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
     * @param string $tab Tab name.
     * @return array
     */
    private function getAllowedKeysForTab($tab)
    {
        $tabKeys = [
            'general' => [
                // Tracking Options
                'useronline',
                'visitors_log',
                // Tracker Configuration
                'bypass_ad_blockers',
                // Legacy keys for backward compatibility
                'visits',
                'visitors',
                'pages',
            ],
            'privacy' => [
                // Data Protection
                'store_ip',
                'hash_rotation_interval',
                // Privacy Compliance
                'privacy_audit',
                // User Preferences
                'consent_integration',
                'consent_level_integration',
                'anonymous_tracking',
            ],
            'notifications' => [
                // Email Reports
                'time_report',
                'send_report',
                'email_list',
                // Email Content
                'content_report',
                'email_free_content_header',
                'email_free_content_footer',
                'show_privacy_issues_in_report',
            ],
            'exclusions' => array_merge(
                array_map(function ($role) {
                    return 'exclude_' . $role['slug'];
                }, $this->getAvailableRoles()),
                [
                    'exclude_anonymous_users',
                    // IP Exclusions
                    'exclude_ip',
                    // Robot Exclusions
                    'robotlist',
                    'robot_threshold',
                    // Geolocation Exclusions
                    'excluded_countries',
                    'included_countries',
                    // URL Exclusions
                    'exclude_loginpage',
                    'exclude_feeds',
                    'exclude_404s',
                    'excluded_urls',
                    // URL Query Parameters
                    'query_params_allow_list',
                    // Referrer Spam (deprecated but still supported)
                    'referrerspam',
                    'schedule_referrerspam',
                    // Host Exclusions
                    'excluded_hosts',
                    // General Exclusions
                    'record_exclusions',
                ]
            ),
            'advanced' => [
                // IP Detection Method
                'ip_method',
                'user_custom_header_ip_method',
                // Geolocation Settings
                'geoip_location_detection_method',
                'geoip_license_type',
                'geoip_license_key',
                'geoip_dbip_license_key_option',
                'schedule_geoip',
                // Content Analytics
                'word_count_analytics',
                // Data Aggregation
                'auto_aggregate_old_data',
                'schedule_dbmaint_days',
                // Anonymous Usage Data
                'share_anonymous_data',
                // Danger Zone
                'delete_on_uninstall',
                // Legacy keys (deprecated in v15)
                'auto_pop',
                'private_country_code',
            ],
            'display' => [
                // Admin Interface
                'disable_editor',
                'disable_column',
                'enable_user_column',
                'display_notifications',
                'hide_notices',
                'menu_bar',
                // Frontend Display
                'show_hits',
                'display_hits_position',
            ],
            'access' => [
                // Roles & Permissions (new tier-based system)
                'access_levels',
                // Legacy keys for backward compatibility
                'read_capability',
                'manage_capability',
            ],
            'data' => [
                // Data Retention Settings
                'data_retention_mode',
                'data_retention_days',
                // Legacy (for backward compatibility)
                'schedule_dbmaint',
                'schedule_dbmaint_days',
            ],
        ];

        return $tabKeys[$tab] ?? [];
    }

    /**
     * Sanitize and validate the access_levels setting.
     *
     * Only valid role slugs and access level values are preserved.
     * Administrator is always forced to 'manage'.
     *
     * @param array $levels Raw access levels from the request.
     * @return array<string, string> Sanitized role => level map.
     */
    private function sanitizeAccessLevels(array $levels): array
    {
        global $wp_roles;

        $sanitized   = [];
        $validLevels = AccessLevel::getAll();
        $validRoles  = is_object($wp_roles) && is_array($wp_roles->roles)
            ? array_keys($wp_roles->roles)
            : ['administrator'];

        foreach ($levels as $roleSlug => $level) {
            $roleSlug = sanitize_key($roleSlug);
            $level    = sanitize_text_field($level);

            if (!in_array($roleSlug, $validRoles, true)) {
                continue;
            }

            if (!in_array($level, $validLevels, true)) {
                continue;
            }

            // Administrator is always manage
            $sanitized[$roleSlug] = ($roleSlug === 'administrator') ? AccessLevel::MANAGE : $level;
        }

        // Ensure administrator is always present
        $sanitized['administrator'] = AccessLevel::MANAGE;

        return $sanitized;
    }

    /**
     * Build email preview HTML.
     *
     * TODO: This will be replaced by EmailReportRenderer.
     *
     * @param array $template Template configuration.
     * @return string
     */
    private function buildEmailPreviewHtml($template)
    {
        $siteName = get_bloginfo('name');
        $siteUrl  = home_url();

        // Default email preview
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WP Statistics Report</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 0; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
        .header { background: #404BF2; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 24px; }
        .content { padding: 24px; }
        .metric { display: inline-block; width: 45%; margin: 10px 2%; padding: 16px; background: #f8f9fa; border-radius: 8px; text-align: center; }
        .metric-value { font-size: 32px; font-weight: bold; color: #1a1a2e; }
        .metric-label { font-size: 14px; color: #666; margin-top: 4px; }
        .footer { padding: 16px; text-align: center; font-size: 12px; color: #999; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>' . esc_html($siteName) . '</h1>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">Weekly Statistics Report</p>
        </div>
        <div class="content">
            <div class="metric">
                <div class="metric-value">1,234</div>
                <div class="metric-label">Visitors</div>
            </div>
            <div class="metric">
                <div class="metric-value">5,678</div>
                <div class="metric-label">Page Views</div>
            </div>
            <p style="text-align: center; margin-top: 24px;">
                <a href="' . esc_url($siteUrl) . '/wp-admin/admin.php?page=wp-statistics" style="display: inline-block; background: #404BF2; color: #fff; padding: 12px 24px; border-radius: 6px; text-decoration: none;">View Full Dashboard</a>
            </p>
        </div>
        <div class="footer">
            This is a preview of your email report from WP Statistics.
        </div>
    </div>
</body>
</html>';

        return $html;
    }
}
