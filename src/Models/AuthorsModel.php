<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Service\Posts\WordCount;
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

    
    public function topPublishingAuthors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'limit'     => 5
        ]);

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(posts.ID) as post_count'])
            ->fromTable('posts')
            ->join('users', ['post_author', 'ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->limit($args['limit'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function topViewingAuthors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'limit'     => 5
        ]);

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'SUM(pages.count) as views'])
            ->fromTable('posts')
            ->join('users', ['post_author', 'ID'])
            ->join('pages', ['ID', 'id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('views')
            ->limit($args['limit'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByCommentsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'limit'     => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'COUNT(comments.comment_ID) / COUNT(DISTINCT posts.ID) AS average_comments'
            ])
            ->fromTable('posts')
            ->join('users', ['post_author', 'ID'])
            ->join('comments', ['ID', 'comment_post_ID'], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('average_comments')
            ->limit($args['limit'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByViewsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'limit'     => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(pages.count) AS total_views',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'SUM(pages.count) / COUNT(DISTINCT posts.ID) AS average_views'
            ])
            ->fromTable('posts')
            ->join('users', ['post_author', 'ID'])
            ->join('pages', ['ID', 'id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('average_views')
            ->limit($args['limit'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByWordsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'limit'     => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(postmeta.meta_value) / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->fromTable('posts')
            ->join('users', ['post_author', 'ID'])
            ->join('postmeta', ['ID', 'post_id'])
            ->where('meta_key', '=', WordCount::WORDS_COUNT_META_KEY)
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->limit($args['limit'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

}