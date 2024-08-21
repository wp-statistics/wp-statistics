<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;

class OnlineModel extends BaseModel
{

    public function countOnlines($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, []);

        $result = Query::select('COUNT(ID)')
            ->from('useronline')
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
    }

    public function getOnlineVisitorsData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'page'      => '',
            'per_page'  => '',
            'order_by'  => '',
            'order'     => '',
        ]);

        $result = Query::select([
            'useronline.ID',
            'ip',
            'created',
            'timestamp',
            'referred',
            'agent',
            'platform',
            'location',
            'region',
            'city',
            'user_id',
            'page_id',
            'date',
            'users.display_name',
            'users.user_email'
        ])
            ->from('useronline')
            ->join('users', ['useronline.user_id', 'users.ID'], [], 'LEFT')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }
}