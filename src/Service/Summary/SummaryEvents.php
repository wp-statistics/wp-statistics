<?php
namespace WP_Statistics\Service\Summary;

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Event;
use WP_Statistics\Models\SummaryModel;
use WP_Statistics\Models\VisitorsModel;

class SummaryEvents
{
    public function register()
    {
        /*
        * Start recording summary data from tomorrow onward, and continue daily.
        * This prevents partial data from being recorded for today, ensuring summaries only include complete days.
        */
        $timestamp = DateTime::get('midnight +2 days', 'U');

        Event::schedule('wp_statistics_record_summary_totals_data', $timestamp, 'daily', [$this, 'recordSummaryTotalsData']);
    }


    public function recordSummaryTotalsData()
    {
        $summaryModel  = new SummaryModel();
        $visitorsModel = new VisitorsModel();

        $data = $visitorsModel->getVisitorsHits([
            'date' => 'yesterday'
        ]);

        $summaryModel->insert([
            'visitors' => $data['visitors'],
            'views'    => $data['hits'],
            'date'     => DateTime::get('yesterday', 'Y-m-d')
        ]);
    }
}