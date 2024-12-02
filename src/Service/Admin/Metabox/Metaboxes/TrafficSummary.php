<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Abstracts\BaseMetabox;
use WP_Statistics\Components\View;

class TrafficSummary extends BaseMetabox
{
    protected $key = 'traffic_summary';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Traffic Summary', 'wp-statistics');
    }

    public function getData()
    {
        $output = View::load('metabox/traffic-summary', [], true);
        wp_send_json([
            'output'    => $output,
            'options'   => []
        ]);
    }

    public function render()
    {
        echo View::load('metabox/metabox-skeleton', [], true);
    }
}