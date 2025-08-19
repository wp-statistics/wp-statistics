<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Query;

class OnlineModel extends BaseModel
{
    /**
     * Timeframe in which visitors are counted as online. Default: 5 minutes.
     */
    protected $onlineTimeframe;

    public function __construct()
    {
        $this->onlineTimeframe = [
            'from' => DateTime::get('-5 min', 'Y-m-d H:i:s'),
            'to'   => DateTime::get('now', 'Y-m-d H:i:s')
        ];
    }

    public function countOnlines($args = [])
    {
        $args = $this->parseArgs($args, []);

        $result = Query::select('COUNT(*)')
            ->from('visitor')
            ->whereDate('last_view', $this->onlineTimeframe)
            ->getVar();

        return $result ? $result : 0;
    }

    public function getOnlineVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'ip'        => '',
            'page'      => 1,
            'per_page'  => '',
            'order_by'  => 'last_view',
            'order'     => 'DESC',
        ]);

        $result = Query::select('*')
            ->from('visitor')
            ->where('ip', '=', $args['ip'])
            ->whereDate('last_view', $this->onlineTimeframe)
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class)
            ->getAll();

        return $result ? $result : [];
    }
}