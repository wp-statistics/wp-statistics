<?php

namespace WP_Statistics\Models;

use WP_STATISTICS\Helper;
use WP_Statistics\Utils\Query;
use WP_Statistics\Abstracts\BaseModel;
use WP_Statistics\Components\DateRange;

class ViewsModel extends BaseModel
{

    public function countViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'     => Helper::get_list_post_type(),
            'resource_type' => '',
            'date'          => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => '',
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

        $total = $query->getVar();

        return $total ? intval($total) : 0;
    }

    /**
     * Returns views from `pages` table without joining with other tables.
     *
     * Used for calculating taxonomies views (Unlike `countViews()` which is suited for calculating posts/pages/cpt views).
     *
     * @param   array   $args           Arguments to include in query (e.g. `post_id`, `resource_type`, `query_param`, `date`, etc.).
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

        return $total ? intval($total) : 0;
    }

    public function countDailyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'         => Helper::get_list_post_type(),
            'ignore_post_type'  => false,
            'resource_type'     => '',
            'date'              => '',
            'author_id'         => '',
            'post_id'           => '',
            'query_param'       => '',
            'taxonomy'          => '',
            'term'              => '',
        ]);

        $query = Query::select([
            'SUM(pages.count) as views',
            'pages.date as date',
        ])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.date');

        if (!empty($args['author_id']) || !empty($args['post_id']) || !empty($args['taxonomy']) || !empty($args['term']) || (!empty($args['post_type']) && !$args['ignore_post_type'])) {
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

        return $result;
    }

    public function getViewsSummary($args = [])
    {
        $result = $this->countDailyViews(array_merge($args, [
            'date' => DateRange::get('this_year')
        ]));

        $summary = [
            'today'      => ['label' => esc_html__('Today', 'wp-statistics'), 'views' => 0],
            'yesterday'  => ['label' => esc_html__('Yesterday', 'wp-statistics'), 'views' => 0],
            'this_week'  => ['label' => esc_html__('This Week', 'wp-statistics'), 'views' => 0],
            'last_week'  => ['label' => esc_html__('Last Week', 'wp-statistics'), 'views' => 0],
            'this_month' => ['label' => esc_html__('This Month', 'wp-statistics'), 'views' => 0],
            'last_month' => ['label' => esc_html__('Last Month', 'wp-statistics'), 'views' => 0],
            '7days'      => ['label' => esc_html__('Last 7 days', 'wp-statistics'), 'views' => 0],
            '30days'     => ['label' => esc_html__('Last 30 days', 'wp-statistics'), 'views' => 0],
            '90days'     => ['label' => esc_html__('Last 90 days', 'wp-statistics'), 'views' => 0],
            '6months'    => ['label' => esc_html__('Last 6 Months', 'wp-statistics'), 'views' => 0],
            'this_year'  => ['label' => esc_html__('This year (Jan - Today)', 'wp-statistics'), 'views' => 0],
        ];

        foreach ($result as $record) {
            $date   = $record->date;
            $views  = $record->views;

            if (DateRange::compare($date, '=', 'today')) {
                $summary['today']['views'] += $views;
            }

            if (DateRange::compare($date, '=', 'yesterday')) {
                $summary['yesterday']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', 'this_week')) {
                $summary['this_week']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', 'last_week')) {
                $summary['last_week']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', 'this_month')) {
                $summary['this_month']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', 'last_month')) {
                $summary['last_month']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', '7days')) {
                $summary['7days']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', '30days')) {
                $summary['30days']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', '90days')) {
                $summary['90days']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', '6months')) {
                $summary['6months']['views'] += $views;
            }

            if (DateRange::compare($date, 'in', 'this_year')) {
                $summary['this_year']['views'] += $views;
            }
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
}
