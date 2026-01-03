<?php

namespace WP_Statistics\Service\EmailReport;

use WP_Statistics\Components\Event;
use WP_Statistics\Globals\Option;

/**
 * Email Report Scheduler
 *
 * Handles scheduling and triggering of automated email reports.
 *
 * @package WP_Statistics\Service\EmailReport
 * @since 15.0.0
 */
class EmailReportScheduler
{
    /**
     * Cron hook name for email reports
     */
    private const CRON_HOOK = 'wp_statistics_email_report';

    /**
     * Email report manager instance
     *
     * @var EmailReportManager
     */
    private EmailReportManager $manager;

    /**
     * Constructor
     *
     * @param EmailReportManager $manager Email report manager instance
     */
    public function __construct(EmailReportManager $manager)
    {
        $this->manager = $manager;
        $this->init();
    }

    /**
     * Initialize scheduler
     *
     * @return void
     */
    private function init(): void
    {
        // Register cron event handler
        add_action(self::CRON_HOOK, [$this, 'sendScheduledReport']);

        // Schedule cron event if email reports are enabled
        $this->maybeSchedule();
    }

    /**
     * Schedule cron event if not already scheduled
     *
     * @return void
     */
    private function maybeSchedule(): void
    {
        if (!$this->isEnabled()) {
            $this->unschedule();
            return;
        }

        if (wp_next_scheduled(self::CRON_HOOK)) {
            return;
        }

        $frequency = $this->getFrequency();
        $interval = $this->getIntervalName($frequency);

        Event::schedule(self::CRON_HOOK, time(), $interval, [$this, 'sendScheduledReport']);
    }

    /**
     * Unschedule cron event
     *
     * @return void
     */
    public function unschedule(): void
    {
        $timestamp = wp_next_scheduled(self::CRON_HOOK);
        if ($timestamp) {
            wp_unschedule_event($timestamp, self::CRON_HOOK);
        }
    }

    /**
     * Reschedule cron event with new frequency
     *
     * @param string $frequency New frequency
     * @return void
     */
    public function reschedule(string $frequency): void
    {
        $this->unschedule();

        if (!$this->isEnabled()) {
            return;
        }

        $interval = $this->getIntervalName($frequency);
        Event::schedule(self::CRON_HOOK, time(), $interval, [$this, 'sendScheduledReport']);
    }

    /**
     * Send scheduled email report
     *
     * @return void
     */
    public function sendScheduledReport(): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            return;
        }

        $frequency = $this->getFrequency();
        $this->manager->send($recipients, $frequency);
    }

    /**
     * Check if email reports are enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return (bool) Option::get('time_report', false);
    }

    /**
     * Get report frequency
     *
     * @return string
     */
    public function getFrequency(): string
    {
        $frequency = Option::get('time_report', 'weekly');

        $validFrequencies = ['daily', 'weekly', 'biweekly', 'monthly'];
        if (!in_array($frequency, $validFrequencies, true)) {
            return 'weekly';
        }

        return $frequency;
    }

    /**
     * Get email recipients
     *
     * @return array
     */
    public function getRecipients(): array
    {
        $emailList = Option::get('email_list', '');

        if (empty($emailList)) {
            // Default to admin email
            return [get_option('admin_email')];
        }

        // Parse comma-separated email list
        $emails = array_map('trim', explode(',', $emailList));
        $emails = array_filter($emails, 'is_email');

        return $emails;
    }

    /**
     * Get WordPress cron interval name from frequency
     *
     * @param string $frequency Frequency (daily, weekly, biweekly, monthly)
     * @return string WordPress cron interval name
     */
    private function getIntervalName(string $frequency): string
    {
        $intervals = [
            'daily' => 'daily',
            'weekly' => 'weekly',
            'biweekly' => 'wp_statistics_biweekly',
            'monthly' => 'wp_statistics_monthly',
        ];

        return $intervals[$frequency] ?? 'weekly';
    }

    /**
     * Register custom cron schedules
     *
     * @param array $schedules Existing schedules
     * @return array Modified schedules
     */
    public static function registerSchedules(array $schedules): array
    {
        $schedules['wp_statistics_biweekly'] = [
            'interval' => 2 * WEEK_IN_SECONDS,
            'display' => __('Every two weeks', 'wp-statistics'),
        ];

        $schedules['wp_statistics_monthly'] = [
            'interval' => 30 * DAY_IN_SECONDS,
            'display' => __('Once monthly', 'wp-statistics'),
        ];

        return $schedules;
    }
}
