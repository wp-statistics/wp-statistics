<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;

class TrafficSummary extends BaseMetabox
{
    protected $key = 'traffic_summary';
    protected $priority = 'side';

    public function getName()
    {
        return esc_html__('Traffic Summary', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => true
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        $data = $this->dataProvider->getTrafficSummaryData($args);

        $output = View::load('metabox/traffic-summary', ['data' => $data], true);

        wp_send_json([
            'output'    => $output,
            'options'   => $this->getOptions()
        ]);
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}