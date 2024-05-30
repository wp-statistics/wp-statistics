<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;


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
            ->whereDate('pages.date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
    }

    public function getVisitorsData($args = [], $bypassCache = false)
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

}