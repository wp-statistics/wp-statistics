<?php

namespace WP_Statistics\Models;
use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;


class ViewsModel extends BaseModel
{

    public function countViews($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'post_type'     => Helper::get_list_post_type(),
            'resource_type' => '',
            'date'          => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => ''
        ]);

        $viewsQuery = Query::select(['id', 'date', 'SUM(count) AS count'])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->where('pages.uri', '=', $args['query_param'])
            ->getQuery();

        $query = Query::select('SUM(pages.count) as total_views')
            ->fromQuery($viewsQuery, 'pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
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

        $total = $query->getVar();

        return $total ? $total : 0;
    }

    /**
     * Returns views from `pages` table without joining with other tables.
     *
     * Used for calculating taxonomies views (Unlike `countViews()` which is suited for calculating posts/pages/cpt views).
     *
     * @param   array   $args           Arguments to include in query (e.g. `post_id`, `resource_type`, `query_param`, `date`, etc.).
     * @param   bool    $bypassCache    Send the cached result.
     *
     * @return  int
     */
    public function countViewsFromPagesOnly($args = [], $bypassCache = false)
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
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->bypassCache($bypassCache);

        $total = $query->getVar();

        return $total ? $total : 0;
    }

    public function countDailyViews($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'post_type'     => Helper::get_list_post_type(),
            'resource_type' => '',
            'date'          => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => ''
        ]);

        $query = Query::select([
                'SUM(pages.count) as views',
                'pages.date as date'
            ])
            ->from('pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.date')
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

    public function getViewsSummary($args = [], $bypassCache = false)
    {
        $result = $this->countDailyViews(array_merge($args, [
            'date' => [
                'from' => (date('Y') - 1) . '-01-01', 
                'to' => date('Y-m-d')]
            ]
        ), $bypassCache);

        $summary = [
            'today'     => ['label' => esc_html__('Today', 'wp-statistics'), 'views' => 0],
            'yesterday' => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'views' => 0],
            '7days'     => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'views' => 0],
            '30days'    => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'views' => 0],
            '60days'    => ['label' => esc_html__('Last 60 days', 'wp-statistics'), 'views' => 0],
            '120days'   => ['label' => esc_html__('Last 120 days', 'wp-statistics'), 'views' => 0],
            'year'      => ['label' => esc_html__('Last 12 months', 'wp-statistics'), 'views' => 0],
            'this_year' => ['label' => esc_html__('This year (Jan - Today)', 'wp-statistics'), 'views' => 0],
            'last_year' => ['label' => esc_html__('Last Year', 'wp-statistics'), 'views' => 0]
        ];

        $todayDate      = date('Y-m-d');
        $yesterdayDate  = date('Y-m-d', strtotime('-1 day'));
        $start7Days     = date('Y-m-d', strtotime('-6 days'));
        $start30Days    = date('Y-m-d', strtotime('-29 days'));
        $start60Days    = date('Y-m-d', strtotime('-59 days'));
        $start120Days   = date('Y-m-d', strtotime('-119 days'));
        $start12Months  = date('Y-m-d', strtotime('-12 months'));
        $thisYearStart  = date('Y') . '-01-01';
        $lastYearStart  = (date('Y') - 1) . '-01-01';
        $lastYearEnd    = (date('Y') - 1) . '-12-31';

        foreach ($result as $record) {
            $date   = $record->date;
            $views  = $record->views;
            
            if ($date === $todayDate) {
                $summary['today']['views'] += $views;
            }
            
            if ($date === $yesterdayDate) {
                $summary['yesterday']['views'] += $views;
            }
            
            if ($date >= $start7Days && $date <= $todayDate) {
                $summary['7days']['views'] += $views;
            }
            
            if ($date >= $start30Days && $date <= $todayDate) {
                $summary['30days']['views'] += $views;
            }
            
            if ($date >= $start60Days && $date <= $todayDate) {
                $summary['60days']['views'] += $views;
            }
            
            if ($date >= $start120Days && $date <= $todayDate) {
                $summary['120days']['views'] += $views;
            }
            
            if ($date >= $start12Months && $date <= $todayDate) {
                $summary['year']['views'] += $views;
            }
            
            if ($date >= $thisYearStart && $date <= $todayDate) {
                $summary['this_year']['views'] += $views;
            }
            
            if ($date >= $lastYearStart && $date <= $lastYearEnd) {
                $summary['last_year']['views'] += $views;
            }
        }

        return $summary;
    }

    public function getViewedPageUri($args = [], $bypassCache = false)
    {
        $args = $this->parseArgs($args, [
            'id' => ''
        ]);

        $results = $this->query::select([
                'uri',
                'page_id',
                'SUM(count) AS total'
            ])
            ->from('pages')
            ->where('id', '=', $args['id'])
            ->groupBy('uri')
            ->orderBy('total')
            ->getAll();

        return $results;
    }
}