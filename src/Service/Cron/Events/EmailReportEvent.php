<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_STATISTICS\Option;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Cron\CronSchedules;

/**
 * Email Report Cron Event.
 *
 * Sends scheduled email/SMS reports.
 *
 * @since 15.0.0
 */
class EmailReportEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_report_hook';

    /**
     * @var string
     */
    protected $recurrence = 'daily'; // Will be overridden based on settings

    /**
     * Check if email report should be scheduled.
     *
     * @return bool
     */
    protected function shouldSchedule()
    {
        $timeReport = Option::get('time_report');
        return !empty($timeReport) && $timeReport !== '0';
    }

    /**
     * Schedule the event with dynamic recurrence.
     *
     * @return void
     */
    protected function schedule()
    {
        $timeReport = Option::get('time_report');
        $schedules  = CronSchedules::getSchedules();

        if (isset($schedules[$timeReport]['next_schedule'])) {
            $timestamp = $schedules[$timeReport]['next_schedule'];
            wp_schedule_event($timestamp, $timeReport, $this->hook);
        }
    }

    /**
     * Execute the email report sending.
     *
     * @return void
     */
    public function execute()
    {
        $emailContent = Option::get('content_report');
        $emailContent = do_shortcode($emailContent);
        $sendType     = Option::get('send_report');

        if ($sendType === 'mail') {
            $this->sendEmail($emailContent);
        } elseif ($sendType === 'sms') {
            $this->sendSMS($emailContent);
        }
    }

    /**
     * Send email report.
     *
     * @param string $content Report content.
     * @return void
     */
    private function sendEmail($content)
    {
        /**
         * Filter for email template content.
         *
         * @param string $content Email content.
         */
        $finalContent = apply_filters('wp_statistics_final_text_report_email', $content);

        /**
         * Filter to modify email subject.
         *
         * @param string $subject Email subject.
         */
        $subject = apply_filters('wp_statistics_report_email_subject', $this->getEmailSubject());

        /**
         * Filter for enable/disable sending email by template.
         *
         * @param bool $useTemplate Whether to use email template.
         */
        $useTemplate = apply_filters('wp_statistics_report_email_template', true);

        /**
         * Filter email receivers.
         *
         * @param array $receivers Email addresses.
         */
        $receivers = apply_filters('wp_statistics_report_email_receivers', Option::getEmailNotification());

        $result = Helper::send_mail($receivers, $subject, $finalContent, $useTemplate);

        /**
         * Action after sending report email.
         *
         * @param bool   $result    Send result.
         * @param array  $receivers Email receivers.
         * @param string $content   Email content.
         */
        do_action('wp_statistics_after_report_email', $result, $receivers, $finalContent);
    }

    /**
     * Send SMS report.
     *
     * @param string $content Report content.
     * @return void
     */
    private function sendSMS($content)
    {
        if (empty($content) || !function_exists('wp_sms_send') || !class_exists('\WP_SMS\Option')) {
            return;
        }

        $adminMobile = \WP_SMS\Option::getOption('admin_mobile_number');
        if (!empty($adminMobile)) {
            wp_sms_send($adminMobile, $content);
        }
    }

    /**
     * Generate email subject based on schedule.
     *
     * @return string Email subject.
     */
    private function getEmailSubject()
    {
        $schedule  = Option::get('time_report', false);
        $subject   = __('Your WP Statistics Report', 'wp-statistics');
        $schedules = CronSchedules::getSchedules();

        if ($schedule && isset($schedules[$schedule])) {
            $scheduleInfo = $schedules[$schedule];

            if (isset($scheduleInfo['start'], $scheduleInfo['end'])) {
                if ($scheduleInfo['start'] === $scheduleInfo['end']) {
                    $subject .= sprintf(' ' . __('for %s', 'wp-statistics'), $scheduleInfo['start']);
                } else {
                    $subject .= sprintf(' ' . __('for %s to %s', 'wp-statistics'), $scheduleInfo['start'], $scheduleInfo['end']);
                }
            }
        }

        return $subject;
    }
}
