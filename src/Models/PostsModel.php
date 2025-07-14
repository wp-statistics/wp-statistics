<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Query;

class PostsModel extends BaseModel
{

    public function countPosts($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'                  => '',
            'post_type'             => Helper::getPostTypes(),
            'author_id'             => '',
            'taxonomy'              => '',
            'term'                  => '',
            'filter_by_view_date'   => false,
            'url'                   => ''
        ]);

        $query = Query::select('COUNT(*)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id']);

        // Count posts within view date
        if ($args['filter_by_view_date'] == true) {
            $viewsQuery = Query::select(['pages.id', 'SUM(pages.count) AS views'])
                ->from('pages')
                ->where('uri', 'LIKE', "%{$args['url']}%")
                ->whereDate('pages.date', $args['date'])
                ->groupBy('pages.id')
                ->getQuery();

            $query->joinQuery($viewsQuery, ['posts.ID', 'views.id'], 'views');
        } else {
            $query
                ->whereDate('post_date', $args['date']);
        }

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

        $result = $query->getVar();

        return $result ? $result : 0;
    }

    public function countDailyPosts($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select('COUNT(*) as posts, Date(post_date) as date')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->whereDate('post_date', $args['date'])
            ->groupBy('Date(post_date)');

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

        return $result;
    }

    public function countWords($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'post_id'   => '',
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $wordsCountMetaKey = WordCountService::WORDS_COUNT_META_KEY;

        $query = Query::select('SUM(meta_value)')
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('posts.ID', '=', $args['post_id'])
            ->where('post_author', '=', $args['author_id'])
            ->where('meta_key', '=', $wordsCountMetaKey);

        // Filter by date when no particular post ID has been set
        if (empty($args['post_id'])) {
            $query
                ->whereDate('post_date', $args['date']);
        }

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

        $result = $query->getVar();

        return $result ? $result : 0;
    }

    public function countComments($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'post_id'   => '',
            'taxonomy'  => '',
            'term'      => ''
        ]);

        $query = Query::select('COUNT(comment_ID)')
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->where('posts.ID', '=', $args['post_id'])
            ->whereDate('comment_date', $args['date']);

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

        $result = $query->getVar();

        return $result ? $result : 0;
    }

    public function getPostsReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => Helper::get_list_post_type(),
            'resource_type' => Helper::get_list_post_type(),
            'order_by'      => 'title',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => 5,
            'author_id'     => '',
            'url'           => ''
        ]);

        $visitorsQuery = Query::select(['pages.id as post_id', 'COUNT(DISTINCT visitor_relationships.visitor_id) AS visitors'])
            ->from('visitor_relationships')
            ->join('pages', ['pages.page_id', 'visitor_relationships.page_id'])
            ->where('type', 'IN', $args['resource_type'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('pages.id')
            ->getQuery();

        $viewsQuery = Query::select(['pages.id', 'SUM(pages.count) AS views'])
            ->from('pages')
            ->where('type', 'IN', $args['resource_type'])
            ->where('uri', 'LIKE', "%{$args['url']}%")
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.id')
            ->getQuery();

        $fields = [
            'posts.ID AS post_id',
            'posts.post_author AS author_id',
            'posts.post_title AS title',
            'posts.post_date AS date',
            'COALESCE(pages.views, 0) AS views',
            'COALESCE(visitors.visitors, 0) AS visitors',
        ];

        if (WordCountService::isActive()) {
            $fields[] = "CAST(MAX(CASE WHEN postmeta.meta_key = 'wp_statistics_words_count' THEN postmeta.meta_value ELSE 0 END) AS UNSIGNED) AS words";
        }

        $query = Query::select($fields)
            ->from('posts')
            ->joinQuery($viewsQuery, ['posts.ID', 'pages.id'], 'pages')
            ->joinQuery($visitorsQuery, ['posts.ID', 'visitors.post_id'], 'visitors', 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy('posts.ID')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (WordCountService::isActive()) {
            $query->join('postmeta', ['posts.ID', 'postmeta.post_id'], [], 'LEFT');
        }

        $result = $query->getAll();

        return $result;
    }

    public function getPostsViewsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => Helper::get_list_post_type(),
            'order_by'      => 'views',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => 5,
            'author_id'     => '',
            'taxonomy'      => '',
            'term'          => '',
            'show_no_views' => false
        ]);

        // Get posts with zero views or not
        $joinType = $args['show_no_views'] ? 'LEFT' : 'INNER';

        $viewsQuery = Query::select(['id', 'SUM(count) AS views'])
            ->from('pages')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $query = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            'posts.post_date',
            'COALESCE(pages.views, 0) AS views',
        ])
            ->from('posts')
            ->joinQuery($viewsQuery, ['posts.ID', 'pages.id'], 'pages', $joinType)
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy('posts.ID')
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

        return $result;
    }

    public function getPostsVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => Helper::get_list_post_type(),
            'post_type'     => Helper::get_list_post_type(),
            'order_by'      => 'visitors',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => 10,
            'author_id'     => '',
            'event_name'    => '',
            'post_ids'      => []
        ]);

        $visitorsQuery = Query::select(['pages.id as post_id', 'COUNT(DISTINCT visitor_relationships.visitor_id) AS visitors'])
            ->from('visitor_relationships')
            ->join('pages', ['pages.page_id', 'visitor_relationships.page_id'])
            ->where('type', 'IN', $args['resource_type'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('pages.id')
            ->getQuery();

        $query = Query::select([
            'posts.ID AS post_id',
            'posts.post_title AS title',
            'COALESCE(visitors.visitors, 0) AS visitors',
        ])
            ->from('posts')
            ->joinQuery($visitorsQuery, ['posts.ID', 'visitors.post_id'], 'visitors', 'LEFT')
            ->where('posts.ID', 'IN', $args['post_ids'])
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->groupBy('posts.ID')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['event_name'])) {
            $query
                ->join('events', ['events.page_id', 'posts.ID'])
                ->where('event_name', 'IN', $args['event_name']);
        }

        return $query->getAll();
    }

    public function getPostsCommentsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'comments',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => '',
            'taxonomy'  => '',
            'term'      => '',
        ]);

        $query = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            'COALESCE(COUNT(comment_ID), 0) AS comments',
        ])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('posts.ID')
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

        return $result;
    }

    public function getPostsWordsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'order_by'  => 'words',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => ''
        ]);

        $result = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            "MAX(CASE WHEN postmeta.meta_key = 'wp_statistics_words_count' THEN postmeta.meta_value ELSE 0 END) AS words",
        ])
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereDate('posts.post_date', $args['date'])
            ->groupBy('posts.ID')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function getInitialPostDate($args = [])
    {
        $args = [
            'post_type' => Helper::getPostTypes()
        ];

        $result = Query::select(['MIN(post_date) AS date'])
            ->from('posts')
            ->where('post_type', 'IN', $args['post_type'])
            ->allowCaching()
            ->getVar();

        return $result;
    }

    public function get404Data($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'order_by'      => 'views',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => 10,
        ]);

        $result = Query::select(['uri', 'SUM(count) AS views'])
            ->from('pages')
            ->where('type', '=', '404')
            ->whereDate('pages.date', $args['date'])
            ->groupBy('uri')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function count404Data($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'page'          => 1,
            'per_page'      => 10,
        ]);

        $result = Query::select(['COUNT(DISTINCT uri)'])
            ->from('pages')
            ->where('type', '=', '404')
            ->whereDate('pages.date', $args['date'])
            ->getVar();

        return $result;
    }
}
