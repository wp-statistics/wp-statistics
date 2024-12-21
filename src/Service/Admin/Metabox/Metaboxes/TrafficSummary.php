<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TrafficSummary extends BaseMetabox
{
    protected $key = 'traffic_summary';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Traffic Summary', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('A quick overview of your website\'s visitor statistics.', 'wp-statistics');
    }

    public function getData()
    {
        $args = [
            'ignore_date'       => true,
            'ignore_post_type'  => true
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