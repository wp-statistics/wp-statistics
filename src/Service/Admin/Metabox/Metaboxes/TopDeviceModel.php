<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TopDeviceModel extends BaseMetabox
{
    protected $key = 'models';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Top Device Model', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('devices', ['tab' => 'models']),
                'title' => esc_html__('View Top Models', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = array_merge($this->dataProvider->getModelChartData($args), [
            'tag_id' => 'wps-top-device-model',
            'url'    => WP_STATISTICS_URL . 'assets/images/no-data/vector-1.svg'
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