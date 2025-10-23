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
     * @param array $args {
     *   @type array{from:string,to:string} $date  Date range (Y-m-d).
     *   @type int                           $limit Number of rows to return. Default 4.
     * }
     * @return array<string, array{views:int}> Country-name keyed totals
     */
    public function getTop($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'limit' => 4,
        ]);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        $rows = Query::select([
                'countries.ID AS country_id',
                'countries.name AS country',
                'SUM(sessions.total_views) AS views',
            ])
            ->from('sessions')
            ->join('countries', ['sessions.country_id', 'countries.ID'])
            ->where('sessions.started_at', '>=', $from)
            ->where('sessions.started_at', '<',  $to)
            ->groupBy('countries.ID, countries.name')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int) $args['limit'])
            ->allowCaching()
            ->getAll();
        
        return $rows;
    }
}
