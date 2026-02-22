<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Messaging\MessagingHelper;
use WP_Statistics\Service\Messaging\Provider\MailProvider;

/**
 * Orchestrates the email report flow: gather data, render HTML, send email.
 *
 * @since 15.0.0
 */
class EmailReportManager
{
    /**
     * Send the scheduled email report.
     *
     * @return bool True on success.
     */
    public function sendReport(): bool
    {
        $frequency = Option::getValue('email_reports_frequency', 'weekly');

        $dataProvider = new EmailReportDataProvider();
        $data         = $dataProvider->gather($frequency);

        /**
         * Filter email report color palette.
         *
         * @since 15.0.0
         * @param array $colors Color palette array.
         */
        $renderer = new EmailReportRenderer();
        $colors   = apply_filters('wp_statistics_email_report_colors', $renderer->getDefaultColors());
        $content  = $renderer->render($data, $colors);

        if (trim(wp_strip_all_tags($content)) === '') {
            return false;
        }

        $recipients = $this->getRecipients();

        if (empty($recipients)) {
            return false;
        }

        $dashboardUrl = admin_url('admin.php?page=wp-statistics');
        $settingsUrl  = admin_url('admin.php?page=wp-statistics#/settings/notifications');

        /**
         * Filter custom logo URL for the email report.
         *
         * @since 15.0.0
         * @param string $logoUrl Logo URL or empty for default.
         */
        $customLogo = apply_filters('wp_statistics_email_report_logo', '');

        /**
         * Filter the "From" name for email reports.
         *
         * @since 15.0.0
         * @param string $fromName From name or empty for default.
         */
        $fromName = apply_filters('wp_statistics_email_report_from_name', '');

        /**
         * Filter the reply-to email address for email reports.
         *
         * @since 15.0.0
         * @param string $replyTo Reply-to email address or empty.
         */
        $replyTo = apply_filters('wp_statistics_email_report_reply_to', '');

        /**
         * Filter the footer text for email reports.
         *
         * @since 15.0.0
         * @param string $footerText Footer text or empty.
         */
        $footerText = apply_filters('wp_statistics_email_report_footer_text', '');

        $templateVars = [
            'content'       => $content,
            'primary_color' => $colors['primary_color'] ?? '#1e40af',
            'report_title'  => $data['report_title'],
            'report_period' => $data['report_period'],
            'dashboard_url' => $dashboardUrl,
            'settings_url'  => $settingsUrl,
        ];

        if (!empty($customLogo)) {
            $templateVars['logo_image'] = $customLogo;
        }

        if (!empty($footerText)) {
            $templateVars['footer_text'] = $footerText;
        }

        $mail = new MailProvider();
        $mail->init()
            ->setTo($recipients)
            ->setSubject($data['report_title'] . ' — ' . get_bloginfo('name'))
            ->setTemplate(true, $templateVars);

        if (!empty($fromName)) {
            $adminEmail = get_option('admin_email');
            $mail->setFrom("{$fromName} <{$adminEmail}>");
        }

        if (!empty($replyTo) && is_email($replyTo)) {
            $mail->setHeaders(["Reply-To: {$replyTo}"]);
        }

        /**
         * Filter BCC setting for email reports.
         *
         * @since 15.0.0
         * @param bool $useBcc Whether to send as BCC.
         */
        $useBcc = apply_filters('wp_statistics_email_report_bcc', false);

        if ($useBcc && count($recipients) > 1) {
            $primaryRecipient = array_shift($recipients);
            $mail->setTo([$primaryRecipient]);
            $mail->setBcc($recipients);
        }

        return $mail->send();
    }

    /**
     * Get recipient email addresses.
     *
     * @return string[]
     */
    private function getRecipients(): array
    {
        $email = MessagingHelper::getEmailNotification();

        $recipients = !empty($email) ? [$email] : [];

        /**
         * Filter email report recipients.
         *
         * @since 15.0.0
         * @param string[] $recipients List of email addresses.
         */
        return apply_filters('wp_statistics_email_report_recipients', $recipients);
    }
}
