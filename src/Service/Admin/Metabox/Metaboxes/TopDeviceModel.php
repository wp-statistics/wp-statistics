<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TopDeviceModel extends BaseMetabox
{
    protected $key = 'top_device_model';
    protected $priority = 'side';

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
            'button'        => View::load('metabox/action-button',['link'=> Menus::admin_url('devices', ['tab' => 'models']),'title'=>'View Top Models'],true)
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        //  @todo  Add data
        $data = [
            'tag_id' => 'wps-top-device-model',
            'data' => [
                10
            ],
            'label' => [
                "Desktop",
             ],
        ];

        $output = View::load('metabox/horizontal-bar', ['data' => $data , 'unique_id' => 'wps-top-device-model'], true);

        return [
            'output' => $output,
            'data' => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}