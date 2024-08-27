<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Query;

class AuthorsModel extends BaseModel
{
    /**
     * Counts the authors based on the given arguments.
     * By default, it will return total number of authors.
     *
     * @param array $args An array of arguments to filter the count.
     * @return int The total number of distinct authors. Returns 0 if no authors are found.
     */
    public function countAuthors($args = [])
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
            ->getVar();
    }

    public function getTopViewingAuthors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select([
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
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query
                ->joinQuery($taxQuery, ['posts.ID', 'tax.object_id'], 'tax');
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }
    
    public function getAuthorsByPostPublishes($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'page'      => 1,
            'per_page'  => 5,
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(posts.ID) as post_count'])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $query
                ->join('term_relationships', ['posts.ID', 'term_relationships.object_id'])
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy']);

            if (!empty($args['term'])) {
                $query
                    ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                    ->where('terms.term_id', '=', $args['term']);
            }
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByCommentsPerPost($args = [])
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
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByViewsPerPost($args = [])
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
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsByWordsPerPost($args = [])
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
            ->where('meta_key', '=', WordCountService::WORDS_COUNT_META_KEY)
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'total_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $commentsQuery  = Query::select(['DISTINCT post_author', 'COUNT(comment_ID) AS total_comments'])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
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
            ->whereDate('pages.date', $args['date'])
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

        $authorQuery = Query::select(['post_author'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->groupBy('post_author')
            ->getQuery();

        $result = Query::select([
                'users.ID AS id',
                'users.display_name AS name',
                'COUNT(DISTINCT posts.ID) AS total_posts',
                'comments.total_comments AS total_comments',
                'views.total_views AS total_views',
                'words.total_words AS total_words',
                'comments.total_comments / COUNT(DISTINCT posts.ID) AS average_comments',
                'views.total_views / COUNT(DISTINCT posts.ID) AS average_views',
                'words.total_words / COUNT(DISTINCT posts.ID) AS average_words'
            ])
            ->from('users')
            ->join(
                'posts', 
                ['users.ID', 'posts.post_author'],
                [
                    ['posts.post_status', '=', 'publish'], 
                    ['posts.post_type', 'IN', $args['post_type']], 
                    ['DATE(posts.post_date)', 'BETWEEN', [$args['date']['from'], $args['date']['to']]]
                ],
                'LEFT'
            )
            ->joinQuery($authorQuery, ['users.ID', 'authors.post_author'], 'authors')
            ->joinQuery($commentsQuery, ['users.ID', 'comments.post_author'], 'comments', 'LEFT')
            ->joinQuery($viewsQuery, ['users.ID', 'views.post_author'], 'views', 'LEFT')
            ->joinQuery($wordsQuery, ['users.ID', 'words.post_author'], 'words', 'LEFT')
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    public function getAuthorsPagesData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'page_views',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5
        ]);

        $viewsQuery  = Query::select(['id AS author_id', 'SUM(count) AS total_author_views'])
            ->from('pages')
            ->where('type', '=', 'author')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $authorQuery = Query::select(['post_author'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->groupBy('post_author')
            ->getQuery();

        $result = Query::select([
                'users.ID AS id',
                'users.display_name AS name',
                'COALESCE(COUNT(DISTINCT posts.ID), 0) AS total_posts',
                'COALESCE(views.total_author_views, 0) AS page_views'
            ])
            ->from('users')
            ->join(
                'posts', 
                ['users.ID', 'posts.post_author'],
                [
                    ['posts.post_status', '=', 'publish'], 
                    ['posts.post_type', 'IN', $args['post_type']], 
                    ['DATE(posts.post_date)', 'BETWEEN', [$args['date']['from'], $args['date']['to']]]
                ],
                'LEFT'
            )
            ->joinQuery($authorQuery, ['users.ID', 'authors.post_author'], 'authors')
            ->joinQuery($viewsQuery, ['users.ID', 'views.author_id'], 'views', 'LEFT')
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }
}