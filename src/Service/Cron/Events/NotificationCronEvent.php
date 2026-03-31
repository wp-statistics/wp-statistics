<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Components\Option;
use WP_Statistics\Service\Admin\Notification\NotificationFetcher;

/**
 * Notification Fetch Cron Event.
 *
 * Fetches remote notifications daily from connect.wp-statistics.com.
 *
 * @since 15.0.0
 */
class NotificationCronEvent extends AbstractCronEvent
{
    /**
     * @var string
     */
    protected $hook = 'wp_statistics_fetch_notifications';

    /**
     * @var string
     */
    protected $recurrence = 'daily';

    /**
     * @var string
     */
    protected $description = 'Fetch Notifications';

    /**
     * Only schedule if notifications are enabled in settings.
     *
     * @return bool
     */
    public function shouldSchedule(): bool
    {
        return (bool) Option::getValue('display_notifications', true);
    }

    /**
     * Schedule at 02:00 AM the next day.
     *
     * @return int Timestamp.
     */
    protected function getNextScheduleTime(): int
    {
        $timezone = wp_timezone();
        $datetime = new \DateTimeImmutable('now', $timezone);
        $nextDate = $datetime->modify('tomorrow')->setTime(2, 0);

        return $nextDate->getTimestamp();
    }

    /**
     * Execute the notification fetch.
     *
     * @return void
     */
    public function execute(): void
    {
        (new NotificationFetcher())->fetchNotifications();
    }
}
