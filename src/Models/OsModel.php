<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to operating systems.
 *
 * Provides methods to query and aggregate metrics by operating system.
 *
 * @since 15.0.0
 */
class OsModel extends BaseModel
{
    /**
     * Get all operating systems by total views within a date range.
     *
     * Default range is the last 30 days (inclusive). Results are ordered by
     * total views descending. Returns all operating systems without limit.
     *
     * Accepted arguments:
     * - 'date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     * - 'previous_date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d'] (optional, for comparison)
     *
     * If 'previous_date' is provided, the query is calculated in a single pass using
     * conditional aggregation and each row will include a `previous_value` key.
     *
     * Example return:
     * [
     *   [
     *     'icon'           => 'windows',
     *     'label'          => 'Windows',
     *     'value'          => 2000,
     *     'previous_value' => 1800,
     *   ],
     *   ...
     * ]
     *
     * @param array $args
     * @return array<int, array<string, mixed>>
     */
    public function getTop($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'previous_date' => null,
        ]);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        // If previous_date is provided, build a single-query conditional aggregation.
        if (! empty($args['previous_date']['from']) && ! empty($args['previous_date']['to'])) {
            $prevFrom = $args['previous_date']['from'] . ' 00:00:00';
            $prevTo   = $args['previous_date']['to']   . ' 23:59:59';

            // Determine the overall time window
            $rangeFrom = (strtotime($prevFrom) < strtotime($from)) ? $prevFrom : $from;
            $rangeTo   = (strtotime($prevTo)   > strtotime($to))   ? $prevTo   : $to;

            // Use sessions table directly with conditional aggregation for better performance
            return Query::select([
                    'device_oss.name AS icon',
                    'device_oss.name AS label',
                    "CAST(SUM(CASE WHEN sessions.started_at >= '{$from}' AND sessions.started_at <= '{$to}' THEN sessions.total_views ELSE 0 END) AS UNSIGNED) AS value",
                    "CAST(SUM(CASE WHEN sessions.started_at >= '{$prevFrom}' AND sessions.started_at <= '{$prevTo}' THEN sessions.total_views ELSE 0 END) AS UNSIGNED) AS previous_value",
                ])
                ->from('sessions')
                ->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
                ->where('sessions.started_at', '>=', $rangeFrom)
                ->where('sessions.started_at', '<=', $rangeTo)
                ->groupBy('device_oss.name')
                ->orderBy('value', 'DESC')
                ->allowCaching()
                ->getAll();
        }

        // Original behaviour: only current period
        return Query::select([
                'device_oss.name AS os',
                'SUM(sessions.total_views) AS views',
            ])
            ->from('sessions')
            ->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
            ->where('sessions.started_at', '>=', $from)
            ->where('sessions.started_at', '<', $to)
            ->groupBy('device_oss.name')
            ->orderBy('views', 'DESC')
            ->allowCaching()
            ->getAll();
    }
}
