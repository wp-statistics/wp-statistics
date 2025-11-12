<?php
namespace WP_Statistics\Service\Summary;

use WP_Statistics\Components\Event;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Models\SummaryModel;
use WP_Statistics\Components\DateRange;
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

        $lastRecord     = $summaryModel->getLastRecord();
        $lastRecordDate = $lastRecord->date ?? null;

        $twoDaysAgo = DateTime::get('-2 days');
        $yesterday  = DateTime::get('yesterday');

        // Set missing date to yesterday by default
        $missingDates = [$yesterday];

        // If last record is older than two days ago, get all missing dates up to yesterday
        if ($lastRecordDate && DateRange::compare($lastRecordDate, '<', $twoDaysAgo)) {
            $missingDates = DateRange::getDatesInRange([$lastRecordDate, $yesterday]);
        }

        // Insert missing records for each date
        foreach ($missingDates as $date) {
            // Check if record already exists, return
            if ($summaryModel->recordExists(['date' => $date])) {
                continue;
            }

            $data = $visitorsModel->getVisitorsHits(['date' => ['from' => $date, 'to' => $date]]);

            $summaryModel->insert([
                'visitors' => $data['visitors'],
                'views'    => $data['hits'],
                'date'     => $date
            ]);
        }
    }
}