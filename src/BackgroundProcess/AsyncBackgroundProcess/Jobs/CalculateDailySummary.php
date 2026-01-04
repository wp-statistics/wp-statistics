<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_Statistics\Components\Option;
use WP_Statistics\Models\SessionModel;
use WP_Statistics\Records\RecordFactory;

class CalculateDailySummary extends ExtendedBackgroundProcess
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
    protected $action = 'calculate_daily_summary';

    /**
     * Process one queue item.
     *
     * @param array $item
     * @return false
     */
    protected function task($item)
    {
        $ids = empty($item['ids']) ? [] : $item['ids'];

        if (empty($ids)) {
            return false;
        }

        $sessionModel = new SessionModel();

        foreach ($ids as $key => $id) {
            if (empty($id)) {
                continue;
            }

            $row = $sessionModel->getDailySummary([
                'date'            => 'yesterday',
                'resource_uri_id' => $id
            ]);


            if (empty($row) || empty($row->date)) {
                continue;
            }

            $isExist = RecordFactory::summary()->get([
                'date'            => $row->date,
                'resource_uri_id' => $row->resource
            ]);

            if (!empty($isExist)) {
                continue;
            }

            RecordFactory::summary()->insert([
                'date'            => $row->date,
                'views'           => $row->views,
                'bounces'         => $row->bounces,
                'visitors'        => $row->visitors,
                'sessions'        => $row->sessions,
                'total_duration'  => $row->total_duration,
                'resource_uri_id' => $row->resource,
            ]);
        }

        return false;
    }

    /**
     * After all items are processed.
     */
    protected function complete()
    {
        parent::complete();

        // Reset initiated flag so it can be dispatched again.
        Option::updateGroup('calculate_daily_summary_initiated', true, 'jobs');
    }

    /**
     * Check if we've already queued this job.
     *
     * @return bool
     */
    public function is_initiated()
    {
        return Option::getGroupValue('jobs', 'calculate_daily_summary_initiated', false);
    }
}