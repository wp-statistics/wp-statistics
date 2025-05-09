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
     *     @type int $visitor_id Visitor ID to search for.
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

        return  $result;
    }

    /**
     * Get the number of unique visitors for a given date or range.
     *
     * Counts unique visitor IDs from the `sessions` table based on session start time.
     *
     * @param array $args {
     *     Optional. Query arguments.
     *
     *     @type string|array $date A string like 'today', or an array ['from' => Y-m-d, 'to' => Y-m-d]
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

        return $result ? (int) $result : 0;
    }

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

        return $result ? (int) $result : 0;
    }

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

        return $result ? (int) $result : 0;
    }

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
            'location'       => ['countries', 'code',          ['sessions.country_id', 'countries.ID']],
            'country'        => ['countries', 'code',          ['sessions.country_id', 'countries.ID']],
            'continent'      => ['countries', 'continent',     ['sessions.country_id', 'countries.ID']],
            'continent_code' => ['countries', 'continent_code',['sessions.country_id', 'countries.ID']],
            'region'         => ['cities',    'region_name',   ['sessions.city_id',    'cities.ID']],
            'city'           => ['cities',    'city_name',     ['sessions.city_id',    'cities.ID']],

            /* devices */
            'device_type'    => ['device_types',            'name',    ['sessions.device_type_id',            'device_types.ID']],
            'platform'       => ['device_oss',              'name',    ['sessions.device_os_id',              'device_oss.ID']],
            'device_os'      => ['device_oss',              'name',    ['sessions.device_os_id',              'device_oss.ID']],
            'agent'          => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'device_browser' => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'version'        => ['device_browser_versions', 'version', ['sessions.device_browser_version_id', 'device_browser_versions.ID']],

            /* tech */
            'resolution'     => ['resolutions', 'ID',       ['sessions.resolution_id', 'resolutions.ID']],
            'language'       => ['languages',   'code',     ['sessions.language_id',   'languages.ID']],
            'timezone'       => ['timezones',   'name',     ['sessions.timezone_id',   'timezones.ID']],

            /* referrers */
            'referrer'       => ['referrers', 'domain',  ['sessions.referrer_id', 'referrers.ID']],
            'source_channel' => ['referrers', 'channel', ['sessions.referrer_id', 'referrers.ID']],
            'source_name'    => ['referrers', 'name',    ['sessions.referrer_id', 'referrers.ID']],
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

        return $result ? (int) $result : 0;
    }

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
            'agent'        => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'browser'      => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'version'      => ['device_browser_versions', 'version', ['sessions.device_browser_version_id', 'device_browser_versions.ID']],
            'platform'     => ['device_oss',              'name',    ['sessions.device_os_id',              'device_oss.ID']],
            'device_type'  => ['device_types',            'name',    ['sessions.device_type_id',            'device_types.ID']],
            'resolution'   => ['resolutions',             'ID',      ['sessions.resolution_id',             'resolutions.ID']],
            'language'     => ['languages',               'code',    ['sessions.language_id',               'languages.ID']],
            'timezone'     => ['timezones',               'name',    ['sessions.timezone_id',               'timezones.ID']],
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
            'agent'          => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'browser'        => ['device_browsers',         'name',    ['sessions.device_browser_id',         'device_browsers.ID']],
            'platform'       => ['device_oss',              'name',    ['sessions.device_os_id',              'device_oss.ID']],
            'device_os'      => ['device_oss',              'name',    ['sessions.device_os_id',              'device_oss.ID']],
            'device_type'    => ['device_types',            'name',    ['sessions.device_type_id',            'device_types.ID']],
            'resolution'     => ['resolutions',             'ID',      ['sessions.resolution_id',             'resolutions.ID']],
            'language'       => ['languages',               'code',    ['sessions.language_id',               'languages.ID']],
            'timezone'       => ['timezones',               'name',    ['sessions.timezone_id',               'timezones.ID']],
            'country'        => ['countries',               'code',    ['sessions.country_id',                'countries.ID']],
            'continent'      => ['countries',               'continent',['sessions.country_id',               'countries.ID']],
            'city'           => ['cities',                  'city_name',['sessions.city_id',                  'cities.ID']],
            'region'         => ['cities',                  'region_name',['sessions.city_id',                'cities.ID']],
            'source_channel' => ['referrers',               'channel', ['sessions.referrer_id',               'referrers.ID']],
            'source_name'    => ['referrers',               'name',    ['sessions.referrer_id',               'referrers.ID']],
            'referrer'       => ['referrers',               'domain',  ['sessions.referrer_id',               'referrers.ID']],
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
            'page'          => '',
            'per_page'      => '',
            'user_info'     => false,
            'date_field'    => 'sessions.started_at',
            'logged_in'     => false,
            'user_role'     => '',
            'event_target'  => '',
            'event_name'    => '',
            'fields'        => [],
            'referrer'      => ''
        ]);

        if ($args['order_by'] === 'hits') {
            $args['order_by'] = 'sessions.total_views';
        }   
             
        if ($args['order_by'] === 'visitor.ID') {
            $args['order_by'] = 'visitors.ID';
        }

        $query = Query::select('*')
            ->from('sessions')
            ->join('visitors', ['sessions.visitor_id', 'visitors.ID'], [], 'LEFT')
            ->join('users', ['sessions.user_id', 'users.ID'], [], 'LEFT')
            ->join('resources', ['sessions.initial_view_id', 'resources.ID'], [], 'LEFT')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->groupBy('sessions.ID')
            ->decorate(SessionDecorator::class);

        $query->whereDate($args['date_field'], $args['date']);
        $query->where('sessions.user_id', '=', $args['user_id']);
        $query->where('sessions.ip', 'LIKE', "%{$args['ip']}%");

        if ($args['logged_in'] === true) {
            $query->where('sessions.user_id', '!=', 0);
            $query->whereNotNull('sessions.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['sessions.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', 'wp_capabilities');
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        if (!empty($args['country'])) {
            $query->join('countries', ['sessions.country_id', 'countries.ID']);
            $query->where('countries.code', '=', $args['country']);
        }

        if (!empty($args['agent'])) {
            $query->join('device_browsers', ['sessions.device_browser_id', 'device_browsers.ID']);
            $query->where('device_browsers.name', '=', $args['agent']);
        }

        if (!empty($args['platform'])) {
            $query->join('device_oss', ['sessions.device_os_id', 'device_oss.ID']);
            $query->where('device_oss.name', '=', $args['platform']);
        }

        if (!empty($args['referrer'])) {
            $query->join('referrers', ['sessions.referrer_id', 'referrers.ID']);
            $query->where('referrers.domain', '=', $args['referrer']);
        }

        if (!empty($args['resource_type'])) {
            $query->where('resources.resource_type', 'IN', $args['resource_type']);
        }

        if (!empty($args['resource_id'])) {
            $query->where('resources.resource_id', '=', $args['resource_id']);
        }

        if (!empty($args['query_param'])) {
            $query->where('resources.resource_url', 'LIKE', "%{$args['query_param']}%");
        }

        return $query->getAll() ?: [];
    }

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
            'country'   => ['countries', 'code',      ['sessions.country_id', 'countries.ID']],
            'region'    => ['cities',    'region_name', ['sessions.city_id', 'cities.ID']],
            'city'      => ['cities',    'city_name', ['sessions.city_id', 'cities.ID']],
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

        return $result ? (int) $result : 0;
    }

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
            ->join('cities', ['sessions.city_id', 'cities.ID']);

        if (!empty($args['country'])) {
            $query->where('countries.code', 'IN', $args['country']);
        }

        if (!empty($args['city'])) {
            $query->where('cities.city_name', 'IN', $args['city']);
        }

        if (!empty($args['region'])) {
            $query->where('cities.region_name', 'IN', $args['region']);
        }

        if (!empty($args['continent'])) {
            $query->where('countries.continent', 'IN', $args['continent']);
        }

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

}
