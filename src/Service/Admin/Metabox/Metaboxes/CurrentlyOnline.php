<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class CurrentlyOnline extends BaseMetabox
{
    protected $key = 'currently_online';
    protected $priority = 'normal';

    public function getName()
    {
        return esc_html__('Currently Online', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
            'button'        => View::load('metabox/action-button',['link'=> Menus::admin_url('visitors', ['tab' => 'online']) ,'title'=>'View Online Visitors'],true)
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        //  @todo  Add data
        $data = [];

        $output = View::load('metabox/currently-online', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}