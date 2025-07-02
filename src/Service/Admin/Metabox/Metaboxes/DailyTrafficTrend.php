<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class DailyTrafficTrend extends BaseMetabox
{
    protected $key = 'hits';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Traffic Trend', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Day-by-day breakdown of views and page views over the selected period.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('visitors', ['tab' => 'views']),
                'title' => esc_html__('Traffic Trend Report', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data   = $this->dataProvider->getTrafficChartData($args);
        $output = View::load('metabox/daily-traffic-trend', [], true);

        return [
            'data'      => $data,
            'output'    => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}