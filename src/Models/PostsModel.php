<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Utils\Query;

/**
 * Posts data model.
 *
 * Contains both WordPress content queries (countPosts, countDailyPosts, etc.)
 * and analytics queries that delegate to AnalyticsQueryHandler.
 *
 * @deprecated 15.0.0 Analytics methods delegate to AnalyticsQueryHandler.
 *                    WordPress content methods (countPosts, countDailyPosts,
 *                    countComments, getPost, getPostsCommentsData,
 *                    getInitialPostDate) remain for content operations.
 * @see \WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler
 */
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

        $result = $query->getAll();

        return $result;
    }

    /**
     * Get posts with view counts.
     *
     * Delegates to AnalyticsQueryHandler for analytics data.
     *
     * @param array $args Query parameters.
     * @return array List of posts with views.
     */
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

        $handler = new AnalyticsQueryHandler();

        $request = [
            'sources'  => ['views'],
            'group_by' => ['page'],
            'page'     => (int) $args['page'],
            'per_page' => (int) $args['per_page'],
            'format'   => 'flat',
        ];

        // Build filters
        $filters = [];
        if (!empty($args['post_type'])) {
            $postTypes = is_array($args['post_type']) ? $args['post_type'] : [$args['post_type']];
            $filters['post_type'] = ['in' => $postTypes];
        }
        if (!empty($args['author_id'])) {
            $filters['author'] = $args['author_id'];
        }
        if (!empty($filters)) {
            $request['filters'] = $filters;
        }

        // Handle date range
        if (!empty($args['date'])) {
            if (is_array($args['date'])) {
                $request['date_from'] = $args['date']['from'] ?? null;
                $request['date_to']   = $args['date']['to'] ?? null;
            } else {
                $request['date_from'] = $args['date'];
                $request['date_to']   = $args['date'];
            }
        }

        $response = $handler->handle($request);

        // Transform response to match legacy format
        $result = [];
        if (!empty($response['data']['rows'])) {
            foreach ($response['data']['rows'] as $row) {
                $result[] = (object) [
                    'ID'          => $row['page_wp_id'] ?? 0,
                    'post_author' => 0, // Not available from analytics query
                    'post_title'  => $row['page_title'] ?? '',
                    'post_date'   => '', // Not available from analytics query
                    'views'       => $row['views'] ?? 0,
                ];
            }
        }

        // If show_no_views is true and we need taxonomy filtering,
        // fall back to the legacy query for full WordPress data
        if ($args['show_no_views'] || !empty($args['taxonomy']) || !empty($args['term'])) {
            return $this->getPostsViewsDataLegacy($args);
        }

        return $result;
    }

    /**
     * Legacy implementation of getPostsViewsData for complex queries.
     *
     * Used when show_no_views is true or taxonomy/term filtering is needed.
     *
     * @param array $args Query parameters.
     * @return array List of posts with views.
     */
    private function getPostsViewsDataLegacy($args)
    {
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

        return $query->getAll();
    }

    /**
     * Get posts with visitor counts.
     *
     * Delegates to AnalyticsQueryHandler for analytics data.
     *
     * @param array $args Query parameters.
     * @return array List of posts with visitors.
     */
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

        // For complex queries with event_name or post_ids, use legacy implementation
        if (!empty($args['event_name']) || !empty($args['post_ids'])) {
            return $this->getPostsVisitorsDataLegacy($args);
        }

        $handler = new AnalyticsQueryHandler();

        $request = [
            'sources'  => ['visitors'],
            'group_by' => ['page'],
            'page'     => (int) $args['page'],
            'per_page' => (int) $args['per_page'],
            'format'   => 'flat',
        ];

        // Build filters
        $filters = [];
        if (!empty($args['post_type'])) {
            $postTypes = is_array($args['post_type']) ? $args['post_type'] : [$args['post_type']];
            $filters['post_type'] = ['in' => $postTypes];
        }
        if (!empty($args['author_id'])) {
            $filters['author'] = $args['author_id'];
        }
        if (!empty($filters)) {
            $request['filters'] = $filters;
        }

        // Handle date range
        if (!empty($args['date'])) {
            if (is_array($args['date'])) {
                $request['date_from'] = $args['date']['from'] ?? null;
                $request['date_to']   = $args['date']['to'] ?? null;
            } else {
                $request['date_from'] = $args['date'];
                $request['date_to']   = $args['date'];
            }
        }

        $response = $handler->handle($request);

        // Transform response to match legacy format
        $result = [];
        if (!empty($response['data']['rows'])) {
            foreach ($response['data']['rows'] as $row) {
                $result[] = (object) [
                    'post_id'  => $row['page_wp_id'] ?? 0,
                    'title'    => $row['page_title'] ?? '',
                    'visitors' => $row['visitors'] ?? 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Legacy implementation of getPostsVisitorsData for complex queries.
     *
     * Used when event_name or post_ids filtering is needed.
     *
     * @param array $args Query parameters.
     * @return array List of posts with visitors.
     */
    private function getPostsVisitorsDataLegacy($args)
    {
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

    /**
     * Get 404 page data with view counts.
     *
     * Delegates to AnalyticsQueryHandler for consistent analytics access.
     *
     * @param array $args Query parameters.
     * @return array List of 404 pages with views.
     */
    public function get404Data($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'     => '',
            'order_by' => 'views',
            'order'    => 'DESC',
            'page'     => 1,
            'per_page' => 10,
        ]);

        $handler = new AnalyticsQueryHandler();

        $request = [
            'sources'  => ['views'],
            'group_by' => ['page'],
            'filters'  => ['post_type' => '404'],
            'page'     => (int) $args['page'],
            'per_page' => (int) $args['per_page'],
            'format'   => 'flat',
        ];

        // Handle date range
        if (!empty($args['date'])) {
            if (is_array($args['date'])) {
                $request['date_from'] = $args['date']['from'] ?? null;
                $request['date_to']   = $args['date']['to'] ?? null;
            } else {
                $request['date_from'] = $args['date'];
                $request['date_to']   = $args['date'];
            }
        }

        $response = $handler->handle($request);

        // Transform response to match legacy format
        $result = [];
        if (!empty($response['data']['rows'])) {
            foreach ($response['data']['rows'] as $row) {
                $result[] = (object) [
                    'uri'   => $row['page_uri'] ?? '',
                    'views' => $row['views'] ?? 0,
                ];
            }
        }

        return $result;
    }

    /**
     * Count distinct 404 URIs.
     *
     * Delegates to AnalyticsQueryHandler for consistent analytics access.
     *
     * @param array $args Query parameters.
     * @return int Count of distinct 404 pages.
     */
    public function count404Data($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => '',
        ]);

        $handler = new AnalyticsQueryHandler();

        $request = [
            'sources'  => ['views'],
            'group_by' => ['page'],
            'filters'  => ['post_type' => '404'],
            'per_page' => 1000, // Fetch enough to count
            'format'   => 'flat',
        ];

        // Handle date range
        if (!empty($args['date'])) {
            if (is_array($args['date'])) {
                $request['date_from'] = $args['date']['from'] ?? null;
                $request['date_to']   = $args['date']['to'] ?? null;
            } else {
                $request['date_from'] = $args['date'];
                $request['date_to']   = $args['date'];
            }
        }

        $response = $handler->handle($request);

        return $response['data']['total'] ?? 0;
    }

    /**
     * Retrieve a single post record.
     *
     * Returns one row from the `posts` table that matches the given criteria.
     * By default it returns the latest published post of type `post`.
     *
     * @param array $args {
     *     Optional. Query parameters.
     * @type string $post_type Post‑type slug. Default 'post'.
     * @type string $order_by Column used for ordering. Default 'post_date'.
     * @type string $order Sort direction ('ASC' or 'DESC'). Default 'DESC'.
     * }
     * @return object|null The matched post row, or null if none found.
     * @since 15.0.0
     */
    public function getPost($args)
    {
        $args = $this->parseArgs($args, [
            'post_type' => 'post',
            'order_by'  => 'post_date',
            'order'     => 'DESC',
        ]);

        $result = Query::select('*')
            ->from('posts')
            ->where('post_type', '=', $args['post_type'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage(1, 1)
            ->allowCaching()
            ->getRow();

        return $result;
    }
}
