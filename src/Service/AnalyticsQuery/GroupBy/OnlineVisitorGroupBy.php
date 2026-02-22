<?php

namespace WP_Statistics\Service\AnalyticsQuery\GroupBy;

/**
 * Online Visitor group by - groups by visitor and uses ended_at for last_visit.
 *
 * This is a specialized version of VisitorGroupBy designed for online visitor tracking.
 * The key difference is that last_visit uses sessions.ended_at instead of sessions.started_at,
 * which is crucial for determining if a visitor is currently online (last activity within X minutes).
 *
 * @since 15.0.0
 */
class OnlineVisitorGroupBy extends VisitorGroupBy
{
    protected $name = 'online_visitor';

    /**
     * Base extra columns for online visitors.
     *
     * Uses sessions.ended_at for last_visit to show last activity time.
     *
     * @var array
     */
    protected $baseExtraColumns = [
        'LEFT(visitors.hash, 6) AS visitor_hash',
        'MIN(sessions.started_at) AS first_visit',
        'MAX(sessions.ended_at) AS last_visit',
        'COUNT(DISTINCT sessions.ID) AS total_sessions',
        'SUM(sessions.total_views) AS total_views',
    ];

    /**
     * Get base extra columns conditionally based on requested columns.
     *
     * Override parent to use ended_at for last_visit.
     *
     * @param array $requestedColumns List of requested column aliases. Empty = include all.
     * @return array
     */
    protected function getBaseExtraColumns(array $requestedColumns = []): array
    {
        $includeAll = empty($requestedColumns);
        $columns = [];

        // visitor_hash (truncated to 6 chars for display)
        if ($includeAll || in_array('visitor_hash', $requestedColumns, true)) {
            $columns[] = 'LEFT(visitors.hash, 6) AS visitor_hash';
        }

        // first_visit
        if ($includeAll || in_array('first_visit', $requestedColumns, true)) {
            $columns[] = 'MIN(sessions.started_at) AS first_visit';
        }

        // last_visit - USES ended_at for online visitor tracking
        if ($includeAll || in_array('last_visit', $requestedColumns, true)) {
            $columns[] = 'MAX(sessions.ended_at) AS last_visit';
        }

        // total_sessions
        if ($includeAll || in_array('total_sessions', $requestedColumns, true)) {
            $columns[] = 'COUNT(DISTINCT sessions.ID) AS total_sessions';
        }

        // total_views
        if ($includeAll || in_array('total_views', $requestedColumns, true)) {
            $columns[] = 'SUM(sessions.total_views) AS total_views';
        }

        return $columns;
    }
}
