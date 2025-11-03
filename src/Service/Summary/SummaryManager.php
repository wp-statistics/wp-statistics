<?php
namespace WP_Statistics\Service\Summary;

class SummaryManager
{
    public function __construct()
    {
        add_action('init', [$this, 'registerEvents']);
        add_action('update_option_timezone_string', [$this, 'rescheduleEvents'], 10, 2);
        add_action('update_option_gmt_offset', [$this, 'rescheduleEvents'], 10, 2);
    }

    /**
     * Register the summary events on init hook
     */
    public function registerEvents()
    {
        $summaryEvents = new SummaryEvents();
        $summaryEvents->register();
    }

    /**
     * Reschedule summary events when timezone or GMT offset is updated
     */
    public function rescheduleEvents()
    {
        $summaryEvents = new SummaryEvents();
        $summaryEvents->reschedule();
    }
}