<?php

namespace WP_Statistics\Service\Cron\Events;

use WP_Statistics\Service\Cron\ScheduledEventInterface;

/**
 * Abstract Cron Event base class.
 *
 * Provides common functionality for all scheduled cron events.
 *
 * @since 15.0.0
 */
abstract class AbstractCronEvent implements ScheduledEventInterface
{
    /**
     * The cron hook name.
     *
     * @var string
     */
    protected $hook;

    /**
     * The schedule recurrence (daily, weekly, monthly, etc.).
     *
     * @var string
     */
    protected $recurrence;

    /**
     * Human-readable description for admin display.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Check if the event should be scheduled.
     *
     * @return bool
     */
    abstract public function shouldSchedule(): bool;

    /**
     * Execute the cron event.
     *
     * @return void
     */
    abstract public function execute(): void;

    /**
     * Get the WordPress cron hook name.
     *
     * @return string
     */
    public function getHook(): string
    {
        return $this->hook;
    }

    /**
     * Get the cron recurrence interval.
     *
     * @return string
     */
    public function getRecurrence(): string
    {
        return $this->recurrence;
    }

    /**
     * Get human-readable description.
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description ?: $this->hook;
    }

    /**
     * Schedule or unschedule the event based on conditions.
     *
     * @return void
     */
    public function maybeSchedule(): void
    {
        $isScheduled    = $this->isScheduled();
        $shouldSchedule = $this->shouldSchedule();

        if (!$isScheduled && $shouldSchedule) {
            $this->schedule();
        } elseif ($isScheduled && !$shouldSchedule) {
            $this->unschedule();
        }
    }

    /**
     * Schedule the event.
     *
     * @return void
     */
    protected function schedule(): void
    {
        $timestamp = $this->getNextScheduleTime();
        wp_schedule_event($timestamp, $this->recurrence, $this->hook);
    }

    /**
     * Unschedule the event.
     *
     * @return void
     */
    public function unschedule(): void
    {
        $timestamp = wp_next_scheduled($this->hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook);
        }
    }

    /**
     * Reschedule the event (unschedule then schedule if enabled).
     *
     * @return void
     */
    public function reschedule(): void
    {
        $this->unschedule();

        if ($this->shouldSchedule()) {
            $this->schedule();
        }
    }

    /**
     * Get the next schedule time.
     *
     * Override in child classes for custom timing.
     *
     * @return int Timestamp.
     */
    protected function getNextScheduleTime(): int
    {
        return time();
    }

    /**
     * Register the callback for the cron hook.
     *
     * @return void
     */
    public function registerCallback(): void
    {
        add_action($this->hook, [$this, 'execute']);
    }

    /**
     * Check if the event is currently scheduled.
     *
     * @return bool
     */
    public function isScheduled(): bool
    {
        return (bool) wp_next_scheduled($this->hook);
    }

    /**
     * Get the next scheduled run time.
     *
     * @return int|false Timestamp or false if not scheduled.
     */
    public function getNextRunTime()
    {
        return wp_next_scheduled($this->hook);
    }

    /**
     * Get event information for admin display.
     *
     * @return array
     */
    public function getInfo(): array
    {
        $nextRun = $this->getNextRunTime();

        return [
            'hook'        => $this->hook,
            'description' => $this->getDescription(),
            'recurrence'  => $this->recurrence,
            'enabled'     => $this->shouldSchedule(),
            'scheduled'   => $this->isScheduled(),
            'next_run'    => $nextRun,
            'next_run_formatted' => $nextRun ? date_i18n('Y-m-d H:i:s', $nextRun) : __('Not scheduled', 'wp-statistics'),
        ];
    }
}
