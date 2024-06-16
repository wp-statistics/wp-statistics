<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class VisitorsModel extends BaseModel
{

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
            'order_by'  => 'visitors',
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
                'COUNT(visitor_relationships.page_id) as views'
            ])
            ->from('visitor')
            ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->whereNotNull($args['not_null'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }
}