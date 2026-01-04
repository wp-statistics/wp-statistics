<?php

namespace WP_Statistics\Service\Admin\Settings;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Utils\Request;
use WP_STATISTICS\Option;
use WP_STATISTICS\User;
use Exception;

/**
 * Handles AJAX requests for the v15 Settings page.
 *
 * Provides endpoints for:
 * - Getting settings values
 * - Saving settings values
 * - Email preview generation
 * - Test email sending
 *
 * @since 15.0.0
 */
class SettingsAjaxHandler
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
        Ajax::register('email_save_template', [$this, 'saveEmailTemplate'], false);
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

            $settings = Request::get('settings', []);

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

                Option::update($sanitizedKey, $sanitizedValue);
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

            $tab      = sanitize_key(Request::get('tab', 'general'));
            $settings = Request::get('settings', []);

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

                Option::update($sanitizedKey, $sanitizedValue);
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
     * Save email template configuration.
     *
     * @return void
     */
    public function saveEmailTemplate()
    {
        try {
            $this->verifyRequest();

            $template = Request::get('template', []);

            if (empty($template)) {
                throw new Exception(__('No template provided.', 'wp-statistics'));
            }

            // Validate and sanitize template structure
            $sanitizedTemplate = $this->sanitizeEmailTemplate($template);

            Option::update('email_report_template', $sanitizedTemplate);

            wp_send_json_success([
                'message' => __('Email template saved successfully.', 'wp-statistics'),
            ]);
        } catch (Exception $e) {
            wp_send_json_error([
                'message' => $e->getMessage(),
                'code'    => 'template_error',
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

        if (!User::Access('manage')) {
            throw new Exception(__('You do not have permission to perform this action.', 'wp-statistics'));
        }

        if (!check_ajax_referer('wp_rest', 'wps_nonce', false)) {
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
        $tabs = ['general', 'privacy', 'notifications', 'exclusions', 'advanced'];

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
        $settings = [];

        foreach ($keys as $key) {
            $settings[$key] = Option::get($key);
        }

        return $settings;
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
                'visitors_log',
                'store_ua',
                'attribution_model',
                // Tracker Configuration
                'use_cache_plugin',
                'bypass_ad_blockers',
                // Legacy keys for backward compatibility
                'useronline',
                'visits',
                'visitors',
                'pages',
            ],
            'privacy' => [
                // IP Address Handling
                'anonymize_ips',
                'hash_ips',
                'ip_method',
                // Data Collection
                'do_not_track',
                'anonymous_tracking',
                'consent_level_integration',
                // Privacy Audit
                'privacy_audit',
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
                // Email Template (v15)
                'email_report_template',
            ],
            'exclusions' => [
                // IP/URL Exclusions
                'exclude_ip',
                'excluded_urls',
                'excluded_countries',
                'included_countries',
                // Bot Exclusions
                'robotlist',
                'robot_threshold',
                'record_exclusions',
                // Page Exclusions
                'exclude_404s',
                'exclude_feeds',
                'exclude_loginpage',
                // Role Exclusions (dynamic keys)
                'exclude_administrator',
                'exclude_editor',
                'exclude_author',
                'exclude_contributor',
                'exclude_subscriber',
                // Query params
                'query_params_allow_list',
            ],
            'advanced' => [
                // GeoIP Settings
                'geoip_license_type',
                'geoip_license_key',
                'geoip_dbip_license_key_option',
                'geoip_location_detection_method',
                'schedule_geoip',
                // Database Settings
                'schedule_dbmaint_days',
                'delete_data_on_uninstall',
                // Other
                'share_anonymous_data',
                'auto_pop',
                'private_country_code',
                // Legacy keys
                'bypass_ad_blockers',
                'use_cache_plugin',
            ],
        ];

        return $tabKeys[$tab] ?? [];
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

    /**
     * Sanitize email template configuration.
     *
     * @param array $template Raw template data.
     * @return array Sanitized template.
     */
    private function sanitizeEmailTemplate($template)
    {
        $sanitized = [
            'blocks'         => [],
            'globalSettings' => [],
        ];

        // Sanitize blocks
        if (!empty($template['blocks']) && is_array($template['blocks'])) {
            foreach ($template['blocks'] as $block) {
                if (!isset($block['type'])) {
                    continue;
                }

                $sanitizedBlock = [
                    'type'     => sanitize_key($block['type']),
                    'id'       => sanitize_key($block['id'] ?? uniqid('block_')),
                    'settings' => [],
                ];

                if (!empty($block['settings']) && is_array($block['settings'])) {
                    foreach ($block['settings'] as $key => $value) {
                        $sanitizedBlock['settings'][sanitize_key($key)] = is_array($value)
                            ? array_map('sanitize_text_field', $value)
                            : sanitize_text_field($value);
                    }
                }

                $sanitized['blocks'][] = $sanitizedBlock;
            }
        }

        // Sanitize global settings
        if (!empty($template['globalSettings']) && is_array($template['globalSettings'])) {
            foreach ($template['globalSettings'] as $key => $value) {
                $sanitizedKey = sanitize_key($key);

                if (is_bool($value)) {
                    $sanitized['globalSettings'][$sanitizedKey] = $value;
                } elseif (is_array($value)) {
                    $sanitized['globalSettings'][$sanitizedKey] = array_map('sanitize_text_field', $value);
                } else {
                    $sanitized['globalSettings'][$sanitizedKey] = sanitize_text_field($value);
                }
            }
        }

        return $sanitized;
    }
}
