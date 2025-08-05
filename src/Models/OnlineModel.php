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

        $result = Query::select('COUNT(*)')
            ->from('useronline')
            ->getVar();

        return $result ? $result : 0;
    }

    public function getOnlineVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'page'      => 1,
            'per_page'  => '',
            'order_by'  => '',
            'order'     => '',
        ]);

        $result = Query::select([
            'useronline.ID as online_id',
            'visitor_id as ID',
            'useronline.ip',
            'useronline.created',
            'useronline.timestamp',
            'visitor.referred',
            'visitor.agent',
            'visitor.platform',
            'visitor.version',
            'visitor.location',
            'visitor.region',
            'visitor.city',
            'visitor.hits',
            'visitor.source_name',
            'visitor.source_channel',
            'visitor.model',
            'visitor.device',
            'visitor.user_id',
            'visitor.last_counter',
            'visitor.last_page as last_page',
            'visitor.last_view as last_view',
            'users.display_name',
            'users.user_email'
        ])
            ->from('useronline')
            ->join('visitor', ['useronline.visitor_id', 'visitor.ID'])
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class)
            ->getAll();

        return $result ? $result : [];
    }
}