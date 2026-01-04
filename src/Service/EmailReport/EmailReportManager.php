<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\Ajax;
use WP_Statistics\Components\Option;
use WP_Statistics\Service\EmailReport\Block\BlockRegistry;
use WP_Statistics\Service\EmailReport\Metric\MetricRegistry;

/**
 * Email Report Manager
 *
 * Main service for v15 email reporting system.
 * Handles template management, rendering, and delivery.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportManager
{
    /**
     * Option key for email template configuration
     */
    private const TEMPLATE_OPTION_KEY = 'email_report_template';

    /**
     * Block registry instance
     *
     * @var BlockRegistry
     */
    private BlockRegistry $blockRegistry;

    /**
     * Metric registry instance
     *
     * @var MetricRegistry
     */
    private MetricRegistry $metricRegistry;

    /**
     * Email renderer instance
     *
     * @var EmailReportRenderer
     */
    private EmailReportRenderer $renderer;

    /**
     * Email scheduler instance
     *
     * @var EmailReportScheduler
     */
    private EmailReportScheduler $scheduler;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->blockRegistry = new BlockRegistry();
        $this->metricRegistry = new MetricRegistry();
        $this->renderer = new EmailReportRenderer($this->blockRegistry, $this->metricRegistry);
        $this->scheduler = new EmailReportScheduler($this);

        $this->registerAjaxHandlers();
        $this->registerScheduleFilters();
    }

    /**
     * Register custom cron schedules
     *
     * @return void
     */
    private function registerScheduleFilters(): void
    {
        add_filter('cron_schedules', [EmailReportScheduler::class, 'registerSchedules']);
    }

    /**
     * Register AJAX handlers for email builder
     *
     * @return void
     */
    private function registerAjaxHandlers(): void
    {
        Ajax::register('email_get_template', [$this, 'ajaxGetTemplate']);
        Ajax::register('email_save_template', [$this, 'ajaxSaveTemplate']);
        Ajax::register('email_preview', [$this, 'ajaxGeneratePreview']);
        Ajax::register('email_send_test', [$this, 'ajaxSendTestEmail']);
        Ajax::register('email_get_blocks', [$this, 'ajaxGetAvailableBlocks']);
        Ajax::register('email_get_metrics', [$this, 'ajaxGetAvailableMetrics']);
    }

    /**
     * Get default email template configuration
     *
     * @return array
     */
    public function getDefaultTemplate(): array
    {
        return [
            'blocks' => [
                ['id' => 'header-1', 'type' => 'header', 'settings' => []],
                ['id' => 'metrics-1', 'type' => 'metrics', 'settings' => ['show' => ['visitors', 'views', 'sessions', 'referrals']]],
                ['id' => 'top-pages-1', 'type' => 'top-pages', 'settings' => ['limit' => 5]],
                ['id' => 'top-referrers-1', 'type' => 'top-referrers', 'settings' => ['limit' => 5]],
                ['id' => 'cta-1', 'type' => 'cta', 'settings' => []],
            ],
            'globalSettings' => [
                'primaryColor' => '#404BF2',
                'showLogo' => true,
                'logoUrl' => '',
                'siteTitle' => get_bloginfo('name'),
            ],
        ];
    }

    /**
     * Get current email template configuration
     *
     * @return array
     */
    public function getTemplate(): array
    {
        $template = Option::get(self::TEMPLATE_OPTION_KEY);

        if (empty($template) || !is_array($template)) {
            return $this->getDefaultTemplate();
        }

        return wp_parse_args($template, $this->getDefaultTemplate());
    }

    /**
     * Save email template configuration
     *
     * @param array $template Template configuration
     * @return bool
     */
    public function saveTemplate(array $template): bool
    {
        // Validate blocks
        if (!isset($template['blocks']) || !is_array($template['blocks'])) {
            return false;
        }

        // Sanitize and validate each block
        $sanitizedBlocks = [];
        foreach ($template['blocks'] as $block) {
            if (!isset($block['type']) || !$this->blockRegistry->has($block['type'])) {
                continue;
            }

            $sanitizedBlocks[] = [
                'id' => sanitize_key($block['id'] ?? uniqid($block['type'] . '-')),
                'type' => sanitize_key($block['type']),
                'settings' => $this->sanitizeBlockSettings($block['settings'] ?? []),
            ];
        }

        $template['blocks'] = $sanitizedBlocks;

        // Sanitize global settings
        if (isset($template['globalSettings'])) {
            $template['globalSettings'] = [
                'primaryColor' => sanitize_hex_color($template['globalSettings']['primaryColor'] ?? '#404BF2'),
                'showLogo' => (bool) ($template['globalSettings']['showLogo'] ?? true),
                'logoUrl' => esc_url_raw($template['globalSettings']['logoUrl'] ?? ''),
                'siteTitle' => sanitize_text_field($template['globalSettings']['siteTitle'] ?? get_bloginfo('name')),
            ];
        }

        return Option::update(self::TEMPLATE_OPTION_KEY, $template);
    }

    /**
     * Sanitize block settings
     *
     * @param array $settings Block settings
     * @return array
     */
    private function sanitizeBlockSettings(array $settings): array
    {
        $sanitized = [];

        foreach ($settings as $key => $value) {
            $key = sanitize_key($key);

            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } elseif (is_bool($value)) {
                $sanitized[$key] = $value;
            } elseif (is_numeric($value)) {
                $sanitized[$key] = intval($value);
            } else {
                $sanitized[$key] = sanitize_text_field($value);
            }
        }

        return $sanitized;
    }

    /**
     * Generate email HTML
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @param array|null $template Optional custom template
     * @return string
     */
    public function render(string $period = 'weekly', ?array $template = null): string
    {
        $template = $template ?? $this->getTemplate();
        return $this->renderer->render($template, $period);
    }

    /**
     * Send email report
     *
     * @param array $recipients Email addresses
     * @param string $period Period type
     * @param array|null $template Optional custom template
     * @return bool
     */
    public function send(array $recipients, string $period = 'weekly', ?array $template = null): bool
    {
        if (empty($recipients)) {
            return false;
        }

        $html = $this->render($period, $template);
        $subject = $this->getEmailSubject($period);

        $headers = [
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>',
        ];

        $sent = true;
        foreach ($recipients as $recipient) {
            $email = sanitize_email($recipient);
            if (!is_email($email)) {
                continue;
            }

            if (!wp_mail($email, $subject, $html, $headers)) {
                $sent = false;
            }
        }

        return $sent;
    }

    /**
     * Get email subject based on period
     *
     * @param string $period Period type
     * @return string
     */
    private function getEmailSubject(string $period): string
    {
        $siteName = get_bloginfo('name');
        $periodLabels = [
            'daily' => __('Daily', 'wp-statistics'),
            'weekly' => __('Weekly', 'wp-statistics'),
            'biweekly' => __('Bi-Weekly', 'wp-statistics'),
            'monthly' => __('Monthly', 'wp-statistics'),
        ];

        $periodLabel = $periodLabels[$period] ?? $periodLabels['weekly'];

        return sprintf(
            /* translators: 1: Period label (Daily/Weekly/etc.), 2: Site name */
            __('%1$s Statistics Report - %2$s', 'wp-statistics'),
            $periodLabel,
            $siteName
        );
    }

    /**
     * Verify nonce for AJAX requests
     *
     * @return bool
     */
    private function verifyNonce(): bool
    {
        $nonce = $_POST['_wpnonce'] ?? $_REQUEST['nonce'] ?? '';
        return wp_verify_nonce($nonce, 'wp_statistics_dashboard_nonce');
    }

    /**
     * AJAX: Get email template
     *
     * @return void
     */
    public function ajaxGetTemplate(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-statistics')]);
        }

        wp_send_json_success([
            'template' => $this->getTemplate(),
            'availableBlocks' => $this->blockRegistry->getAvailableBlocks(),
            'availableMetrics' => $this->metricRegistry->getAvailableMetrics(),
        ]);
    }

    /**
     * AJAX: Save email template
     *
     * @return void
     */
    public function ajaxSaveTemplate(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-statistics')]);
        }

        $template = isset($_POST['template']) ? json_decode(stripslashes($_POST['template']), true) : null;

        if (!$template) {
            wp_send_json_error(['message' => __('Invalid template data.', 'wp-statistics')]);
        }

        if ($this->saveTemplate($template)) {
            wp_send_json_success(['message' => __('Template saved successfully.', 'wp-statistics')]);
        } else {
            wp_send_json_error(['message' => __('Failed to save template.', 'wp-statistics')]);
        }
    }

    /**
     * AJAX: Generate email preview
     *
     * @return void
     */
    public function ajaxGeneratePreview(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-statistics')]);
        }

        $template = isset($_POST['template']) ? json_decode(stripslashes($_POST['template']), true) : null;
        $period = sanitize_key($_POST['period'] ?? 'weekly');

        $html = $this->render($period, $template);

        wp_send_json_success(['html' => $html]);
    }

    /**
     * AJAX: Send test email
     *
     * @return void
     */
    public function ajaxSendTestEmail(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permission denied.', 'wp-statistics')]);
        }

        // Default to current user's email if no email specified
        $email = sanitize_email($_POST['email'] ?? '');
        if (empty($email)) {
            $currentUser = wp_get_current_user();
            $email = $currentUser->user_email;
        }

        $template = isset($_POST['template']) ? json_decode(stripslashes($_POST['template']), true) : null;
        $period = sanitize_key($_POST['period'] ?? 'weekly');

        if (!is_email($email)) {
            wp_send_json_error(['message' => __('Invalid email address.', 'wp-statistics')]);
        }

        if ($this->send([$email], $period, $template)) {
            wp_send_json_success([
                'message' => __('Test email sent successfully.', 'wp-statistics'),
                'email' => $email,
            ]);
        } else {
            wp_send_json_error(['message' => __('Failed to send test email.', 'wp-statistics')]);
        }
    }

    /**
     * AJAX: Get available blocks
     *
     * @return void
     */
    public function ajaxGetAvailableBlocks(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        wp_send_json_success(['blocks' => $this->blockRegistry->getAvailableBlocks()]);
    }

    /**
     * AJAX: Get available metrics
     *
     * @return void
     */
    public function ajaxGetAvailableMetrics(): void
    {
        if (!$this->verifyNonce()) {
            wp_send_json_error(['message' => __('Invalid nonce.', 'wp-statistics')]);
        }

        wp_send_json_success(['metrics' => $this->metricRegistry->getAvailableMetrics()]);
    }

    /**
     * Get block registry
     *
     * @return BlockRegistry
     */
    public function getBlockRegistry(): BlockRegistry
    {
        return $this->blockRegistry;
    }

    /**
     * Get metric registry
     *
     * @return MetricRegistry
     */
    public function getMetricRegistry(): MetricRegistry
    {
        return $this->metricRegistry;
    }
}
