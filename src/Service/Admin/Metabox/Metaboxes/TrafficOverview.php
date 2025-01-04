<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Menus;

class TrafficOverview extends BaseMetabox
{
    protected $key = 'quickstats';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Traffic Overview', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getScreen()
    {
        return ['dashboard'];
    }

    public function getData()
    {
        $args = [
            'ignore_post_type'  => true,
            'include_total'     => true
        ];

        $chartData  = $this->dataProvider->getTrafficChartData(array_merge($args, ['date' => DateRange::get('15days'), 'prev_data' => true]));
        $data       = $this->dataProvider->getTrafficOverviewData($args);

        // Merge chart data with template data
        $data = array_merge($data, [
            'total' => [
                'visitors'  => [
                    'current'   => array_sum($chartData['data']['datasets'][0]['data']),
                    'prev'      => array_sum($chartData['previousData']['datasets'][0]['data'])
                ],
                'views'     => [
                    'current'   => array_sum($chartData['data']['datasets'][1]['data']),
                    'prev'      => array_sum($chartData['previousData']['datasets'][1]['data'])
                ]
            ]
        ]);

        // Prevent previous data from being sent to the js
        unset($chartData['previousData']);

        $output = View::load('metabox/traffic-overview', ['data' => $data], true);

        return [
            'data'      => $chartData,
            'output'    => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}