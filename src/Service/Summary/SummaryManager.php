<?php
namespace WP_Statistics\Service\Summary;

class SummaryManager
{
    public function __construct()
    {
        add_action('init', [$this, 'registerEvents']);
    }

    public function registerEvents()
    {
        $summaryEvents = new SummaryEvents();
        $summaryEvents->register();
    }
}