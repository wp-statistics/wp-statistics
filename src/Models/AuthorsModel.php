<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Components\BaseModel;
use WP_Statistics\Service\Posts\WordCount;

class AuthorsModel extends BaseModel
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
            'date'      => '',
            'post_type' => Helper::get_list_post_type()
        ]);

        return Query::select('COUNT(DISTINCT post_author)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->bypassCache($bypassCache)
            ->getVar();
    }

    public function getTopViewingAuthors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(pages.count) AS total_views'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('post_author')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }
    
    public function getAuthorsByPostPublishes($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(posts.ID) as post_count'])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByCommentsPerPost($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'COUNT(comments.comment_ID) / COUNT(DISTINCT posts.ID) AS average_comments'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
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
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'average_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(pages.count) AS total_views',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'SUM(pages.count) / COUNT(DISTINCT posts.ID) AS average_views'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
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
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5
        ]);

        $result = Query::select([
                'DISTINCT posts.post_author AS id', 
                'display_name AS name', 
                'SUM(postmeta.meta_value) / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('meta_key', '=', WordCount::WORDS_COUNT_META_KEY)
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsPerformanceData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_posts',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $authorsQuery  = Query::select(['id AS author_id', 'SUM(count) AS total_author_views'])
            ->from('pages')
            ->where('type', '=', 'author')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $commentsQuery  = Query::select(['DISTINCT post_author', 'COUNT(comment_ID) AS total_comments'])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->getQuery();

        $viewsQuery = Query::select(['DISTINCT post_author', 'SUM(count) AS total_views'])
            ->from('posts')
            ->join('pages', ['posts.ID', 'pages.id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->getQuery();

        $wordsQuery = Query::select(['DISTINCT post_author', 'SUM(meta_value) AS total_words'])
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('postmeta.meta_key', '=', 'wp_statistics_words_count')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->getQuery();

        $result = Query::select([
                'users.ID AS id',
                'users.display_name AS name',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'comments.total_comments AS total_comments',
                'views.total_views AS total_views',
                'words.total_words AS total_words',
                'authors.total_author_views AS total_author_views',
                'comments.total_comments / COUNT(DISTINCT posts.ID) AS average_comments',
                'views.total_views / COUNT(DISTINCT posts.ID) AS average_views',
                'words.total_words / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->from('users')
            ->join(
                'posts', 
                ['users.ID', 'posts.post_author'],
                [['posts.post_status', '=', 'publish'], ['posts.post_type', 'IN', $args['post_type']]],
                'LEFT'
            )
            ->joinQuery($commentsQuery, ['users.ID', 'comments.post_author'], 'comments', 'LEFT')
            ->joinQuery($viewsQuery, ['users.ID', 'views.post_author'], 'views', 'LEFT')
            ->joinQuery($wordsQuery, ['users.ID', 'words.post_author'], 'words', 'LEFT')
            ->joinQuery($authorsQuery, ['users.ID', 'authors.author_id'], 'authors', 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }
}