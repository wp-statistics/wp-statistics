<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class VisitorsModel extends BaseModel
{
    /**
     * Returns total number of views from all visitors.
     *
     * @param   array       $args           Arguments to include in query (e.g. `date`).
     * @param   bool        $bypassCache    Send the cached result.
     *
     * @return  int
     */
    public function countTotalViews($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'where_col' => 'ID',
            'where_val' => '',
        ]);

        $result = Query::select('SUM(`hits`) as `views_sum`')
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
            ->whereDate('last_counter', $args['date'])
            ->perPage(1, 1)
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? intval($result) : 0;
    }

    public function countVisitors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'post_id'   => ''
        ]);

        $result = Query::select('COUNT(DISTINCT visitor_id) as total_visitors')
            ->from('visitor_relationships')
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
    }

    /**
     * Returns `COUNT DISTINCT` of a column from visitors table.
     *
     * @param   array       $args           Arguments to include in query (e.g. `count_field`, `date`, etc.).
     * @param   bool        $bypassCache    Send the cached result.
     *
     * @return  int
     */
    public function countColumnDistinct($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'count_field' => 'ID',
            'date'        => '',
        ]);

        $result = Query::select("COUNT(DISTINCT `{$args['count_field']}`) as `total`")
            ->from('visitor')
            ->whereDate('last_counter', $args['date'])
            ->perPage(1, 1)
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Returns visitors' device information.
     *
     * @param   array   $args           Arguments to include in query (e.g. `date`, `group_by`, etc.).
     * @param   bool    $bypassCache    Send the cached result.
     *
     * @return  array
     */
    public function getVisitorsDevices($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'where_col' => 'ID',
            'where_val' => '',
            'group_by'  => [],
            'order_by'  => 'views',
            'order'     => 'DESC',
            'per_page'  => '',
            'page'      => 1
        ]);

        $result = Query::select([
                'agent',
                'platform',
                'version',
                'device',
                'model',
                'SUM(`hits`) as `views`',
            ])
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
            ->whereDate('last_counter', $args['date'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getVisitors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => ''
        ]);

        $result = Query::select([
                'visitor.ID',
                'visitor.platform',
                'visitor.agent',
                'visitor.location'
            ])
            ->from('visitor')
            ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('visitor.ID')
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function countGeoData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'count_field'   => 'location',
            'continent'     => '',
            'country'       => '',
            'region'        => '',
            'city'          => '',
            'not_null'      => ''
        ]);

        $result = Query::select([
                "COUNT(DISTINCT {$args['count_field']}) as total"
            ])
            ->from('visitor')
            ->whereDate('visitor.last_counter', $args['date'])
            ->where('visitor.continent', '=', $args['continent'])
            ->where('visitor.location', '=', $args['country'])
            ->where('visitor.region', '=', $args['region'])
            ->where('visitor.city', '=', $args['city'])
            ->whereNotNull("visitor.{$args['count_field']}")
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
    }

    public function getVisitorsGeoData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'country'   => '',
            'city'      => '',
            'region'    => '',
            'continent' => '',
            'group_by'  => 'visitor.location',
            'not_null'  => '',
            'order_by'  => ['visitors', 'views'],
            'order'     => 'DESC',
            'per_page'  => '',
            'page'      => 1
        ]);

        $result = Query::select([
                'visitor.city as city',
                'visitor.location as country',
                'visitor.region as region',
                'visitor.continent as continent',
                'COUNT(DISTINCT visitor.ID) as visitors',
                'SUM(visitor.hits) as views'
            ])
            ->from('visitor')
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }
}