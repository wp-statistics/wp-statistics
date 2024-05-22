<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;

class AuthorModel extends DataProvider
{
    /**
     * Calculates the average number of posts per author based on the given arguments.
     *
     * @param array $args An array of arguments to filter the count.
     * @param bool $bypassCache Flag to bypass the cache.
     * @return int The average number of posts per author. Returns 0 if no authors are found.
     */
    public function averagePostsPerAuthor(array $args, $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => '',
        ]);

        $query = $this->query::select('COUNT(ID)')
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache) // Use this method to control caching
            ->getFirst();

        $totalPosts   = $query;
        $totalAuthors = $this->count();
        $averagePosts = $totalAuthors ? intdiv($totalPosts, $totalAuthors) : 0;

        return $averagePosts;
    }

    /**
     * Counts the authors based on the given arguments.
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function count($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type()
        ]);

        return $this->query::select('COUNT(DISTINCT post_author)')
            ->fromTable('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->bypassCache($bypassCache)
            ->getCount();
    }

}