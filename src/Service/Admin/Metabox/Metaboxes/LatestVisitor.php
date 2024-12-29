<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class LatestVisitor extends BaseMetabox
{
    protected $key = 'recent';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Latest Visitor Breakdown', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Details of the most recent visitors to your site.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
            'button'        => View::load('metabox/action-button',['link'=> Menus::admin_url('visitors') ,'title'=>'View Latest Visitor Breakdown'],true)
        ];
    }

    public function getData()
    {
        $args = ['ignore_date' => true];
        $data = $this->dataProvider->getLatestVisitorsData($args);

        $output = View::load('metabox/latest-visitor', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}