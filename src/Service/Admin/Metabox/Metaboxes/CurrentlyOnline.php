<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;
use WP_STATISTICS\UserOnline;

class CurrentlyOnline extends BaseMetabox
{
    protected $key = 'useronline';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Currently Online', 'wp-statistics');
    }

    public function getDescription()
    {
        return '';
    }

    public function isActive()
    {
        return UserOnline::active();
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
            'button'        => View::load('metabox/action-button',[
                'link'  => Menus::admin_url('visitors', ['tab' => 'online']) ,
                'title' => esc_html__('View Online Visitors', 'wp-statistics')
            ],true)
        ];
    }

    public function getData()
    {
        $data = $this->dataProvider->getOnlineVisitorsData();

        $output = View::load('metabox/currently-online', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}