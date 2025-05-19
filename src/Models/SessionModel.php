<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Decorators\ReferrerDecorator;
use WP_Statistics\Decorators\SessionDecorator;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Query;
use WP_STATISTICS\TimeZone;

/**
 * Model class for performing database operations related to visitor sessions.
 *
 * Provides methods to query and interact with the sessions table.
 */
class SessionModel extends BaseModel
{
    /**
     * Find an open session for a visitor that was started today.
     *
     * @param array $args {
     * @type int $visitor_id Visitor ID to search for.
     * }
     * @return object|null
     * @since 15.0.0
     */
    public function getTodaySession($args = [])
    {
        $args = $this->parseArgs($args, [
            'visitor_id' => 0
        ]);

        if (empty($args['visitor_id'])) {
            return null;
        }

        $today = TimeZone::getCurrentDate('Y-m-d');

        return Query::select('*')
            ->from('sessions')
            ->where('visitor_id', '=', $args['visitor_id'])
            ->where('started_at', '>=', $today . ' 00:00:00')
            ->where('started_at', '<=', $today . ' 23:59:59')
            ->perPage(1)
            ->getRow();
    }

    /**
     * Retrieve currently online users based on recent view activity.
     *
     * A session is considered "online" if its last view has no next view (indicating an active session)
     * and the last viewed time is within the configured timeout window.
     *
     * @return array List of online session records.
     *
     * @since 15.0.0
     */
    public function getOnlineUsers()
    {
        return Query::select(['COUNT(*)'])
            ->from('sessions')
            ->where('ended_at', '>=', gmdate('Y-m-d H:i:s', time() - 300))
            ->getVar();

        return $result;
    }

    /**
     * Get the number of unique visitors for a given date or range.
     *
     * Counts unique visitor IDs from the `sessions` table based on session start time.
     *
     * @param array $args {
     *     Optional. Query arguments.
     *
     * @type string|array $date A string like 'today', or an array ['from' => Y-m-d, 'to' => Y-m-d]
     * }
     * @return int Number of visitors.
     * @since 15.0.0
     */
    public function getSessionByTime($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => 'today',
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
     * Return a day-by-day breakdown of visitor counts (and optionally hits).
     *
     * @param array $args {
     * @type string|array $date Date or range to analyse.
     * @type string|string[] $post_type Optional post types to filter.
     * @type bool $include_hits Whether to include hit totals.
     *     ... (see implementation for remaining keys)
     * }
     * @return array[] Each row contains `date`, `visitors`, and optionally `hits`.
     * @since 15.0.0
     */
    public function countDailyVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => '',
            'resource_id'   => '',
            'resource_type' => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'country'       => '',
            'user_id'       => '',
            'logged_in'     => false,
            'include_hits'  => false,
            'user_role'     => ''
        ]);

        $fields = [
            'DATE(sessions.started_at) AS date',
            'COUNT(DISTINCT sessions.visitor_id) AS visitors',
        ];

        if ($args['include_hits']) {
            $fields[] = 'SUM(sessions.total_views) AS hits';
        }

        $query = Query::select($fields)
            ->from('sessions')
            ->groupBy('DATE(sessions.started_at)')
            ->whereDate('sessions.started_at', $args['date'])
            ->where('sessions.user_id', '=', $args['user_id']);

        if ($args['country'] !== '') {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
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

        return $query->getAll();
    }

    /**
     * Count distinct visitors that satisfy a complex set of filters.
     *
     * @param array $args See method body for accepted keys.
     * @return int Visitor count.
     * @since 15.0.0
     */
    public function countVisitors($args = [])
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
     * @param array $args Arguments passed through to {@see countVisitors()}.
     * @return array Associative array keyed by period slug.
     * @since 15.0.0
     */
    public function getVisitorsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label'    => esc_html__('Today', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label'    => esc_html__('Yesterday', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label'    => esc_html__('This week', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label'    => esc_html__('Last week', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label'    => esc_html__('This month', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label'    => esc_html__('Last month', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label'    => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label'    => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label'    => esc_html__('Last 90 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label'    => esc_html__('Last 6 months', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label'    => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label'    => esc_html__('Total', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
    }

    /**
     * Count total page views (“hits”) for a specific date and filter set.
     *
     * @param array $args See method body for accepted keys.
     * @return int Total hits.
     * @since 15.0.0
     */
    public function countHits($args = [])
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
     * @param array $args Arguments passed through to {@see countHits()}.
     * @return array Associative array keyed by period slug.
     * @since 15.0.0
     */
    public function getHitsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label' => esc_html__('Today', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label' => esc_html__('Yesterday', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label' => esc_html__('This week', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label' => esc_html__('Last week', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label' => esc_html__('This month', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label' => esc_html__('Last month', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label' => esc_html__('Last 7 days', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label' => esc_html__('Last 30 days', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label' => esc_html__('Last 90 days', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label' => esc_html__('Last 6 months', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label' => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
    }

    /**
     * Count referred visitors on a given day, filtered by channel/name/domain.
     *
     * @param array $args See method body for accepted keys.
     * @return int Visitor count.
     * @since 15.0.0
     */
    public function countDailyReferrers($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'source_channel' => '',
            'source_name'    => '',
            'referrer'       => '',
        ]);

        $query = Query::select(['COUNT(DISTINCT sessions.visitor_id) AS visitors'])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->whereDate('sessions.started_at', $args['date'])
            ->where('referrers.channel', '=', $args['source_channel'])
            ->where('referrers.name', '=', $args['source_name'])
            ->where('referrers.domain', '=', $args['referrer'])
            ->whereNotNull('sessions.referrer_id');

        $result = $query->getVar();

        return $result ? (int)$result : 0;
    }

    /**
     * Count the number of distinct non-null values in a chosen column.
     *
     * The `$field` argument can reference a virtual column (e.g. `country`),
     * which is internally resolved to the proper joined table/column.
     *
     * @param array $args See method body for accepted keys.
     * @return int Distinct count.
     * @since 15.0.0
     */
    public function countColumnDistinct($args = [])
    {
        $args = $this->parseArgs($args, [
            'field'          => 'ID',
            'date'           => '',
            'where_col'      => 'ID',
            'where_val'      => '',
            'where_not_null' => '',
        ]);

        $lookup = [
            /* geo */
            'location'       => ['countries', 'code', ['sessions.country_id', 'countries.ID']],
            'country'        => ['countries', 'code', ['sessions.country_id', 'countries.ID']],
            'continent'      => ['countries', 'continent', ['sessions.country_id', 'countries.ID']],
            'continent_code' => ['countries', 'continent_code', ['sessions.country_id', 'countries.ID']],
            'region'         => ['cities', 'region_name', ['sessions.city_id', 'cities.ID']],
            'city'           => ['cities', 'city_name', ['sessions.city_id', 'cities.ID']],

            /* devices */
            'device_type'    => ['device_types', 'name', ['sessions.device_type_id', 'device_types.ID']],
            'platform'       => ['device_oss', 'name', ['sessions.device_os_id', 'device_oss.ID']],
            'device_os'      => ['device_oss', 'name', ['sessions.device_os_id', 'device_oss.ID']],
            'agent'          => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'device_browser' => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'version'        => ['device_browser_versions', 'version', ['sessions.device_browser_version_id', 'device_browser_versions.ID']],

            /* tech */
            'resolution'     => ['resolutions', 'ID', ['sessions.resolution_id', 'resolutions.ID']],
            'language'       => ['languages', 'code', ['sessions.language_id', 'languages.ID']],
            'timezone'       => ['timezones', 'name', ['sessions.timezone_id', 'timezones.ID']],

            /* referrers */
            'referrer'       => ['referrers', 'domain', ['sessions.referrer_id', 'referrers.ID']],
            'source_channel' => ['referrers', 'channel', ['sessions.referrer_id', 'referrers.ID']],
            'source_name'    => ['referrers', 'name', ['sessions.referrer_id', 'referrers.ID']],
        ];

        $selectCol = $args['field'];
        if (isset($lookup[$args['field']])) {
            $meta      = $lookup[$args['field']];
            $selectCol = "{$meta[0]}.{$meta[1]}";
        }

        $query = Query::select(["COUNT(DISTINCT {$selectCol}) AS total"])
            ->from('sessions')
            ->whereDate('sessions.started_at', $args['date']);

        if (isset($lookup[$args['field']])) {
            $meta = $lookup[$args['field']];
            $query->join($meta[0], $meta[2]);
        }

        if (!empty($args['where_val'])) {
            if (isset($lookup[$args['where_col']])) {
                $meta = $lookup[$args['where_col']];
                $query->join($meta[0], $meta[2])
                    ->where("{$meta[0]}.{$meta[1]}", '=', $args['where_val']);
            } else {
                $query->where($args['where_col'], '=', $args['where_val']);
            }
        }

        if (!empty($args['where_not_null'])) {
            if (isset($lookup[$args['where_not_null']])) {
                $meta = $lookup[$args['where_not_null']];
                $query->join($meta[0], $meta[2])
                    ->whereNotNull("{$meta[0]}.{$meta[1]}");
            } else {
                $query->whereNotNull($args['where_not_null']);
            }
        }

        $result = $query->perPage(1, 1)->getVar();

        return $result ? (int)$result : 0;
    }

    /**
     * Get aggregated visitor counts grouped by a device-related dimension.
     *
     * @param array $args See method body for accepted keys.
     * @return array[] Result rows each containing the requested dimension and
     *                 `visitors`.
     * @since 15.0.0
     */
    public function getVisitorsDevices($args = [])
    {
        $args = $this->parseArgs($args, [
            'field'          => 'agent',
            'date'           => '',
            'where_not_null' => '',
            'group_by'       => [],
            'order_by'       => 'visitors',
            'order'          => 'DESC',
            'per_page'       => '',
            'page'           => 1,
        ]);

        $map = [
            'agent'       => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'browser'     => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'version'     => ['device_browser_versions', 'version', ['sessions.device_browser_version_id', 'device_browser_versions.ID']],
            'platform'    => ['device_oss', 'name', ['sessions.device_os_id', 'device_oss.ID']],
            'device_type' => ['device_types', 'name', ['sessions.device_type_id', 'device_types.ID']],
            'resolution'  => ['resolutions', 'ID', ['sessions.resolution_id', 'resolutions.ID']],
            'language'    => ['languages', 'code', ['sessions.language_id', 'languages.ID']],
            'timezone'    => ['timezones', 'name', ['sessions.timezone_id', 'timezones.ID']],
        ];

        /* column to select */
        $sel = $args['field'];
        if (isset($map[$args['field']])) {
            $meta = $map[$args['field']];
            $sel  = "{$meta[0]}.{$meta[1]}";
        }

        $query = Query::select([
            $sel . ' AS ' . $args['field'],
            'COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) AS visitors',
        ])
            ->from('sessions')
            ->whereDate('sessions.started_at', $args['date']);

        /* join for main field */
        if (isset($meta)) {
            $query->join($meta[0], $meta[2]);
        }

        /* where_not_null */
        if (!empty($args['where_not_null'])) {
            if (isset($map[$args['where_not_null']])) {
                $m = $map[$args['where_not_null']];
                $query->join($m[0], $m[2])
                    ->whereNotNull("{$m[0]}.{$m[1]}");
            } else {
                $query->whereNotNull($args['where_not_null']);
            }
        }

        /* group_by */
        $groups = array_filter($args['group_by']);
        if (empty($groups)) {
            $groups = [$sel];
        } else {
            foreach ($groups as $g) {
                if (isset($map[$g])) {
                    $m = $map[$g];
                    $query->join($m[0], $m[2]);
                    $groups[array_search($g, $groups, true)] = "{$m[0]}.{$m[1]}";
                }
            }
        }
        $query->groupBy($groups);

        /* ordering & pagination */
        $orderCol = $args['order_by'] === 'visitors' ? 'visitors' : $sel;
        $query->orderBy($orderCol, $args['order'])
            ->perPage($args['page'], $args['per_page']);

        $rows = $query->getAll();

        return $rows ?: [];
    }

    /**
     * Get browser-version distribution for the selected subset of sessions.
     *
     * @param array $args See method body for accepted keys.
     * @return array[] Rows with `casted_version` and `visitors`.
     * @since 15.0.0
     */
    public function getVisitorsDevicesVersions($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'where_col' => 'agent',
            'where_val' => '',
            'order_by'  => 'visitors',
            'order'     => 'DESC',
            'per_page'  => '',
            'page'      => 1,
        ]);

        $map = [
            'agent'          => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'browser'        => ['device_browsers', 'name', ['sessions.device_browser_id', 'device_browsers.ID']],
            'platform'       => ['device_oss', 'name', ['sessions.device_os_id', 'device_oss.ID']],
            'device_os'      => ['device_oss', 'name', ['sessions.device_os_id', 'device_oss.ID']],
            'device_type'    => ['device_types', 'name', ['sessions.device_type_id', 'device_types.ID']],
            'resolution'     => ['resolutions', 'ID', ['sessions.resolution_id', 'resolutions.ID']],
            'language'       => ['languages', 'code', ['sessions.language_id', 'languages.ID']],
            'timezone'       => ['timezones', 'name', ['sessions.timezone_id', 'timezones.ID']],
            'country'        => ['countries', 'code', ['sessions.country_id', 'countries.ID']],
            'continent'      => ['countries', 'continent', ['sessions.country_id', 'countries.ID']],
            'city'           => ['cities', 'city_name', ['sessions.city_id', 'cities.ID']],
            'region'         => ['cities', 'region_name', ['sessions.city_id', 'cities.ID']],
            'source_channel' => ['referrers', 'channel', ['sessions.referrer_id', 'referrers.ID']],
            'source_name'    => ['referrers', 'name', ['sessions.referrer_id', 'referrers.ID']],
            'referrer'       => ['referrers', 'domain', ['sessions.referrer_id', 'referrers.ID']],
        ];

        $query = Query::select([
            'CAST(device_browser_versions.version AS SIGNED) AS casted_version',
            'COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) AS visitors',
        ])
            ->from('sessions')
            ->join('device_browser_versions', ['sessions.device_browser_version_id', 'device_browser_versions.ID'])
            ->whereDate('sessions.started_at', $args['date'])
            ->groupBy('casted_version')
            ->orderBy($args['order_by'] === 'visitors' ? 'visitors' : 'casted_version', $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['where_val']) && isset($map[$args['where_col']])) {
            $meta = $map[$args['where_col']];
            $query->join($meta[0], $meta[2])
                ->where($meta[0] . '.' . $meta[1], '=', $args['where_val']);
        }

        return $query->getAll() ?: [];
    }

    /**
     * Retrieve a list of sessions with rich, backward-compatible field mapping.
     *
     * @param array $args See method body for accepted keys.
     * @return array[]|object[] Decorated session rows.
     * @since 15.0.0
     */
    public function getVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => '',
            'resource_id'   => '',
            'post_type'     => '',
            'author_id'     => '',
            'post_id'       => '',
            'country'       => '',
            'agent'         => '',
            'platform'      => '',
            'user_id'       => '',
            'ip'            => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'order_by'      => 'sessions.ID',
            'order'         => 'DESC',
            'page'          => 1,
            'per_page'      => '',
            'user_info'     => false,
            'date_field'    => 'sessions.started_at',
            'logged_in'     => false,
            'user_role'     => '',
            'event_target'  => '',
            'event_name'    => '',
            'fields'        => [],
            'referrer'      => '',
        ]);

        $legacyMap = [
            // 'legacy column'         => [ table,               column,               alias ]
            'visitor.ID'           => ['sessions', 'ID', 'ID'],
            'visitor.ip'           => ['sessions', 'ip', 'ip'],
            'visitor.agent'        => ['device_browsers', 'name', 'agent'],
            'visitor.platform'     => ['device_oss', 'name', 'platform'],
            'visitor.model'        => ['device_types', 'name', 'model'],
            'visitor.location'     => ['countries', 'code', 'country'],
            'visitor.region'       => ['cities', 'region_name', 'region'],
            'visitor.city'         => ['cities', 'city_name', 'city'],
            'visitor.hits'         => ['sessions', 'total_views', 'total_views'],
            'visitor.last_counter' => ['sessions', 'started_at', 'last_counter'],
            'visitor.device'       => ['device_types', 'name', 'device'],
            /* add any other legacy columns you still need */
        ];

        $essential = [
            'sessions.ID',
            'sessions.initial_view_id',
            'sessions.last_view_id',
            'sessions.device_browser_id',
            'sessions.device_os_id',
            'sessions.device_type_id',
            'sessions.device_browser_version_id',
            'sessions.country_id',
            'sessions.city_id',
            'sessions.visitor_id',
            'sessions.user_id',
        ];

        if (!empty($args['referrer'])) {
            $legacyMap['visitor.source_name']    = ['referrers', 'name', 'source_name'];
            $legacyMap['visitor.source_channel'] = ['referrers', 'channel', 'source_channel'];

            $essential[] = 'sessions.referrer_id';
        }

        $requested = $args['fields'];
        if (empty($requested)) {
            $requested = array_keys($legacyMap);
        }

        $select = $essential;
        $joins  = [];

        foreach ($requested as $legacyCol) {
            if (isset($legacyMap[$legacyCol])) {
                [$tbl, $col, $alias] = $legacyMap[$legacyCol];
                $select[]    = "{$tbl}.{$col} AS {$alias}";
                $joins[$tbl] = true;
            } else {
                $select[] = $legacyCol;
            }
        }

        $query = Query::select($select)
            ->from('sessions')
            ->perPage($args['page'], $args['per_page'])
            ->groupBy('sessions.ID')
            ->decorate(SessionDecorator::class);

        $query->join('visitors', ['sessions.visitor_id', 'visitors.ID'], [], 'LEFT')
            ->join('resources', ['sessions.initial_view_id', 'resources.ID'], [], 'LEFT')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT');

        if (isset($joins['device_browsers'])) {
            $query->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID']);
        }
        if (isset($joins['device_oss'])) {
            $query->join('device_oss', ['sessions.device_os_id', 'device_oss.ID']);
        }
        if (isset($joins['device_types'])) {
            $query->join('device_types', ['sessions.device_type_id', 'device_types.ID']);
        }
        if (isset($joins['countries'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID']);
        }
        if (isset($joins['cities'])) {
            $query->join('cities', ['sessions.city_id', 'cities.ID']);
        }
        if (!empty($joins['referrers'])) {
            $query->join('referrers', ['sessions.referrer_id', 'referrers.ID']);
        }

        $query->whereDate($args['date_field'], $args['date']);

        $query->where('sessions.ip', 'LIKE', "%{$args['ip']}%")
            ->where('sessions.user_id', '=', $args['user_id']);

        if ($args['logged_in']) {
            $query->where('sessions.user_id', '!=', 0)
                ->whereNotNull('sessions.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['sessions.user_id', 'usermeta.user_id'])
                    ->where('usermeta.meta_key', '=', 'wp_capabilities')
                    ->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        if (!empty($args['country'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
        }
        if (!empty($args['agent'])) {
            $query->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID'])
                ->where('device_browsers.name', '=', $args['agent']);
        }
        if (!empty($args['platform'])) {
            $query->join('device_oss', ['sessions.device_os_id', 'device_oss.ID'])
                ->where('device_oss.name', '=', $args['platform']);
        }

        if (!empty($args['referrer'])) {
            $query->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
                ->where('referrers.domain', '=', $args['referrer']);
        }

        $query->where('resources.resource_type', 'IN', $args['resource_type'])
            ->where('resources.resource_id', '=', $args['resource_id'])
            ->where('resources.resource_url', 'LIKE', "%{$args['query_param']}%");

        $orderCol = $args['order_by'];

        if ($orderCol === 'visitor.ID' || $orderCol === 'visitors.ID') {
            $orderCol = 'visitors.ID';
        } elseif ($orderCol === 'hits') {
            $orderCol = 'sessions.total_views';
        }
        $query->orderBy($orderCol, $args['order']);

        return $query->getAll() ?: [];
    }

    /**
     * Return detailed sessions that originated from a referrer.
     *
     * @param array $args See method body for accepted keys.
     * @return array[]|object[] Decorated session rows.
     * @since 15.0.0
     */
    public function getReferredVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'source_channel' => '',
            'source_name'    => '',
            'referrer'       => '',
            'order_by'       => 'sessions.ID',
            'order'          => 'desc',
            'page'           => '',
            'per_page'       => '',
        ]);

        $query = Query::select([
            'sessions.*',
            'users.display_name',
            'users.user_email'
        ])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->join('visitors', ['sessions.visitor_id', 'visitors.ID'], [], 'LEFT')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT')
            ->whereNotNull('sessions.referrer_id')
            ->whereDate('sessions.started_at', $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->where('referrers.name', '=', $args['source_name'])
            ->where('referrers.domain', '=', $args['referrer'])
            ->decorate(SessionDecorator::class);

        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('referrers.channel');
        } elseif (!empty($args['source_channel'])) {
            $query->where('referrers.channel', '=', $args['source_channel']);
        }

        return $query->getAll() ?: [];
    }

    /**
     * Count sessions that match a referrer filter.
     *
     * @param array $args See method body for accepted keys.
     * @return int Session count.
     * @since 15.0.0
     */
    public function countReferredVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'source_channel' => '',
            'source_name'    => '',
            'referrer'       => ''
        ]);

        $query = Query::select('COUNT(*)')
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->whereNotNull('sessions.referrer_id')
            ->where('referrers.name', '=', $args['source_name'])
            ->where('referrers.domain', '=', $args['referrer'])
            ->whereDate('sessions.started_at', $args['date']);

        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('referrers.channel');
        } elseif (!empty($args['source_channel'])) {
            $query->where('referrers.channel', '=', $args['source_channel']);
        }

        return $query->getVar() ?? 0;
    }

    /**
     * Search sessions by user/visitor identifiers.
     *
     * @param array $args See method body for accepted keys.
     * @return array[] Result rows.
     * @since 15.0.0
     */
    public function searchVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'user_id'  => '',
            'ip'       => '',
            'username' => '',
            'email'    => '',
        ]);

        $query = Query::select([
            'sessions.ID',
            'sessions.visitor_id',
            'sessions.ip',
            'users.display_name',
            'users.user_email',
            'users.user_login'
        ])
            ->from('sessions')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT')
            ->where('sessions.user_id', '=', $args['user_id'])
            ->where('users.user_email', 'LIKE', "%{$args['email']}%")
            ->where('users.user_login', 'LIKE', "%{$args['username']}%")
            ->whereRelation('OR')
            ->whereRaw(
                "sessions.ip LIKE '#hash#%%' AND sessions.ip LIKE %s",
                ["#hash#{$args['ip']}%"]
            )
            ->whereRaw(
                "sessions.ip NOT LIKE '#hash#%%' AND sessions.ip LIKE %s",
                ["{$args['ip']}%"]
            )
            ->getAll();

        return $query ?: [];
    }

    /**
     * Fetch the most recent session for a visitor, including optional joins.
     *
     * @param array $args See method body for accepted keys.
     * @return object|null Decorated session or null.
     * @since 15.0.0
     */
    public function getVisitorData($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'     => [],
            'visitor_id' => '',
            'ip'         => '',
            'decorate'   => true,
            'page_info'  => true,
            'user_info'  => true,
        ]);

        // If visitor_id not given, attempt to resolve via IP
        if (empty($args['visitor_id']) && !empty($args['ip'])) {
            $args['visitor_id'] = Query::select(['visitor_id'])
                ->from('sessions')
                ->where('ip', '=', $args['ip'])
                ->orderBy('ID', 'DESC')
                ->perPage(1)
                ->getVar();
        }

        if (empty($args['visitor_id'])) {
            return null;
        }

        // Define default fields
        $fields = !empty($args['fields']) ? $args['fields'] : [
            'sessions.ID',
            'sessions.platform',
            'sessions.agent',
            'sessions.device_browser_version_id',
            'sessions.device_browser_id',
            'sessions.device_os_id',
            'sessions.device_type_id',
            'sessions.resolution_id',
            'sessions.language_id',
            'sessions.timezone_id',
            'sessions.user_id',
            'sessions.ip',
            'sessions.country_id',
            'sessions.city_id',
            'sessions.started_at',
            'sessions.ended_at',
            'sessions.duration',
            'sessions.total_views',
            'sessions.initial_view_id',
            'sessions.last_view_id',
            'sessions.referrer_id',
        ];

        if ($args['page_info']) {
            $fields[] = 'resources.resource_url AS first_uri';
        }

        if ($args['user_info']) {
            $fields[] = 'users.display_name';
            $fields[] = 'users.user_email';
            $fields[] = 'users.user_login';
            $fields[] = 'users.user_registered';
        }

        $query = Query::select($fields)
            ->from('sessions')
            ->where('sessions.visitor_id', '=', $args['visitor_id'])
            ->orderBy('sessions.started_at', 'DESC')
            ->perPage(1);

        if ($args['page_info']) {
            $query->join('resources', ['sessions.initial_view_id', 'resources.ID'], [], 'LEFT');
        }

        if ($args['user_info']) {
            $query->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT');
        }

        if ($args['decorate']) {
            $query->decorate(SessionDecorator::class);
        }

        return $query->getRow();
    }

    /**
     * Return the chronological journey (views) for a visitor.
     *
     * @param array $args {
     * @type int $visitor_id Required visitor ID.
     * @type bool $ignore_date Whether to ignore date filtering.
     * }
     * @return array[] Rows ordered by `date`.
     * @since 15.0.0
     */
    public function getVisitorJourney($args)
    {
        $args = $this->parseArgs($args, [
            'visitor_id'  => '',
            'ignore_date' => true,
        ]);

        if (empty($args['visitor_id'])) {
            return [];
        }

        return Query::select([
            'views.viewed_at AS date',
            'views.resource_id',
            'resources.resource_url',
            'resources.cached_title',
        ])
            ->from('sessions')
            ->join('views', ['sessions.ID', 'views.session_id'])
            ->join('resources', ['views.resource_id', 'resources.ID'])
            ->where('sessions.visitor_id', '=', $args['visitor_id'])
            ->orderBy('views.viewed_at', 'ASC')
            ->getAll() ?: [];
    }

    /**
     * Count distinct geo-location items (city, region, country, …).
     *
     * @param array $args See method body for accepted keys.
     * @return int Count result.
     * @since 15.0.0
     */
    public function countGeoData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'count_field' => 'country',
            'continent'   => '',
            'country'     => '',
            'region'      => '',
            'city'        => '',
            'not_null'    => '',
        ]);

        $geoMap = [
            'continent' => ['countries', 'continent', ['sessions.country_id', 'countries.ID']],
            'country'   => ['countries', 'code', ['sessions.country_id', 'countries.ID']],
            'region'    => ['cities', 'region_name', ['sessions.city_id', 'cities.ID']],
            'city'      => ['cities', 'city_name', ['sessions.city_id', 'cities.ID']],
        ];

        if (!isset($geoMap[$args['count_field']])) {
            return 0;
        }

        list($table, $column, $join) = $geoMap[$args['count_field']];

        $query = Query::select(["COUNT(DISTINCT {$table}.{$column}) AS total"])
            ->from('sessions')
            ->join($table, $join)
            ->whereDate('sessions.started_at', $args['date']);

        if (!empty($args['continent'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.continent', '=', $args['continent']);
        }

        if (!empty($args['country'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
        }

        if (!empty($args['region'])) {
            $query->join('cities', ['sessions.city_id', 'cities.ID'])
                ->where('cities.region_name', '=', $args['region']);
        }

        if (!empty($args['city'])) {
            $query->join('cities', ['sessions.city_id', 'cities.ID'])
                ->where('cities.city_name', '=', $args['city']);
        }

        if (!empty($args['not_null']) && isset($geoMap[$args['not_null']])) {
            list($ntTable, $ntColumn, $ntJoin) = $geoMap[$args['not_null']];
            $query->join($ntTable, $ntJoin)
                ->whereNotNull("{$ntTable}.{$ntColumn}");
        }

        $result = $query->getVar();

        return $result ? (int)$result : 0;
    }

    /**
     * Aggregate visitor and view counts grouped by geographic columns.
     *
     * @param array $args See method body for accepted keys.
     * @return array[] Result rows.
     * @since 15.0.0
     */
    public function getVisitorsGeoData($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'       => [
                'countries.code as country',
                'cities.city_name as city',
                'cities.region_name as region',
                'countries.continent as continent',
                'COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) as visitors',
                'SUM(sessions.total_views) as views',
            ],
            'date'         => '',
            'country'      => '',
            'city'         => '',
            'region'       => '',
            'continent'    => '',
            'not_null'     => '',
            'post_type'    => '',
            'author_id'    => '',
            'post_id'      => '',
            'per_page'     => '',
            'query_param'  => '',
            'taxonomy'     => '',
            'term'         => '',
            'page'         => 1,
            'group_by'     => 'countries.code',
            'event_name'   => '',
            'event_target' => '',
            'order_by'     => ['visitors', 'views'],
            'order'        => 'DESC',
        ]);

        $query = Query::select($args['fields'])
            ->from('sessions')
            ->whereDate('sessions.started_at', $args['date'])
            ->perPage($args['page'], $args['per_page']);

        $query->join('countries', ['sessions.country_id', 'countries.ID'])
            ->join('cities', ['sessions.city_id', 'cities.ID'])
            ->where('countries.continent', 'IN', $args['continent'])
            ->where('cities.city_name', 'IN', $args['city'])
            ->where('cities.region_name', 'IN', $args['region'])
            ->where('countries.code', 'IN', $args['country']);

        if (!empty($args['not_null'])) {
            $map = [
                'country'   => ['countries', 'code'],
                'city'      => ['cities', 'city_name'],
                'region'    => ['cities', 'region_name'],
                'continent' => ['countries', 'continent']
            ];

            if (isset($map[$args['not_null']])) {
                list($table, $column) = $map[$args['not_null']];
                $query->whereNotNull("{$table}.{$column}");
            }
        }

        $resourceFilters = array_filter(array_intersect_key($args, array_flip([
            'post_type', 'post_id', 'query_param', 'author_id', 'taxonomy', 'term'
        ])));

        if (!empty($resourceFilters)) {
            $query->join('views', ['views.session_id', 'sessions.ID'])
                ->join('resources', ['views.resource_id', 'resources.ID'])
                ->join('posts', ['posts.ID', 'resources.resource_id'], [], 'LEFT')
                ->where('resources.resource_type', 'IN', $args['post_type'])
                ->where('resources.resource_id', '=', $args['post_id'])
                ->where('resources.resource_url', '=', $args['query_param'])
                ->where('resources.cached_author_id', '=', $args['author_id']);

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
        }

        if (!empty($args['event_target']) || !empty($args['event_name'])) {
            $query->join('events', ['events.session_id', 'sessions.ID'])
                ->where('event_name', 'IN', $args['event_name'])
                ->whereJson('event_data', 'target_url', '=', $args['event_target']);
        }

        $query->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order']);

        $result = $query->getAll();

        return $result ?: [];
    }

    /**
     * Fetch sessions whose country/continent information is incomplete.
     *
     * @param bool $returnCount When true, return an integer count instead of rows.
     * @return int|array[] Count or rows depending on `$returnCount`.
     * @since 15.0.0
     */
    public function getVisitorsWithIncompleteLocation($returnCount = false)
    {
        $privateCountry = GeolocationFactory::getProviderInstance()->getPrivateCountryCode();

        $selectFields = $returnCount ? 'COUNT(*)' : ['sessions.ID'];

        $query = Query::select($selectFields)
            ->from('sessions')
            ->join('countries', ['sessions.country_id', 'countries.ID'], [], 'LEFT')
            ->whereRaw(
                "(
                    countries.code = ''
                    OR countries.code = %s
                    OR countries.code IS NULL
                    OR countries.continent = ''
                    OR countries.continent IS NULL
                    OR countries.continent = countries.code
                )
                AND sessions.ip NOT LIKE '#hash#%'",
                [$privateCountry]
            );

        return $returnCount ? intval($query->getVar()) : $query->getAll();
    }

    /**
     * List sessions that have a referrer but no source channel/name assigned.
     *
     * @return array[] Session IDs.
     * @since 15.0.0
     */
    public function getVisitorsWithIncompleteSourceChannel()
    {
        $query = Query::select(['sessions.ID'])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->whereNotNull('sessions.referrer_id')
            ->whereNull('referrers.channel')
            ->whereNull('referrers.name');

        return $query->getAll() ?: [];
    }

    /**
     * Get aggregated referrer data with optional decoration.
     *
     * @param array $args See method body for accepted keys.
     * @return array[]|object[] Result rows (optionally decorated).
     * @since 15.0.0
     */
    public function getReferrers($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'post_type'      => '',
            'source_channel' => '',
            'post_id'        => '',
            'country'        => '',
            'query_param'    => '',
            'taxonomy'       => '',
            'term'           => '',
            'referrer'       => '',
            'not_null'       => 'referrers.domain',
            'group_by'       => 'referrers.domain',
            'page'           => 1,
            'per_page'       => 10,
            'decorate'       => false
        ]);

        $filteredArgs = array_filter($args);

        $query = Query::select([
            'COUNT(DISTINCT IFNULL(sessions.visitor_id, sessions.ID)) AS visitors',
            'referrers.domain AS domain',
            'referrers.channel AS source_channel',
            'referrers.name AS source_name',
            'MAX(sessions.started_at) AS last_counter'
        ])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->where('countries.code', '=', $args['country'])
            ->groupBy($args['group_by'])
            ->orderBy('visitors', 'DESC')
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['source_channel'])) {
            $query->where('referrers.channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['referrer'])) {
            $query->where('referrers.domain', 'LIKE', "%{$args['referrer']}%");
        }

        if (!empty($args['not_null'])) {
            $query->whereNotNull($args['not_null']);
        } else {
            // fallback to broad match for referral
            $query->whereRaw("
                AND (
                    (referrers.domain != '' AND referrers.domain IS NOT NULL)
                    OR (referrers.channel IS NOT NULL AND referrers.channel != '')
                )
            ");
        }

        if (!empty($args['date']) && !array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query->whereDate('sessions.started_at', $args['date']);
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('views', ['views.session_id', 'sessions.ID'])
                ->join('resources', ['views.resource_id', 'resources.ID'])
                ->join('posts', ['posts.ID', 'resources.resource_id'], [], 'LEFT')
                ->where('resources.resource_type', 'IN', $args['post_type'])
                ->where('resources.resource_url', '=', $args['query_param'])
                ->where('resources.resource_id', '=', $args['post_id'])
                ->where('resources.cached_author_id', '=', $args['author_id']);

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
        }

        if ($args['decorate']) {
            $query->decorate(ReferrerDecorator::class);
        }

        return $query->getAll() ?: [];
    }

    /**
     * Count the number of distinct referrers that satisfy the filters.
     *
     * @param array $args See method body for accepted keys.
     * @return int Referrer count.
     * @since 15.0.0
     */
    public function countReferrers($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'source_channel' => '',
            'post_type'      => '',
            'post_id'        => '',
            'country'        => '',
            'query_param'    => '',
            'taxonomy'       => '',
            'term'           => '',
            'not_null'       => 'referrers.domain'
        ]);

        $query = Query::select(['COUNT(DISTINCT referrers.domain) AS total'])
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'])
            ->whereNotNull($args['not_null'])
            ->whereDate('sessions.started_at', $args['date']);

        if (!empty($args['source_channel'])) {
            $query->where('referrers.channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['country'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID'])
                ->where('countries.code', '=', $args['country']);
        }

        $resourceFilters = array_filter(array_intersect_key($args, array_flip([
            'post_type', 'post_id', 'query_param', 'author_id', 'taxonomy', 'term'
        ])));

        if (!empty($resourceFilters)) {
            $query
                ->join('views', ['views.session_id', 'sessions.ID'])
                ->join('resources', ['views.resource_id', 'resources.ID'])
                ->join('posts', ['posts.ID', 'resources.resource_id'], [], 'LEFT')
                ->where('resources.resource_type', 'IN', $args['post_type'])
                ->where('resources.resource_id', '=', $args['post_id'])
                ->where('resources.resource_url', '=', $args['query_param']);

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
        }

        return (int)($query->getVar() ?? 0);
    }

    /**
     * Return day-level statistics for visitors, visits, and referrers.
     *
     * @param array $args See method body for accepted keys.
     * @return array[] Rows keyed by date.
     * @since 15.0.0
     */
    public function getDailyStats($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => DateRange::get('30days'),
            'post_type'     => '',
            'post_id'       => '',
            'resource_type' => '',
            'author_id'     => '',
            'taxonomy'      => '',
            'term_id'       => '',
        ]);

        $range = is_array($args['date']) ? $args['date'] : DateRange::get('30days');
        $start = $range['from'] . ' 00:00:00';
        $end   = date('Y-m-d', strtotime($range['to'] . ' +1 day')) . ' 00:00:00';

        $fields = [
            'DATE(sessions.started_at) AS date',
            'COUNT(DISTINCT sessions.visitor_id) AS visitors',
            'SUM(sessions.total_views) AS visits',
            'COUNT(DISTINCT CASE WHEN referrers.domain IS NOT NULL AND referrers.domain != "" THEN sessions.visitor_id END) AS referrers',
        ];

        $query = Query::select($fields)
            ->from('sessions')
            ->join('referrers', ['sessions.referrer_id', 'referrers.ID'], [], 'LEFT')
            ->where('sessions.started_at', '>=', $start)
            ->where('sessions.started_at', '<', $end)
            ->groupBy('DATE(sessions.started_at)');

        $filteredArgs = array_filter($args);

        if (array_intersect(['post_type', 'post_id', 'resource_type', 'author_id', 'taxonomy', 'term_id'], array_keys($filteredArgs))) {
            $query
                ->join('views', ['views.session_id', 'sessions.ID'])
                ->join('resources', ['views.resource_id', 'resources.ID'])
                ->join('posts', ['posts.ID', 'resources.resource_id'], [], 'LEFT');

            if (!empty($args['resource_type'])) {
                $query->where('resources.resource_type', 'IN', $args['resource_type']);

                if (!empty($args['post_id'])) {
                    $query->where('resources.resource_id', '=', $args['post_id']);
                }
            } else {
                if (!empty($args['post_type'])) {
                    $query->where('posts.post_type', 'IN', $args['post_type']);
                }

                if (!empty($args['post_id'])) {
                    $query->where('posts.ID', '=', $args['post_id']);
                }

                if (!empty($args['author_id'])) {
                    $query->where('posts.post_author', '=', $args['author_id']);
                }
            }

            if (!empty($args['taxonomy']) && !empty($args['term_id']) && empty($args['resource_type'])) {
                $taxQuery = Query::select(['DISTINCT object_id'])
                    ->from('term_relationships')
                    ->join('term_taxonomy', ['term_relationships.term_taxonomy_id', 'term_taxonomy.term_taxonomy_id'])
                    ->join('terms', ['term_taxonomy.term_id', 'terms.term_id'])
                    ->where('term_taxonomy.taxonomy', 'IN', $args['taxonomy'])
                    ->where('terms.term_id', '=', $args['term_id'])
                    ->getQuery();

                $query->joinQuery($taxQuery, ['posts.ID', 'tax.object_id'], 'tax');
            }
        }

        return $query->getAll() ?: [];
    }

    /**
     * Sum total views generated by a specific user on a given date.
     *
     * @param array $args {
     * @type string $date Date to filter.
     * @type int $user_id User ID.
     * }
     * @return int Hit total.
     * @since 15.0.0
     */
    public function getVisitorHits($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'    => '',
            'user_id' => '',
        ]);

        $query = Query::select('SUM(sessions.total_views) as hits')
            ->from('sessions')
            ->where('sessions.user_id', '=', $args['user_id'])
            ->whereDate('sessions.started_at', $args['date']);

        $result = $query->getVar();

        return intval($result);
    }

    /**
     * Count sessions currently online (five-minute window).
     *
     * @return int Number of online sessions.
     * @since 15.0.0
     */
    public function countOnlines($args = [])
    {
        return Query::select('COUNT(*)')
            ->from('sessions')
            ->where('ended_at', '>=', gmdate('Y-m-d H:i:s', time() - 300))
            ->getVar();
    }

    /**
     * Retrieve a paginated list of sessions that are still online.
     *
     * @param array $args {
     * @type int $page Page number.
     * @type int $per_page Items per page.
     * @type string $order_by Column for ordering.
     * @type string $order ASC|DESC.
     * }
     * @return array[]|object[] Decorated session rows.
     * @since 15.0.0
     */
    public function getOnlineVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'page'     => 1,
            'per_page' => '',
            'order_by' => 'sessions.ended_at',
            'order'    => 'DESC',
        ]);

        $query = Query::select([
            'sessions.ID as session_id',
            'sessions.visitor_id',
            'sessions.user_id',
            'sessions.ip',
            'sessions.started_at',
            'sessions.ended_at',
            'sessions.referrer_id',
            'sessions.device_browser_id',
            'sessions.device_os_id',
            'sessions.device_browser_version_id',
            'sessions.country_id',
            'sessions.city_id',
            'sessions.total_views',
            'sessions.initial_view_id',
            'sessions.last_view_id',
            'users.display_name',
            'users.user_email'
        ])
            ->from('sessions')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT')
            ->where('sessions.ended_at', '>=', gmdate('Y-m-d H:i:s', time() - 300))
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->decorate(SessionDecorator::class);

        return $query->getAll() ?: [];
    }
}
