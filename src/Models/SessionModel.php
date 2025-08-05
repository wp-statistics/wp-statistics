<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_STATISTICS\DB;
use WP_Statistics\Utils\Query;

/**
 * Model class for performing database operations related to visitor sessions.
 *
 * Provides methods to query and interact with the sessions table.
 *
 * @since 15.0.0
 */
class SessionModel extends BaseModel
{
    /**
     * Count sessions that started within the specified date or date‑range.
     *
     * If no date is supplied, the preset **'today'** is used. The `date` element
     * accepts either a preset understood by {@see DateRange::get()} (e.g.
     * `'today'`, `'yesterday'`, `'7days'`) or an associative array
     * `['from' => 'Y-m-d', 'to' => 'Y-m-d']`.
     *
     * @param array{
     *     date?: string|array{from:string,to:string}
     * } $args Optional. Query arguments.
     * @return int Number of sessions that started in the period.
     */
    public function countDaily($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => DateRange::get('today')
        ]);

        $query = Query::select(['COUNT(*)'])
            ->from('sessions')
            ->where('started_at', '>=', $args['date']['from'] . ' 00:00:00')
            ->where('started_at', '<=', $args['date']['to'] . ' 23:59:59');

        return (int)$query->getVar();
    }

    /**
     * Count all sessions ever recorded.
     *
     * @return int Lifetime session count.
     */
    public function countTotal()
    {
        return (int)Query::select(['COUNT(*)'])
            ->from('sessions')
            ->getVar();
    }

    /**
     * Return the still‑active (or recently ended) session for the given visitor.
     *
     * A session qualifies as *active* when its `ended_at` timestamp is within
     * the last 30 minutes. Only sessions that started **today** are considered.
     *
     * @param array{
     *     visitor_id?: int
     * } $args {
     *     Optional. Visitor identifier to search for.
     * }
     * @return object|null The matching session row or `null` if not found.
     */
    public function getActiveSession($args = [])
    {
        $args = $this->parseArgs($args, [
            'visitor_id' => 0
        ]);

        if (empty($args['visitor_id'])) {
            return null;
        }

        $thirtyMinutesAgo = DateTime::getUtc('Y-m-d H:i:s', '-30 minutes');

        return Query::select('*')
            ->from('sessions')
            ->where('visitor_id', '=', $args['visitor_id'])
            ->where('ended_at', '>=', $thirtyMinutesAgo)
            ->perPage(1)
            ->getRow();
    }

    /**
     * Retrieve sessions whose last activity occurred in the past five minutes.
     *
     * @return array<array<string,mixed>> Array of session rows.
     */
    public function getOnlineUsers()
    {
        return Query::select('*')
            ->from('sessions')
            ->where('ended_at', '>=', gmdate('Y-m-d H:i:s', time() - 300))
            ->getAll();
    }

    /**
     * Count sessions considered *online* (last activity < 5 minutes).
     *
     * @return int Number of live sessions.
     */
    public function countOnlineUsers()
    {
        return Query::select('COUNT(*)')
            ->from('sessions')
            ->where('ended_at', '>=', gmdate('Y-m-d H:i:s', time() - 300))
            ->getVar();
    }

    /**
     * Count distinct `visitor_id` values inside the supplied date window.
     *
     * Uses the same `date` argument format accepted by {@see countDaily()}.
     *
     * @param array{
     *     date?: string|array{from:string,to:string}
     * } $args Optional. Date filter.
     * @return int Unique‑visitor total.
     */
    public function getByTime($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => '',
        ]);

        $range = is_array($args['date']) && isset($args['date']['from'], $args['date']['to'])
            ? $args['date']
            : DateRange::get($args['date']);

        $start = $range['from'] . ' 00:00:00';
        $end   = $range['to'] . ' 23:59:59';

        $result = Query::select(['COUNT(DISTINCT visitor_id) AS count'])
            ->from('sessions')
            ->where('started_at', '>=', $start)
            ->where('started_at', '<=', $end)
            ->getVar();

        return $result;
    }

    /**
     * Count distinct sessions matching a rich filter set.
     *
     * The method supports filters for agent, platform, country, referrer,
     * resource attributes, login status, user‑role and more.  Internally it
     * executes `COUNT(DISTINCT IFNULL(visitor_id, ID))`.
     *
     * @param array<string,mixed> $args Optional. See method body for keys
     *                                  and default values.
     * @return int Unique‑session total after filtering.
     */
    public function countDistinct($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => '',
            'resource_type' => '',
            'resource_id'   => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'agent'         => '',
            'platform'      => '',
            'country'       => '',
            'user_id'       => '',
            'ip'            => '',
            'logged_in'     => false,
            'user_role'     => '',
            'referrer'      => '',
        ]);

        $query = Query::select(['COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) AS total_visitors'])
            ->from('sessions')
            ->whereDate('sessions.started_at', $args['date'])
            ->where('sessions.user_id', '=', $args['user_id'])
            ->where('sessions.ip', 'LIKE', "%{$args['ip']}%");

        if ($args['agent'] !== '') {
            $query->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'])
                ->where('device_browsers.name', '=', $args['agent']);
        }

        if ($args['platform'] !== '') {
            $query->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
                ->where('device_oss.name', '=', $args['platform']);
        }

        if ($args['country'] !== '') {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
        }

        if ($args['referrer'] !== '') {
            $query->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
                ->where('referrers.domain', 'LIKE', "%{$args['referrer']}%");
        }

        if ($args['logged_in']) {
            $query->where('sessions.user_id', '!=', 0)
                ->whereNotNull('sessions.user_id');

            if ($args['user_role'] !== '') {
                $query->join('usermeta', ['sessions.user_id', 'usermeta.user_id'])
                    ->where('usermeta.meta_key', '=', 'wp_capabilities')
                    ->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        $resourceFilters = array_intersect(
            [
                'resource_type', 'resource_id', 'query_param',
                'post_type', 'author_id', 'post_id',
                'taxonomy', 'term',
            ],
            array_keys(array_filter($args))
        );

        if ($resourceFilters) {
            $query->join('views', ['views.session_id', 'sessions.ID'])
                ->join('resources', ['views.resource_id', 'resources.ID'])
                ->where('resources.resource_type', 'IN', $args['resource_type'])
                ->where('resources.resource_id', '=', $args['resource_id'])
                ->where('resources.resource_url', 'LIKE', '%' . $args['query_param'] . '%')
                ->where('resources.cached_author_id', '=', $args['author_id'])
                ->where('resources.resource_id', '=', $args['post_id']);

            if ($args['post_type'] !== '') {
                $query->join('parameters AS pt_param', ['sessions.ID', 'pt_param.session_id'])
                    ->where('pt_param.parameter', '=', 'post_type')
                    ->where('pt_param.value', 'IN', $args['post_type']);
            }

            if ($args['taxonomy'] !== '' || $args['term'] !== '') {
                $taxQuery = Query::select(['DISTINCT object_id'])
                    ->from('term_relationships')
                    ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                    ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                    ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                    ->where('terms.term_id', '=', $args['term'])
                    ->getQuery();

                $query->joinQuery($taxQuery, ['resources.resource_id', 'tax.object_id'], 'tax');
            }
        }

        $result = $query->getVar();

        return $result ? (int)$result : 0;
    }

    /**
     * Produce a summary array (today, yesterday, this week, …) of visitor totals.
     *
     * @param array $args Arguments passed through to {@see count()}.
     * @return array Associative array keyed by period slug.
     * @todo It should be replaced after the dashboard bootstrap is merged.
     */
    public function getSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label'    => esc_html__('Today', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label'    => esc_html__('Yesterday', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label'    => esc_html__('This week', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label'    => esc_html__('Last week', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label'    => esc_html__('This month', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label'    => esc_html__('Last month', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label'    => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label'    => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label'    => esc_html__('Last 90 days', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label'    => esc_html__('Last 6 months', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label'    => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label'    => esc_html__('Total', 'wp-statistics'),
                'visitors' => $this->countDistinct(array_merge($args, ['ignore_date' => true]))
            ];
        }

        return $summary;
    }

    /**
     * Sum `sessions.total_views` across sessions that satisfy the filters.
     *
     * Accepts the same filter keys recognised by {@see countDistinct()}.
     *
     * @param array<string,mixed> $args Optional. Filter arguments.
     * @return int Aggregated view count.
     */
    public function countViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'agent'     => '',
            'platform'  => '',
            'country'   => '',
            'user_id'   => '',
            'ip'        => '',
            'logged_in' => false,
            'user_role' => '',
        ]);

        $query = Query::select(['SUM(sessions.total_views) AS total_hits'])
            ->from('sessions')
            ->whereDate('sessions.started_at', $args['date'])
            ->where('sessions.user_id', '=', $args['user_id'])
            ->where('sessions.ip', 'LIKE', "%{$args['ip']}%");

        if (!empty($args['agent'])) {
            $query->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'])
                ->where('device_browsers.name', '=', $args['agent']);
        }

        if (!empty($args['platform'])) {
            $query->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
                ->where('device_oss.name', '=', $args['platform']);
        }

        if (!empty($args['country'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
        }

        if ($args['logged_in']) {
            $query->where('sessions.user_id', '!=', 0)
                ->whereNotNull('sessions.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['sessions.user_id', 'usermeta.user_id'])
                    ->where('usermeta.meta_key', '=', 'wp_capabilities')
                    ->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        $result = $query->getVar();

        return $result ? (int)$result : 0;
    }

    /**
     * Return a summary array (today, yesterday, this week, …) of hit totals.
     *
     * @param array $args Arguments passed through to {@see countViews()}.
     * @return array Associative array keyed by period slug.
     * @todo It should be replaced after the dashboard bootstrap is merged.
     */
    public function getHitsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label' => esc_html__('Today', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label' => esc_html__('Yesterday', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label' => esc_html__('This week', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label' => esc_html__('Last week', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label' => esc_html__('This month', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label' => esc_html__('Last month', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label' => esc_html__('Last 7 days', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label' => esc_html__('Last 30 days', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label' => esc_html__('Last 90 days', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label' => esc_html__('Last 6 months', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label' => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'hits'  => $this->countViews(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
    }

    /**
     * Return a breakdown of sessions grouped by one lookup dimension
     * (browser, OS, device type, country, etc.).
     *
     * Pass the dimension via the `'by'` argument, e.g.
     * `['device_browsers' => 'device_browser_id']`.
     *
     * Each result row contains:
     *  • **label** – Human‑readable value from the lookup table.
     *  • **session_count** – Number of matching sessions.
     *
     * @param array{
     *     by:       array<string,string>,
     *     date?:    array{from:string,to:string},
     *     per_page?: int
     * } $args Arguments controlling dimension, date filter and limit.
     * @return array<array{label:string,session_count:int}>
     */
    public function countUsage($args = [])
    {
        $args = $this->parseArgs($args, [
            'by'   => [
                'device_browsers' => 'device_browser_id'
            ],
            'date' => '',
        ]);

        $lookupTable      = key($args['by']);
        $foreignKeyColumn = current($args['by']);

        /*
         * Single‑pass aggregation:
         *  sessions  →  lookup table
         *  GROUP BY sessions.<fk>
         *  ORDER BY COUNT(*) DESC
         */
        $query = Query::select([
            "{$lookupTable}.name AS label",
            "COUNT(*) AS session_count",
        ])
            ->from('sessions')
            ->join($lookupTable, ["sessions.$foreignKeyColumn", "{$lookupTable}.ID"])
            ->whereNotNull("sessions.$foreignKeyColumn");

        if (!empty($args['date'])) {
            $query->where('sessions.started_at', '>=', $args['date']['from'] . ' 00:00:00')
                ->where('sessions.started_at', '<=', $args['date']['to'] . ' 23:59:59');
        }

        $query->groupBy("sessions.$foreignKeyColumn")
            ->orderBy('session_count', 'DESC');

        if (!empty($args['per_page'])) {
            $query->perPage(1, $args['per_page']);
        }

        return $query->getAll();
    }

    /**
     * Per‑resource daily summary (visitors, sessions, views, duration, bounces).
     *
     * Aggregates metrics for a single resource URI (identified by
     * `resource_uri_id`).
     *
     * @param array $args Arguments: target `resource_uri_id`.
     * @return object|null Aggregated row for the resource, or `null` if none.
     */
    public function getDailySummary($args = [])
    {
        $args = $this->parseArgs($args, [
            'resource_uri_id' => null,
        ]);

        $oneResSub = Query::select([
            'session_id',
            'COUNT(DISTINCT resource_uri_id) AS res_cnt',
            'MIN(resource_uri_id) AS resource_uri_id',
        ])
            ->from('views')
            ->groupBy(['session_id'])
            ->getQuery();

        $bounceSessionsSub = Query::select([
            'sessions.ID AS session_id',
            'one.resource_uri_id',
        ])
            ->from('sessions')
            ->joinQuery($oneResSub, ['one.session_id', 'sessions.ID'], 'one', 'LEFT')
            ->whereRaw('COALESCE(one.res_cnt, 0) <= 1')
            ->getQuery();

        $query = Query::select([
            "DATE(sessions.started_at) AS date",
            "COALESCE(views.resource_uri_id, 'no_views') AS resource",
            'COUNT(DISTINCT visitors.hash)           AS visitors',
            'COUNT(DISTINCT sessions.ID)             AS sessions',
            'COUNT(views.ID)                         AS views',
            'SUM(sessions.duration)                  AS total_duration',
            'COUNT(DISTINCT b.session_id)            AS bounces',
        ])
            ->from('sessions')
            ->join('visitors', ['visitors.ID', 'sessions.visitor_id'])
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->joinQuery($bounceSessionsSub, ['b.session_id', 'sessions.ID'], 'b', 'LEFT')
            ->where('views.resource_uri_id', '=', $args['resource_uri_id']);

        return $query->getRow();
    }

    /**
     * List distinct resource URI IDs that occurred on a specific day.
     *
     * Returns the set of `views.resource_uri_id` values among sessions whose
     * `started_at` date matches the target day. Sessions without any view rows
     * are represented by the sentinel `'no_views'`.
     *
     * @param array $args Arguments: optional `date` preset; defaults to 'yesterday'.
     * @return array<int|string> Ordered list of resource URI IDs (may include 'no_views').
     */
    public function getResourceUriIdsByDate($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => 'yesterday',
        ]);

        $targetDate = DateTime::getUtc('Y-m-d', $args['date']);

        $rows = Query::select("DISTINCT COALESCE(views.resource_uri_id, 'no_views') AS resource_uri_id")
            ->from('sessions')
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->where('DATE(sessions.started_at)', '=', $targetDate)
            ->orderBy('resource_uri_id')
            ->getAll();

        if (empty($rows)) {
            return [];
        }

        return array_column($rows, 'resource_uri_id');
    }


    /**
     * Site‑wide daily totals (visitors, sessions, views, duration, bounces).
     *
     * Aggregates metrics across all resources for a single calendar day.
     * A *bounce* is defined here as a session with **at most one** view
     * (i.e., zero or one rows in `views` for that session).
     *
     * @param array $args Arguments: optional `date` preset; defaults to 'yesterday'.
     * @return object|null Aggregated totals for the day, or `null` if none.
     */
    public function getDailySummaryTotal($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => 'yesterday',
        ]);

        $targetDate = DateTime::getUtc('Y-m-d', $args['date']);

        $oneResSub = Query::select([
            'session_id',
            'COUNT(*) AS view_count',
        ])
            ->from('views')
            ->groupBy(['session_id'])
            ->getQuery();

        $bounceSessionsSub = Query::select([
            'sessions.ID AS session_id',
        ])
            ->from('sessions')
            ->joinQuery($oneResSub, ['one.session_id', 'sessions.ID'], 'one', 'LEFT')
            ->whereRaw('COALESCE(one.view_count, 0) <= 1')
            ->getQuery();

        $query = Query::select([
            "DATE(sessions.started_at) AS date",
            'COUNT(DISTINCT visitors.hash) AS visitors',
            'COUNT(DISTINCT sessions.ID) AS sessions',
            'COUNT(views.ID) AS views',
            'SUM(sessions.duration) AS total_duration',
            'COUNT(DISTINCT b.session_id) AS bounces',
        ])
            ->from('sessions')
            ->join('visitors', ['visitors.ID', 'sessions.visitor_id'])
            ->join('views', ['views.session_id', 'sessions.ID'], null, 'LEFT')
            ->joinQuery($bounceSessionsSub, ['b.session_id', 'sessions.ID'], 'b', 'LEFT')
            ->where('DATE(sessions.started_at)', '=', $targetDate);

        return $query->getRow();
    }
}
