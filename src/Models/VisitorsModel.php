<?php

namespace WP_Statistics\Models;

use WP_Statistics\Abstracts\BaseModel;
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
            'logged_in'     => false,
            'user_role'     => ''
        ]);

        $query = Query::select('COUNT(visitor.id) as total_visitors')
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

        $filteredArgs = array_filter($args);

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
            'date'          => '',
            'agent'         => '',
            'platform'      => '',
            'country'       => '',
            'user_id'       => '',
            'ip'            => '',
            'logged_in'     => false,
            'user_role'     => ''
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

        $additionalFields = !empty($args['include_hits']) ? ['SUM(visitor.hits) as hits'] : [];

        $query = Query::select(array_merge([
            'visitor.last_counter as date',
            'COUNT(visitor.ID) as visitors'
        ], $additionalFields))
            ->from('visitor')
            ->where('location', '=', $args['country'])
            ->where('user_id', '=', $args['user_id'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->groupBy('visitor.last_counter');

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
            'date'              => '',
            'source_channel'    => '',
            'source_name'       => '',
            'referrer'          => ''
        ]);

        $result = Query::select('COUNT(visitor.ID) as visitors, last_counter as date')
            ->from('visitor')
            ->where('source_channel', '=', $args['source_channel'])
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull('visitor.referred')
            ->groupBy('last_counter')
            ->getVar();

        return $result ?? [];
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
            'COUNT(DISTINCT `ID`) AS `visitors`',
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
            'COUNT(DISTINCT `ID`) AS `visitors`',
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
        $summary = [
            'today'      => [
                'label'     => esc_html__('Today', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label'     => esc_html__('Yesterday', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label'     => esc_html__('This week', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label'     => esc_html__('Last week', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label'     => esc_html__('This month', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label'     => esc_html__('Last month', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label'     => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label'     => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label'     => esc_html__('Last 90 days', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label'     => esc_html__('Last 6 months', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label'     => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label'     => esc_html__('Total', 'wp-statistics'),
                'visitors'  => $this->countVisitors(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
        }

        return $summary;
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
            'order_by'      => 'visitor.ID',
            'order'         => 'DESC',
            'page'          => '',
            'per_page'      => '',
            'page_info'     => false,
            'user_info'     => false,
            'date_field'    => 'visitor.last_counter',
            'logged_in'     => false,
            'user_role'     => '',
            'fields'        => []
        ]);

        // Set default fields
        if (empty($args['fields'])) {
            $args['fields'] = [
                'visitor.ID',
                'visitor.ip',
                'visitor.platform',
                'visitor.agent',
                'CAST(`visitor`.`version` AS SIGNED) as version',
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
            ];
        }

        // If page info is true, get last page the visitor has visited
        if ($args['page_info'] === true) {

            $lastHit = Query::select([
                'visitor_id',
                'MAX(date) as date'
            ])
                ->from('visitor_relationships')
                ->groupBy('visitor_id')
                ->getQuery();

            $subQuery = Query::select([
                'visitor_relationships.visitor_id',
                'page_id',
                'date'
            ])
                ->from('visitor_relationships')
                ->whereRaw("(visitor_id, date) IN ($lastHit)")
                ->groupBy('visitor_id')
                ->getQuery();

            $args['fields'][] = 'last_hit.page_id as last_page';
            $args['fields'][] = 'last_hit.date as last_view';
        }

        if ($args['user_info'] === true) {
            $args['fields'][] = 'users.display_name';
            $args['fields'][] = 'users.user_email';
        }

        // When retrieving data for a single resource, get the page view date
        if (!empty($args['resource_id']) && ($args['resource_type'])) {
            $args['fields'][] = 'visitor_relationships.date as page_view';
        }

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->where('agent', '=', $args['agent'])
            ->where('platform', '=', $args['platform'])
            ->where('user_id', '=', $args['user_id'])
            ->where('ip', 'LIKE', "%{$args['ip']}%")
            ->where('visitor.location', '=', $args['country'])
            ->whereDate($args['date_field'], $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class)
            ->groupBy('visitor.ID');

        if ($args['logged_in'] === true) {
            $query->where('visitor.user_id', '!=', 0);
            $query->whereNotNull('visitor.user_id');

            if (!empty($args['user_role'])) {
                $query->join('usermeta', ['visitor.user_id', 'usermeta.user_id']);
                $query->where('usermeta.meta_key', '=', "wp_capabilities");
                $query->where('usermeta.meta_value', 'LIKE', "%{$args['user_role']}%");
            }
        }

        // If last page is true, get last page the visitor has visited
        if ($args['page_info'] === true) {
            $query->joinQuery($subQuery, ['visitor.ID', 'last_hit.visitor_id'], 'last_hit', 'LEFT');
        }

        if ($args['user_info']) {
            $query->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        $filteredArgs = array_filter($args);

        if (array_intersect(['resource_type', 'resource_id', 'query_param'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->where('pages.type', 'IN', $args['resource_type'])
                ->where('pages.id', '=', $args['resource_id'])
                ->where('pages.uri', '=', $args['query_param']);
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
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

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getReferredVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'source_channel'    => '',
            'source_name'       => '',
            'referrer'          => '',
            'order_by'          => 'visitor.ID',
            'order'             => 'desc',
            'page'              => '',
            'per_page'          => '',
        ]);

        $firstHit = Query::select([
            'MIN(ID) as ID',
            'visitor_id'
        ])
            ->from('visitor_relationships')
            ->groupBy('visitor_id')
            ->getQuery();

        $firstHitQuery = Query::select([
            'visitor_relationships.visitor_id',
            'page_id',
            'date'
        ])
            ->from('visitor_relationships')
            ->whereRaw("(ID, visitor_id) IN ($firstHit)")
            ->groupBy('visitor_id')
            ->getQuery();

        $lastHit = Query::select([
            'visitor_id',
            'MAX(date) as date'
        ])
            ->from('visitor_relationships')
            ->groupBy('visitor_id')
            ->getQuery();

        $lastHitQuery = Query::select([
            'visitor_relationships.visitor_id',
            'page_id',
            'date'
        ])
            ->from('visitor_relationships')
            ->whereRaw("(visitor_id, date) IN ($lastHit)")
            ->groupBy('visitor_id')
            ->getQuery();

        $query = Query::select([
            'visitor.ID',
            'visitor.ip',
            'visitor.platform',
            'visitor.agent',
            'CAST(`visitor`.`version` AS SIGNED) as version',
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
            'users.display_name',
            'users.user_email',
            'first_hit.page_id as first_page',
            'first_hit.date as first_view',
            'last_hit.page_id as last_page',
            'last_hit.date as last_view'
        ])
            ->from('visitor')
            ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT')
            ->joinQuery($firstHitQuery, ['visitor.ID', 'first_hit.visitor_id'], 'first_hit', 'LEFT')
            ->joinQuery($lastHitQuery, ['visitor.ID', 'last_hit.visitor_id'], 'last_hit', 'LEFT')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereNotNull('visitor.referred')
            ->whereDate('visitor.last_counter', $args['date'])
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->decorate(VisitorDecorator::class);

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('source_channel', '=', $args['source_channel']);
        }

        $result = $query->getAll();

        return $result ?? [];
    }

    public function countReferredVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'              => '',
            'source_channel'    => '',
            'source_name'       => '',
            'referrer'          => ''
        ]);

        $query = Query::select('COUNT(visitor.ID)')
            ->from('visitor')
            ->where('source_name', '=', $args['source_name'])
            ->where('referred', '=', $args['referrer'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull('visitor.referred');

        // When source_channel is `unassigned`, only get visitors without source_channel
        if ($args['source_channel'] === 'unassigned') {
            $query
                ->whereNull('visitor.source_channel');
        } else {
            $query
                ->where('source_channel', '=', $args['source_channel']);
        }

        return $query->getVar() ?? 0;
    }

    public function searchVisitors($args = [])
    {
        $args = $this->parseArgs($args, [
            'user_id'     => '',
            'ip'          => '',
            'username'    => '',
            'email'       => '',
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
            'CAST(`visitor`.`version` AS SIGNED) as version',
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
            'visitor.ip'
        ];

        // If visitor_id is empty, get visitor_id by IP
        if (empty($args['visitor_id']) || !empty($args['ip'])) {
            $visitorId = Query::select(['ID'])
                ->from('visitor')
                ->where('ip', '=', $args['ip'])
                ->getVar();

            $args['visitor_id'] = $visitorId ?? '';
        }

        if ($args['page_info'])  {
            $firstPage = Query::select(['MIN(ID)', 'page_id', 'visitor_id'])
                ->from('visitor_relationships')
                ->where('visitor_id', '=', $args['visitor_id'])
                ->getQuery();

            $firstView = Query::select(['MIN(date) as date', 'visitor_id'])
                ->from('visitor_relationships')
                ->where('visitor_id', '=', $args['visitor_id'])
                ->getQuery();

            $fields[] = 'first_view.date as first_view';
            $fields[] = 'first_page.page_id as first_page';
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
                ->joinQuery($firstPage, ['visitor.ID', 'first_page.visitor_id'], 'first_page', 'LEFT')
                ->joinQuery($firstView, ['visitor.ID', 'first_view.visitor_id'], 'first_view', 'LEFT')
                ->join('pages', ['first_page.page_id', 'pages.page_id'], [], 'LEFT');
        }

        if ($args['user_info']) {
            $query
                ->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        if ($args['decorate']) {
            $query
                ->decorate(VisitorDecorator::class);
        }

        return $query->getRow();
    }

    public function getVisitorJourney($args)
    {
        $args = $this->parseArgs($args, [
            'visitor_id'    => '',
            'ignore_date'   => true,
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
            'fields'      => [
                'visitor.city as city',
                'visitor.location as country',
                'visitor.region as region',
                'visitor.continent as continent',
                'COUNT(visitor.ID) as visitors',
                'SUM(visitor.hits) as views', // All views are counted and results can't be filtered by author, post type, etc...
            ],
            'date'        => '',
            'country'     => '',
            'city'        => '',
            'region'      => '',
            'continent'   => '',
            'not_null'    => '',
            'post_type'   => '',
            'author_id'   => '',
            'post_id'     => '',
            'per_page'    => '',
            'query_param' => '',
            'taxonomy'    => '',
            'term'        => '',
            'page'        => 1,
            'group_by'    => 'visitor.location',
            'order_by'    => ['visitors', 'views'],
            'order'       => 'DESC',
        ]);

        $query = Query::select($args['fields'])
            ->from('visitor')
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order']);


        $filteredArgs = array_filter($args);
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
            'date'          => '',
            'post_type'     => '',
            'source_channel'=> '',
            'post_id'       => '',
            'country'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'referrer'      => '',
            'not_null'      => 'visitor.referred',
            'group_by'      => 'visitor.referred',
            'page'          => 1,
            'per_page'      => 10,
            'decorate'      => false
        ]);

        $filteredArgs = array_filter($args);

        $query = Query::select([
            'COUNT(DISTINCT visitor.ID) AS visitors',
            'visitor.referred',
            'visitor.source_channel',
            'visitor.source_name',
            'visitor.last_counter'
        ])
            ->from('visitor')
            ->where('visitor.location', '=', $args['country'])
            ->where('source_channel', 'IN', $args['source_channel'])
            ->whereNotNull($args['not_null'])
            ->groupBy($args['group_by'])
            ->orderBy('visitors')
            ->perPage($args['page'], $args['per_page']);

        // If not null is not set, get all referrers including those coming with just UTM without any source
        if (empty($args['not_null'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '' AND visitor.referred IS NOT NULL)
                    OR (visitor.source_channel IS NOT NULL AND visitor.source_channel != '')
                )
            ");
        }

        if (!empty($args['referrer'])) {
            $query->where('visitor.referred', 'LIKE', "%{$args['referrer']}%");
        }

        // When date is passed, but all other parameters below are empty, compare the given date with `visitor.last_counter`
        if (!empty($args['date']) && !array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query->whereDate('visitor.last_counter', $args['date']);
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'], [], 'LEFT')
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('pages.uri', '=', $args['query_param'])
                ->whereDate('pages.date', $args['date']);

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
            'date'          => '',
            'source_channel'=> '',
            'post_type'     => '',
            'post_id'       => '',
            'country'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
            'not_null'      => 'visitor.referred'
        ]);

        $filteredArgs = array_filter($args);

        $query = Query::select([
            'COUNT(DISTINCT visitor.referred)'
        ])
            ->from('visitor')
            ->where('source_channel', 'IN', $args['source_channel'])
            ->whereNotNull($args['not_null']);

        // If not null is not set, get all referrers including those coming with just UTM without any source
        if (empty($args['not_null'])) {
            $query->whereRaw("
                AND (
                    (visitor.referred != '' AND visitor.referred IS NOT NULL)
                    OR (visitor.source_channel IS NOT NULL AND visitor.source_channel != '')
                )
            ");
        }

        // When date is passed, but all other parameters below are empty, compare the given date with `visitor.last_counter`
        if (!empty($args['date']) && !array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query->whereDate('visitor.last_counter', $args['date']);
        }

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'], [], 'LEFT')
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id'])
                ->where('pages.uri', '=', $args['query_param'])
                ->whereDate('pages.date', $args['date']);

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

        if (!empty($args['country'])) {
            $query
                ->where('visitor.location', '=', $args['country'])
                ->whereDate('visitor.last_counter', $args['date']);
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
     * @todo    Make the query faster for date ranges greater than one month.
     */
    public function getDailyStats($args = [])
    {
        $args = $this->parseArgs($args, [
            'date'          => [
                'from' => date('Y-m-d', strtotime('-30 days')),
                'to'   => date('Y-m-d'),
            ],
            'post_type'     => '',
            'post_id'       => '',
            'resource_type' => '',
            'author_id'     => '',
            'taxonomy'      => '',
            'term_id'       => '',
        ]);

        $fields = [
            '`visitor`.`last_counter` AS `date`',
            'COUNT(DISTINCT `visitor`.`ID`) AS `visitors`',
            '`visit`.`visit` AS `visits`',
            'COUNT(DISTINCT CASE WHEN(`visitor`.`referred` NOT LIKE "%%' . Helper::get_domain_name(home_url()) . '%%" AND `visitor`.`referred` <> "") THEN `visitor`.`ID` END) AS `referrers`',
        ];
        if (is_numeric($args['post_id']) || !empty($args['author_id']) || !empty($args['term_id'])) {
            // For single pages/posts/authors/terms
            $fields[2] = 'SUM(DISTINCT `pages`.`count`) AS `visits`';
        }

        $query = Query::select($fields)->from('visitor');
        if (!is_numeric($args['post_id']) && empty($args['author_id']) && empty($args['term_id'])) {
            // For single pages/posts/authors/terms
            $query->join('visit', ['`visitor`.`last_counter`', '`visit`.`last_counter`']);
        }
        $query
            ->whereDate('`visitor`.`last_counter`', $args['date'])
            ->groupBy('`visitor`.`last_counter`');

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
            'date'      => '',
            'user_id'   => '',
        ]);

        $query = Query::select('SUM(visitor.hits) as hits')
            ->from('visitor')
            ->where('user_id', '=', $args['user_id'])
            ->whereDate('last_counter', $args['date']);

        $result = $query->getVar();

        return intval($result);
    }
}
