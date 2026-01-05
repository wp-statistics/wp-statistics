<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;

class CalculateDailySummaryTotal extends ExtendedBackgroundProcess
{
    /**
     * Prefix for the background process.
     *
     * @var string
     */
    protected $prefix = 'wp_statistics';

    /**
     * Action name for the background process.
     *
     * @var string
     */
    protected $action = 'calculate_daily_summary_total';

    /**
     * Process one queue item.
     *
     * @param array $item ['date' => 'YYYY-MM-DD']
     * @return false
     */
    protected function task($item)
    {
        $isTotal = !empty($item['is_total']) ? $item['is_total'] : false;

        if (empty($isTotal)) {
            return false;
        }

        $row = $this->getDailySummaryTotal();

        if (empty($row) || empty($row->date)) {
            return false;
        }

        $isExist = RecordFactory::summaryTotals()->get([
            'date' => $row->date
        ]);

        if (!empty($isExist)) {
            return false;
        }

        RecordFactory::summaryTotals()->insert([
            'date'           => $row->date,
            'views'          => $row->views,
            'bounces'        => $row->bounces,
            'visitors'       => $row->visitors,
            'sessions'       => $row->sessions,
            'total_duration' => $row->total_duration,
        ]);

        return false;
    }

    /**
     * Site-wide daily totals (visitors, sessions, views, duration, bounces).
     *
     * Aggregates metrics across all resources for a single calendar day.
     * A *bounce* is defined here as a session with **at most one** view
     * (i.e., zero or one rows in `views` for that session).
     *
     * @return object|null Aggregated totals for the day, or `null` if none.
     */
    private function getDailySummaryTotal()
    {
        $dateRange = DateTime::getUtcRangeForLocalDate('yesterday');
        $startUtc  = $dateRange['startUtc'];
        $endUtc    = $dateRange['endUtc'];
        $labelDate = $dateRange['labelDate'];

        $oneResSub = Query::select([
            'session_id',
            'COUNT(*) AS view_count',
        ])
            ->from('views')
            ->groupBy(['session_id'])
            ->getQuery();

        $bounceSessionsSub = Query::select([
            'sessions.ID AS session_id',
        ])
            ->from('sessions')
            ->joinQuery($oneResSub, ['one.session_id', 'sessions.ID'], 'one', 'LEFT')
            ->whereRaw('COALESCE(one.view_count, 0) <= 1')
            ->getQuery();

        $query = Query::select([
            "'{$labelDate}' AS date",
            'COUNT(DISTINCT visitors.hash) AS visitors',
            'COUNT(DISTINCT sessions.ID) AS sessions',
            'COUNT(views.ID) AS views',
            'SUM(sessions.duration) AS total_duration',
            'COUNT(DISTINCT b.session_id) AS bounces',
        ])
            ->from('sessions')
            ->join('visitors', ['visitors.ID', 'sessions.visitor_id'])
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->joinQuery($bounceSessionsSub, ['b.session_id', 'sessions.ID'], 'b', 'LEFT')
            ->where('sessions.started_at', '>=', $startUtc)
            ->where('sessions.started_at', '<', $endUtc);

        return $query->getRow();
    }

    /**
     * After all items are processed.
     */
    protected function complete()
    {
        parent::complete();

        // Reset initiated flag so it can be dispatched again.
        Option::updateGroup('calculate_daily_summary_total_initiated', true, 'jobs');
    }

    /**
     * Check if we've already queued this job.
     *
     * @return bool
     */
    public function is_initiated()
    {
        return Option::getGroupValue('jobs', 'calculate_daily_summary_total_initiated', false);
    }
}