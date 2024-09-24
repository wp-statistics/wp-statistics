<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Query;

class OnlineModel extends BaseModel
{

    public function countOnlines($args = [])
    {
        $args = $this->parseArgs($args, []);

        $result = Query::select('COUNT(ID)')
            ->from('useronline')
            ->getVar();

        return $result ? $result : 0;
    }

    public function getOnlineVisitorsData($args = [])
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
            'CAST(version AS SIGNED) as version',
            'location',
            'region',
            'city',
            'user_id',
            'page_id as last_page',
            'date as last_view',
            'users.display_name',
            'users.user_email'
        ])
            ->from('useronline')
            ->join('users', ['useronline.user_id', 'users.ID'], [], 'LEFT')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class)
            ->getAll();

        return $result ? $result : [];
    }
}