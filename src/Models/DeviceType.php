<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to device types.
 *
 * Provides methods to query and aggregate views by device type within a date range.
 *
 * @since 15.0.0
 */
class DeviceType extends BaseModel
{
    /**
     * Get top device types by total views within a date range.
     *
     * Default range is the last 30 days (inclusive). Results are ordered by
     * total views descending. Control the number of rows via `limit`.
     *
     * Accepted arguments:
     * - 'date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     * - 'previous_date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d'] (optional, for comparison)
     * - 'limit' => int (optional, default: 5)
     *
     * If 'previous_date' is provided, the query is calculated in a single pass using
     * conditional aggregation and each row will include a `previous_value` key.
     *
     * Example return:
     * [
     *   [
     *     'icon'           => 'desktop',
     *     'label'          => 'Desktop',
     *     'value'          => 1500,
     *     'previous_value' => 1200,
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
            'limit' => 5,
        ]);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        // If previous_date is provided, build a single-query conditional aggregation.
        if (! empty($args['previous_date']['from']) && ! empty($args['previous_date']['to'])) {
            $prevFrom = $args['previous_date']['from'] . ' 00:00:00';
            $prevTo   = $args['previous_date']['to']   . ' 23:59:59';

            // Determine the overall time window that covers both periods.
            $rangeFrom = (strtotime($prevFrom) < strtotime($from)) ? $prevFrom : $from;
            $rangeTo   = (strtotime($prevTo)   > strtotime($to))   ? $prevTo   : $to;

            // Subquery: count views per session for both periods combined
            $subSql = Query::select([
                    'session_id',
                    "SUM(CASE WHEN viewed_at >= '{$from}' AND viewed_at <= '{$to}' THEN 1 ELSE 0 END) AS current_cnt",
                    "SUM(CASE WHEN viewed_at >= '{$prevFrom}' AND viewed_at <= '{$prevTo}' THEN 1 ELSE 0 END) AS previous_cnt",
                ])
                ->from('views')
                ->where('viewed_at', '>=', $rangeFrom)
                ->where('viewed_at', '<=', $rangeTo)
                ->groupBy('session_id')
                ->getQuery();

            return Query::select([
                    'device_types.name AS icon',
                    'device_types.name AS label',
                    'SUM(v.current_cnt) AS value',
                    'SUM(v.previous_cnt) AS previous_value',
                ])
                ->fromQuery($subSql, 'v')
                ->join('sessions', ['v.session_id', 'sessions.ID'])
                ->join('device_types', ['sessions.device_type_id', 'device_types.ID'])
                ->groupBy('device_types.name')
                ->orderBy('value', 'DESC')
                ->perPage(1, (int) $args['limit'])
                ->allowCaching()
                ->getAll();
        }

        // Original behaviour: only current period
        $subSql = Query::select(['session_id', 'COUNT(*) AS cnt'])
            ->from('views')
            ->where('viewed_at', '>=', $from)
            ->where('viewed_at', '<', $to)
            ->groupBy('session_id')
            ->getQuery();

        return Query::select([
                'device_types.name AS device_type',
                'SUM(v.cnt) AS views',
            ])
            ->fromQuery($subSql, 'v')
            ->join('sessions', ['v.session_id', 'sessions.ID'])
            ->join('device_types', ['sessions.device_type_id', 'device_types.ID'])
            ->groupBy('device_types.name')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int) $args['limit'])
            ->allowCaching()
            ->getAll();
    }
}
