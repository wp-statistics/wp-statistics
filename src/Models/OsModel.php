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

        $subSql = Query::select(['session_id', 'COUNT(*) AS cnt'])
            ->from('views')
            ->where('viewed_at', '>=', $from)
            ->where('viewed_at', '<', $to)
            ->groupBy('session_id')
            ->getQuery();

        $rows = Query::select([
                'device_oss.name AS os',
                'SUM(v.cnt) AS views',
            ])
            ->fromQuery($subSql, 'v')
            ->join('sessions', ['v.session_id', 'sessions.ID'])
            ->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
            ->groupBy('device_oss.name')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int) $args['limit'])
            ->allowCaching()
            ->getAll();

        return $rows;
    }
}
