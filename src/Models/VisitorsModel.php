<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\DB;
use WP_STATISTICS\GeoIP;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class VisitorsModel extends BaseModel
{
    protected $table = 'visitors';

    public function countVisitors($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'post_id'   => ''
        ]);

        $result = Query::select('COUNT(DISTINCT visitor_id) as total_visitors')
            ->from('visitor_relationships')
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('visitor_relationships.date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->bypassCache($bypassCache)
            ->getVar();

        return $result ? $result : 0;
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
            'field'     => 'ID',
            'date'      => '',
            'where_col' => 'ID',
            'where_val' => '',
        ]);

        $result = Query::select("COUNT(DISTINCT `{$args['field']}`) as `total`")
            ->from('visitor')
            ->where($args['where_col'], '=', $args['where_val'])
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
            'field'    => 'agent',
            'date'     => '',
            'group_by' => [],
            'order_by' => 'visitors',
            'order'    => 'DESC',
            'per_page' => '',
            'page'     => 1
        ]);

        $result = Query::select([
            $args['field'],
            'COUNT(DISTINCT `ID`) AS `visitors`',
        ])
            ->from('visitor')
            ->whereDate('last_counter', $args['date'])
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
        return [
            'today'     => [
                'label'    => esc_html__('Today', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => 'today'])),
            ],
            'yesterday' => [
                'label'    => esc_html__('Yesterday', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => 'yesterday'])),
            ],
            '7days'     => [
                'label'    => esc_html__('Last 7 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => '7days'])),
            ],
            '30days'    => [
                'label'    => esc_html__('Last 30 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => '30days'])),
            ],
            '60days'    => [
                'label'    => esc_html__('Last 60 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => '60days'])),
            ],
            '120days'   => [
                'label'    => esc_html__('Last 120 days', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => '120days'])),
            ],
            'year'      => [
                'label'    => esc_html__('Last 12 months', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => 'year'])),
            ],
            'this_year' => [
                'label'    => esc_html__('This year (Jan - Today)', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => 'this_year'])),
            ],
            'last_year' => [
                'label'    => esc_html__('Last Year', 'wp-statistics'),
                'visitors' => $this->countVisitors(array_merge($args, ['date' => 'last_year'])),
            ]
        ];
    }

    public function getVisitorsData($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'author_id' => '',
            'post_id'   => ''
        ]);

        $result = Query::select([
            'visitor.ID',
            'visitor.platform',
            'visitor.agent',
            'visitor.model',
            'visitor.device',
            'visitor.location'
        ])
            ->from('visitor')
            ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'])
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('visitor.ID')
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getParsedVisitorsData($args, $bypassCache = false)
    {
        $data = $this->getVisitorsData($args, $bypassCache);

        $result = [
            'platform' => [],
            'agent'    => [],
            'device'   => [],
            'model'    => [],
            'country'  => []
        ];

        if (!empty($data)) {
            foreach ($data as $item) {
                if (empty($result['platform'][$item->platform])) {
                    $result['platform'][$item->platform] = 1;
                } else {
                    $result['platform'][$item->platform]++;
                }

                if (empty($result['agent'][$item->agent])) {
                    $result['agent'][$item->agent] = 1;
                } else {
                    $result['agent'][$item->agent]++;
                }

                if (empty($result['country'][$item->location])) {
                    $result['country'][$item->location] = 1;
                } else {
                    $result['country'][$item->location]++;
                }

                if (empty($result['device'][$item->device])) {
                    $result['device'][$item->device] = 1;
                } else {
                    $result['device'][$item->device]++;
                }

                if (empty($result['model'][$item->model])) {
                    $result['model'][$item->model] = 1;
                } else {
                    $result['model'][$item->model]++;
                }
            }

            // Sort and limit country
            arsort($result['country']);
            $result['country'] = array_slice($result['country'], 0, 10);
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
            'date'      => '',
            'country'   => '',
            'city'      => '',
            'region'    => '',
            'continent' => '',
            'group_by'  => 'visitor.location',
            'not_null'  => '',
            'order_by'  => ['visitors', 'views'],
            'order'     => 'DESC',
            'per_page'  => '',
            'page'      => 1
        ]);

        $result = Query::select([
            'visitor.city as city',
            'visitor.location as country',
            'visitor.region as region',
            'visitor.continent as continent',
            'COUNT(DISTINCT visitor.ID) as visitors',
            'SUM(visitor.hits) as views'
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
            ->bypassCache($bypassCache)
            ->getAll();

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
            'date'      => '',
            'post_type' => '',
            'post_id'   => '',
            'page'      => 1,
            'per_page'  => 10
        ]);

        $result = Query::select([
            'COUNT(DISTINCT visitor.ID) AS visitors',
            'visitor.referred as referrer'
        ])
            ->from('visitor')
            ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'visitor.ID'], [], 'LEFT')
            ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'], [], 'LEFT')
            ->join('posts', ['posts.ID', 'pages.id'], [], 'LEFT')
            ->where('post_type', 'IN', $args['post_type'])
            ->where('posts.ID', '=', $args['post_id'])
            ->whereNotNull('visitor.referred')
            ->whereDate('pages.date', $args['date'])
            ->groupBy('visitor.referred')
            ->perPage($args['page'], $args['per_page'])
            ->bypassCache($bypassCache)
            ->getAll();

        return $result ? $result : [];
    }

    public function getSearchEngineReferrals($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'date'      => '',
            'post_type' => '',
            'post_id'   => '',
            'country'   => '',
            'group_by'  => ['search.last_counter', 'search.engine'],
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

        if (!empty($args['post_type']) || !empty($args['post_id'])) {
            $query
                ->join('visitor_relationships', ['visitor_relationships.visitor_id', 'search.visitor'])
                ->join('pages', ['visitor_relationships.page_id', 'pages.page_id'])
                ->join('posts', ['posts.ID', 'pages.id'])
                ->where('post_type', 'IN', $args['post_type'])
                ->where('posts.ID', '=', $args['post_id']);
        }

        if (!empty($args['country'])) {
            $query
                ->join('visitor', ['search.visitor', 'visitor.ID'])
                ->where('visitor.location', '=', $args['country']);
        }

        $result = $query->getAll();

        return $result ? $result : [];
    }
}