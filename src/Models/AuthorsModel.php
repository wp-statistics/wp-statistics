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
            'page'     => 1,
            'per_page' => 5
        ]);

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(posts.ID) as post_count'])
            ->fromTable('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->perPage($args['page'], $args['per_page'])
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
            'page'     => 1,
            'per_page' => 5
        ]);

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'SUM(pages.count) as views'])
            ->fromTable('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('views')
            ->perPage($args['page'], $args['per_page'])
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
            'page'     => 1,
            'per_page' => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'COUNT(comments.comment_ID) / COUNT(DISTINCT posts.ID) AS average_comments'
            ])
            ->fromTable('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'], [], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('average_comments')
            ->perPage($args['page'], $args['per_page'])
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
            'order_by'  => 'average_views',
            'order'     => 'DESC',
            'page'     => 1,
            'per_page' => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(pages.count) AS total_views',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'SUM(pages.count) / COUNT(DISTINCT posts.ID) AS average_views'
            ])
            ->fromTable('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
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
            'page'     => 1,
            'per_page' => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(postmeta.meta_value) / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->fromTable('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('meta_key', '=', WordCount::WORDS_COUNT_META_KEY)
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', [$args['from'], $args['to']])
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsPerformanceReport($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'from'      => '',
            'to'        => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'average_pages',
            'order'     => 'DESC',
            'page'     => 1,
            'per_page' => 5
        ]);

        $query = "
            SELECT 
                users.ID AS author_id,
                users.display_name AS author_name,
                COUNT(DISTINCT posts.ID) AS total_posts,
                IFNULL(comments.total_comments, 0) AS total_comments,
                IFNULL(visits.total_visits, 0) AS total_visits,
                IFNULL(words.total_words, 0) AS total_words
            FROM 
                wp_users AS users
            LEFT JOIN 
                wp_posts AS posts ON users.ID = posts.post_author AND posts.post_status = 'publish' AND posts.post_type IN ('post', 'page', 'product')
            LEFT JOIN 
                (SELECT post_author, COUNT(comment_ID) AS total_comments
                FROM wp_posts
                JOIN wp_comments ON wp_posts.ID = wp_comments.comment_post_ID
                WHERE wp_posts.post_status = 'publish' AND wp_posts.post_type IN ('post', 'page', 'product')
                GROUP BY post_author) AS comments ON users.ID = comments.post_author
            LEFT JOIN 
                (SELECT post_author, SUM(count) AS total_visits
                FROM wp_posts
                JOIN wp_statistics_pages ON wp_posts.ID = wp_statistics_pages.id
                WHERE wp_posts.post_status = 'publish' AND wp_posts.post_type IN ('post', 'page', 'product')
                GROUP BY post_author) AS visits ON users.ID = visits.post_author
            LEFT JOIN 
                (SELECT post_author, SUM(meta_value) AS total_words
                FROM wp_posts
                JOIN wp_postmeta ON wp_posts.ID = wp_postmeta.post_id
                WHERE wp_postmeta.meta_key = 'wp_statistics_words_count' AND wp_posts.post_status = 'publish' AND wp_posts.post_type IN ('post', 'page', 'product')
                GROUP BY post_author) AS words ON users.ID = words.post_author
            GROUP BY 
                users.ID, users.display_name
            ORDER BY 
                total_posts DESC
            LIMIT 10
        ";

        // $result = Query::raw($query, []);

        $result = [];
        return $result ? $result : [];
    }
}