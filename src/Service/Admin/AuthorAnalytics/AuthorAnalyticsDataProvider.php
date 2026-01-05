<?php

namespace WP_Statistics\Service\Admin\AuthorAnalytics;

use WP_STATISTICS\Helper;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Query;

/**
 * Data provider for author analytics page.
 *
 * Provides data for:
 * - Authors performance overview (glance data)
 * - Authors report data
 * - Single author data
 * - Chart data for author analytics
 *
 * @since 15.0.0 Refactored to use AnalyticsQueryHandler instead of legacy models.
 */
class AuthorAnalyticsDataProvider
{
    /**
     * Query arguments.
     *
     * @var array
     */
    protected $args;

    /**
     * Analytics query handler instance.
     *
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    /**
     * Constructor.
     *
     * @param array $args Query arguments.
     */
    public function __construct($args)
    {
        $this->args         = $args;
        $this->queryHandler = new AnalyticsQueryHandler(false);
    }

    /**
     * Get the date range from args in proper format for AnalyticsQueryHandler.
     *
     * @param array|null $dateOverride Optional date override.
     * @return array Array with 'from' and 'to' keys.
     */
    protected function getDateRange($dateOverride = null)
    {
        $date = $dateOverride ?? ($this->args['date'] ?? []);

        if (is_array($date) && isset($date['from'], $date['to'])) {
            return $date;
        }

        // Default to last 30 days if no date is set
        return DateRange::get('30days');
    }

    /**
     * Get visitors count using AnalyticsQueryHandler.
     *
     * @param array $args Query arguments.
     * @return int Visitors count.
     */
    protected function countVisitors($args = [])
    {
        $date    = $this->getDateRange($args['date'] ?? null);
        $filters = [];

        if (!empty($args['author_id'])) {
            $filters['author_id'] = $args['author_id'];
        }

        if (!empty($args['post_type'])) {
            $filters['post_type'] = $args['post_type'];
        }

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $date['from'],
            'date_to'   => $date['to'],
            'filters'   => $filters,
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['visitors'] ?? 0);
    }

    /**
     * Get views count using AnalyticsQueryHandler.
     *
     * @param array $args Query arguments.
     * @return int Views count.
     */
    protected function countViews($args = [])
    {
        $date    = $this->getDateRange($args['date'] ?? null);
        $filters = [];

        if (!empty($args['author_id'])) {
            $filters['author_id'] = $args['author_id'];
        }

        if (!empty($args['post_type'])) {
            $filters['post_type'] = $args['post_type'];
        }

        $result = $this->queryHandler->handle([
            'sources'   => ['views'],
            'date_from' => $date['from'],
            'date_to'   => $date['to'],
            'filters'   => $filters,
            'format'    => 'flat',
        ]);

        return intval($result['data']['totals']['views'] ?? 0);
    }

    /**
     * Count posts within date range.
     *
     * @param array $args Query arguments.
     * @return int Posts count.
     */
    protected function countPosts($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';

        $query = Query::select('COUNT(*)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->where('post_author', '=', $authorId)
            ->whereDate('post_date', $date);

        $result = $query->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Count authors within date range.
     *
     * @param array $args Query arguments.
     * @return int Authors count.
     */
    protected function countAuthors($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();

        $result = Query::select('COUNT(DISTINCT post_author)')
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Count comments within date range.
     *
     * @param array $args Query arguments.
     * @return int Comments count.
     */
    protected function countComments($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';

        $query = Query::select('COUNT(comment_ID)')
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->where('post_author', '=', $authorId)
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->whereDate('comment_date', $date);

        $result = $query->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Count words within date range.
     *
     * @param array $args Query arguments.
     * @return int Words count.
     */
    protected function countWords($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';

        $wordsCountMetaKey = WordCountService::WORDS_COUNT_META_KEY;

        $query = Query::select('SUM(meta_value)')
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->where('post_author', '=', $authorId)
            ->where('meta_key', '=', $wordsCountMetaKey)
            ->whereDate('post_date', $date);

        $result = $query->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Get authors by post publishes.
     *
     * @param array $args Query arguments.
     * @return array Authors data.
     */
    protected function getAuthorsByPostPublishes($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $result = Query::select(['DISTINCT post_author as id', 'display_name as name', 'COUNT(*) as post_count'])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->groupBy('posts.post_author')
            ->orderBy('post_count')
            ->perPage($page, $perPage)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Get top viewing authors.
     *
     * @param array $args Query arguments.
     * @return array Authors data.
     */
    protected function getTopViewingAuthors($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $orderBy  = $args['order_by'] ?? 'total_views';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $result = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'SUM(pages.count) AS total_views'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('pages', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('date', $date)
            ->groupBy('post_author')
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Get authors by comments per post.
     *
     * @param array $args Query arguments.
     * @return array Authors data.
     */
    protected function getAuthorsByCommentsPerPost($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $result = Query::select([
                'DISTINCT posts.post_author AS id',
                'display_name AS name',
                'COUNT(comments.comment_ID) / COUNT(DISTINCT posts.ID) AS average_comments'
            ])
            ->from('posts')
            ->join('users', ['posts.post_author', 'users.ID'])
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->groupBy('post_author')
            ->orderBy('average_comments')
            ->perPage($page, $perPage)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Get authors by views per post.
     *
     * @param array $args Query arguments.
     * @return array Authors data.
     */
    protected function getAuthorsByViewsPerPost($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $orderBy  = $args['order_by'] ?? 'average_views';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

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
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->groupBy('post_author')
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Get authors by words per post.
     *
     * @param array $args Query arguments.
     * @return array Authors data.
     */
    protected function getAuthorsByWordsPerPost($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

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
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->groupBy('post_author')
            ->orderBy('average_words')
            ->perPage($page, $perPage)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Fetch authors report data from database.
     *
     * @param array $args Query arguments.
     * @return array Authors report data.
     */
    protected function fetchAuthorsReportData($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $orderBy  = $args['order_by'] ?? 'total_views';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $commentsQuery  = Query::select(['DISTINCT post_author', 'COUNT(comment_ID) AS total_comments'])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('post_date', $date)
            ->groupBy('post_author')
            ->getQuery();

        $viewsQuery = Query::select(['DISTINCT post_author', 'SUM(count) AS total_views'])
            ->from('posts')
            ->join('pages', ['posts.ID', 'pages.id'])
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->whereDate('pages.date', $date)
            ->groupBy('post_author')
            ->getQuery();

        $authorQuery = Query::select(['post_author'])
            ->from('posts')
            ->where('post_status', '=', 'publish')
            ->where('post_type', 'IN', $postType)
            ->groupBy('post_author')
            ->getQuery();

        $fields = [
            'users.ID AS id',
            'users.display_name AS name',
            'COUNT(DISTINCT posts.ID) AS total_posts',
            'comments.total_comments AS total_comments',
            'views.total_views AS total_views',
            'comments.total_comments / COUNT(DISTINCT posts.ID) AS average_comments',
            'views.total_views / COUNT(DISTINCT posts.ID) AS average_views'
        ];

        if (WordCountService::isActive()) {
            $fields[] = 'words.total_words AS total_words';
            $fields[] = 'words.total_words / COUNT(DISTINCT posts.ID) AS average_words';
        }

        $query = Query::select($fields)
            ->from('users')
            ->join(
                'posts',
                ['users.ID', 'posts.post_author'],
                [
                    ['posts.post_status', '=', 'publish'],
                    ['posts.post_type', 'IN', $postType],
                    ['DATE(posts.post_date)', 'BETWEEN', [$date['from'], $date['to']]]
                ],
                'LEFT'
            )
            ->joinQuery($authorQuery, ['users.ID', 'authors.post_author'], 'authors')
            ->joinQuery($commentsQuery, ['users.ID', 'comments.post_author'], 'comments', 'LEFT')
            ->joinQuery($viewsQuery, ['users.ID', 'views.post_author'], 'views', 'LEFT')
            ->groupBy(['users.ID', 'users.display_name'])
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage);

        if (WordCountService::isActive()) {
            $wordsQuery = Query::select(['DISTINCT post_author', 'SUM(meta_value) AS total_words'])
                ->from('posts')
                ->join('postmeta', ['posts.ID', 'postmeta.post_id'])
                ->where('postmeta.meta_key', '=', 'wp_statistics_words_count')
                ->where('post_status', '=', 'publish')
                ->where('post_type', 'IN', $postType)
                ->whereDate('post_date', $date)
                ->groupBy('post_author')
                ->getQuery();

            $query->joinQuery($wordsQuery, ['users.ID', 'words.post_author'], 'words', 'LEFT');
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Get taxonomies data for an author.
     *
     * @param array $args Query arguments.
     * @return array Taxonomies data.
     */
    protected function getTaxonomiesData($args = [])
    {
        $date       = $this->getDateRange($args['date'] ?? null);
        $postType   = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId   = $args['author_id'] ?? '';
        $taxonomies = array_keys(Helper::get_list_taxonomy(true));
        $page       = $args['page'] ?? 1;
        $perPage    = $args['per_page'] ?? 5;

        $categoryViewsQuery = Query::select(['id', 'date', 'SUM(count) AS views'])
            ->from('pages')
            ->where('type', 'IN', $taxonomies)
            ->whereDate('date', $date)
            ->groupBy('id')
            ->getQuery();

        $query = Query::select([
                'terms.term_id AS term_id',
                'terms.name AS name',
                'term_taxonomy.taxonomy AS taxonomy',
                'COUNT(DISTINCT posts.ID) AS post_count',
                'COALESCE(SUM(category_views.views), 0) AS views'
            ])
            ->from('term_taxonomy')
            ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
            ->join('term_relationships', ['term_taxonomy.term_taxonomy_id', 'term_relationships.term_taxonomy_id'])
            ->join('posts', ['term_relationships.object_id', 'posts.ID'])
            ->joinQuery($categoryViewsQuery, ['terms.term_id', 'category_views.id'], 'category_views', 'LEFT')
            ->where('term_taxonomy.taxonomy', 'IN', $taxonomies)
            ->where('posts.post_status', '=', 'publish')
            ->where('posts.post_type', 'IN', $postType)
            ->where('posts.post_author', '=', $authorId)
            ->groupBy(['terms.term_id', 'term_taxonomy.taxonomy'])
            ->orderBy(['term_taxonomy.taxonomy', 'post_count'])
            ->perPage($page, $perPage);

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Get posts views data.
     *
     * @param array $args Query arguments.
     * @return array Posts views data.
     */
    protected function getPostsViewsData($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';
        $orderBy  = $args['order_by'] ?? 'views';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $viewsQuery = Query::select(['id', 'SUM(count) AS views'])
            ->from('pages')
            ->whereDate('date', $date)
            ->groupBy('id')
            ->getQuery();

        $result = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            'posts.post_date',
            'COALESCE(pages.views, 0) AS views',
        ])
            ->from('posts')
            ->joinQuery($viewsQuery, ['posts.ID', 'pages.id'], 'pages', 'INNER')
            ->where('post_type', 'IN', $postType)
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $authorId)
            ->groupBy('posts.ID')
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage)
            ->getAll();

        return $result ?? [];
    }

    /**
     * Get posts comments data.
     *
     * @param array $args Query arguments.
     * @return array Posts comments data.
     */
    protected function getPostsCommentsData($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';
        $orderBy  = $args['order_by'] ?? 'comments';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $result = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            'COALESCE(COUNT(comment_ID), 0) AS comments',
        ])
            ->from('posts')
            ->join('comments', ['posts.ID', 'comments.comment_post_ID'])
            ->where('post_type', 'IN', $postType)
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $authorId)
            ->where('comments.comment_type', '=', 'comment')
            ->where('comments.comment_approved', '=', '1')
            ->whereDate('posts.post_date', $date)
            ->groupBy('posts.ID')
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage)
            ->getAll();

        return $result ?? [];
    }

    /**
     * Get posts words data.
     *
     * @param array $args Query arguments.
     * @return array Posts words data.
     */
    protected function getPostsWordsData($args = [])
    {
        $date     = $this->getDateRange($args['date'] ?? null);
        $postType = $args['post_type'] ?? Helper::get_list_post_type();
        $authorId = $args['author_id'] ?? '';
        $orderBy  = $args['order_by'] ?? 'words';
        $order    = $args['order'] ?? 'DESC';
        $page     = $args['page'] ?? 1;
        $perPage  = $args['per_page'] ?? 5;

        $result = Query::select([
            'posts.ID',
            'posts.post_author',
            'posts.post_title',
            "MAX(CASE WHEN postmeta.meta_key = 'wp_statistics_words_count' THEN postmeta.meta_value ELSE 0 END) AS words",
        ])
            ->from('posts')
            ->join('postmeta', ['posts.ID', 'postmeta.post_id'], [], 'LEFT')
            ->where('post_type', 'IN', $postType)
            ->where('post_status', '=', 'publish')
            ->where('posts.post_author', '=', $authorId)
            ->whereDate('posts.post_date', $date)
            ->groupBy('posts.ID')
            ->orderBy($orderBy, $order)
            ->perPage($page, $perPage)
            ->getAll();

        return $result ?? [];
    }

    /**
     * Get visitors summary using AnalyticsQueryHandler.
     *
     * @param array $args Query arguments.
     * @return array Visitors summary by period.
     */
    protected function getVisitorsSummary($args = [])
    {
        $periods = [
            'today'      => ['label' => esc_html__('Today', 'wp-statistics'), 'date' => 'today'],
            'yesterday'  => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'date' => 'yesterday'],
            'this_week'  => ['label' => esc_html__('This week', 'wp-statistics'), 'date' => 'this_week'],
            'last_week'  => ['label' => esc_html__('Last week', 'wp-statistics'), 'date' => 'last_week'],
            'this_month' => ['label' => esc_html__('This month', 'wp-statistics'), 'date' => 'this_month'],
            'last_month' => ['label' => esc_html__('Last month', 'wp-statistics'), 'date' => 'last_month'],
            '7days'      => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'date' => '7days'],
            '30days'     => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'date' => '30days'],
            '90days'     => ['label' => esc_html__('Last 90 days', 'wp-statistics'), 'date' => '90days'],
            '6months'    => ['label' => esc_html__('Last 6 months', 'wp-statistics'), 'date' => '6months'],
            'this_year'  => ['label' => esc_html__('This year (Jan-Today)', 'wp-statistics'), 'date' => 'this_year'],
        ];

        $summary = [];
        $filters = [];

        if (!empty($args['author_id'])) {
            $filters['author_id'] = $args['author_id'];
        }

        if (!empty($args['post_type'])) {
            $filters['post_type'] = $args['post_type'];
        }

        foreach ($periods as $key => $period) {
            $dateRange = DateRange::get($period['date']);

            $result = $this->queryHandler->handle([
                'sources'   => ['visitors'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'filters'   => $filters,
                'format'    => 'flat',
            ]);

            $summary[$key] = [
                'label'    => $period['label'],
                'visitors' => intval($result['data']['totals']['visitors'] ?? 0)
            ];
        }

        return $summary;
    }

    /**
     * Get views summary using AnalyticsQueryHandler.
     *
     * @param array $args Query arguments.
     * @return array Views summary by period.
     */
    protected function getViewsSummary($args = [])
    {
        $periods = [
            'today'      => ['label' => esc_html__('Today', 'wp-statistics'), 'date' => 'today'],
            'yesterday'  => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'date' => 'yesterday'],
            'this_week'  => ['label' => esc_html__('This week', 'wp-statistics'), 'date' => 'this_week'],
            'last_week'  => ['label' => esc_html__('Last week', 'wp-statistics'), 'date' => 'last_week'],
            'this_month' => ['label' => esc_html__('This month', 'wp-statistics'), 'date' => 'this_month'],
            'last_month' => ['label' => esc_html__('Last month', 'wp-statistics'), 'date' => 'last_month'],
            '7days'      => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'date' => '7days'],
            '30days'     => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'date' => '30days'],
            '90days'     => ['label' => esc_html__('Last 90 days', 'wp-statistics'), 'date' => '90days'],
            '6months'    => ['label' => esc_html__('Last 6 months', 'wp-statistics'), 'date' => '6months'],
            'this_year'  => ['label' => esc_html__('This year (Jan-Today)', 'wp-statistics'), 'date' => 'this_year'],
        ];

        $summary = [];
        $filters = [];

        if (!empty($args['author_id'])) {
            $filters['author_id'] = $args['author_id'];
        }

        if (!empty($args['post_type'])) {
            $filters['post_type'] = $args['post_type'];
        }

        foreach ($periods as $key => $period) {
            $dateRange = DateRange::get($period['date']);

            $result = $this->queryHandler->handle([
                'sources'   => ['views'],
                'date_from' => $dateRange['from'],
                'date_to'   => $dateRange['to'],
                'filters'   => $filters,
                'format'    => 'flat',
            ]);

            $summary[$key] = [
                'label' => $period['label'],
                'views' => intval($result['data']['totals']['views'] ?? 0)
            ];
        }

        return $summary;
    }

    /**
     * Get visitors geo data using AnalyticsQueryHandler.
     *
     * @param array $args Query arguments.
     * @return array Visitors geo data.
     */
    protected function getVisitorsGeoData($args = [])
    {
        $date    = $this->getDateRange($args['date'] ?? null);
        $perPage = $args['per_page'] ?? 10;
        $filters = [];

        if (!empty($args['author_id'])) {
            $filters['author_id'] = $args['author_id'];
        }

        if (!empty($args['post_type'])) {
            $filters['post_type'] = $args['post_type'];
        }

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['country'],
            'date_from' => $date['from'],
            'date_to'   => $date['to'],
            'filters'   => $filters,
            'format'    => 'table',
            'per_page'  => $perPage,
        ]);

        $geoData = [];
        if (!empty($result['data']['rows'])) {
            foreach ($result['data']['rows'] as $row) {
                $geoData[] = (object) [
                    'country'  => $row['country'] ?? '',
                    'visitors' => intval($row['visitors'] ?? 0)
                ];
            }
        }

        return $geoData;
    }

    /**
     * Get authors performance data.
     *
     * Returns overview data including counts, percentage changes,
     * and top authors by various metrics.
     *
     * @return array Authors performance data.
     */
    public function getAuthorsPerformanceData()
    {
        $currentArgs = $this->args;
        $prevArgs    = array_merge($this->args, ['date' => DateRange::getPrevPeriod()]);

        $posts     = $this->countPosts($currentArgs);
        $prevPosts = $this->countPosts($prevArgs);

        $authors     = $this->countAuthors($currentArgs);
        $prevAuthors = $this->countAuthors($prevArgs);

        $visitors     = $this->countVisitors($currentArgs);
        $prevVisitors = $this->countVisitors($prevArgs);

        $views     = $this->countViews($currentArgs);
        $prevViews = $this->countViews($prevArgs);

        $comments        = $this->countComments($currentArgs);
        $prevComments    = $this->countComments($prevArgs);
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

        $topPublishingAuthors = $this->getAuthorsByPostPublishes($currentArgs);
        $topViewingAuthors    = $this->getTopViewingAuthors($currentArgs);
        $topAuthorsByComment  = $this->getAuthorsByCommentsPerPost($currentArgs);
        $topAuthorsByViews    = $this->getAuthorsByViewsPerPost($currentArgs);

        $result = [
            'glance'  => [
                'authors' => [
                    'value'  => $authors,
                    'change' => Helper::calculatePercentageChange($prevAuthors, $authors)
                ],
                'posts' => [
                    'value'  => $posts,
                    'change' => Helper::calculatePercentageChange($prevPosts, $posts)
                ],
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'views' => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'comments'  => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments)
                ],
                'comments_avg' => [
                    'value'  => $avgComments,
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'top_publishing'    => $topPublishingAuthors,
            'top_viewing'       => $topViewingAuthors,
            'top_by_comments'   => $topAuthorsByComment,
            'top_by_views'      => $topAuthorsByViews
        ];

        if (WordCountService::isActive()) {
            $words    = $this->countWords($currentArgs);
            $avgWords = Helper::divideNumbers($words, $posts);

            $topAuthorsByWords = $this->getAuthorsByWordsPerPost($currentArgs);

            $result['top_by_words']        = $topAuthorsByWords;
            $result['glance']['words']     = ['value' => $words];
            $result['glance']['words_avg'] = ['value' => $avgWords];
        }

        return $result;
    }

    /**
     * Get authors report data.
     *
     * Returns paginated list of authors with their stats.
     *
     * @return array Authors report data with authors list and total count.
     */
    public function getAuthorsReportData()
    {
        $authors = $this->fetchAuthorsReportData($this->args);
        $total   = $this->countAuthors($this->args);

        return [
            'authors'   => $authors,
            'total'     => $total
        ];
    }

    /**
     * Get author single chart data.
     *
     * Returns chart data for a single author's page including
     * OS distribution, browser distribution, and publish overview.
     *
     * @return array Chart data for single author.
     */
    public function getAuthorSingleChartData()
    {
        $platformDataProvider           = ChartDataProviderFactory::platformCharts($this->args);
        $publishOverviewDataProvider    = ChartDataProviderFactory::publishOverview(
            Helper::filterArrayByKeys($this->args, ['post_type', 'author_id'])
        );

        $data = [
            'os_chart_data'         => $platformDataProvider->getOsData(),
            'browser_chart_data'    => $platformDataProvider->getBrowserData(),
            'publish_chart_data'    => $publishOverviewDataProvider->getData()
        ];

        return $data;
    }

    /**
     * Get authors chart data.
     *
     * Returns chart data for all authors including publish overview
     * and views per posts distribution.
     *
     * @return array Chart data for authors overview.
     */
    public function getAuthorsChartData()
    {
        $authorsPostViewsDataProvider   = ChartDataProviderFactory::authorsPostViews(array_merge($this->args, ['per_page' => -1]));
        $publishOverviewDataProvider    = ChartDataProviderFactory::publishOverview(
            Helper::filterArrayByKeys($this->args, ['post_type', 'author_id'])
        );

        $data = [
            'publish_chart_data'         => $publishOverviewDataProvider->getData(),
            'views_per_posts_chart_data' => $authorsPostViewsDataProvider->getData()
        ];

        return $data;
    }

    /**
     * Get single author data.
     *
     * Returns comprehensive data for a single author including:
     * - Glance metrics (posts, views, visitors, comments)
     * - Top posts by views and comments
     * - Visit summary across different time periods
     * - Visitor country distribution
     * - Taxonomies data
     *
     * @return array Single author data.
     */
    public function getAuthorSingleData()
    {
        $currentArgs = $this->args;
        $prevArgs    = array_merge($this->args, ['date' => DateRange::getPrevPeriod()]);

        $views     = $this->countViews($currentArgs);
        $prevViews = $this->countViews($prevArgs);

        $posts     = $this->countPosts($currentArgs);
        $prevPosts = $this->countPosts($prevArgs);

        $comments        = $this->countComments($currentArgs);
        $prevComments    = $this->countComments($prevArgs);
        $avgComments     = Helper::divideNumbers($comments, $posts);
        $prevAvgComments = Helper::divideNumbers($prevComments, $prevPosts);

        $visitors     = $this->countVisitors($currentArgs);
        $prevVisitors = $this->countVisitors($prevArgs);

        $taxonomies        = $this->getTaxonomiesData($currentArgs);
        $topPostsByView    = $this->getPostsViewsData($currentArgs);
        $topPostsByComment = $this->getPostsCommentsData($currentArgs);

        $visitorsSummary = $this->getVisitorsSummary($currentArgs);
        $viewsSummary    = $this->getViewsSummary($currentArgs);

        $visitorsCountry = $this->getVisitorsGeoData(array_merge($currentArgs, ['per_page' => 10]));

        $data = [
            'glance' => [
                'posts' => [
                    'value'  => $posts,
                    'change' => Helper::calculatePercentageChange($prevPosts, $posts)
                ],
                'views' => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'comments'  => [
                    'value'  => $comments,
                    'change' => Helper::calculatePercentageChange($prevComments, $comments)
                ],
                'comments_avg' => [
                    'value'  => $avgComments,
                    'change' => Helper::calculatePercentageChange($prevAvgComments, $avgComments)
                ]
            ],
            'posts' => [
                'top_views'    => $topPostsByView,
                'top_comments' => $topPostsByComment,
            ],
            'visit_summary'    => array_replace_recursive($visitorsSummary, $viewsSummary),
            'visitors_country' => $visitorsCountry,
            'taxonomies'       => $taxonomies
        ];

        if (WordCountService::isActive()) {
            $words    = $this->countWords($currentArgs);
            $avgWords = Helper::divideNumbers($words, $posts);

            $topPostsByWords = $this->getPostsWordsData($currentArgs);

            $data['glance']['words']     = ['value' => $words];
            $data['glance']['words_avg'] = ['value' => $avgWords];
            $data['posts']['top_words']  = $topPostsByWords;
        }

        return $data;
    }
}
