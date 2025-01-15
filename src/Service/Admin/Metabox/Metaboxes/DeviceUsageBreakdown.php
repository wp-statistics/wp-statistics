<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class DeviceUsageBreakdown extends BaseMetabox
{
    protected $key = 'devices';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Device Usage Breakdown', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Distribution of visitors based on the devices they use to access your site.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('devices', ['tab' => 'categories']),
                'title' => esc_html__('View Most Device Categories', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = array_merge($this->dataProvider->getDeviceChartData($args), [
            'tag_id' => 'wps-device-usage-breakdown',
            'url'    => WP_STATISTICS_URL . 'assets/images/no-data/vector-4.svg'
        ]);

        $output = View::load('metabox/horizontal-bar', ['data' => $data], true);

        return [
            'output'    => $output,
            'data'      => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}