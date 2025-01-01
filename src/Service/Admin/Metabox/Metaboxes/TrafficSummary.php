<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TrafficSummary extends BaseMetabox
{
    protected $key = 'traffic-summary';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Traffic Summary', 'wp-statistics');
    }

    public function getScreen()
    {
        return [Menus::get_action_menu_slug('overview')];
    }

    public function getDescription()
    {
        return esc_html__('A quick overview of your website\'s visitor statistics.', 'wp-statistics');
    }

    public function getData()
    {
        $args = [
            'ignore_post_type'  => true,
            'include_total'     => true
        ];

        $data = $this->dataProvider->getTrafficSummaryData($args);

        $output = View::load('metabox/traffic-summary', ['data' => $data], true);

        return $output;
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}