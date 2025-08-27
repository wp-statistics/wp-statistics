<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Utils\Query;
use WP_STATISTICS\Visitor;

class OnlineModel extends BaseModel
{
    protected $timeframe;

    /**
     * @param array $args
     *      - timeframe: int Timeframe in minutes. Default: 5 minutes.
     */
    public function __construct($args = [])
    {
        $args = wp_parse_args($args, [
            'timeframe' => 5
        ]);

        $this->timeframe = [
            'from' => DateTime::get('-' . $args['timeframe'] . ' min', 'Y-m-d H:i:s'),
            'to'   => DateTime::get('now', 'Y-m-d H:i:s')
        ];
    }

    public function countOnlines($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_type' => '',
            'resource_id'   => '',
            'page_id'       => '',
            'agent'         => '',
            'platform'      => '',
            'country'       => '',
            'logged_in'     => false
        ]);

        $query = Query::select('COUNT(*)')
            ->from('visitor')
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('agent', '=', $args['agent'])
            ->where('last_page', '=', $args['page_id'])
            ->whereDate('last_view', $this->timeframe);

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
        }

        if (!empty($args['resource_type']) || !empty($args['resource_id'])) {
            $query
                ->join('pages', ['visitor.last_page', 'pages.page_id'])
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id']);
        }

        $result = $query->getVar();

        return $result ? $result : 0;
    }

    public function getOnlineVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'ip'            => '',
            'resource_type' => '',
            'resource_id'   => '',
            'page_id'       => '',
            'agent'         => '',
            'platform'      => '',
            'country'       => '',
            'logged_in'     => false,
            'page'          => 1,
            'per_page'      => '',
            'order_by'      => 'last_view',
            'order'         => 'DESC',
        ]);

        $query = Query::select('*')
            ->from('visitor')
            ->where('ip', '=', $args['ip'])
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('agent', '=', $args['agent'])
            ->where('last_page', '=', $args['page_id'])
            ->whereDate('last_view', $this->timeframe)
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class);

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
        }

        if (!empty($args['resource_type']) || !empty($args['resource_id'])) {
            $query
                ->join('pages', ['visitor.last_page', 'pages.page_id'])
                ->where('pages.type', '=', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id']);
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getActivePages($args = [])
    {
        $args = $this->parseArgs($args, [
            // ...
        ]);

        $result = Query::select(['page_id', 'COUNT(DISTINCT visitor_id) as visitors', 'COUNT(*) as views'])
            ->from('visitor_relationships')
            ->whereDate('date', $this->timeframe)
            ->groupBy('page_id')
            ->getAll();

        return $result ?? [];
    }
}