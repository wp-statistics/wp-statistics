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
        $chartData = $this->dataProvider->getTrafficChartData(['date' => DateRange::get('7days', true)]);
        $data      = $this->dataProvider->getTrafficOverviewData();

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