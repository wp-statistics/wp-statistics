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
            'date'      => '',
            'post_type' => Helper::get_list_post_type(),
            'author_id' => ''
        ]);

        $subQuery = Query::select('SUM(count) as total_views')
            ->from('pages')
            ->join('posts', ['pages.id', 'posts.ID'])
            ->where('post_type', 'IN', $args['post_type'])
            ->whereDate('date', $args['date'])
            ->where('post_author', '=', $args['author_id'])
            ->groupBy('type')
            ->bypassCache($bypassCache)
            ->getQuery();

        $query = Query::select('SUM(total_views)')
            ->fromQuery($subQuery);

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

}