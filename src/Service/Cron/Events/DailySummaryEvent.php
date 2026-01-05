<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\BackgroundProcessFactory;

/**
 * Daily Summary Cron Event.
 *
 * Processes daily summary calculations.
 *
 * @since 15.0.0
 */
class DailySummaryEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_queue_daily_summary';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * @var string
     */
    protected $description = 'Daily Summary';

    /**
     * Daily summary should always be scheduled.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        return true;
    }

    /**
     * Get the next schedule time for daily summary.
     *
     * Schedules at 00:01 AM the next day.
     *
     * @return int Timestamp.
     */
    protected function getNextScheduleTime(): int
    {
        $timezone = wp_timezone();
        $datetime = new \DateTimeImmutable('now', $timezone);
        $nextDate = $datetime->modify('tomorrow')->setTime(0, 1);

        return $nextDate->getTimestamp();
    }

    /**
     * Execute the daily summary processing.
     *
     * @return void
     */
    public function execute(): void
    {
        BackgroundProcessFactory::processDailySummaryTotal();
        BackgroundProcessFactory::processDailySummary();
    }
}
