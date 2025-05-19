<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations on tracked resources
 * (posts, pages, custom-post-types and other URLs).
 *
 * Provides convenience methods that query the `resources`
 * table and its related data (views, comments, meta, taxonomy, …).
 */
class ResourceModel extends BaseModel
{
    /**
     * Count resources that match the supplied filters.
     *
     * @param array $args {
     * @type string|array $date Y-m-d string or ['from'=>Y-m-d,'to'=>Y-m-d].
     * @type string[] $post_type Resource types (post, page …).
     * @type int|string $author_id Author ID.
     * @type string[] $taxonomy Taxonomy slugs.
     * @type int|string $term Term ID.
     * @type bool $filter_by_view_date Count only if viewed on $date.
     * @type string $url Sub-string of resource URL.
     * }
     * @return int
     * @since 15.0.0
     */
    public function countResources($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'                => '',
            'post_type'           => [],
            'author_id'           => '',
            'taxonomy'            => '',
            'term'                => '',
            'filter_by_view_date' => false,
            'url'                 => '',
        ]);

        // Core SELECT
        $query = Query::select('COUNT(*)')
            ->from('resources')
            ->where('resource_type', 'IN', $args['post_type'])
            ->where('is_deleted', '=', 0)
            ->where('cached_author_id', '=', $args['author_id']);

        // URL filter (slug/URI part)
        if (!empty($args['url'])) {
            $query->where('resource_url', 'LIKE', "%{$args['url']}%");
        }

        // Filter by the date the post was *viewed*
        if ($args['filter_by_view_date']) {
            $views = Query::select(['resource_id'])
                ->from('views')
                ->whereDate('viewed_at', $args['date'])
                ->getQuery();

            $query->joinQuery($views, ['resources.ID', 'v.resource_id'], 'v');
        } elseif (!empty($args['date'])) {
            // Otherwise use the post’s own cached date
            $query->whereDate('cached_date', $args['date']);
        }

        // Taxonomy / term filter requires the classic WP tables
        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        $count = $query->getVar();

        return $count ? (int)$count : 0;
    }

    /**
     * Return daily totals of published resources.
     *
     * @param array $args {
     * @type string|array $date Day or range.
     * @type string[] $post_type
     * @type int|string $author_id
     * @type string[] $taxonomy
     * @type int|string $term
     * }
     * @return array[] Each row has keys `date` and `posts`.
     * @since 15.0.0
     */
    public function countDailyResources($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'author_id' => '',
            'taxonomy'  => '',
            'term'      => '',
        ]);

        $query = Query::select([
            'DATE(cached_date) AS date',
            'COUNT(*)          AS posts',
        ])
            ->from('resources')
            ->where('resource_type', 'IN', $args['post_type'])
            ->where('is_deleted', '=', 0)
            ->where('cached_author_id', '=', $args['author_id'])
            ->whereDate('cached_date', $args['date'])
            ->groupBy('DATE(cached_date)');

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return $query->getAll() ?: [];
    }

    /**
     * Calculate total words for resources matching the filters.
     *
     * @param array $args {
     * @type string|array $date
     * @type string[] $post_type
     * @type int|string $author_id
     * @type int|string $post_id
     * @type string[] $taxonomy
     * @type int|string $term
     * }
     * @return int
     * @since 15.0.0
     */
    public function countWords($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'author_id' => '',
            'post_id'   => '',
            'taxonomy'  => '',
            'term'      => '',
        ]);

        $wordsKey = WordCountService::WORDS_COUNT_META_KEY;

        $query = Query::select('SUM(postmeta.meta_value)')
            ->from('resources')
            ->join('postmeta', ['resources.resource_id', 'postmeta.post_id'])
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.is_deleted', '=', 0)
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->where('postmeta.meta_key', '=', $wordsKey)
            ->where('resources.resource_id', '=', $args['post_id']);

        /* filter by date only when no specific post is requested */
        if (empty($args['post_id'])) {
            $query->whereDate('resources.cached_date', $args['date']);
        }

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return (int)($query->getVar() ?: 0);
    }

    /**
     * Count approved comments for resources that match the filters.
     *
     * @param array $args See {@see countWords()} for accepted keys.
     * @return int
     * @since 15.0.0
     */
    public function countComments($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'author_id' => '',
            'post_id'   => '',
            'taxonomy'  => '',
            'term'      => '',
        ]);

        $query = Query::select('COUNT(comments.comment_ID)')
            ->from('resources')
            ->join('comments', ['resources.resource_id', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->where('resources.resource_id', '=', $args['post_id'])
            ->whereDate('resources.cached_date', $args['date']);

        if (!empty($args['taxonomy']) || !empty($args['term'])) {
            $taxQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return (int)($query->getVar() ?: 0);
    }

    /**
     * Build a report containing views, visitors, comments and word-count for each resource.
     *
     * @param array $args {
     * @type string|array $date
     * @type string[] $post_type
     * @type string $order_by title|views|visitors|comments|words
     * @type string $order ASC|DESC
     * @type int $page
     * @type int $per_page
     * @type int|string $author_id
     * @type string $url
     * }
     * @return array[]
     * @since 15.0.0
     */
    public function getResourcesReportData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'order_by'  => 'title',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => '',
            'url'       => '',
        ]);

        $start = '';
        $end   = '';
        if (is_array($args['date']) && isset($args['date']['from'], $args['date']['to'])) {
            $start = $args['date']['from'] . ' 00:00:00';
            $end   = $args['date']['to'] . ' 23:59:59';
        } elseif (is_string($args['date']) && $args['date'] !== '') {
            $start = $args['date'] . ' 00:00:00';
            $end   = $args['date'] . ' 23:59:59';
        }

        $commentsSub = Query::select([
            'resources.resource_id AS post_id',
            'COUNT(comments.comment_ID) AS total_comments',
        ])
            ->from('resources')
            ->join('comments', ['resources.resource_id', 'comments.comment_post_ID'])
            ->where('comments.comment_type', '=', 'comment')
            ->whereDate('comments.comment_date', ['from' => $start, 'to' => $end])
            ->groupBy('resources.resource_id')
            ->getQuery();

        $visitorsSub = Query::select([
            'views.resource_id',
            'COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) AS visitors',
        ])
            ->from('views')
            ->join('sessions', ['views.session_id', 'sessions.ID'])
            ->whereDate('views.viewed_at', ['from' => $start, 'to' => $end])
            ->groupBy('views.resource_id')
            ->getQuery();

        $viewsSub = Query::select([
            'views.resource_id',
            'COUNT(*) AS views',
        ])
            ->from('views')
            ->whereDate('views.viewed_at', ['from' => $start, 'to' => $end])
            ->groupBy('views.resource_id')
            ->getQuery();

        $rows = Query::select([
            'resources.resource_id        AS post_id',
            'resources.cached_author_id   AS author_id',
            'resources.cached_title       AS title',
            'resources.cached_date        AS date',
            'COALESCE(vs.views,       0)  AS views',
            'COALESCE(u.visitors,     0)  AS visitors',
            'COALESCE(c.total_comments,0) AS comments',
            "CAST(MAX(CASE WHEN pm.meta_key = 'wp_statistics_words_count'
                               THEN pm.meta_value ELSE 0 END) AS UNSIGNED) AS words",
        ])
            ->from('resources')
            ->joinQuery($commentsSub, ['resources.resource_id', 'c.post_id'], 'c', 'LEFT')
            ->joinQuery($viewsSub, ['resources.ID', 'vs.resource_id'], 'vs')
            ->joinQuery($visitorsSub, ['resources.ID', 'u.resource_id'], 'u', 'LEFT')
            ->join('postmeta AS pm', ['resources.resource_id', 'pm.post_id'], [], 'LEFT')
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->where('resources.resource_url', 'LIKE', "%{$args['url']}%")
            ->groupBy('resources.ID')
            ->orderBy(match ($args['order_by']) {
                'views' => 'views',
                'visitors' => 'visitors',
                'comments' => 'comments',
                'words' => 'words',
                default => 'title',
            }, $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $rows ?: [];
    }

    /**
     * Retrieve resources ordered by view count.
     *
     * @param array $args Same keys as {@see getResourcesReportData()} plus:
     * @type bool $show_no_views Include items without views.
     * }
     * @return array[]
     * @since 15.0.0
     */
    public function getResourcesViewsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => [],
            'order_by'      => 'views',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => 5,
            'author_id'     => '',
            'taxonomy'      => [],
            'term'          => '',
            'show_no_views' => false,
        ]);

        $from = $to = '';
        if (is_array($args['date']) && isset($args['date']['from'], $args['date']['to'])) {
            $from = $args['date']['from'] . ' 00:00:00';
            $to   = $args['date']['to'] . ' 23:59:59';
        } elseif ($args['date'] !== '') {
            $from = $args['date'] . ' 00:00:00';
            $to   = $args['date'] . ' 23:59:59';
        }

        $viewsSubQuery = Query::select(['resource_id', 'COUNT(*) AS views'])
            ->from('views')
            ->whereDate('viewed_at', ['from' => $from, 'to' => $to])
            ->groupBy('resource_id')
            ->getQuery();

        $joinType = $args['show_no_views'] ? 'LEFT' : 'INNER';

        $query = Query::select([
            'resources.resource_id      AS post_id',
            'resources.cached_author_id AS author_id',
            'resources.cached_title     AS title',
            'resources.cached_date      AS date',
            'COALESCE(v.views,0)        AS views',
        ])
            ->from('resources')
            ->joinQuery($viewsSubQuery, ['resources.ID', 'v.resource_id'], 'v', $joinType)
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->groupBy('resources.ID')
            ->orderBy($args['order_by'] === 'title' ? 'title' : 'views', $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['taxonomy']) || $args['term'] !== '') {
            $taxonomyQuery = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxonomyQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return $query->getAll() ?: [];
    }

    /**
     * Retrieve resources ordered by number of comments.
     *
     * @param array $args Same keys as {@see getResourcesViewsData()}.
     * @return array[]
     * @since 15.0.0
     */
    public function getResourcesCommentsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'order_by'  => 'comments',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => '',
            'taxonomy'  => [],
            'term'      => '',
        ]);

        $dateFrom = $dateTo = '';
        if (is_array($args['date']) && isset($args['date']['from'], $args['date']['to'])) {
            $dateFrom = $args['date']['from'] . ' 00:00:00';
            $dateTo   = $args['date']['to'] . ' 23:59:59';
        } elseif ($args['date'] !== '') {
            $dateFrom = $args['date'] . ' 00:00:00';
            $dateTo   = $args['date'] . ' 23:59:59';
        }

        $commentSub = Query::select(['comment_post_ID', 'COUNT(comment_ID) AS total_comments'])
            ->from('comments')
            ->where('comment_type', '=', 'comment')
            ->whereDate('comment_date', ['from' => $dateFrom, 'to' => $dateTo])
            ->groupBy('comment_post_ID')
            ->getQuery();

        $query = Query::select([
            'resources.resource_id      AS post_id',
            'resources.cached_author_id AS author_id',
            'resources.cached_title     AS title',
            'COALESCE(c.total_comments,0) AS comments',
        ])
            ->from('resources')
            ->joinQuery($commentSub, ['resources.resource_id', 'c.comment_post_ID'], 'c', 'LEFT')
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->groupBy('resources.resource_id')
            ->orderBy($args['order_by'] === 'title' ? 'title' : 'comments', $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['taxonomy']) || $args['term'] !== '') {
            $taxSub = Query::select(['DISTINCT object_id'])
                ->from('term_relationships')
                ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                ->where('terms.term_id', '=', $args['term'])
                ->getQuery();

            $query->joinQuery($taxSub, ['resources.resource_id', 'tax.object_id'], 'tax');
        }

        return $query->getAll() ?: [];
    }

    /**
     * Retrieve resources ordered by stored word-count meta.
     *
     * @param array $args Same keys as {@see getResourcesViewsData()}.
     * @return array[]
     * @since 15.0.0
     */
    public function getResourcesWordsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => [],
            'order_by'  => 'words',
            'order'     => 'DESC',
            'page'      => 1,
            'per_page'  => 5,
            'author_id' => '',
        ]);

        $dateFrom = $dateTo = '';
        if (is_array($args['date']) && isset($args['date']['from'], $args['date']['to'])) {
            $dateFrom = $args['date']['from'] . ' 00:00:00';
            $dateTo   = $args['date']['to'] . ' 23:59:59';
        } elseif ($args['date'] !== '') {
            $dateFrom = $args['date'] . ' 00:00:00';
            $dateTo   = $args['date'] . ' 23:59:59';
        }

        $metaKey = WordCountService::WORDS_COUNT_META_KEY;

        $query = Query::select([
            'resources.resource_id      AS post_id',
            'resources.cached_author_id AS author_id',
            'resources.cached_title     AS title',
            "COALESCE(MAX(CASE WHEN postmeta.meta_key = '{$metaKey}' THEN postmeta.meta_value ELSE 0 END),0) AS words",
        ])
            ->from('resources')
            ->join('postmeta', ['resources.resource_id', 'postmeta.post_id'], [], 'LEFT')
            ->where('resources.resource_type', 'IN', $args['post_type'])
            ->where('resources.cached_author_id', '=', $args['author_id'])
            ->whereDate('resources.created_at', ['from' => $dateFrom, 'to' => $dateTo])
            ->groupBy('resources.resource_id')
            ->orderBy($args['order_by'] === 'title' ? 'title' : 'words', $args['order'])
            ->perPage($args['page'], $args['per_page']);

        return $query->getAll() ?: [];
    }

    /**
     * Get the earliest cached date for the specified resource types.
     *
     * @param array $args {
     * @type string[] $post_type
     * }
     * @return string|null Y-m-d or null if none.
     * @since 15.0.0
     */
    public function getInitialResourceDate($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type' => Helper::getPostTypes(),
        ]);

        $query = Query::select(['MIN(resources.cached_date) AS date'])
            ->from('resources')
            ->where('resource_type', 'IN', $args['post_type'])
            ->whereNotNull('cached_date')
            ->allowCaching();

        $date = $query->getVar();

        return $date ?: null;
    }

    /**
     * List 404 URLs and their view counts for the selected period.
     *
     * @param array $args {
     * @type string|array $date
     * @type string $order_by views|uri
     * @type string $order ASC|DESC
     * @type int $page
     * @type int $per_page
     * }
     * @return array[]
     * @since 15.0.0
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

        $dateRange = is_array($args['date'])
            ? $args['date']
            : DateRange::get($args['date']);

        $query = Query::select([
            'resources.resource_url AS uri',
            'COUNT(*)                AS views',
        ])
            ->from('views')
            ->join(
                'resources',
                ['views.resource_id', 'resources.ID']
            )
            ->where('resources.resource_type', '=', '404')
            ->where('views.viewed_at', '>=', $dateRange['from'] . ' 00:00:00')
            ->where('views.viewed_at', '<=', $dateRange['to'] . ' 23:59:59')
            ->groupBy('resources.resource_url')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        return $query->getAll() ?: [];
    }

    /**
     * Count distinct 404 URLs viewed in the chosen period.
     *
     * @param array $args {
     * @type string|array $date
     * }
     * @return int
     * @since 15.0.0
     */
    public function count404Data($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => '',
        ]);

        $range = is_array($args['date'])
            ? $args['date']
            : DateRange::get($args['date']);

        $count = Query::select('COUNT(DISTINCT resources.resource_url)')
            ->from('views')
            ->join('resources', ['views.resource_id', 'resources.ID'])
            ->where('resources.resource_type', '=', '404')
            ->where('views.viewed_at', '>=', $range['from'] . ' 00:00:00')
            ->where('views.viewed_at', '<=', $range['to'] . ' 23:59:59')
            ->getVar();

        return $count ? (int)$count : 0;
    }
}