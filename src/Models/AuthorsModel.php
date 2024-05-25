<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;

class AuthorsModel extends DataProvider
{
    /**
     * Counts the authors based on the given arguments.
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @param bool $bypassCache Flag to bypass the cache.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function countAuthors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type()
        ]);

        return Query::select('COUNT(DISTINCT post_author)')
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getVar();
    }

}