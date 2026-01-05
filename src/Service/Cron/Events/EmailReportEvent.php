<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Cron\CronSchedules;
use WP_Statistics\Service\EmailReport\EmailReportManager;
use WP_Statistics\Service\EmailReport\EmailReportLogger;

/**
 * Email Report Cron Event.
 *
 * Handles scheduled email report delivery.
 *
 * @since 15.0.0
 */
class EmailReportEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_email_report';

    /**
     * @var string
     */
    protected $recurrence = 'weekly';

    /**
     * @var string
     */
    protected $description = 'Email Report';

    /**
     * @var EmailReportManager|null
     */
    private ?EmailReportManager $manager = null;

    /**
     * @var EmailReportLogger|null
     */
    private ?EmailReportLogger $logger = null;

    /**
     * Check if email reports should be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        $timeReport = Option::get('time_report', '0');
        return !empty($timeReport) && $timeReport !== '0';
    }

    /**
     * Get the recurrence interval based on settings.
     *
     * @return string
     */
    public function getRecurrence(): string
    {
        $frequency = Option::get('time_report', 'weekly');

        $validFrequencies = ['daily', 'weekly', 'biweekly', 'monthly'];
        if (!in_array($frequency, $validFrequencies, true)) {
            return 'weekly';
        }

        return $frequency;
    }

    /**
     * Get the next schedule time for email reports.
     *
     * @return int Timestamp based on frequency (8:00 AM).
     */
    protected function getNextScheduleTime(): int
    {
        $schedules = CronSchedules::getSchedules();
        $frequency = $this->getRecurrence();

        return $schedules[$frequency]['next_schedule'] ?? time();
    }

    /**
     * Schedule the event with dynamic recurrence.
     *
     * @return void
     */
    protected function schedule(): void
    {
        $timestamp  = $this->getNextScheduleTime();
        $recurrence = $this->getRecurrence();

        wp_schedule_event($timestamp, $recurrence, $this->hook);
    }

    /**
     * Execute the email report send.
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->shouldSchedule()) {
            return;
        }

        $recipients = $this->getRecipients();
        if (empty($recipients)) {
            $this->log(false, [], 'No recipients configured');
            return;
        }

        $frequency = $this->getRecurrence();
        $manager   = $this->getManager();

        try {
            $result = $manager->send($recipients, $frequency);
            $this->log($result, $recipients, $result ? null : 'Send failed');
        } catch (\Throwable $e) {
            $this->log(false, $recipients, $e->getMessage());

            // Log to WP Statistics error log
            if (defined('WP_DEBUG') && WP_DEBUG) {
                error_log('WP Statistics Email Report Error: ' . $e->getMessage());
            }
        }
    }

    /**
     * Get email recipients.
     *
     * @return array
     */
    private function getRecipients(): array
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
     * Get EmailReportManager instance.
     *
     * @return EmailReportManager
     */
    private function getManager(): EmailReportManager
    {
        if ($this->manager === null) {
            $this->manager = new EmailReportManager();
        }

        return $this->manager;
    }

    /**
     * Set EmailReportManager instance (for dependency injection).
     *
     * @param EmailReportManager $manager
     * @return void
     */
    public function setManager(EmailReportManager $manager): void
    {
        $this->manager = $manager;
    }

    /**
     * Get EmailReportLogger instance.
     *
     * @return EmailReportLogger
     */
    private function getLogger(): EmailReportLogger
    {
        if ($this->logger === null) {
            $this->logger = new EmailReportLogger();
        }

        return $this->logger;
    }

    /**
     * Set EmailReportLogger instance (for dependency injection).
     *
     * @param EmailReportLogger $logger
     * @return void
     */
    public function setLogger(EmailReportLogger $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Log email send result.
     *
     * @param bool $success
     * @param array $recipients
     * @param string|null $error
     * @return void
     */
    private function log(bool $success, array $recipients, ?string $error = null): void
    {
        $logger = $this->getLogger();

        $logger->log([
            'success'    => $success,
            'recipients' => $recipients,
            'frequency'  => $this->getRecurrence(),
            'error'      => $error,
        ]);
    }

    /**
     * Get event information for admin display.
     *
     * @return array
     */
    public function getInfo(): array
    {
        $info = parent::getInfo();

        // Add email-specific info
        $info['recipients']    = $this->getRecipients();
        $info['frequency']     = $this->getRecurrence();
        $info['last_sent']     = $this->getLogger()->getLastSent();

        return $info;
    }

    /**
     * Check if the event needs rescheduling due to frequency change.
     *
     * @return bool
     */
    public function needsReschedule(): bool
    {
        if (!$this->isScheduled()) {
            return $this->shouldSchedule();
        }

        $event = wp_get_scheduled_event($this->hook);
        if (!$event) {
            return true;
        }

        return $event->schedule !== $this->getRecurrence();
    }

    /**
     * Reschedule if needed (check frequency change).
     *
     * @return void
     */
    public function maybeReschedule(): void
    {
        if ($this->needsReschedule()) {
            $this->reschedule();
        }
    }
}
