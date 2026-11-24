<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Utils\Query;

class VisitorsModel extends BaseModel
{
    /**
     * Order for "latest visitors" lists: newest day first, then the visitor's last view, then ID.
     * Visitor IDs follow the first visit of the day, so ordering by ID alone puts a visitor who
     * came back in the evening below one who only came at noon.
     *
     * `last_view` has no index, so getVisitorsData() and getReferredVisitors() first bound a paged list with this order to
     * the days that hold the rows of the page (see getLatestOrderSince()).
     */
    const LATEST_ORDER_BY = ['visitor.last_counter', 'visitor.last_view', 'visitor.ID'];

    /**
     * Page types that hold post rows for the given post types (all listed post types when empty).
     * The `home` type is left out, as countViews() does.
     *
     * @param string|array $postType
     * @return array
     */
    private function getPostResourceTypes($postType)
    {
        return $this->getResourceTypes(!empty($postType) ? $postType : Helper::get_list_post_type(), false);
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
            'exact_ip'      => false,
            'source_name'   => '',
            'source_channel' => '',
            'logged_in'     => false,
            'user_role'     => '',
            'referrer'      => '',
            'not_null'      => '',
            'date_field'    => 'last_counter',
            'referred_visitors' => false,
        ]);

        $filteredArgs = array_filter($args);

        $field = '*';

        if (array_intersect(['resource_type', 'resource_id', 'query_param', 'post_type', 'author_id', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $field = 'DISTINCT visitor.ID';
        }

        $query = Query::select("COUNT($field) as total_visitors")
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('referred', '=', $args['referrer'])
            ->where('source_name', 'IN', $args['source_name'])
            ->whereNotNull($args['not_null'])
            ->whereDate($args['date_field'], $args['date']);

        // Use the same IP and source_channel filters as getVisitorsData() so the total matches the list
        $this->applyIpFilter($query, $args['ip'], $args['exact_ip']);

        // `unassigned` means visitors without a source channel
        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('visitor.source_channel');
        } else {
            $query->where('visitor.source_channel', 'IN', $args['source_channel']);
        }

        // Visitors with a referrer or a non-direct source, same as countReferredVisitors()
        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $this->applyUserRoleFilter($query, $args['user_role']);
            }
        }

        if (array_intersect(['resource_type', 'resource_id', 'query_param'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.uri', '=', $args['query_param']);
        }

        if (array_intersect(['post_type', 'author_id', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            // Only match post rows, like countViews(); home, term and author rows reuse the same IDs
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $this->getPostResourceTypes($args['post_type']))
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id']);

            $this->joinTermFilter($query, $args);
        }

        $result = $query->getVar();
        $total  = $result ? intval($result) : 0;

        $total += $this->historicalModel->getVisitors($args);

        return $total;
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
            'user_role' => ''
        ]);

        $query = Query::select('SUM(visitor.hits) as total_visitors')
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('ip', '=', $args['ip'])
            ->whereDate('last_counter', $args['date']);

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $this->applyUserRoleFilter($query, $args['user_role']);
            }
        }

        $result = $query->getVar();
        $total  = $result ? intval($result) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    public function getVisitorsHits($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'agent'         => '',
            'platform'      => '',
            'country'       => '',
            'user_id'       => '',
            'ip'            => '',
            'source_name'   => '',
            'referrer'      => '',
            'referred_visitors' => false
        ]);

        $query = Query::select(['COUNT(visitor.ID) as visitors', 'SUM(visitor.hits) as hits'])
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('location', '=', $args['country'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('referred', '=', $args['referrer'])
            ->where('ip', '=', $args['ip'])
            ->where('source_name', 'IN', $args['source_name'])
            ->whereDate('last_counter', $args['date']);

        // Visitors with a referrer or a non-direct source, same as countReferredVisitors()
        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        $result = $query->getRow();

        return [
            'visitors' => $result->visitors + $this->historicalModel->getVisitors($args),
            'hits'     => $result->hits + $this->historicalModel->getViews($args)
        ];
    }

    public function countDailyVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'post_type'         => '',
            'resource_id'       => '',
            'resource_type'     => '',
            'author_id'         => '',
            'post_id'           => '',
            'query_param'       => '',
            'taxonomy'          => '',
            'term'              => '',
            'country'           => '',
            'user_id'           => '',
            'logged_in'         => false,
            'include_hits'      => false,
            'user_role'         => '',
            'source_channel'    => '',
            'not_null'          => '',
            'referred_visitors' => false,
            'bypass_cache'      => false
        ]);

        $filteredArgs = array_filter($args);

        $fields = [
            'date'     => 'visitor.last_counter as date',
            'visitors' => 'COUNT(visitor.ID) as visitors'
        ];

        if (!empty($args['include_hits'])) {
            $fields['hits'] = 'SUM(visitor.hits) as hits';
        }

        // If joined to other tables, add DISTINCT to count unique visitors
        if (array_intersect(['resource_type', 'resource_id', 'query_param', 'post_type', 'author_id', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $fields['visitors'] = 'COUNT(DISTINCT visitor.ID) as visitors';
        }

        $query = Query::select($fields)
            ->from('visitor')
            ->where('location', '=', $args['country'])
            ->where('user_id', '=', $args['user_id'])
            ->where('source_channel', 'IN', $args['source_channel'])
            ->whereNotNull($args['not_null'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->groupBy('visitor.last_counter');

        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $this->applyUserRoleFilter($query, $args['user_role']);
            }
        }

        if (array_intersect(['resource_type', 'resource_id', 'query_param'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.uri', '=', $args['query_param']);
        }

        if (array_intersect(['post_type', 'author_id', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            // Only match post rows, like countViews(); home, term and author rows reuse the same IDs
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $this->getPostResourceTypes($args['post_type']))
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id']);

            $this->joinTermFilter($query, $args);
        }

        if (!empty($args['bypass_cache'])) {
            $query->allowCaching(false);
        }

        $result = $query->getAll();

        return $result;
    }

    public function countDailyReferrers($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'           => '',
            'resource_id'    => '',
            'resource_type'  => '',
            'source_channel' => '',
            'source_name'    => '',
            'referrer'       => '',
            'fields'         => [],
            'group_by'       => []
        ]);

        $result = Query::select(array_merge(['COUNT(*) as referrers, last_counter as date'], $args['fields']))
            ->from('visitor')
            ->where('source_channel', '=', $args['source_channel'])
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ")
            ->groupBy(array_merge(['last_counter'], $args['group_by']));

        if (!empty($args['resource_id']) || !empty($args['resource_type'])) {
            $result
                ->join('pages', ['visitor.first_page', 'pages.page_id'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type']);
        }

        return $result->getAll() ?? [];
    }

    /**
     * Returns `COUNT DISTINCT` of a column from visitors table.
     *
     * @param array $args Arguments to include in query (e.g. `field`, `date`, `where_col`, `where_val`, etc.).
     *
     * @return  int
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

        $result = Query::select("COUNT(DISTINCT `{$args['field']}`) as `total`")
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
            ->whereNotNull($args['where_not_null'])
            ->whereDate('last_counter', $args['date'])
            ->perPage(1, 1)
            ->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Returns visitors' device information.
     *
     * @param array $args Arguments to include in query (e.g. `field`, `date`, `group_by`, etc.).
     *
     * @return  array
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

        $result = Query::select([
            $args['field'],
            'COUNT(visitor.ID) AS `visitors`',
        ])
            ->from('visitor')
            ->whereDate('last_counter', $args['date'])
            ->whereNotNull($args['where_not_null'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Returns visitors' device versions for single view pages.
     *
     * @param array $args Arguments to include in query (e.g. `date`, etc.).
     *
     * @return  array
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

        $result = Query::select([
            'CAST(`version` AS SIGNED) AS `casted_version`',
            'COUNT(visitor.ID) AS `visitors`',
        ])
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
            ->whereDate('last_counter', $args['date'])
            ->groupBy('casted_version')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page'])
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Counts the rows that getVisitorsDevicesVersions() returns (distinct versions), for pagination.
     *
     * @param array $args Same arguments as getVisitorsDevicesVersions().
     *
     * @return int
     */
    public function countVisitorsDevicesVersions($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'where_col' => 'agent',
            'where_val' => '',
        ]);

        $result = Query::select('COUNT(DISTINCT CAST(`version` AS SIGNED)) AS `total`')
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
            ->whereDate('last_counter', $args['date'])
            ->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * @deprecated Use SummaryChartDataProvider instead.
     */
    public function getVisitorsSummary($args = [])
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

        $exclude = $args['exclude'] ?? [];
        $summary = [];

        foreach ($periods as $key => $period) {
            if (in_array($key, $exclude, true)) {
                continue; // Skip excluded periods
            }

            $summary[$key] = [
                'label'    => $period['label'],
                'visitors' => $this->countVisitors(array_merge($args, ['date' => DateRange::get($period['date'])])),
            ];
        }

        // Conditionally add 'total' (if not excluded)
        if (!empty($args['include_total']) && !in_array('total', $exclude, true)) {
            $summary['total'] = [
                'label'    => esc_html__('Total', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['ignore_date' => true, 'historical' => true])),
            ];
        }

        return $summary;
    }

    /**
     * @deprecated Use SummaryChartDataProvider instead.
     */
    public function getHitsSummary($args = [])
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

        $exclude = $args['exclude'] ?? [];
        $summary = [];

        foreach ($periods as $key => $period) {
            if (in_array($key, $exclude, true)) {
                continue; // Skip excluded periods
            }

            $summary[$key] = [
                'label' => $period['label'],
                'hits'  => $this->countHits(array_merge($args, ['date' => DateRange::get($period['date'])])),
            ];
        }

        // Conditionally add 'total' (if not excluded)
        if (!empty($args['include_total']) && !in_array('total', $exclude, true)) {
            $summary['total'] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'hits'  => $this->countHits(array_merge($args, ['ignore_date' => true, 'historical' => true])),
            ];
        }

        return $summary;
    }

    /**
     * @deprecated Use SummaryChartDataProvider instead.
     */
    public function getVisitorsHitsSummary($args = [])
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

        $exclude = $args['exclude'] ?? [];
        $summary = [];

        foreach ($periods as $key => $period) {
            if (in_array($key, $exclude)) {
                continue; // Skip excluded periods
            }

            $data = $this->getVisitorsHits(array_merge($args, ['date' => DateRange::get($period['date'])]));

            $summary[$key] = [
                'label'     => $period['label'],
                'visitors'  => $data['visitors'],
                'hits'      => $data['hits'],
            ];
        }

        // Conditionally add 'total' (if not excluded)
        if (!empty($args['include_total']) && !in_array('total', $exclude)) {
            $data = $this->getVisitorsHits(array_merge($args, ['ignore_date' => true, 'historical' => true]));

            $summary['total'] = [
                'label'     => esc_html__('Total', 'wp-statistics'),
                'visitors'  => $data['visitors'],
                'hits'      => $data['hits'],
            ];
        }

        return $summary;
    }

    /**
     * Get a summary of referred visitors from search engines.
     *
     * @param array $args
     *
     * @return array
     */
    public function getSearchEnginesSummary($args = [])
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

        $exclude = $args['exclude'] ?? [];
        $summary = [];

        foreach ($periods as $key => $period) {
            if (in_array($key, $exclude, true)) {
                continue; // Skip excluded periods
            }

            $summary[$key] = [
                'label'          => $period['label'],
                'search_engines' => $this->countReferredVisitors(array_merge($args, ['date' => DateRange::get($period['date']), 'source_channel' => ['search', 'paid_search']])),
            ];
        }

        // Conditionally add 'total' (if not excluded)
        if (!empty($args['include_total']) && !in_array('total', $exclude, true)) {
            $summary['total'] = [
                'label'          => esc_html__('Total', 'wp-statistics'),
                'search_engines' => $this->countReferredVisitors(array_merge($args, ['ignore_date' => true, 'historical' => true, 'source_channel' => ['search', 'paid_search']])),
            ];
        }

        return $summary;
    }

    public function getVisitorsData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'                  => '',
            'resource_type'         => '',
            'resource_id'           => '',
            'post_type'             => '',
            'author_id'             => '',
            'post_id'               => '',
            'country'               => '',
            'agent'                 => '',
            'platform'              => '',
            'user_id'               => '',
            'ip'                    => '',
            'exact_ip'              => false,
            'query_param'           => '',
            'taxonomy'              => '',
            'term'                  => '',
            'order_by'              => 'visitor.ID',
            'order'                 => 'DESC',
            'page'                  => '',
            'per_page'              => '',
            'date_field'            => 'visitor.last_counter',
            'logged_in'             => false,
            'user_role'             => '',
            'event_target'          => '',
            'event_name'            => '',
            'fields'                => [],
            'referrer'              => '',
            'not_null'              => '',
            'source_channel'        => '',
            'referred_visitors'     => '',
            'utm_source'            => '',
            'utm_medium'            => '',
            'utm_campaign'          => '',
            'source_name'           => '',
            'group_by'              => 'visitor.ID',
            'decorate'              => true,
            'exclude_ids'           => [],
            'since'                 => ''
        ]);

        // A paged "latest" list only needs the days that hold its rows: sorting those by last_view
        // is cheap, sorting a whole year of rows is not.
        if ($this->isBoundableLatestList($args)) {
            $args['since'] = $this->getLatestOrderSince($args);
        }

        // Set default fields
        if (empty($args['fields'])) {
            $args['fields'] = [
                'visitor.ID',
                'visitor.ip',
                'visitor.platform',
                'visitor.agent',
                'version',
                'visitor.model',
                'visitor.device',
                'visitor.location',
                'visitor.user_id',
                'visitor.region',
                'visitor.city',
                'visitor.hits',
                'visitor.referred',
                'visitor.last_counter',
                'visitor.source_channel',
                'visitor.source_name',
                'visitor.first_page',
                'visitor.first_view',
                'visitor.last_page',
                'visitor.last_view',
            ];

            // When retrieving data for a single resource, get the page view date
            if (!empty($args['resource_id']) && ($args['resource_type'])) {
                $args['fields'][] = 'visitor_relationships.date as page_view';
            }
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('referred', '=', $args['referrer'])
            ->where('visitor.location', '=', $args['country'])
            ->where('visitor.source_name', 'IN', $args['source_name'])
            ->where('visitor.ID', 'NOT IN', $args['exclude_ids'])
            ->where('visitor.last_counter', '>=', $args['since'])
            ->whereNotNull($args['not_null'])
            ->whereDate($args['date_field'], $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->groupBy($args['group_by']);

        $this->applyIpFilter($query, $args['ip'], $args['exact_ip']);

        if ($args['decorate'] == true) {
            $query->decorate(VisitorDecorator::class);
        }

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('visitor.source_channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $this->applyUserRoleFilter($query, $args['user_role']);
            }
        }

        $filteredArgs = array_filter($args);

        if (array_intersect(['resource_type', 'resource_id', 'query_param', 'utm_source', 'utm_medium', 'utm_campaign'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.uri', '=', $args['query_param'])
                ->where('pages.uri', 'LIKE', $args['utm_source'] ? "%utm_source={$args['utm_source']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_medium'] ? "%utm_medium={$args['utm_medium']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_campaign'] ? "%utm_campaign={$args['utm_campaign']}%" : '');
        }

        if (array_intersect(['post_type', 'post_id', 'author_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id']);

            // Only match post rows, like countVisitors(); a query_param-only filter keeps any page type
            if (array_intersect(['post_type', 'post_id', 'author_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
                $query->where('pages.type', 'IN', $this->getPostResourceTypes($args['post_type']));
            }

            $this->joinTermFilter($query, $args);
        }

        if (!empty($args['event_target']) || !empty($args['event_name'])) {
            $query
                ->join('events', ['events.visitor_id', 'visitor.ID'])
                ->where('event_name', 'IN', $args['event_name'])
                ->whereJson('event_data', 'target_url', '=', $args['event_target']);
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    /**
     * Day of the last row on the requested page when the same list is sorted by day only.
     *
     * LATEST_ORDER_BY sorts by day first, so every row of pages 1..N has a `last_counter` on or after
     * the day of row N*per_page in day order. That row is found from the `date_ip_agent` index (no
     * sort of `last_view`), and the real query then sorts only the rows of those days.
     *
     * @param array  $args   Parsed args of the list method.
     * @param string $method List method that runs the same filters (getVisitorsData or getReferredVisitors).
     * @return string Date (Y-m-d), or '' when the list has fewer rows than the page needs.
     */
    private function getLatestOrderSince($args, $method = 'getVisitorsData')
    {
        $rows = max(1, intval($args['page'])) * intval($args['per_page']);

        $result = $this->$method(array_merge($args, [
            'fields'   => ['visitor.last_counter'],
            'order_by' => 'visitor.last_counter',
            'order'    => 'DESC',
            'page'     => $rows,
            'per_page' => 1,
            'decorate' => false,
            'since'    => '',
        ]));

        return !empty($result[0]->last_counter) ? (string)$result[0]->last_counter : '';
    }

    /**
     * Whether a list can be bounded with getLatestOrderSince(): a paged, newest-first LATEST_ORDER_BY
     * list with no `since` of its own (exports keep the full sort).
     *
     * @param array $args Parsed args of the list method.
     * @return bool
     */
    private function isBoundableLatestList($args)
    {
        return empty($args['since'])
            && empty($args['export'])
            && $args['order_by'] === self::LATEST_ORDER_BY
            && strtoupper((string)$args['order']) === 'DESC'
            && intval($args['per_page']) > 0;
    }

    /**
     * Earliest day among the newest visitor rows, used to bound an all-time "latest visitors" list.
     *
     * The newest `$limit` rows by ID span the days from this date on, so at least `$limit` rows have
     * `last_counter` on or after it, and the first `$limit` rows in LATEST_ORDER_BY order are all
     * among them. Passing it as `since` lets an all-time list sort a day or two of rows instead
     * of the whole table (ID DESC used a primary key scan; last_view has no index).
     *
     * @param int $limit Rows the list needs (page * per_page).
     * @return string Date (Y-m-d), or '' when there are no visitors.
     */
    public function getLatestVisitorsSince($limit = 10)
    {
        $limit = max(1, intval($limit));
        $table = \WP_STATISTICS\DB::table('visitor');

        global $wpdb;

        $since = $wpdb->get_var($wpdb->prepare(
            "SELECT MIN(latest.last_counter) FROM (SELECT last_counter FROM {$table} ORDER BY ID DESC LIMIT %d) AS latest", // phpcs:ignore
            $limit
        ));

        return $since ? (string) $since : '';
    }

    public function getReferredVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'         => ['visitor.ID', 'visitor.ip', 'visitor.platform', 'visitor.agent', 'version', 'visitor.model', 'visitor.device', 'visitor.location', 'visitor.user_id', 'visitor.region', 'visitor.city', 'visitor.hits', 'visitor.referred', 'visitor.last_counter', 'visitor.source_channel', 'visitor.source_name', 'users.display_name', 'users.user_email', 'visitor.first_page', 'visitor.first_view', 'visitor.last_page', 'visitor.last_view'],
            'date'           => '',
            'source_channel' => '',
            'source_name'    => '',
            'referrer'       => '',
            'order_by'       => 'visitor.ID',
            'order'          => 'desc',
            'page'           => '',
            'per_page'       => '',
            'utm_source'     => '',
            'utm_medium'     => '',
            'utm_campaign'   => '',
            'resource_id'    => '',
            'resource_type'  => Helper::getPostTypes(),
            'group_by'       => '',
            'decorate'       => true,
            'since'          => ''
        ]);

        // Same bound as getVisitorsData(): sort only the days that hold the rows of the page
        if ($this->isBoundableLatestList($args)) {
            $args['since'] = $this->getLatestOrderSince($args, 'getReferredVisitors');
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->where('visitor.last_counter', '>=', $args['since'])
            ->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ")
            ->whereDate('visitor.last_counter', $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->groupBy($args['group_by']);

        if ($args['decorate'] == true) {
            $query->decorate(VisitorDecorator::class);
        }

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('source_channel', '=', $args['source_channel']);
        }

        if (!empty($args['utm_source']) || !empty($args['utm_medium']) || !empty($args['utm_campaign']) || !empty($args['resource_id'])) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.uri', 'LIKE', $args['utm_source'] ? "%utm_source={$args['utm_source']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_medium'] ? "%utm_medium={$args['utm_medium']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_campaign'] ? "%utm_campaign={$args['utm_campaign']}%" : '');
        }

        $result = $query->getAll();

        return $result ?? [];
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
            ->from('visitor')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('source_channel', 'IN', $args['source_channel']);
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

        $result = Query::select([
            'visitor.ID',
            'visitor.user_id',
            'visitor.ip',
            'users.display_name',
            'users.user_email',
            'users.user_login'
        ])
            ->from('visitor')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->where('user_id', '=', $args['user_id'])
            ->where('user_email', 'LIKE', "%{$args['email']}%")
            ->where('user_login', 'LIKE', "%{$args['username']}%")
            ->whereRaw(
                "OR (ip LIKE '#hash#%' AND ip LIKE %s)",
                ["#hash#{$args['ip']}%"]
            )
            ->whereRaw(
                "OR (ip NOT LIKE '#hash#%' AND ip LIKE %s)",
                ["{$args['ip']}%"]
            )
            ->whereRelation('OR')
            ->getAll();

        return $result ? $result : [];
    }

    public function getVisitorData($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'     => [],
            'visitor_id' => '',
            'ip'         => '', // not recommended to get visitor data by ip, it's less efficient
            'decorate'   => true,
            'page_info'  => true,
            'user_info'  => true
        ]);

        $fields = !empty($args['fields']) && is_array($args['fields']) ? $args['fields'] : [
            'visitor.ID',
            'visitor.platform',
            'visitor.agent',
            'version',
            'visitor.model',
            'visitor.device',
            'visitor.location',
            'visitor.user_id',
            'visitor.region',
            'visitor.city',
            'visitor.hits',
            'visitor.last_counter',
            'visitor.referred',
            'visitor.source_channel',
            'visitor.source_name',
            'visitor.ip',
            'visitor.first_page',
            'visitor.first_view',
            'visitor.last_page',
            'visitor.last_view'
        ];

        // If visitor_id is empty, get visitor_id by IP
        if (empty($args['visitor_id']) || !empty($args['ip'])) {
            $visitorId = Query::select(['ID'])
                ->from('visitor')
                ->where('ip', '=', $args['ip'])
                ->getVar();

            $args['visitor_id'] = $visitorId ?? '';
        }

        if ($args['page_info']) {
            $fields[] = 'pages.uri as first_uri';
        }

        if ($args['user_info']) {
            $fields[] = 'users.display_name';
            $fields[] = 'users.user_email';
            $fields[] = 'users.user_login';
            $fields[] = 'users.user_registered';
        }

        $query = Query::select($fields)
            ->from('visitor')
            ->where('visitor.ID', '=', $args['visitor_id']);

        if ($args['page_info']) {
            $query
                ->join('pages', ['first_page', 'pages.page_id'], [], 'LEFT');
        }

        if ($args['user_info']) {
            $query
                ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        if ($args['decorate']) {
            $query
                ->decorate(VisitorDecorator::class);
        }

        $result = $query
            ->getRow();

        return $result;
    }

    public function getVisitorJourney($args)
    {
        $args = $this->parseArgs($args, [
            'visitor_id'  => '',
            'ignore_date' => true,
        ]);

        $result = Query::select([
            'date',
            'page_id',
        ])
            ->from('visitor_relationships')
            ->where('visitor_relationships.visitor_id', '=', $args['visitor_id'])
            ->orderBy('date')
            ->getAll();

        return $result;
    }

    /**
     * Page views of several visitor rows (visits) in one query, oldest first.
     * Used by the single visitor journey to show every visit from the same IP or user.
     *
     * Each visit gets its own newest `per_visit` views, so a long recent visit can not push the
     * older visits out of the list (one UNION query, one indexed part per visit).
     *
     * @param array $args {
     *     @type int[] $visitor_ids Visitor row IDs.
     *     @type int   $per_visit   Max views per visit, newest kept (0 = no limit). Default 100.
     *     @type int   $limit       Max rows over all visits (0 = no limit). Default 0.
     * }
     * @return array Rows with visitor_id, date, page_id.
     */
    public function getVisitorsJourneys($args = [])
    {
        $args = $this->parseArgs($args, [
            'visitor_ids' => [],
            'per_visit'   => 100,
            'limit'       => 0,
        ]);

        $ids = array_values(array_unique(array_filter(array_map('intval', (array)$args['visitor_ids']))));

        if (empty($ids)) {
            return [];
        }

        $fields   = [
            'visitor_relationships.ID',
            'visitor_relationships.visitor_id',
            'visitor_relationships.date',
            'visitor_relationships.page_id',
        ];
        $perVisit = intval($args['per_visit']);

        if ($perVisit > 0) {
            // The row ID keeps two views of the same page in the same second apart (UNION drops duplicates).
            $parts = [];
            foreach ($ids as $id) {
                $parts[] = Query::select($fields)
                    ->from('visitor_relationships')
                    ->where('visitor_relationships.visitor_id', '=', $id)
                    ->orderBy(['visitor_relationships.date', 'visitor_relationships.ID'], 'DESC')
                    ->perPage(1, $perVisit);
            }

            $query = count($parts) === 1 ? $parts[0] : Query::union($parts)->orderBy(['date', 'ID'], 'DESC');
        } else {
            $query = Query::select($fields)
                ->from('visitor_relationships')
                ->where('visitor_relationships.visitor_id', 'IN', $ids)
                ->orderBy(['visitor_relationships.date', 'visitor_relationships.ID'], 'DESC');
        }

        // Newest first so a limit drops the oldest views, then flip back to oldest first.
        if (intval($args['limit']) > 0) {
            $query->perPage(1, intval($args['limit']));
        }

        $result = $query->getAll();

        return $result ? array_reverse($result) : [];
    }

    /**
     * SQL that puts every visitor without a known country (NULL, empty or the unknown code) in one
     * group, so lists, totals and pages agree on a single "(not set)" row.
     */
    const UNKNOWN_COUNTRY_SQL = "COALESCE(NULLIF(visitor.location, ''), '000')";

    /**
     * Filters shared by getVisitorsGeoData(), countGeoData() and countGeoVisitors(), so a list,
     * its row count and its "Share" base always look at the same visitors.
     *
     * @param array $defaults Extra defaults of the calling method.
     * @return array Defaults for parseArgs().
     */
    private function geoFilterDefaults($defaults = [])
    {
        return array_merge([
            'date'                  => '',
            'country'               => '',
            'city'                  => '',
            'region'                => '',
            'continent'             => '',
            'not_null'              => '',
            'post_type'             => '',
            'author_id'             => '',
            'post_id'               => '',
            'query_param'           => '',
            'taxonomy'              => '',
            'term'                  => '',
            'source_channel'        => '',
            'event_name'            => '',
            'event_target'          => '',
            'utm_source'            => '',
            'utm_medium'            => '',
            'utm_campaign'          => '',
            'referrer'              => '',
            'resource_id'           => '',
            'resource_type'         => '',
            'referred_visitors'     => false,
            'merge_unknown_country' => false,
        ], $defaults);
    }

    /**
     * Whether the geo filters join other tables (so one visitor can match several rows).
     *
     * @param array $args
     * @return bool
     */
    private function geoFiltersJoin($args)
    {
        return (bool)array_intersect(['resource_type', 'resource_id', 'query_param', 'post_type', 'author_id', 'post_id', 'taxonomy', 'term', 'event_name', 'event_target', 'utm_source', 'utm_medium', 'utm_campaign'], array_keys(array_filter($args)));
    }

    /**
     * Apply the shared geo filters to a query on the visitor table.
     *
     * @param Query $query
     * @param array $args Parsed with geoFilterDefaults().
     * @return void
     */
    private function applyGeoFilters($query, $args)
    {
        $filteredArgs = array_filter($args);

        $query
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->where('visitor.referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null']);

        // `unassigned` means visitors without a source channel, as in countVisitors()
        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('visitor.source_channel');
        } else {
            $query->where('visitor.source_channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        if (array_intersect(['resource_id', 'resource_type'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type']);
        }

        if (array_intersect(['utm_source', 'utm_medium', 'utm_campaign'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [])
                ->where('pages.uri', 'LIKE', $args['utm_source'] ? "%utm_source={$args['utm_source']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_medium'] ? "%utm_medium={$args['utm_medium']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_campaign'] ? "%utm_campaign={$args['utm_campaign']}%" : '');
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'author_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('pages.uri', '=', $args['query_param']);

            $this->joinTermFilter($query, $args);
        }

        if (!empty($args['event_target']) || !empty($args['event_name'])) {
            $query
                ->join('events', ['events.visitor_id', 'visitor.ID'])
                ->where('event_name', 'IN', $args['event_name'])
                ->whereJson('event_data', 'target_url', '=', $args['event_target']);
        }
    }

    public function countGeoData($args = [])
    {
        $args = $this->parseArgs($args, $this->geoFilterDefaults([
            'count_field'  => 'location',
            'count_fields' => [],
            'include_null' => false,
        ]));

        // Count the same groups that getVisitorsGeoData() lists
        if (!empty($args['merge_unknown_country']) && $args['count_field'] === 'location' && empty($args['count_fields'])) {
            // Unknown locations are one group, as in the list
            $countValue = 'COUNT(DISTINCT ' . self::UNKNOWN_COUNTRY_SQL . ')';
            $notNull    = $args['not_null'];
        } elseif (!empty($args['count_fields'])) {
            $parts      = array_map(function ($field) {
                // Keep NULL and '' apart, as GROUP BY does
                return "IFNULL(CONCAT('v', {$field}), 'n')";
            }, (array)$args['count_fields']);
            $countValue = "COUNT(DISTINCT CONCAT_WS('|', " . implode(', ', $parts) . '))';
            $notNull    = $args['not_null'] ?: "visitor.{$args['count_field']}";
        } else {
            $countValue = "COUNT(DISTINCT visitor.{$args['count_field']})";

            // The list can keep rows without a value as one more row
            if ($args['include_null']) {
                $countValue .= " + COALESCE(MAX(visitor.{$args['count_field']} IS NULL), 0)";
            }

            $notNull = $args['include_null'] ? $args['not_null'] : ($args['not_null'] ?: "visitor.{$args['count_field']}");
        }

        $query = Query::select(["{$countValue} as total"])->from('visitor');

        $this->applyGeoFilters($query, array_merge($args, ['not_null' => $notNull]));

        $result = $query->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Counts the visitors behind all rows of getVisitorsGeoData() (every page, not only the one shown).
     *
     * This is the base of the "Share" column on the Geographic tables: each row's visitors divided by
     * this total, so the column adds up to 100% over all rows.
     *
     * @param array $args Same filters as getVisitorsGeoData() (see geoFilterDefaults()).
     *
     * @return int
     */
    public function countGeoVisitors($args = [])
    {
        $args = $this->parseArgs($args, $this->geoFilterDefaults());

        // Joined rows repeat a visitor: count each one once, as the list does
        $count = $this->geoFiltersJoin($args) ? 'COUNT(DISTINCT visitor.ID)' : 'COUNT(visitor.ID)';
        $query = Query::select("{$count} as total")->from('visitor');

        $this->applyGeoFilters($query, $args);

        $result = $query->getVar();

        return $result ? intval($result) : 0;
    }

    public function getVisitorsGeoData($args = [])
    {
        $args = $this->parseArgs($args, $this->geoFilterDefaults([
            'fields'   => [
                'city'      => 'visitor.city as city',
                'country'   => 'visitor.location as country',
                'region'    => 'visitor.region as region',
                'continent' => 'visitor.continent as continent',
                'visitors'  => 'COUNT(visitor.ID) as visitors',
                'views'     => 'SUM(visitor.hits) as views', // All views are counted and results can't be filtered by author, post type, etc...
            ],
            'per_page' => '',
            'page'     => 1,
            'group_by' => 'visitor.location',
            'order_by' => ['visitors', 'views'],
            'order'    => 'DESC',
        ]));

        // If joined to other tables, add DISTINCT to count unique visitors
        if (isset($args['fields']['visitors']) && $this->geoFiltersJoin($args)) {
            $args['fields']['visitors'] = 'COUNT(DISTINCT visitor.ID) as visitors';
        }

        // One "(not set)" group for NULL, empty and unknown locations (the count does the same)
        if (!empty($args['merge_unknown_country']) && isset($args['fields']['country'])) {
            $args['fields']['country'] = self::UNKNOWN_COUNTRY_SQL . ' as country';
            $args['group_by']          = array_map(function ($field) {
                return in_array($field, ['visitor.location', 'location'], true) ? self::UNKNOWN_COUNTRY_SQL : $field;
            }, (array)$args['group_by']);
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order']);

        $this->applyGeoFilters($query, $args);

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getVisitorsWithIncompleteLocation($returnCount = false)
    {
        $privateCountry = GeolocationFactory::getProviderInstance()->getPrivateCountryCode();

        // Determine the select fields based on the returnCount parameter
        $selectFields = $returnCount ? 'COUNT(*)' : ['ID'];

        // Build the query
        $query = Query::select($selectFields)
            ->from('visitor')
            ->whereRaw(
                "(location = ''
            OR location = %s
            OR location IS NULL
            OR continent = ''
            OR continent IS NULL
            OR (continent = location))
            AND ip NOT LIKE '#hash#%'",
                [$privateCountry]
            );

        // Execute the query and return the result based on the returnCount parameter
        if ($returnCount) {
            return intval($query->getVar());
        } else {
            return $query->getAll();
        }
    }

    public function getVisitorsWithIncompleteSourceChannel($args = [])
    {
        $result = Query::select([
            'visitor.ID'
        ])
            ->from('visitor')
            ->whereNotNull('referred')
            ->whereNull('source_channel')
            ->whereNull('source_name')
            ->getAll();

        return $result ? $result : [];
    }

    public function updateVisitor($id, $data)
    {
        Query::update('visitor')
            ->set($data)
            ->where('ID', '=', $id)
            ->execute();
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
            'not_null'       => 'visitor.referred',
            'group_by'       => 'visitor.referred',
            'page'           => 1,
            'per_page'       => 10,
            'decorate'       => false,
            'utm_source'    => '',
            'utm_medium'    => '',
            'utm_campaign'  => '',
            'resource_id'   => '',
            'resource_type' => ''
        ]);

        $filteredArgs = array_filter($args);

        $fields = [
            'visitors'       => 'COUNT(visitor.ID) AS visitors',
            'referred'       => 'visitor.referred',
            'source_channel' => 'visitor.source_channel',
            'source_name'    => 'visitor.source_name',
            'last_counter'   => 'visitor.last_counter'
        ];

        // If joined to other tables, add DISTINCT to count unique visitors
        if (array_intersect(['post_type', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $fields['visitors'] = 'COUNT(DISTINCT visitor.ID) AS visitors';
        }

        $query = Query::select($fields)
            ->from('visitor')
            ->where('visitor.location', '=', $args['country'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null'])
            ->groupBy($args['group_by'])
            ->orderBy('visitors')
            ->perPage($args['page'], $args['per_page']);

        // If not null is not set, get all referrers including those coming with just UTM without any source
        if (empty($args['not_null'])) {
            $query->whereRaw("AND ((visitor.referred != '') OR (visitor.source_channel IS NOT NULL))");
        }

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('visitor.source_channel');
        } else {
            $query->where('source_channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['referrer'])) {
            $query->where('visitor.referred', 'LIKE', "%{$args['referrer']}%");
        }

        if (array_intersect(['resource_id', 'resource_type', 'query_param', 'utm_source', 'utm_medium', 'utm_campaign'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [], 'LEFT')
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.uri', '=', $args['query_param'])
                ->where('pages.uri', 'LIKE', $args['utm_source'] ? "%utm_source={$args['utm_source']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_medium'] ? "%utm_medium={$args['utm_medium']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_campaign'] ? "%utm_campaign={$args['utm_campaign']}%" : '');
        }

        if (array_intersect(['post_type', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id']);

            $this->joinTermFilter($query, $args);
        }

        if ($args['decorate']) {
            $query->decorate(ReferralDecorator::class);
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

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
            'referrer'       => '',
            'not_null'       => 'visitor.referred',
            'group_by'       => '',
            'utm_source'     => '',
            'utm_medium'     => '',
            'utm_campaign'   => '',
            'resource_id'    => '',
            'resource_type'  => '',
            'sum_visitors'   => false,
        ]);

        $filteredArgs = array_filter($args);

        // `sum_visitors` counts the visitors (referrals) behind all rows instead of the rows,
        // the base of the "Share" column: same filters, so the column adds up to 100%.
        if ($args['sum_visitors']) {
            $select = array_intersect(['post_type', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))
                ? 'COUNT(DISTINCT visitor.ID)'
                : 'COUNT(visitor.ID)';
        } else {
            $select = empty($args['group_by']) ? 'COUNT(DISTINCT visitor.referred)' : 'visitor.referred';
        }

        // Use the same filters as getReferrers() so the total matches the list
        $query = Query::select($select)
            ->from('visitor')
            ->where('visitor.location', '=', $args['country'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null']);

        // If not null is not set, get all referrers including those coming with just UTM without any source
        if (empty($args['not_null'])) {
            $query->whereRaw("AND ((visitor.referred != '') OR (visitor.source_channel IS NOT NULL))");
        }

        // When source_channel is `unassigned`, only count visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query->whereNull('visitor.source_channel');
        } else {
            $query->where('source_channel', 'IN', $args['source_channel']);
        }

        if (!empty($args['referrer'])) {
            $query->where('visitor.referred', 'LIKE', "%{$args['referrer']}%");
        }

        if (array_intersect(['resource_id', 'resource_type', 'query_param', 'utm_source', 'utm_medium', 'utm_campaign'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [], 'LEFT')
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.uri', '=', $args['query_param'])
                ->where('pages.uri', 'LIKE', $args['utm_source'] ? "%utm_source={$args['utm_source']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_medium'] ? "%utm_medium={$args['utm_medium']}%" : '')
                ->where('pages.uri', 'LIKE', $args['utm_campaign'] ? "%utm_campaign={$args['utm_campaign']}%" : '');
        }

        if (array_intersect(['post_type', 'post_id', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id']);

            $this->joinTermFilter($query, $args);
        }

        // When the list is grouped by more than the referrer (e.g. referrer + channel), count the grouped rows
        if (!empty($args['group_by']) && !$args['sum_visitors']) {
            $groupedQuery = $query
                ->groupBy($args['group_by'])
                ->getQuery();

            $query = Query::select('COUNT(*)')
                ->fromQuery($groupedQuery, 'referrers');
        }

        $result = $query->getVar();

        return $result ? $result : 0;
    }

    /**
     * Returns visitors, visits and referrers for the past given days, separated daily.
     *
     * @param array $args Arguments to include in query (e.g. `date`, `post_type`, `post_id`, etc.).
     *
     * @return  array   Format: `[{'date' => "STRING", 'visitors' => INT, 'visits' => INT, 'referrers' => INT}, ...]`.
     *
     * @deprecated Do NOT use this class anymore as it's been deprecated. Instead, use countDailyVisitors, countDailyViews, and countDailyReferrers
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

        $range = DateRange::get('30days');

        $startDate = $range['from'] . ' 00:00:00';
        $endDate   = gmdate('Y-m-d', strtotime($range['to'] . ' +1 day')) . ' 00:00:00';

        $fields = [
            '`visitor`.`last_counter` AS `date`',
            'COUNT(DISTINCT `visitor`.`ID`) AS `visitors`',
            'SUM(`visitor`.`hits`) AS `visits`',
            'COUNT(DISTINCT CASE WHEN(`visitor`.`referred` <> "") THEN `visitor`.`ID` END) AS `referrers`',
        ];
        if (is_numeric($args['post_id']) || !empty($args['author_id']) || !empty($args['term_id'])) {
            // For single pages/posts/authors/terms
            $fields[2] = 'SUM(DISTINCT `pages`.`count`) AS `visits`';
        }

        $query = Query::select($fields)->from('visitor');
        $query->where('visitor.last_counter', '>=', $startDate)
            ->where('visitor.last_counter', '<', $endDate)
            ->groupBy('visitor.last_counter');

        $filteredArgs = array_filter($args);
        if (array_intersect(['post_type', 'post_id', 'resource_type', 'author_id', 'taxonomy', 'term_id'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['`visitor_relationships`.`visitor_id`', '`visitor`.`ID`'])
                ->join('pages', '`visitor_relationships`.`page_id` = `pages`.`page_id` AND `visitor`.`last_counter` = `pages`.`date`');

            if (!empty($args['resource_type'])) {
                $query
                    ->where('pages.type', 'IN', $args['resource_type']);

                if (is_numeric($args['post_id'])) {
                    $query->where('pages.ID', '=', intval($args['post_id']));
                }
            } else {
                $query->join('posts', ['`posts`.`ID`', '`pages`.`id`']);

                if (!empty($args['post_type'])) {
                    $query->where('posts.post_type', 'IN', $args['post_type']);
                }

                if (is_numeric($args['post_id'])) {
                    $query->where('posts.ID', '=', intval($args['post_id']));
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

                $query
                    ->joinQuery($taxQuery, ['posts.ID', 'tax.object_id'], 'tax');
            }
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getVisitorHits($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'    => '',
            'user_id' => '',
        ]);

        $query = Query::select('SUM(visitor.hits) as hits')
            ->from('visitor')
            ->where('user_id', '=', $args['user_id'])
            ->whereDate('last_counter', $args['date']);

        $result = $query->getVar();

        return intval($result);
    }

    /**
     * Returns the bounce rate as a percentage.
     *
     * A bounce is a visit that viewed only one page. WP Statistics stores one visitor row per
     * IP/browser per calendar day, so a "visit" here means one visitor on one day, not a
     * 30-minute session. Reloading the same page counts as a second view, so a reload is not
     * a bounce.
     *
     * When a resource is passed, the rate is scoped to the visits that landed on that resource
     * (the landing page bounce rate). Without a resource it returns the site-wide bounce rate.
     *
     * @param array $args
     * @return float
     */
    public function getBounceRate($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_id'   => '',
            'resource_type' => '',
            'query_param'   => ''
        ]);

        $bounceVisitors = $this->countBounceVisitors($args);

        $totalVisitors = $this->isResourceScoped($args)
            ? $this->countEntryPageVisitors($args)
            : $this->countVisitors(['date' => $args['date']]);

        return Helper::calculatePercentage($bounceVisitors, $totalVisitors);
    }

    /**
     * Counts the visits that viewed a single page.
     *
     * `visitor.hits` is incremented on every recorded hit, so `hits = 1` is the number of
     * single-page visits without touching any other table.
     *
     * @param array $args
     * @return int
     */
    public function countBounceVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_id'   => '',
            'resource_type' => '',
            'query_param'   => ''
        ]);

        $query = Query::select(['COUNT(DISTINCT visitor.ID) as visitors'])
            ->from('visitor')
            ->where('visitor.hits', '=', 1)
            ->whereDate('visitor.last_counter', $args['date']);

        if ($this->isResourceScoped($args)) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.uri', '=', $args['query_param']);
        }

        return intval($query->getVar());
    }

    /**
     * Whether the given arguments limit the query to a specific resource.
     *
     * @param array $args
     * @return bool
     */
    private function isResourceScoped($args)
    {
        return !empty($args['resource_id']) || !empty($args['resource_type']) || !empty($args['query_param']);
    }

    public function countEntryPageVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_id'   => '',
            'resource_type' => '',
            'query_param'   => ''
        ]);

        $query = Query::select(['COUNT(visitor.ID) as visitors'])
            ->from('visitor')
            ->join('pages', ['visitor.first_page', 'pages.page_id'])
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('last_counter', $args['date']);

        $result = $query->getVar();

        return $result ?? 0;
    }

    public function countExitPageVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_id'   => '',
            'resource_type' => '',
            'query_param'   => ''
        ]);

        $query = Query::select(['COUNT(visitor.ID) as visitors'])
            ->from('visitor')
            ->join('pages', ['visitor.last_page', 'pages.page_id'])
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('last_counter', $args['date']);

        $result = $query->getVar();

        return $result ?? 0;
    }

    public function getEntryPages($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'resource_type'     => Helper::getPostTypes(),
            'page'              => 1,
            'per_page'          => Admin_Template::$item_per_page,
            'author_id'         => '',
            'uri'               => '',
            'order_by'          => 'visitors',
            'order'             => 'DESC',
            'source_channel'    => '',
            'not_null'          => '',
            'referred_visitors' => false
        ]);

        $query = Query::select([
            'COUNT(visitor.ID) as visitors',
            'COALESCE(SUM(CASE WHEN visitor.hits = 1 THEN 1 ELSE 0 END) / COUNT(visitor.ID), 0) * 100 as bounce_rate',
            'pages.id as post_id',
            'pages.page_id',
            'posts.post_title',
            'posts.post_date'
        ])
            ->from('visitor')
            ->join('pages', ['visitor.first_page', 'pages.page_id'])
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('visitor.source_channel', 'IN', $args['source_channel'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['uri'])
            ->where('posts.post_author', '=', $args['author_id'])
            ->whereNotNull($args['not_null'])
            ->whereDate('last_counter', $args['date'])
            ->groupBy('pages.id')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        return $query->getAll();
    }

    /**
     * Key used by getPagesEngagement() for one page row.
     *
     * Rows with a resource id are keyed by id (the views lists group them by id);
     * rows with id 0 (home, 404, archives, search) are keyed by type + uri.
     *
     * @param int|string $id
     * @param string $uri
     * @param string $type
     * @return string
     */
    public static function pageEngagementKey($id, $uri = '', $type = '')
    {
        return intval($id) > 0 ? 'id:' . intval($id) : 'uri:' . $type . '|' . $uri;
    }

    /**
     * Visitors and bounce rate for a short list of page rows, e.g. the rows a
     * Top Pages card already shows. Two grouped queries, limited to those rows:
     *  - visitors: distinct visitors from visitor_relationships (as PostsModel::getPostsReportData()).
     *  - bounce rate: visitors who landed on the page (visitor.first_page) and viewed only
     *    one page (hits = 1), over all who landed on it (as getEntryPages()).
     *
     * @param array $args {
     *  @type array        $date          ['from' => 'Y-m-d', 'to' => 'Y-m-d'].
     *  @type array        $resources     Rows with `id`, `uri` and `type` (objects or arrays).
     *  @type array|string $id_types      Optional page types the id rows must have (e.g. post types only).
     *  @type array        $fields        'visitors' and/or 'bounce_rate'. Both by default.
     * }
     * @return array Keyed by pageEngagementKey(): ['visitors' => int, 'entrances' => int, 'bounces' => int, 'bounce_rate' => float|null].
     *               bounce_rate is null when nobody landed on the page.
     */
    public function getPagesEngagement($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'resources' => [],
            'id_types'  => [],
            'fields'    => ['visitors', 'bounce_rate'],
        ]);

        $ids  = [];
        $uris = [];
        foreach ((array)$args['resources'] as $row) {
            $row = (object)$row;
            $id  = isset($row->id) ? intval($row->id) : 0;

            if ($id > 0) {
                $ids[$id] = $id;
            } elseif (isset($row->uri) && $row->uri !== '') {
                $uris[$row->uri] = (string)$row->uri;
            }
        }

        $result = [];
        if (empty($ids) && empty($uris)) {
            return $result;
        }

        $idTypes = $this->getResourceTypes($args['id_types'], true);

        // Build "AND (pages.id IN (...) OR (pages.id = 0 AND pages.uri IN (...)))"
        $parts  = [];
        $values = [];
        if (!empty($ids)) {
            $part = 'pages.id IN (' . implode(', ', array_fill(0, count($ids), '%d')) . ')';
            $values = array_merge($values, array_values($ids));

            if (!empty($idTypes)) {
                $part   .= ' AND pages.type IN (' . implode(', ', array_fill(0, count($idTypes), '%s')) . ')';
                $values = array_merge($values, array_values($idTypes));
                $part   = "($part)";
            }

            $parts[] = $part;
        }
        if (!empty($uris)) {
            $parts[] = '(pages.id = 0 AND pages.uri IN (' . implode(', ', array_fill(0, count($uris), '%s')) . '))';
            $values  = array_merge($values, array_values($uris));
        }
        $condition = '(' . implode(' OR ', $parts) . ')';

        // id 0 rows are told apart by uri + type; other rows by id only
        $uriExpr  = "CASE WHEN pages.id = 0 THEN pages.uri ELSE '' END";
        $typeExpr = "CASE WHEN pages.id = 0 THEN pages.type ELSE '' END";

        $blank = function () {
            return ['visitors' => 0, 'entrances' => 0, 'bounces' => 0, 'bounce_rate' => null];
        };

        if (in_array('visitors', (array)$args['fields'], true)) {
            $rows = Query::select([
                'pages.id AS id',
                "$uriExpr AS match_uri",
                "$typeExpr AS match_type",
                'COUNT(DISTINCT visitor_relationships.visitor_id) AS visitors',
            ])
                ->from('visitor_relationships')
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'])
                ->whereDate('visitor_relationships.date', $args['date'])
                ->whereRaw('AND ' . $condition, $values)
                ->groupBy(['pages.id', $uriExpr, $typeExpr])
                ->getAll();

            foreach ((array)$rows as $row) {
                $key = self::pageEngagementKey($row->id, $row->match_uri, $row->match_type);
                $result[$key] = $result[$key] ?? $blank();
                $result[$key]['visitors'] = intval($row->visitors);
            }
        }

        if (in_array('bounce_rate', (array)$args['fields'], true)) {
            // Count landings per first_page first (one pass over the visitor table, grouped
            // by an integer), then join the small result to pages. Joining every visitor
            // row to pages and grouping by the uri/type expressions was 2x slower on big sites.
            $landingsQuery = Query::select([
                'visitor.first_page',
                'COUNT(visitor.ID) AS entrances',
                'SUM(CASE WHEN visitor.hits = 1 THEN 1 ELSE 0 END) AS bounces',
            ])
                ->from('visitor')
                ->whereDate('visitor.last_counter', $args['date'])
                ->groupBy('visitor.first_page')
                ->getQuery();

            $rows = Query::select([
                'pages.id AS id',
                "$uriExpr AS match_uri",
                "$typeExpr AS match_type",
                'SUM(landings.entrances) AS entrances',
                'SUM(landings.bounces) AS bounces',
            ])
                ->fromQuery($landingsQuery, 'landings')
                ->join('pages', ['landings.first_page', 'pages.page_id'])
                ->whereRaw($condition, $values)
                ->groupBy(['pages.id', $uriExpr, $typeExpr])
                ->getAll();

            foreach ((array)$rows as $row) {
                $key       = self::pageEngagementKey($row->id, $row->match_uri, $row->match_type);
                $entrances = intval($row->entrances);
                $bounces   = intval($row->bounces);

                $result[$key]              = $result[$key] ?? $blank();
                $result[$key]['entrances'] = $entrances;
                $result[$key]['bounces']   = $bounces;
                $result[$key]['bounce_rate'] = $entrances > 0 ? round($bounces / $entrances * 100, 1) : null;
            }
        }

        return $result;
    }

    /**
     * Adds `visitors` and `bounce_rate` to each page row (objects with id, uri, type).
     * Rows nobody visited get 0 visitors; rows nobody landed on get a null bounce rate.
     *
     * @param array $rows
     * @param array $args Same as getPagesEngagement(), without `resources`.
     * @return array The same rows.
     */
    public function attachPagesEngagement($rows, $args = [])
    {
        if (empty($rows) || !is_array($rows)) {
            return $rows;
        }

        $engagement = $this->getPagesEngagement(array_merge($args, ['resources' => $rows]));
        $fields     = $args['fields'] ?? ['visitors', 'bounce_rate'];

        foreach ($rows as $row) {
            $key  = self::pageEngagementKey($row->id ?? 0, $row->uri ?? '', $row->type ?? '');
            $data = $engagement[$key] ?? null;

            if (in_array('visitors', $fields, true)) {
                $row->visitors = $data ? $data['visitors'] : 0;
            }

            if (in_array('bounce_rate', $fields, true)) {
                $row->bounce_rate = $data ? $data['bounce_rate'] : null;
            }
        }

        return $rows;
    }

    public function countEntryPages($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'resource_type'     => Helper::getPostTypes(),
            'author_id'         => '',
            'uri'               => '',
            'source_channel'    => '',
            'not_null'          => '',
            'referred_visitors' => false
        ]);

        // Keep the same filters as getEntryPages() so the total matches the list
        $query = Query::select('COUNT(DISTINCT pages.id)')
            ->from('visitor')
            ->join('pages', ['visitor.first_page', 'pages.page_id'])
            ->where('visitor.source_channel', 'IN', $args['source_channel'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['uri'])
            ->whereNotNull($args['not_null'])
            ->whereDate('last_counter', $args['date']);

        if (!empty($args['referred_visitors'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '')
                    OR
                    (visitor.source_channel IS NOT NULL AND visitor.source_channel != 'direct')
                )
            ");
        }

        if (!empty($args['author_id'])) {
            $query
                ->join('posts', ['posts.ID', 'pages.id'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        $result = $query->getVar();

        return intval($result);
    }

    public function getExitPages($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => Helper::getPostTypes(),
            'page'          => 1,
            'per_page'      => Admin_Template::$item_per_page,
            'author_id'     => '',
            'uri'           => '',
            'order_by'      => 'exits',
            'order'         => 'DESC'
        ]);

        $subQuery = Query::select("pages.id, COUNT(DISTINCT visitor.ID) as visitors")
            ->from('visitor')
            ->join('visitor_relationships', ['visitor.ID', 'visitor_relationships.visitor_id'])
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->groupBy('pages.id')
            ->getQuery();

        $query = Query::select([
            'page_visitors.visitors as visitors',
            'COUNT(visitor.ID) as exits',
            'pages.id as post_id, pages.page_id',
            "COALESCE(COUNT(visitor.ID) / page_visitors.visitors, 0) * 100 AS exit_rate"
        ])
            ->from('visitor')
            ->join('pages', ['visitor.last_page', 'pages.page_id'])
            ->joinQuery($subQuery, ['pages.id', 'page_visitors.id'], 'page_visitors')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['uri'])
            ->whereDate('last_counter', $args['date'])
            ->groupBy('pages.id')
            ->orderBy($args['order_by'], $args['order'])
            ->perPage($args['page'], $args['per_page']);

        if (!empty($args['author_id'])) {
            $query
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('posts.post_author', '=', $args['author_id']);
        }

        $result = $query->getAll();

        return $result;
    }

    public function countExitPages($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => Helper::getPostTypes(),
            'author_id'     => '',
            'uri'           => ''
        ]);

        $query = Query::select('COUNT(DISTINCT pages.id)')
            ->from('visitor')
            ->join('pages', ['visitor.last_page', 'pages.page_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['uri'])
            ->whereDate('last_counter', $args['date']);

        if (!empty($args['author_id'])) {
            $query
                ->join('posts', ['posts.ID', 'pages.id'])
                ->where('posts.post_author', '=', $args['author_id']);
        }

        $result = $query->getVar();

        return intval($result);
    }

    /**
     * Filter visitors by IP.
     *
     * A partial value matches anywhere in the stored IP, so a typed fragment or
     * a shortened hash (shown as "#abcd1234") finds the visitor. With $exact,
     * only the same stored IP matches.
     *
     * @param Query $query
     * @param string $ip
     * @param bool $exact
     * @return void
     */
    protected function applyIpFilter($query, $ip, $exact = false)
    {
        if (empty($ip)) {
            return;
        }

        if ($exact) {
            $query->where('visitor.ip', '=', $ip);
            return;
        }

        global $wpdb;
        $query->where('visitor.ip', 'LIKE', '%' . $wpdb->esc_like($ip) . '%');
    }

    /**
     * Limit a visitor query to users that have the given role on the current site.
     *
     * @param Query $query
     * @param string $role
     * @return void
     */
    protected function applyUserRoleFilter($query, $role)
    {
        global $wpdb;

        $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
        $query->where('usermeta.meta_key', '=', $wpdb->get_blog_prefix() . 'capabilities');
        $query->where('usermeta.meta_value', 'LIKE', '%"' . $wpdb->esc_like($role) . '";b:1%');
    }
}
