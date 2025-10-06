<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Components\DateRange;

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
            'ignore_post_type' => true,
            'include_total'    => true,
            'exclude'          => ['this_week', 'last_week', 'this_month', 'last_month', '90days', '6months'],
        ];

        $chartData = $this->dataProvider->getTrafficChartData(array_merge($args, ['date' => DateRange::get('7days', true)]));
        $data      = $this->dataProvider->getTrafficOverviewData($args);

        $output = View::load('metabox/traffic-overview', ['data' => $data], true);

        return [
            'data'   => $chartData,
            'output' => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}