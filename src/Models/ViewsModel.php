<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Decorators\ViewDecorator;
use WP_Statistics\Utils\PostType;

class ViewsModel extends BaseModel
{
    public function countViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'        => Helper::get_list_post_type(),
            'resource_type'    => '',
            'date'             => '',
            'author_id'        => '',
            'post_id'          => '',
            'query_param'      => '',
            'taxonomy'         => '',
            'term'             => '',
            'ignore_post_type' => false
        ]);

        $viewsQuery = Query::select(['id', 'date', 'SUM(count) AS count'])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->where('pages.uri', '=', $args['query_param'])
            ->getQuery();

        $query = Query::select('SUM(pages.count) as total_views')
            ->fromQuery($viewsQuery, 'pages');

        if (!empty($args['author_id']) || !empty($args['post_id']) || !empty($args['taxonomy']) || !empty($args['term']) || (!empty($args['post_type']) && !$args['ignore_post_type'])) {
            $query
                ->join('posts', ['pages.id', 'posts.ID'])
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id']);

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
        }

        $total = $query->getVar();
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    /**
     * Returns views from `pages` table without joining with other tables.
     *
     * Used for calculating taxonomies views (Unlike `countViews()` which is suited for calculating posts/pages/cpt views).
     *
     * @param array $args Arguments to include in query (e.g. `post_id`, `resource_type`, `query_param`, `date`, etc.).
     *
     * @return  int
     */
    public function countViewsFromPagesOnly($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_id'       => '',
            'resource_type' => '',
            'query_param'   => '',
            'date'          => '',
        ]);

        $query = Query::select(['SUM(`count`) AS `count`'])
            ->from('pages')
            ->where('pages.id', '=', $args['post_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('date', $args['date']);

        if (is_numeric($args['post_id'])) {
            $query->groupBy('id');
        }

        $total = $query->getVar();
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    public function countDailyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'        => Helper::get_list_post_type(),
            'ignore_post_type' => false,
            'resource_type'    => '',
            'resource_id'      => '',
            'date'             => '',
            'author_id'        => '',
            'post_id'          => '',
            'query_param'      => '',
            'taxonomy'         => '',
            'term'             => '',
        ]);

        $query = Query::select([
            'SUM(pages.count) as views',
            'pages.date as date',
        ])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.date');

        if (empty($args['resource_id']) && (!empty($args['author_id']) || !empty($args['post_id']) || !empty($args['taxonomy']) || !empty($args['term']) || (!empty($args['post_type']) && !$args['ignore_post_type']))) {
            $query
                ->join('posts', ['pages.id', 'posts.ID'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('post_type', 'IN', $args['post_type']);

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
        }

        $result = $query->getAll();

        return $result ?? [];
    }

    public function getViewsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'     => '',
            'page'     => 1,
            'per_page' => 20
        ]);

        $result = Query::select([
            'visitor.ID',
            'visitor.ip',
            'visitor.platform',
            'visitor.agent',
            'CAST(`visitor`.`version` AS SIGNED) as version',
            'visitor.model',
            'visitor.device',
            'visitor.region',
            'visitor.city',
            'visitor.location',
            'visitor.hits',
            'visitor.user_id',
            'visitor.referred',
            'visitor.source_channel',
            'visitor.source_name',
            'visitor_relationships.page_id as last_page',
            'visitor_relationships.date as last_view',
        ])
            ->from('visitor_relationships')
            ->join('visitor', ['visitor_relationships.visitor_id', 'visitor.ID'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->decorate(VisitorDecorator::class)
            ->orderBy('visitor_relationships.date')
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result;
    }

    public function countViewRecords($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => '',
        ]);

        $result = Query::select([
            'COUNT(*)',
        ])
            ->from('visitor_relationships')
            ->whereDate('visitor_relationships.date', $args['date'])
            ->getVar();

        return $result;
    }

    public function getHourlyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => ''
        ]);

        $result = Query::select([
            'HOUR(date) as hour',
            'COUNT(DISTINCT visitor_id) as visitors',
            'COUNT(*) as views'
        ])
            ->from('visitor_relationships')
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('hour')
            ->getAll();

        return $result;
    }

    public function getViewsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label' => esc_html__('Today', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label' => esc_html__('Yesterday', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label' => esc_html__('This week', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label' => esc_html__('Last week', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label' => esc_html__('This month', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label' => esc_html__('Last month', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label' => esc_html__('Last 7 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label' => esc_html__('Last 30 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label' => esc_html__('Last 90 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label' => esc_html__('Last 6 months', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label' => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
    }

    public function getViewedPageUri($args = [])
    {
        $args = $this->parseArgs($args, [
            'id' => '',
        ]);

        $results = Query::select([
            'uri',
            'page_id',
            'SUM(count) AS total',
        ])
            ->from('pages')
            ->where('id', '=', $args['id'])
            ->groupBy('uri')
            ->orderBy('total')
            ->getAll();

        return $results;
    }

    public function getResourcesViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'        => ['id', 'uri', 'type', 'SUM(count) as views'],
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'group_by'      => 'id',
            'not_null'      => '',
            'order_by'      => 'views',
            'page'          => 1,
            'per_page'      => 10
        ]);

        // If resource_id and resource_type are empty, get all views including 404, categories, home, etc...
        if (empty($args['resource_id']) && empty($args['resource_type'])) {
            $queries = [];

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '!=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy('id')
                ->getQuery();

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy(['uri', 'type'])
                ->getQuery();

            $results = Query::union($queries)
                ->perPage($args['page'], $args['per_page'])
                ->orderBy('views', 'DESC')
                ->getAll();
        } else {
            $results = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', $args['resource_id'])
                ->where('type', 'IN', $args['resource_type'])
                ->whereDate('date', $args['date'])
                ->whereNotNull($args['not_null'])
                ->orderBy($args['order_by'])
                ->perPage($args['page'], $args['per_page'])
                ->groupBy($args['group_by'])
                ->getAll();
        }

        return $results;
    }

    public function countPagesRecords($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'not_null'      => '',
        ]);

        $result = Query::select('COUNT(*)')
            ->from('pages')
            ->where('id', '=', $args['resource_id'])
            ->where('type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->whereNotNull($args['not_null'])
            ->getVar();

        return $result;
    }

    /**
     * Retrieve the most recent view record for a given session ID.
     *
     * @param array $args {
     * @type int $session_id Required. The session ID to fetch the latest view for.
     * }
     *
     * @return object|null
     * @since 15.0.0
     */
    public function getLastViewBySessionId($args = [])
    {
        $args = $this->parseArgs($args, [
            'session_id' => 0
        ]);

        if (empty($args['session_id'])) {
            return null;
        }

        $query = Query::select('*')
            ->from('views')
            ->where('session_id', '=', $args['session_id'])
            ->orderBy('ID', 'DESC')
            ->perPage(1);

        return $query->getRow();
    }

    /**
     * Count the number of views for a specific resource URL ID.
     *
     * @param array $args {
     *     Optional. Array of arguments.
     *
     * @type int $resource_uri_id Required. The resource URL ID to count views for.
     * }
     *
     * @return int Total number of views for the resource URL ID.
     * @since 15.0.0
     */
    public function countByResourceUriId($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_uri_id' => '',
        ]);

        $query = Query::select(['COUNT(*) AS count'])
            ->from('views')
            ->where('resource_uri_id', '=', $args['resource_uri_id']);

        return (int)$query->getVar();
    }

    /**
     * Get the number of views for a given date or range.
     *
     * @param array $args {
     * @type string|array $date Date or range to analyse.
     * }
     * @return int Number of views.
     * @since 15.0.0
     */
    public function countDaily($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => DateRange::get('today')
        ]);

        $query = Query::select(['COUNT(*) AS count'])
            ->from('views')
            ->where('viewed_at', '>=', $args['date']['from'] . ' 00:00:00')
            ->where('viewed_at', '<=', $args['date']['to'] . ' 23:59:59');

        return (int)$query->getVar();
    }

    /**
     * Get the most recent views with basic metadata.
     *
     * @param array $args {
     *     Optional. Arguments to filter/sort results.
     *
     * @type array $date Range with 'from' and 'to' (Y-m-d). Default: today.
     * @type int $page Page number. Default 1.
     * @type int $per_page Items per page. Default 20.
     * @type string $order_by Column to order by. Default 'v.viewed_at'.
     * @type string $order 'ASC' or 'DESC'. Default 'DESC'.
     * }
     *
     * @return array Recent views with basic metadata.
     * @since 15.0.0
     */
    public function getLatestView($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'     => DateRange::get('today'),
            'page'     => 1,
            'per_page' => 20,
            'order_by' => 'v.viewed_at',
            'order'    => 'DESC',
        ]);

        $base = Query::select([
            'ID',
            'session_id',
            'resource_uri_id',
            'viewed_at',
        ])
            ->from('views')
            ->where('viewed_at', '>=', $args['date']['from'] . ' 00:00:00')
            ->where('viewed_at', '<=', $args['date']['to'] . ' 23:59:59')
            ->orderBy('viewed_at', $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getQuery();

        $query = Query::select([
            'v.ID AS view_id',
            'v.viewed_at AS view_time',
            'resource_uris.uri AS page',
            'countries.name AS country',
            'device_browsers.name AS browser',
            'device_browser_versions.version AS browser_version',
            'device_types.name AS device',
            'device_oss.name AS os',
            'referrers.domain AS referrer_name',
            'sessions.total_views AS session_total_views',
        ])
            ->fromQuery($base, 'v')
            ->join('sessions', ['v.session_id', 'sessions.ID'])
            ->join('resource_uris', ['v.resource_uri_id', 'resource_uris.ID'], [], 'LEFT')
            ->join('countries', ['sessions.country_id', 'countries.ID'], [], 'LEFT')
            ->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'], [], 'LEFT')
            ->join('device_browser_versions', ['sessions.device_browser_version_id', 'device_browser_versions.ID'], [], 'LEFT')
            ->join('device_types', ['sessions.device_type_id', 'device_types.ID'], [], 'LEFT')
            ->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'], [], 'LEFT')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'], [], 'LEFT')
            ->orderBy($args['order_by'], $args['order']);

        return $query->getAll();
    }

    /**
     * Get top N pages (title, URL, views) within a datetime range.
     *
     * Mirrors the SQL (using direct resource_id for speed):
     * SELECT t.resource_id, r.cached_title AS title, MIN(ru.uri) AS uri, t.views
     * FROM (
     *   SELECT v.resource_id, COUNT(*) AS views
     *   FROM views v
     *   JOIN resources r ON r.ID = v.resource_id
     *   WHERE v.viewed_at BETWEEN :from AND :to
     *     AND r.is_deleted = 0 AND r.resource_type IN (...)
     *   GROUP BY v.resource_id
     *   ORDER BY views DESC
     *   LIMIT :per_page
     * ) AS t
     * JOIN resources r ON r.ID = t.resource_id
     * LEFT JOIN resource_uris ru ON ru.resource_id = r.ID
     * GROUP BY t.resource_id
     * ORDER BY t.views DESC;
     *
     * @param array $args {
     * @type string $start DATETIME inclusive lower bound (Y-m-d H:i:s).
     * @type string $end DATETIME exclusive upper bound (Y-m-d H:i:s).
     * @type int $limit Number of rows to return. Default 5.
     * }
     * @return array Top rows with keys: title, uri, views.
     * @since 15.0.0
     */
    public function getTopViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'     => '',
            'per_page' => 5,
        ]);

        $inner = Query::select([
            'views.resource_id',
            'COUNT(*) AS views',
        ])
            ->from('views')
            ->join('resources', ['views.resource_id', 'resources.ID'])
            ->where('viewed_at', '>=', $args['date']['from'] . ' 00:00:00')
            ->where('viewed_at', '<=', $args['date']['to'] . ' 23:59:59')
            ->where('resources.is_deleted', '=', 0)
            ->where('resources.resource_type', 'IN', PostType::getQueryableTypes())
            ->groupBy('views.resource_id')
            ->orderBy('views', 'DESC')
            ->perPage(1, (int)$args['per_page'])
            ->getQuery();

        // Outer query: join metadata for title and URL.
        $query = Query::select([
            'resources.cached_title AS title',
            'MIN(resource_uris.uri) AS uri',
            't.views AS views',
        ])
            ->fromQuery($inner, 't')
            ->join('resources', ['t.resource_id', 'resources.ID'])
            ->join('resource_uris', ['resources.ID', 'resource_uris.resource_id'], [], 'LEFT')
            ->groupBy('t.resource_id')
            ->orderBy('t.views', 'DESC');

        return $query->getAll();
    }
}
