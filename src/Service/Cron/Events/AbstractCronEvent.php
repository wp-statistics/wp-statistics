<?php

namespace WP_Statistics\Service\Cron\Events;

/**
 * Abstract Cron Event base class.
 *
 * @since 15.0.0
 */
abstract class AbstractCronEvent
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
     * Check if the event should be scheduled.
     *
     * @return bool
     */
    abstract protected function shouldSchedule();

    /**
     * Execute the cron event.
     *
     * @return void
     */
    abstract public function execute();

    /**
     * Schedule or unschedule the event based on conditions.
     *
     * @return void
     */
    public function maybeSchedule()
    {
        $isScheduled   = wp_next_scheduled($this->hook);
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
    protected function schedule()
    {
        $timestamp = $this->getNextScheduleTime();
        wp_schedule_event($timestamp, $this->recurrence, $this->hook);
    }

    /**
     * Unschedule the event.
     *
     * @return void
     */
    protected function unschedule()
    {
        $timestamp = wp_next_scheduled($this->hook);
        if ($timestamp) {
            wp_unschedule_event($timestamp, $this->hook);
        }
    }

    /**
     * Get the next schedule time.
     *
     * Override in child classes for custom timing.
     *
     * @return int Timestamp.
     */
    protected function getNextScheduleTime()
    {
        return time();
    }

    /**
     * Register the callback for the cron hook.
     *
     * @return void
     */
    public function registerCallback()
    {
        add_action($this->hook, [$this, 'execute']);
    }

    /**
     * Get the hook name.
     *
     * @return string
     */
    public function getHook()
    {
        return $this->hook;
    }

    /**
     * Check if the event is currently scheduled.
     *
     * @return bool
     */
    public function isScheduled()
    {
        return (bool) wp_next_scheduled($this->hook);
    }
}
