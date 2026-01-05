<?php

namespace WP_Statistics\Service\Cron;

/**
 * Interface for scheduled cron events.
 *
 * Provides a contract for all scheduled events in WP Statistics,
 * enabling centralized management and admin visibility.
 *
 * @since 15.0.0
 */
interface ScheduledEventInterface
{
    /**
     * Get the WordPress cron hook name.
     *
     * @return string Unique hook name (e.g., 'wp_statistics_email_report').
     */
    public function getHook(): string;

    /**
     * Get the cron recurrence interval.
     *
     * @return string WordPress cron interval name (e.g., 'daily', 'weekly').
     */
    public function getRecurrence(): string;

    /**
     * Check if the event should be scheduled.
     *
     * @return bool True if event should be active.
     */
    public function shouldSchedule(): bool;

    /**
     * Check if the event is currently scheduled.
     *
     * @return bool True if event is scheduled in WordPress cron.
     */
    public function isScheduled(): bool;

    /**
     * Execute the scheduled task.
     *
     * @return void
     */
    public function execute(): void;

    /**
     * Get a human-readable description for admin display.
     *
     * @return string Translated description.
     */
    public function getDescription(): string;

    /**
     * Schedule the event if conditions are met.
     *
     * @return void
     */
    public function maybeSchedule(): void;

    /**
     * Register the callback for the cron hook.
     *
     * @return void
     */
    public function registerCallback(): void;

    /**
     * Reschedule the event (unschedule then schedule).
     *
     * Useful when settings change (e.g., frequency update).
     *
     * @return void
     */
    public function reschedule(): void;

    /**
     * Unschedule the event.
     *
     * @return void
     */
    public function unschedule(): void;

    /**
     * Get the next scheduled run time.
     *
     * @return int|false Timestamp or false if not scheduled.
     */
    public function getNextRunTime();

    /**
     * Get event information for admin display.
     *
     * @return array{
     *     hook: string,
     *     description: string,
     *     recurrence: string,
     *     enabled: bool,
     *     scheduled: bool,
     *     next_run: int|false
     * }
     */
    public function getInfo(): array;
}
