<?php

namespace WP_Statistics\Models;

use WP_Statistics\Utils\Query;

class PostModel extends DataProvider
{

    public function count($args, $bypassCache = false)
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

        return $totalPosts ? $totalPosts : 0;
    }
}