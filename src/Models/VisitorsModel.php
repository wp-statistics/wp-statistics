<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\GeoIP;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class VisitorsModel extends BaseModel
{

    public function countVisitors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => '',
            'resource_type' => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
        ]);

        $query = Query::select('COUNT(DISTINCT visitor_id) as total_visitors')
            ->from('visitor_relationships')
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->bypassCache($bypassCache);

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

    public function countDailyVisitors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'          => '',
            'post_type'     => '',
            'resource_type' => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
        ]);

        $query = Query::select([
            'DATE(visitor_relationships.date) as date',
            'COUNT(DISTINCT visitor_id) as visitors'
        ])
            ->from('visitor_relationships')
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('DATE(visitor_relationships.date)')
            ->bypassCache($bypassCache);

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

    /**
     * Returns `COUNT DISTINCT` of a column from visitors table.
     *
     * @param array $args Arguments to include in query (e.g. `field`, `date`, `where_col`, `where_val`, etc.).
     * @param bool $bypassCache Send the cached result.
     *
     * @return  int
     */
    public function countColumnDistinct($args = [], $bypassCache = false)
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
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? intval($result) : 0;
    }

    /**
     * Returns visitors' device information.
     *
     * @param array $args Arguments to include in query (e.g. `field`, `date`, `group_by`, etc.).
     * @param bool $bypassCache Send the cached result.
     *
     * @return  array
     */
    public function getVisitorsDevices($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'field'          => 'agent',
            'date'           => '',
            'where_not_null' => '',
            'group_by'       => [],
            'order_by'       => 'visitors',
            'order'          => 'DESC',
            'per_page'       => '',
            'page'           => 1
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
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    /**
     * Returns visitors' device versions for single view pages.
     *
     * @param array $args Arguments to include in query (e.g. `date`, etc.).
     * @param bool $bypassCache Send the cached result.
     *
     * @return  array
     */
    public function getVisitorsDevicesVersions($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'where_col' => 'agent',
            'where_val' => '',
            'order_by'  => 'visitors',
            'order'     => 'DESC',
            'per_page'  => '',
            'page'      => 1
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
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getVisitorsSummary($args = [], $bypassCache = false)
    {
        $result = $this->countDailyVisitors(array_merge($args, [
                'date' => [
                    'from' => (date('Y') - 1) . '-01-01',
                    'to'   => date('Y-m-d')]
            ]
        ), $bypassCache);

        $summary = [
            'today'     => ['label' => esc_html__('Today', 'wp-statistics'), 'visitors' => 0],
            'yesterday' => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'visitors' => 0],
            '7days'     => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'visitors' => 0],
            '30days'    => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'visitors' => 0],
            '60days'    => ['label' => esc_html__('Last 60 days', 'wp-statistics'), 'visitors' => 0],
            '120days'   => ['label' => esc_html__('Last 120 days', 'wp-statistics'), 'visitors' => 0],
            'year'      => ['label' => esc_html__('Last 12 months', 'wp-statistics'), 'visitors' => 0],
            'this_year' => ['label' => esc_html__('This year (Jan - Today)', 'wp-statistics'), 'visitors' => 0],
            'last_year' => ['label' => esc_html__('Last Year', 'wp-statistics'), 'visitors' => 0]
        ];

        // Init date ranges
        $todayDate     = date('Y-m-d');
        $yesterdayDate = date('Y-m-d', strtotime('-1 day'));
        $start7Days    = date('Y-m-d', strtotime('-7 days'));
        $start30Days   = date('Y-m-d', strtotime('-30 days'));
        $start60Days   = date('Y-m-d', strtotime('-60 days'));
        $start120Days  = date('Y-m-d', strtotime('-120 days'));
        $start12Months = date('Y-m-d', strtotime('-12 months'));
        $thisYearStart = date('Y') . '-01-01';
        $lastYearStart = (date('Y') - 1) . '-01-01';
        $lastYearEnd   = (date('Y') - 1) . '-12-31';

        foreach ($result as $record) {
            $date     = $record->date;
            $visitors = $record->visitors;

            if ($date === $todayDate) {
                $summary['today']['visitors'] += $visitors;
            }

            if ($date === $yesterdayDate) {
                $summary['yesterday']['visitors'] += $visitors;
            }

            if ($date >= $start7Days && $date <= $todayDate) {
                $summary['7days']['visitors'] += $visitors;
            }

            if ($date >= $start30Days && $date <= $todayDate) {
                $summary['30days']['visitors'] += $visitors;
            }

            if ($date >= $start60Days && $date <= $todayDate) {
                $summary['60days']['visitors'] += $visitors;
            }

            if ($date >= $start120Days && $date <= $todayDate) {
                $summary['120days']['visitors'] += $visitors;
            }

            if ($date >= $start12Months && $date <= $todayDate) {
                $summary['year']['visitors'] += $visitors;
            }

            if ($date >= $thisYearStart && $date <= $todayDate) {
                $summary['this_year']['visitors'] += $visitors;
            }

            if ($date >= $lastYearStart && $date <= $lastYearEnd) {
                $summary['last_year']['visitors'] += $visitors;
            }
        }

        return $summary;
    }

    public function getVisitorsData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'post_type'   => '',
            'author_id'   => '',
            'post_id'     => '',
            'country'     => '',
            'query_param' => '',
            'taxonomy'    => '',
            'term'        => '',
            'order_by'    => '',
            'order'       => '',
            'page'        => '',
            'per_page'    => '',
            'page_info'   => false,
            'user_info'   => false
        ]);

        $additionalFields = [];

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

            $additionalFields[] = 'last_hit.page_id';
            $additionalFields[] = 'last_hit.date';
        }

        if ($args['user_info'] === true) {
            $additionalFields[] = 'users.display_name';
            $additionalFields[] = 'users.user_email';
        }

        $query = Query::select(array_merge([
            'visitor.ID',
            'visitor.platform',
            'visitor.agent',
            'visitor.model',
            'visitor.device',
            'visitor.location',
            'visitor.user_id',
            'visitor.region',
            'visitor.city',
            'visitor.hits',
            'visitor.referred'
        ], $additionalFields))
            ->from('visitor')
            ->perPage($args['page'], $args['per_page'])
            ->orderBy($args['order_by'], $args['order'])
            ->groupBy('visitor.ID')
            ->bypassCache($bypassCache);

        // If last page is true, get last page the visitor has visited
        if ($args['page_info'] === true) {
            $query
                ->joinQuery($subQuery, ['visitor.ID', 'last_hit.visitor_id'], 'last_hit')
                ->whereDate('last_hit.date', $args['date']);
        }

        if ($args['user_info']) {
            $query->join('users', ['visitor.user_id', 'users.ID'], [], 'LEFT');
        }

        $filteredArgs = array_filter($args);

        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
                ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
                ->where('post_type', 'IN', $args['post_type'])
                ->where('post_author', '=', $args['author_id'])
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

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getVisitorsPlatformData($args, $bypassCache = false)
    {
        $data = $this->getVisitorsData($args, $bypassCache);

        $result = [
            'platform' => [],
            'agent'    => [],
            'device'   => [],
            'model'    => []
        ];

        if (!empty($data)) {
            foreach ($data as $item) {
                // Remove device subtype, for example: mobile:smart -> mobile
                $item->device = !empty($item->device) ? ucfirst(Helper::getDeviceCategoryName($item->device)) : esc_html__('Unknown', 'wp-statistics');

                if (!empty($item->platform) && $item->platform !== 'Unknown') {
                    if (empty($result['platform'][$item->platform])) {
                        $result['platform'][$item->platform] = 1;
                    } else {
                        $result['platform'][$item->platform]++;
                    }
                }

                if (!empty($item->agent) && $item->agent !== 'Unknown') {
                    if (empty($result['agent'][$item->agent])) {
                        $result['agent'][$item->agent] = 1;
                    } else {
                        $result['agent'][$item->agent]++;
                    }
                }

                if (!empty($item->device) && $item->device !== 'Unknown') {
                    if (empty($result['device'][$item->device])) {
                        $result['device'][$item->device] = 1;
                    } else {
                        $result['device'][$item->device]++;
                    }
                }

                if (!empty($item->model) && $item->model !== 'Unknown') {
                    if (empty($result['model'][$item->model])) {
                        $result['model'][$item->model] = 1;
                    } else {
                        $result['model'][$item->model]++;
                    }
                }
            }

            foreach ($result as $key => $data) {
                arsort($data);

                if (count($data) > 4) {
                    // Get top 5 results
                    $topData = array_slice($data, 0, 4, true);

                    // Show the rest of the results as others
                    $otherLabel   = esc_html__('Other', 'wp-statistics');
                    $otherData    = [$otherLabel => array_sum(array_diff_key($data, $topData))];
                    $result[$key] = array_merge($topData, $otherData);
                }
            }
        }

        return $result;
    }

    public function countGeoData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'count_field' => 'location',
            'continent'   => '',
            'country'     => '',
            'region'      => '',
            'city'        => '',
            'not_null'    => ''
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
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
    }

    public function getVisitorsGeoData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
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
            'order'       => 'DESC'
        ]);

        $query = Query::select([
            'visitor.city as city',
            'visitor.location as country',
            'visitor.region as region',
            'visitor.continent as continent',
            'COUNT(DISTINCT visitor.ID) as visitors',
            'SUM(visitor.hits) as views' // All views are counted and results can't be filtered by author, post type, etc...
        ])
            ->from('visitor')
            ->where('visitor.location', 'IN', $args['country'])
            ->where('visitor.city', 'IN', $args['city'])
            ->where('visitor.region', 'IN', $args['region'])
            ->where('visitor.continent', 'IN', $args['continent'])
            ->whereDate('visitor.last_counter', $args['date'])
            ->whereNotNull($args['not_null'])
            ->perPage($args['page'], $args['per_page'])
            ->groupBy($args['group_by'])
            ->orderBy($args['order_by'], $args['order'])
            ->bypassCache($bypassCache);


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
        $privateCountry = GeoIP::$private_country;

        // Determine the select fields based on the returnCount parameter
        $selectFields = $returnCount ? 'COUNT(*)' : ['ID', 'ip', 'location', 'city', 'region', 'continent'];

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
            )->bypassCache();

        // Execute the query and return the result based on the returnCount parameter
        if ($returnCount) {
            return $query->getVar();
        } else {
            return $query->getAll();
        }
    }

    public function updateVisitor($id, $data)
    {
        Query::update('visitor')
            ->set($data)
            ->where('ID', '=', $id)
            ->execute();
    }

    public function getReferrers($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'post_type'   => '',
            'post_id'     => '',
            'country'     => '',
            'query_param' => '',
            'taxonomy'    => '',
            'term'        => '',
            'page'        => 1,
            'per_page'    => 10
        ]);

        $query = Query::select([
            'COUNT(DISTINCT visitor.ID) AS visitors',
            'visitor.referred as referrer'
        ])
            ->from('visitor')
            ->where('visitor.referred', 'NOT LIKE', '%' . Helper::get_domain_name(home_url()) . '%')
            ->whereNotNull('visitor.referred')
            ->groupBy('visitor.referred')
            ->orderBy('visitors')
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache);

        $filteredArgs = array_filter($args);

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

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getSearchEngineReferrals($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'        => '',
            'post_type'   => '',
            'post_id'     => '',
            'country'     => '',
            'query_param' => '',
            'taxonomy'    => '',
            'term'        => '',
            'group_by'    => ['search.last_counter', 'search.engine'],
        ]);

        $query = Query::select([
            'search.last_counter AS date',
            'COUNT(DISTINCT search.visitor) AS visitors',
            'search.engine',
        ])
            ->from('search')
            ->whereDate('search.last_counter', $args['date'])
            ->groupBy($args['group_by'])
            ->orderBy('date', 'DESC')
            ->bypassCache($bypassCache);

        $filteredArgs = array_filter($args);
        if (array_intersect(['post_type', 'post_id', 'query_param', 'taxonomy', 'term'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'search.visitor'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'])
                ->join('posts', ['posts.ID', 'pages.id'])
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

        if (!empty($args['country'])) {
            $query
                ->join('visitor', ['search.visitor', 'visitor.ID'])
                ->where('visitor.location', '=', $args['country']);
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }

    public function getSearchEnginesChartData($args)
    {
        // Get results up to 30 days
        $newArgs = [];
        $days    = TimeZone::getNumberDayBetween($args['date']['from'], $args['date']['to']);
        if ($days > 30) {
            $newArgs = [
                'date' => [
                    'from' => date('Y-m-d', strtotime("-30 days", strtotime($args['date']['to']))),
                    'to'   => $args['date']['to']
                ]
            ];
        }

        $args = array_merge($args, $newArgs);

        $datesList = TimeZone::getListDays($args['date']);
        $datesList = array_keys($datesList);

        $result = [
            'labels'   => array_map(function ($date) {
                return date_i18n(Helper::getDefaultDateFormat(false, true), strtotime($date));
            }, $datesList
            ),
            'datasets' => []
        ];

        $data       = $this->getSearchEngineReferrals($args);
        $parsedData = [];
        $totalData  = array_fill_keys($datesList, 0);

        // Format and parse data
        foreach ($data as $item) {
            $parsedData[$item->engine][$item->date] = $item->visitors;
            $totalData[$item->date]                 += $item->visitors;
        }

        foreach ($parsedData as $searchEngine => &$data) {
            // Fill out missing visitors with 0
            $data = array_merge(array_fill_keys($datesList, 0), $data);

            // Sort data by date
            ksort($data);

            // Generate dataset
            $result['datasets'][] = [
                'label' => ucfirst($searchEngine),
                'data'  => array_values($data)
            ];
        }

        if (!empty($result['datasets'])) {
            $result['datasets'][] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'data'  => array_values($totalData)
            ];
        }

        return $result;
    }

    /**
     * Returns visitors, visits and referrers for the past given days, separated daily.
     *
     * @param array $args Arguments to include in query (e.g. `date`, `post_type`, `post_id`, etc.).
     * @param bool $bypassCache Send the cached result.
     *
     * @return  array   Format: `[{'date' => "STRING", 'visitors' => INT, 'visits' => INT, 'referrers' => INT}, ...]`.
     *
     * @todo    Make the query faster for date ranges greater than one month.
     */
    public function getDailyStats($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => [
                'from' => date('Y-m-d', strtotime('-30 days')),
                'to'   => date('Y-m-d'),
            ],
            'post_type' => '',
            'post_id'   => '',
            'page_type' => '',
            'author_id' => '',
            'taxonomy'  => '',
            'term_id'   => '',
        ]);

        $fields = [
            '`visitor`.`last_counter` AS `date`',
            'COUNT(DISTINCT `visitor`.`ID`) AS `visitors`',
            '`visit`.`visit` AS `visits`',
            'COUNT(DISTINCT CASE WHEN(`visitor`.`referred` NOT LIKE "%%' . Helper::get_domain_name(home_url()) . '%%" AND `visitor`.`referred` <> "" AND `visitor`.`referred` REGEXP "^(https?://|www\.)[\.A-Za-z0-9\-]+\.[a-zA-Z]{2,4}" AND LENGTH(`visitor`.`referred`) >= 12) THEN `visitor`.`ID` END) AS `referrers`',
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
            ->groupBy('`visitor`.`last_counter`')
            ->bypassCache($bypassCache);

        $filteredArgs = array_filter($args);
        if (array_intersect(['post_type', 'post_id', 'page_type', 'author_id', 'taxonomy', 'term_id'], array_keys($filteredArgs))) {
            $query
                ->join('visitor_relationships', ['`visitor_relationships`.`visitor_id`', '`visitor`.`ID`'])
                ->join('pages', '`visitor_relationships`.`page_id` = `pages`.`page_id` AND `visitor`.`last_counter` = `pages`.`date`');

            if (!empty($args['page_type'])) {
                $query
                    ->where('pages.type', '=', $args['page_type']);

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

            if (!empty($args['taxonomy']) && !empty($args['term_id']) && empty($args['page_type'])) {
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
}