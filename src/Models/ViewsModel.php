<?php

namespace WP_Statistics\Models;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class ViewsModel extends BaseModel
{

    public function countViews($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'author_id' => ''
        ]);

        $subQuery = Query::select('SUM(count) as total_views')
            ->from('pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->groupBy('type')
            ->bypassCache($bypassCache)
            ->getQuery();

        $query = Query::select('SUM(total_views)')
            ->fromQuery($subQuery);

        $total = $query->getVar();

        return $total ? $total : 0;
    }

}