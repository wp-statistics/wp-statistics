<?php

namespace WP_Statistics\Models;
use WP_Statistics\Utils\Query;


class ViewsModel extends DataProvider
{

    public function averageViewsPerPost($args, $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => '',
        ]);

        $totalPosts = Query::select('COUNT(ID)')
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache) 
            ->getVar();

        $totalViews     = $this->count($args);
        $averageViews   = $totalPosts ? ($totalViews / $totalPosts) : 0;

        return $averageViews;
    }


    public function count($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => ''
        ]);

        $query = Query::select('SUM(count) as total_count')
            ->fromTable('pages')
            ->where('type', 'IN', $args['post_type'])
            ->whereDate('date', [$args['from'], $args['to']])
            ->groupBy('type')
            ->bypassCache($bypassCache);

        // If we have multiple post types, we need to sum the total count of all post types
        if (is_array($args['post_type']) && count($args['post_type']) > 1) {
            $query = Query::select('SUM(total_count)')
                ->fromQuery($query->getQuery());
        }

        return $query->getVar();
    }

}