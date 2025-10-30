<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to referrers.
 *
 * Provides methods to query and aggregate metrics by country.
 *
 * @since 15.0.0
 */
class ReferrerModel extends BaseModel
{
    /**
     * Get top referrer by views within a date range.
     *
     * Uses sessions with SUM(total_views) grouped by referrer. Defaults to the last
     * 30 days (inclusive). Results are ordered by total views descending.
     *
     * @param array $args {
     *   @type array{from:string,to:string} $date  Date range (Y-m-d).
     *   @type int                           $limit Number of rows to return. Default 4.
     * }
     * @return array<string, array{views:int}> referrer-domain keyed totals
     */
    public function getTop($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields' => [
                'referrers.domain',
                'SUM(sessions.total_views) AS views',
            ],
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'limit' => 4,
        ]);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        $rows = Query::select($args['fields'])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->where('sessions.started_at', '>=', $from)
            ->where('sessions.started_at', '<',  $to)
            ->groupBy('referrers.ID, referrers.name')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int) $args['limit'])
            ->allowCaching()
            ->getAll();


        return $rows;
    }
}
