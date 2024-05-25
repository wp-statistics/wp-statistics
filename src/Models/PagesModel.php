<?php

namespace WP_Statistics\Models;
use WP_Statistics\Utils\Query;


class PagesModel extends DataProvider
{

    public function countViews($args = [], $bypassCache = false)
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
            $subQuery = $query->getQuery();
            
            $query = Query::select('SUM(total_count)')
                ->fromQuery($subQuery);
        }

        $total = $query->getVar();

        return $total ? $total : 0;
    }

}