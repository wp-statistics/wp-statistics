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
            'post_type'         => Helper::get_list_post_type(),
            'resource_type'     => '',
            'date'              => '',
            'author_id'         => '',
            'post_id'           => '',
            'query_param'       => '',
            'taxonomy'          => '',
            'term'              => '',
            'ignore_post_type'  => false
        ]);

        $viewsQuery = Query::select(['id', 'date', 'SUM(count) AS count'])
            ->from('pages')
            ->where('pages.type', 'IN', $args['resource_type'])
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->where('pages.uri', '=', $args['query_param'])
            ->getQuery();

        $query = Query::select('SUM(pages.count) as total_views')
            ->fromQuery($viewsQuery, 'pages');

        if (!empty($args['author_id']) || !empty($args['post_id']) || !empty($args['taxonomy']) || !empty($args['term']) || (!empty($args['post_type']) && !$args['ignore_post_type'])) {
            $query
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
        }

        $total = $query->getVar();
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
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
        $total = $total ? intval($total) : 0;

        $total += $this->historicalModel->getViews($args);

        return $total;
    }

    public function countDailyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'post_type'         => Helper::get_list_post_type(),
            'ignore_post_type'  => false,
            'resource_type'     => '',
            'resource_id'       => '',
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
            ->where('pages.id', '=', $args['resource_id'])
            ->where('pages.uri', '=', $args['query_param'])
            ->whereDate('pages.date', $args['date'])
            ->groupBy('pages.date');

        if (empty($args['resource_id']) && (!empty($args['author_id']) || !empty($args['post_id']) || !empty($args['taxonomy']) || !empty($args['term']) || (!empty($args['post_type']) && !$args['ignore_post_type']))) {
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

        return $result ?? [];
    }

    public function getHourlyViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'date' => ''
        ]);

        $result = Query::select([
            'HOUR(date) as hour',
            'COUNT(DISTINCT visitor_id) as visitors',
            'COUNT(*) as views'
            ])
            ->from('visitor_relationships')
            ->whereDate('visitor_relationships.date', $args['date'])
            ->groupBy('hour')
            ->getAll();

        return $result;
    }

    public function getViewsSummary($args = [])
    {
        $summary = [
            'today'      => [
                'label'     => esc_html__('Today', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('today')]))
            ],
            'yesterday'  => [
                'label'     => esc_html__('Yesterday', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('yesterday')]))
            ],
            'this_week'  => [
                'label'     => esc_html__('This week', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_week')]))
            ],
            'last_week'  => [
                'label'     => esc_html__('Last week', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('last_week')]))
            ],
            'this_month' => [
                'label'     => esc_html__('This month', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_month')]))
            ],
            'last_month' => [
                'label'     => esc_html__('Last month', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('last_month')]))
            ],
            '7days'      => [
                'label'     => esc_html__('Last 7 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('7days')]))
            ],
            '30days'     => [
                'label'     => esc_html__('Last 30 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('30days')]))
            ],
            '90days'     => [
                'label'     => esc_html__('Last 90 days', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('90days')]))
            ],
            '6months'    => [
                'label'     => esc_html__('Last 6 months', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('6months')]))
            ],
            'this_year'  => [
                'label'     => esc_html__('This year (Jan-Today)', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['date' => DateRange::get('this_year')]))
            ]
        ];

        if (!empty($args['include_total'])) {
            $summary['total'] = [
                'label'     => esc_html__('Total', 'wp-statistics'),
                'views'     => $this->countViews(array_merge($args, ['ignore_date' => true, 'historical' => true]))
            ];
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

    public function getResourcesViews($args = [])
    {
        $args = $this->parseArgs($args, [
            'fields'        => ['id', 'uri', 'type', 'SUM(count) as views'],
            'resource_id'   => '',
            'resource_type' => '',
            'date'          => '',
            'page'          => 1,
            'per_page'      => 10
        ]);

        // If resource_id and resource_type are empty, get all views including 404, categories, home, etc...
        if (empty($args['resource_id']) && empty($args['resource_type'])) {
            $queries = [];

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '!=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy('id')
                ->getQuery();

            $queries[] = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', '0')
                ->whereDate('date', $args['date'])
                ->groupBy(['uri', 'type'])
                ->getQuery();

            $results = Query::union($queries)
                ->perPage($args['page'], $args['per_page'])
                ->orderBy('views', 'DESC')
                ->getAll();
        } else {
            $results = Query::select($args['fields'])
                ->from('pages')
                ->where('id', '=', $args['resource_id'])
                ->where('type', 'IN', $args['resource_type'])
                ->whereDate('date', $args['date'])
                ->perPage($args['page'], $args['per_page'])
                ->groupBy('id')
                ->getAll();
        }

        return $results;
    }
}
