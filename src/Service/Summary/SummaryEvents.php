<?php
namespace WP_Statistics\Service\Summary;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Event;
use WP_Statistics\Models\SummaryModel;
use WP_Statistics\Models\VisitorsModel;

class SummaryEvents
{
    /**
     * Register the `record_summary_totals_data` event to run daily
     */
    public function register()
    {
        /*
        * Start recording summary data from tomorrow onward, and continue daily.
        * This prevents partial data from being recorded for today, ensuring summaries only include complete days.
        */
        $timestamp = DateTime::get('midnight +1 days', 'U');
        Event::schedule('wp_statistics_record_daily_summary', $timestamp, 'daily', [$this, 'recordSummaryTotalsData']);
    }

    /**
     * Reschedules `record_summary_totals_data` event when timezone or GMT offset is updated
     */
    public function reschedule()
    {
        $timestamp = DateTime::get('midnight +1 days', 'U');
        Event::reschedule('wp_statistics_record_daily_summary', 'daily', $timestamp);
    }

    /**
     * Record summary data for yesterday.
     */
    public function recordSummaryTotalsData()
    {
        $summaryModel  = new SummaryModel();
        $visitorsModel = new VisitorsModel();

        $date = DateTime::get('yesterday', 'Y-m-d');

        // Check if record already exists, return
        if ($summaryModel->recordExists(['date' => $date])) {
            return;
        }

        $data = $visitorsModel->getVisitorsHits(['date' => 'yesterday']);

        $summaryModel->insert([
            'visitors' => $data['visitors'],
            'views'    => $data['hits'],
            'date'     => $date
        ]);
    }
}