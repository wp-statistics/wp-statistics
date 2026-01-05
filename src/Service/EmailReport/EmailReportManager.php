<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\Option;
use WP_Statistics\Components\Template;
use WP_Statistics\Service\Messaging\Provider\MailProvider;
use WP_Statistics\Service\Messaging\MessagingService;

/**
 * Email Report Manager
 *
 * Simplified service for email reporting.
 * Uses MailProvider for sending and a single unified template.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportManager
{
    /**
     * Email report logger instance
     *
     * @var EmailReportLogger
     */
    private $logger;

    /**
     * Primary brand color
     *
     * @var string
     */
    private $primaryColor = '#404BF2';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->logger = new EmailReportLogger();
    }

    /**
     * Get email subject based on period
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return string
     */
    public function getSubject($period)
    {
        $siteName = get_bloginfo('name');
        $periodLabels = [
            'daily'    => __('Daily', 'wp-statistics'),
            'weekly'   => __('Weekly', 'wp-statistics'),
            'biweekly' => __('Bi-Weekly', 'wp-statistics'),
            'monthly'  => __('Monthly', 'wp-statistics'),
        ];

        $periodLabel = isset($periodLabels[$period]) ? $periodLabels[$period] : $periodLabels['weekly'];

        $subject = sprintf(
            /* translators: 1: Period label (Daily/Weekly/etc.), 2: Site name */
            __('%1$s Statistics Report - %2$s', 'wp-statistics'),
            $periodLabel,
            $siteName
        );

        /**
         * Filter the email report subject.
         *
         * @since 15.0.0
         * @param string $subject The email subject.
         * @param string $period  The report period.
         */
        return apply_filters('wp_statistics_email_report_subject', $subject, $period);
    }

    /**
     * Get report data for template
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return array
     */
    public function getData($period)
    {
        $dataProvider = new EmailReportDataProvider($period);
        return $dataProvider->toArray();
    }

    /**
     * Get the email template path
     *
     * @return string
     */
    public function getTemplatePath()
    {
        $templatePath = WP_STATISTICS_DIR . 'src/Service/Messaging/Templates/Emails/report.php';

        /**
         * Filter the email report template path.
         *
         * @since 15.0.0
         * @param string $templatePath The template file path.
         */
        return apply_filters('wp_statistics_email_report_template', $templatePath);
    }

    /**
     * Render email HTML
     *
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return string
     */
    public function render($period = 'weekly')
    {
        $data = $this->getData($period);

        // Add template-specific variables
        $data['primary_color'] = $this->primaryColor;
        $data['is_rtl']        = is_rtl();

        /**
         * Filter the email report template variables.
         *
         * @since 15.0.0
         * @param array  $data   Template variables.
         * @param string $period The report period.
         */
        $data = apply_filters('wp_statistics_email_report_template_vars', $data, $period);

        // Render template
        $templatePath = $this->getTemplatePath();

        ob_start();
        extract($data);
        include $templatePath;
        $html = ob_get_clean();

        /**
         * Filter the rendered email report HTML.
         *
         * @since 15.0.0
         * @param string $html   The rendered HTML.
         * @param string $period The report period.
         * @param array  $data   The template data.
         */
        return apply_filters('wp_statistics_email_report_html', $html, $period, $data);
    }

    /**
     * Send email report
     *
     * @param array  $recipients Email addresses
     * @param string $period     Period type (daily, weekly, biweekly, monthly)
     * @return bool
     */
    public function send($recipients, $period = 'weekly')
    {
        if (empty($recipients)) {
            return false;
        }

        /**
         * Action fired before sending email report.
         *
         * @since 15.0.0
         * @param array  $recipients The email recipients.
         * @param string $period     The report period.
         */
        do_action('wp_statistics_email_report_before_send', $recipients, $period);

        $html    = $this->render($period);
        $subject = $this->getSubject($period);

        try {
            $mailProvider = MessagingService::make(MailProvider::class)->provider();

            $result = $mailProvider
                ->setTo($recipients)
                ->setSubject($subject)
                ->setBody($html)
                ->sendAsHtml(true)
                ->send();

            /**
             * Action fired after sending email report.
             *
             * @since 15.0.0
             * @param bool   $result     Whether the send was successful.
             * @param array  $recipients The email recipients.
             * @param string $period     The report period.
             */
            do_action('wp_statistics_email_report_after_send', $result, $recipients, $period);

            return $result;

        } catch (\Exception $e) {
            /**
             * Action fired after sending email report (on failure).
             *
             * @since 15.0.0
             * @param bool   $result     Whether the send was successful (false).
             * @param array  $recipients The email recipients.
             * @param string $period     The report period.
             */
            do_action('wp_statistics_email_report_after_send', false, $recipients, $period);

            return false;
        }
    }

    /**
     * Send test email to single recipient
     *
     * @param string $email  Email address
     * @param string $period Period type (daily, weekly, biweekly, monthly)
     * @return bool
     */
    public function sendTest($email, $period = 'weekly')
    {
        if (!is_email($email)) {
            return false;
        }

        return $this->send([$email], $period);
    }

    /**
     * Get logger instance
     *
     * @return EmailReportLogger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * Set primary color
     *
     * @param string $color Hex color code
     * @return void
     */
    public function setPrimaryColor($color)
    {
        if (preg_match('/^#[a-fA-F0-9]{6}$/', $color)) {
            $this->primaryColor = $color;
        }
    }

    /**
     * Get primary color
     *
     * @return string
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }
}
