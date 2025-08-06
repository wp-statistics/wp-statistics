<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\Globals\Option;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\WP_Background_Process;

class CalculateDailySummaryTotal extends WP_Background_Process
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

        $sessionModel = new SessionModel();

        $row = $sessionModel->getDailySummaryTotal();

        if (empty($row)) {
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