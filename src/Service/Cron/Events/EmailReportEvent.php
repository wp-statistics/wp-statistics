<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\EmailReport\EmailReportManager;

/**
 * Scheduled event for sending periodic email reports.
 *
 * @since 15.0.0
 */
class EmailReportEvent extends AbstractCronEvent
{
    /**
     * Allowed recurrence values for this event.
     *
     * @var string[]
     */
    private const ALLOWED_RECURRENCES = ['daily', 'weekly', 'monthly'];

    protected $hook = 'wp_statistics_email_report';
    protected $recurrence = 'weekly';
    protected $description = 'Email Report';

    /**
     * Only schedule if email reports are enabled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        return (bool) Option::getValue('email_reports_enabled', false);
    }

    /**
     * Get recurrence from settings.
     *
     * @return string
     */
    public function getRecurrence(): string
    {
        $recurrence = Option::getValue('email_reports_frequency', 'weekly');

        if (!is_string($recurrence) || !in_array($recurrence, self::ALLOWED_RECURRENCES, true)) {
            $recurrence = 'weekly';
        }

        $this->recurrence = $recurrence;

        return $this->recurrence;
    }

    /**
     * Schedule at an appropriate time based on frequency.
     *
     * @return int
     */
    protected function getNextScheduleTime(): int
    {
        $timezone  = wp_timezone();
        $now       = new \DateTimeImmutable('now', $timezone);
        $frequency = $this->getRecurrence();
        $hour      = max(0, min(23, (int) Option::getValue('email_reports_delivery_hour', 8)));
        $todayAtHour = $now->setTime($hour, 0);

        switch ($frequency) {
            case 'daily':
                $next = $todayAtHour <= $now ? $todayAtHour->modify('+1 day') : $todayAtHour;
                return $next->getTimestamp();

            case 'monthly':
                $firstOfMonth = $now->modify('first day of this month')->setTime($hour, 0);
                $next = $firstOfMonth <= $now ? $firstOfMonth->modify('first day of next month') : $firstOfMonth;
                return $next->getTimestamp();

            case 'weekly':
            default:
                $startOfWeek = (int) get_option('start_of_week', 0);
                if ($startOfWeek < 0 || $startOfWeek > 6) {
                    $startOfWeek = 0;
                }
                $todayDow   = (int) $now->format('w');
                $daysUntil  = ($startOfWeek - $todayDow + 7) % 7;
                $candidate  = $now->modify("+{$daysUntil} days")->setTime($hour, 0);
                $next       = $candidate <= $now ? $candidate->modify('+7 days') : $candidate;
                return $next->getTimestamp();
        }
    }

    /**
     * Ensure the current recurrence value is applied before scheduling.
     *
     * @return void
     */
    protected function schedule(): void
    {
        $this->getRecurrence();
        parent::schedule();
    }

    /**
     * Keep event info in sync with the selected recurrence.
     *
     * @return array
     */
    public function getInfo(): array
    {
        $this->getRecurrence();
        return parent::getInfo();
    }

    /**
     * Execute: send the email report.
     *
     * @return void
     */
    public function execute(): void
    {
        $manager = new EmailReportManager();
        $manager->sendReport();
    }
}
