<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Traits\WpCacheTrait;
use WP_Statistics\Utils\Query;

class OnlineModel extends BaseModel
{
    use WpCacheTrait;

    protected $timeframe;

    /**
     * @param array $args
     *      - timeframe: int Timeframe in minutes. Default: 5 minutes.
     */
    public function __construct($args = [])
    {
        $this->setArgs($args);
    }

    /**
     * Set the arguments for the OnlineModel.
     *
     * @return void
     */
    public function setArgs($args = [])
    {
        $args = wp_parse_args($args, [
            'timeframe' => 5
        ]);

        $this->timeframe = [
            'from' => DateTime::get('-' . $args['timeframe'] . ' min', 'Y-m-d H:i:s'),
            'to'   => DateTime::get('now', 'Y-m-d H:i:s')
        ];
    }


    /**
     * Get the number of online visitors.
     *
     * @return int
     */
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
            ->where('last_counter', '=', DateTime::get())
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

        $cacheKey = 'count_online_' . md5(json_encode(array_filter($args)));

        $result = $this->getCachedData($cacheKey, function () use ($query) {
            return $query->getVar();
        }, 3);

        return $result ? $result : 0;
    }

    /**
     * Returns online visitors.
     *
     * @param array $args Arguments to include in query (e.g. `date`, etc.).
     * @return array
     */
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
            'decorate'      => true
        ]);

        $query = Query::select('*')
            ->from('visitor')
            ->where('ip', '=', $args['ip'])
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('agent', '=', $args['agent'])
            ->where('last_page', '=', $args['page_id'])
            ->where('last_counter', '=', DateTime::get())
            ->whereDate('last_view', $this->timeframe)
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order']);

        if ($args['decorate'] === true) {
            $query->decorate(VisitorDecorator::class);
        }

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

    /**
     * Returns a list of active pages with their respective visitors and views.
     *
     * @param array $args Arguments to include in query (e.g. `date`, etc.).
     *
     * @return array
     */
    public function getActivePages($args = [])
    {
        $args = $this->parseArgs($args, [
            'order_by' => 'visitors',
            'order'    => 'DESC'
        ]);

        $result = Query::select(['pages.page_id', 'COUNT(DISTINCT visitor_id) as visitors', 'COUNT(*) as views'])
            ->from('pages')
            ->join('visitor_relationships', ['visitor_relationships.page_id', 'pages.page_id'])
            ->whereDate('pages.date', 'today')
            ->whereDate('visitor_relationships.date', $this->timeframe)
            ->groupBy('pages.page_id')
            ->orderBy($args['order_by'], $args['order'])
            ->getAll();

        return $result ?? [];
    }

    /**
     * Returns the number of visitors per minute
     *
     * @param array $args Arguments to include in query (e.g. `date`, etc.).
     *
     * @return array
     */
    public function getVisitorsPerMinute($args = [])
    {
        $args = $this->parseArgs($args, [
            // ...
        ]);

        $result = Query::select([
                "DATE_FORMAT(visitor_relationships.date, '%%H:%%i') AS time",
                'COUNT(DISTINCT visitor_id) as visitors'
            ])
            ->from('pages')
            ->join('visitor_relationships', ['visitor_relationships.page_id', 'pages.page_id'])
            ->whereDate('pages.date', 'today')
            ->whereDate('visitor_relationships.date', $this->timeframe)
            ->groupBy('time')
            ->getAll();

        return $result;
    }
}