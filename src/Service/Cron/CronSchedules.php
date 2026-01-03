<?php

namespace WP_Statistics\Service\Cron;

use WP_Statistics\Components\DateTime;

/**
 * Cron Schedules for WP Statistics v15.
 *
 * Defines custom WordPress cron schedule intervals.
 *
 * @since 15.0.0
 */
class CronSchedules
{
    /**
     * Register custom cron schedules with WordPress.
     *
     * @return void
     */
    public static function register()
    {
        add_filter('cron_schedules', [__CLASS__, 'addCustomSchedules']);
    }

    /**
     * Add custom schedule intervals to WordPress cron.
     *
     * @param array $schedules Existing WordPress schedules.
     * @return array Modified schedules.
     */
    public static function addCustomSchedules($schedules)
    {
        $customSchedules = self::getSchedules();

        foreach ($customSchedules as $key => $schedule) {
            if (!isset($schedules[$key])) {
                $schedules[$key] = [
                    'interval' => $schedule['interval'],
                    'display'  => $schedule['display'],
                ];
            }
        }

        return $schedules;
    }

    /**
     * Get all custom schedule definitions.
     *
     * @return array Schedule definitions with intervals, display names, and next scheduled times.
     */
    public static function getSchedules()
    {
        $timestamp = time();
        $timezone  = wp_timezone();
        $datetime  = new \DateTime('@' . $timestamp);
        $datetime->setTimezone($timezone);

        // Determine the day name based on the start of the week setting
        $startDayName = DateTime::getStartOfWeek();

        // Daily schedule - 8:00 AM tomorrow
        $daily = clone $datetime;
        $daily->modify('tomorrow')->setTime(8, 0);

        // Weekly schedule - 8:00 AM next week start
        $weekly = clone $datetime;
        $weekly->modify("next {$startDayName}")->setTime(8, 0);

        // Bi-Weekly schedule - 8:00 AM two weeks from week start
        $biweekly = clone $datetime;
        $biweekly->modify("next {$startDayName} +1 week")->setTime(8, 0);

        // Monthly schedule - 8:00 AM first day of next month
        $monthly = clone $datetime;
        $monthly->modify('first day of next month')->setTime(8, 0);

        $schedules = [
            'every_minute' => [
                'interval' => 60,
                'display'  => __('Every Minute', 'wp-statistics'),
            ],
            'daily' => [
                'interval'      => DAY_IN_SECONDS,
                'display'       => __('Daily', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime('-1 day')),
                'end'           => wp_date('Y-m-d', strtotime('-1 day')),
                'next_schedule' => $daily->getTimestamp(),
            ],
            'weekly' => [
                'interval'      => WEEK_IN_SECONDS,
                'display'       => __('Weekly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime('-7 days')),
                'end'           => wp_date('Y-m-d', strtotime('-1 day')),
                'next_schedule' => $weekly->getTimestamp(),
            ],
            'biweekly' => [
                'interval'      => 2 * WEEK_IN_SECONDS,
                'display'       => __('Bi-Weekly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime('-14 days')),
                'end'           => wp_date('Y-m-d', strtotime('-1 day')),
                'next_schedule' => $biweekly->getTimestamp(),
            ],
            'monthly' => [
                'interval'      => MONTH_IN_SECONDS,
                'display'       => __('Monthly', 'wp-statistics'),
                'start'         => wp_date('Y-m-d', strtotime('First day of previous month')),
                'end'           => wp_date('Y-m-d', strtotime('Last day of previous month')),
                'next_schedule' => $monthly->getTimestamp(),
            ],
        ];

        /**
         * Filter the cron schedules.
         *
         * @param array $schedules Schedule definitions.
         */
        return apply_filters('wp_statistics_cron_schedules', $schedules);
    }

    /**
     * Get next scheduled time for an event.
     *
     * @param string $event Event hook name.
     * @return int|false Timestamp or false if not scheduled.
     */
    public static function getNextScheduledTime($event)
    {
        return wp_next_scheduled($event);
    }
}
