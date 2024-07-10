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
            'date'          => '',
            'author_id'     => '',
            'post_id'       => '',
            'query_param'   => '',
            'taxonomy'      => '',
            'term'          => ''
        ]);

        $viewsQuery = Query::select(['id', 'date', 'SUM(count) AS count'])
            ->from('pages')
            ->whereDate('date', $args['date'])
            ->groupBy('id')
            ->getQuery();

        $query = Query::select('SUM(pages.count) as total_views')
            ->fromQuery($viewsQuery, 'pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->where('post_author', '=', $args['author_id'])
            ->where('posts.ID', '=', $args['post_id'])
            ->where('pages.uri', '=', $args['query_param'])
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
                ->joinQuery($taxQuery, ['posts.id', 'tax.object_id'], 'tax');
        }

        $total = $query->getVar();

        return $total ? $total : 0;
    }

    public function getViewsSummary($args = [], $bypassCache = false)
    {
        return [
            'today'     => [
                'label' => esc_html__('Today', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => 'today'])),
            ],
            'yesterday' => [
                'label' => esc_html__('Yesterday', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => 'yesterday'])),
            ],
            '7days'     => [
                'label' => esc_html__('Last 7 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => '7days'])),
            ],
            '30days'    => [
                'label' => esc_html__('Last 30 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => '30days'])),
            ],
            '60days'    => [
                'label' => esc_html__('Last 60 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => '60days'])),
            ],
            '120days'   => [
                'label' => esc_html__('Last 120 days', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => '120days'])),
            ],
            'year'      => [
                'label' => esc_html__('Last 12 months', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => 'year'])),
            ],
            'this_year' => [
                'label' => esc_html__('This year (Jan - Today)', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => 'this_year'])),
            ],
            'last_year' => [
                'label' => esc_html__('Last Year', 'wp-statistics'),
                'views' => $this->countViews(array_merge($args, ['date' => 'last_year'])),
            ]
        ];
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