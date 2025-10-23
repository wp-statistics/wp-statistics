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
     * @param array $args {
     *   @type array{from:string,to:string} $date  Date range (Y-m-d).
     *   @type int                           $limit Number of rows to return. Default 10.
     * }
     * @return array<string, array{views:int}> Device-type keyed totals
     */
    public function getTop($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => [
                'from' => date('Y-m-d', strtotime('-29 days')),
                'to'   => date('Y-m-d'),
            ],
            'limit' => 5,
        ]);

        $from = $args['date']['from'] . ' 00:00:00';
        $to   = $args['date']['to']   . ' 23:59:59';

        // Subquery: count views per session inside the date window
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
