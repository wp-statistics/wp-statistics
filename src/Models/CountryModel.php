<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to countries.
 *
 * Provides methods to query and aggregate metrics by country.
 *
 * @since 15.0.0
 */
class CountryModel extends BaseModel
{
    /**
     * Get top countries by views within a date range.
     *
     * Uses sessions with SUM(total_views) grouped by country. Defaults to the last
     * 30 days (inclusive). Results are ordered by total views descending.
     *
     * Accepted arguments:
     * - 'date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d']
     * - 'previous_date' => ['from' => 'Y-m-d', 'to' => 'Y-m-d'] (optional, for comparison)
     * - 'limit' => int (optional, default: 4)
     *
     * If 'previous_date' is provided, the query is calculated in a single pass using
     * conditional aggregation and each row will include a `previous_value` key.
     *
     * Example return:
     * [
     *   [
     *     'icon'           => 'us',
     *     'label'          => 'United States',
     *     'value'          => 120,
     *     'previous_value' => 90,
     *   ],
     *   ...
     * ]
     *
     * @param array $args
     * @return array<int, array<string, mixed>>
     */
    public function getTop($args = [])
    {
        $defaults = [
            'fields' => [
                'countries.code AS icon',
                'countries.name AS label',
                'SUM(sessions.total_views) AS value',
            ],
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'previous_date' => null,
            'limit' => 4,
        ];

        $args = $this->parseArgs($args, $defaults);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        // If previous_date is provided, build a single-query conditional aggregation.
        if (! empty($args['previous_date']['from']) && ! empty($args['previous_date']['to'])) {
            $prevFrom = $args['previous_date']['from'] . ' 00:00:00';
            $prevTo   = $args['previous_date']['to']   . ' 23:59:59';

            // Determine the overall time window that covers both periods.
            $rangeFrom = (strtotime($prevFrom) < strtotime($from)) ? $prevFrom : $from;
            $rangeTo   = (strtotime($prevTo)   > strtotime($to))   ? $prevTo   : $to;

            $fields = [
                'countries.code AS icon',
                'countries.name AS label',
                // Current period views
                "SUM(CASE WHEN sessions.started_at >= '{$from}' AND sessions.started_at <= '{$to}' THEN sessions.total_views ELSE 0 END) AS value",
                // Previous period views
                "SUM(CASE WHEN sessions.started_at >= '{$prevFrom}' AND sessions.started_at <= '{$prevTo}' THEN sessions.total_views ELSE 0 END) AS previous_value",
            ];

            $rows = Query::select($fields)
                ->from('sessions')
                ->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('sessions.started_at', '>=', $rangeFrom)
                ->where('sessions.started_at', '<=', $rangeTo)
                ->groupBy('countries.ID, countries.name')
                ->orderBy('value', 'DESC')
                ->perPage(1, (int) $args['limit'])
                ->allowCaching()
                ->getAll();
        } else {
            // Original behaviour: only current period, using configurable fields.
            $rows = Query::select($args['fields'])
                ->from('sessions')
                ->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('sessions.started_at', '>=', $from)
                ->where('sessions.started_at', '<',  $to)
                ->groupBy('countries.ID, countries.name')
                ->orderBy('value', 'DESC')
                ->perPage(1, (int) $args['limit'])
                ->allowCaching()
                ->getAll();
        }

        return $rows;
    }
}
