<?php

namespace WP_Statistics\Models;
use WP_Statistics\Utils\Query;
use WP_Statistics\Components\BaseModel;


class PagesModel extends BaseModel
{

    public function countViews($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => ''
        ]);

        $query = Query::select('SUM(count) as total_views')
            ->from('pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->groupBy('type')
            ->bypassCache($bypassCache);

        // If we have multiple post types, we need to sum the total count of all post types
        if (is_array($args['post_type']) && count($args['post_type']) > 1) {
            $subQuery = $query->getQuery();
            
            $query = Query::select('SUM(total_views)')
                ->fromQuery($subQuery);
        }

        $total = $query->getVar();

        return $total ? $total : 0;
    }

}