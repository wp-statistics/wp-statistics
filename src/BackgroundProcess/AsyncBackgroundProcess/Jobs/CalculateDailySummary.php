<?php

namespace WP_Statistics\BackgroundProcess\AsyncBackgroundProcess\Jobs;

use WP_Statistics\BackgroundProcess\ExtendedBackgroundProcess;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Option;
use WP_Statistics\Records\RecordFactory;
use WP_Statistics\Utils\Query;

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

        foreach ($ids as $key => $id) {
            if (empty($id)) {
                continue;
            }

            $row = $this->getDailySummary($id);

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
     * Per-resource daily summary (visitors, sessions, views, duration, bounces).
     *
     * Aggregates metrics for a single resource URI (identified by
     * `resource_uri_id`).
     *
     * @param int|string $resourceUriId Resource URI ID.
     * @return object|null Aggregated row for the resource, or `null` if none.
     */
    private function getDailySummary($resourceUriId)
    {
        $dateRange = DateTime::getUtcRangeForLocalDate('yesterday');
        $labelDate = $dateRange['labelDate'];

        $oneViewSub = Query::select([
            'session_id',
            'COUNT(*) AS view_count',
            'MIN(resource_uri_id) AS only_resource_uri_id',
        ])
            ->from('views')
            ->groupBy(['session_id'])
            ->getQuery();

        $bounceSessionsSub = Query::select([
            'sessions.ID AS session_id',
        ])
            ->from('sessions')
            ->joinQuery($oneViewSub, ['one_view.session_id', 'sessions.ID'], 'one_view', 'LEFT')
            ->whereRaw('COALESCE(one_view.view_count, 0) = 1 AND one_view.only_resource_uri_id = %s', [
                $resourceUriId
            ])
            ->getQuery();

        $firstViewIdSub = Query::select([
            'session_id',
            'MIN(ID) AS first_view_id',
        ])
            ->from('views')
            ->groupBy(['session_id'])
            ->getQuery();

        $entrancesSub = Query::select([
            'sessions.ID AS session_id',
        ])
            ->from('sessions')
            ->joinQuery($firstViewIdSub, ['first_view_id.session_id', 'sessions.ID'], 'first_view_id', 'LEFT')
            ->join('views', ['views.ID', 'first_view_id.first_view_id'])
            ->where('views.resource_uri_id', '=', $resourceUriId)
            ->getQuery();

        $query = Query::select([
            "'{$labelDate}' AS date",
            "COALESCE(views.resource_uri_id, '') AS resource",
            'COUNT(DISTINCT visitors.hash) AS visitors',
            'COUNT(DISTINCT entrance_sessions.session_id) AS sessions',
            'COUNT(views.ID) AS views',
            'SUM(sessions.duration) AS total_duration',
            'COALESCE(ROUND(COUNT(DISTINCT bounce_sessions.session_id) / NULLIF(COUNT(DISTINCT entrance_sessions.session_id), 0), 4), 0) AS bounces',
        ])
            ->from('sessions')
            ->join('visitors', ['visitors.ID', 'sessions.visitor_id'])
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->joinQuery($bounceSessionsSub, ['bounce_sessions.session_id', 'sessions.ID'], 'bounce_sessions', 'LEFT')
            ->joinQuery($entrancesSub, ['entrance_sessions.session_id', 'sessions.ID'], 'entrance_sessions', 'LEFT')
            ->where('views.resource_uri_id', '=', $resourceUriId);

        return $query->getRow();
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