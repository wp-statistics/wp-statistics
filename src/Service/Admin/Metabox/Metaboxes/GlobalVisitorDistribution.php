<?php

namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class GlobalVisitorDistribution extends BaseMetabox
{
    protected $key = 'global_visitor_distribution';
    protected $priority = 'normal';

    public function getName()
    {
        return esc_html__('Global Visitor Distribution', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('Geographical representation of where your site\'s visitors come from.', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker' => true,
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = [

            "labels" => [
                "Germany",
                "United Arab Emirates"
            ],
            "codes" => [
                "DE",
                "AE"
            ],
            "flags" => [
                "http://wp-statistic.localhost/wp-content/plugins/wp-statistics/assets/images/flags/de.svg",
                "http://wp-statistic.localhost/wp-content/plugins/wp-statistics/assets/images/flags/ae.svg"
            ],
            "data" => [
                "8",
                "4"
            ]
        ];

        $output = View::load('metabox/global-visitor-distribution',  ['data' => $data], true);

        return [
            'data' => $data,
            'output' => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}