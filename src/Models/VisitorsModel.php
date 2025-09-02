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
            'source_name'   => '',
            'logged_in'     => false,
            'user_role'     => '',
            'referrer'      => '',
            'not_null'      => '',
            'date_field'    => 'last_counter',
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
            ->where('ip', '=', $args['ip'])
            ->where('source_name', 'IN', $args['source_name'])
            ->whereNotNull($args['not_null'])
            ->whereDate($args['date_field'], $args['date']);

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
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
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
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
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
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
            'referrer'      => ''
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
            'referred_visitors' => false
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
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
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
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
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
            'decorate'              => true
        ]);

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
            ->where('ip', 'LIKE', "%{$args['ip']}%")
            ->where('referred', '=', $args['referrer'])
            ->where('visitor.location', '=', $args['country'])
            ->where('visitor.source_channel', 'IN', $args['source_channel'])
            ->where('visitor.source_name', 'IN', $args['source_name'])
            ->whereNotNull($args['not_null'])
            ->whereDate($args['date_field'], $args['date'])
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
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
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

            if (array_intersect(['taxonomy', 'term'], array_keys($filteredArgs))) {
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

        if (!empty($args['event_target']) || !empty($args['event_name'])) {
            $query
                ->join('events', ['events.visitor_id', 'visitor.ID'])
                ->where('event_name', 'IN', $args['event_name'])
                ->whereJson('event_data', 'target_url', '=', $args['event_target']);
        }

        $result = $query->getAll();

        return $result ? $result : [];
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
            'decorate'       => true
        ]);

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
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

    public function countGeoData($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'count_field' => 'location',
            'continent'   => '',
            'country'     => '',
            'region'      => '',
            'city'        => '',
            'not_null'    => '',
        ]);

        $result = Query::select([
            "COUNT(DISTINCT {$args['count_field']}) as total"
        ])
            ->from('visitor')
            ->whereDate('visitor.last_counter', $args['date'])
            ->where('visitor.continent', '=', $args['continent'])
            ->where('visitor.location', '=', $args['country'])
            ->where('visitor.region', '=', $args['region'])
            ->where('visitor.city', '=', $args['city'])
            ->whereNotNull("visitor.{$args['count_field']}")
            ->getVar();

        return $result ? intval($result) : 0;
    }

    public function getVisitorsGeoData($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'            => [
                'city'      => 'visitor.city as city',
                'country'   => 'visitor.location as country',
                'region'    => 'visitor.region as region',
                'continent' => 'visitor.continent as continent',
                'visitors'  => 'COUNT(visitor.ID) as visitors',
                'views'     => 'SUM(visitor.hits) as views', // All views are counted and results can't be filtered by author, post type, etc...
            ],
            'date'                  => '',
            'country'               => '',
            'city'                  => '',
            'region'                => '',
            'continent'             => '',
            'not_null'              => '',
            'post_type'             => '',
            'author_id'             => '',
            'post_id'               => '',
            'per_page'              => '',
            'query_param'           => '',
            'taxonomy'              => '',
            'term'                  => '',
            'page'                  => 1,
            'source_channel'        => '',
            'group_by'              => 'visitor.location',
            'event_name'            => '',
            'event_target'          => '',
            'order_by'              => ['visitors', 'views'],
            'order'                 => 'DESC',
            'utm_source'            => '',
            'utm_medium'            => '',
            'utm_campaign'          => '',
            'referrer'              => '',
            'resource_id'           => '',
            'resource_type'         => '',
            'referred_visitors'     => false
        ]);

        $filteredArgs = array_filter($args);

        // If joined to other tables, add DISTINCT to count unique visitors
        if (isset($args['fields']['visitors']) && array_intersect(['resource_type', 'resource_id', 'query_param', 'post_type', 'author_id', 'post_id', 'taxonomy', 'term', 'event_name', 'event_target'], array_keys($filteredArgs))) {
            $args['fields']['visitors'] = 'COUNT(DISTINCT visitor.ID) as visitors';
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->where('visitor.referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order']);


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

            if (array_intersect(['taxonomy', 'term'], array_keys($filteredArgs))) {
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

        if (!empty($args['event_target']) || !empty($args['event_name'])) {
            $query
                ->join('events', ['events.visitor_id', 'visitor.ID'])
                ->where('event_name', 'IN', $args['event_name'])
                ->whereJson('event_data', 'target_url', '=', $args['event_target']);
        }

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

            if (array_intersect(['taxonomy', 'term'], array_keys($filteredArgs))) {
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
            'not_null'       => 'visitor.referred'
        ]);

        $filteredArgs = array_filter($args);

        $query = Query::select([
            'COUNT(DISTINCT visitor.referred)'
        ])
            ->from('visitor')
            ->where('source_channel', 'IN', $args['source_channel'])
            ->where('visitor.location', '=', $args['country'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null']);

        // If not null is not set, get all referrers including those coming with just UTM without any source
        if (empty($args['not_null'])) {
            $query->whereRaw("AND ((visitor.referred != '') OR (visitor.source_channel IS NOT NULL))");
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('pages', ['visitor.first_page', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('pages.uri', '=', $args['query_param']);

            if (array_intersect(['taxonomy', 'term'], array_keys($filteredArgs))) {
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
        $endDate   = date('Y-m-d', strtotime($range['to'] . ' +1 day')) . ' 00:00:00';

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

    public function getBounceRate($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_id'   => '',
            'resource_type' => '',
            'query_param'   => ''
        ]);

        $singlePageVisitors = Query::select('visitor_id')
            ->from('visitor_relationships')
            ->whereDate('date', $args['date'])
            ->groupBy('visitor_id')
            ->having('COUNT(page_id) = 1')
            ->getQuery();

        $query = Query::select(['COUNT(visitor.ID) as visitors'])
            ->fromQuery($singlePageVisitors, 'single')
            ->join('visitor', ['visitor.ID', 'single.visitor_id'])
            ->join('pages', ['visitor.first_page', 'pages.page_id'])
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param']);

        $singlePageVisits = $query->getVar() ?? 0;
        $totalPageEntries = $this->countEntryPageVisitors($args);

        $result = Helper::calculatePercentage($singlePageVisits, $totalPageEntries);

        return $result;
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

    public function countEntryPages($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'resource_type' => Helper::getPostTypes(),
            'author_id'     => '',
            'uri'           => ''
        ]);

        $query = Query::select('COUNT(DISTINCT pages.id)')
            ->from('visitor')
            ->join('pages', ['visitor.first_page', 'pages.page_id'])
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
}
