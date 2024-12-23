<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class TrafficOverview extends BaseMetabox
{
    protected $key = 'traffic_overview';
    protected $context = 'side';

    public function getName()
    {
        return esc_html__('Traffic Overview', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getScreen()
    {
        return ['dashboard'];
    }

    public function getData()
    {
        $args = [
            'ignore_date'       => true,
            'ignore_post_type'  => true
        ];

        $data = [
            "data" => [
                "labels" => [
                    [
                        "formatted_date" => "Dec 9",
                        "date" => "2024-12-09",
                        "day" => "Monday"
                    ],
                    [
                        "formatted_date" => "Dec 10",
                        "date" => "2024-12-10",
                        "day" => "Tuesday"
                    ],
                    [
                        "formatted_date" => "Dec 11",
                        "date" => "2024-12-11",
                        "day" => "Wednesday"
                    ],
                    [
                        "formatted_date" => "Dec 12",
                        "date" => "2024-12-12",
                        "day" => "Thursday"
                    ],
                    [
                        "formatted_date" => "Dec 13",
                        "date" => "2024-12-13",
                        "day" => "Friday"
                    ],
                    [
                        "formatted_date" => "Dec 14",
                        "date" => "2024-12-14",
                        "day" => "Saturday"
                    ],
                    [
                        "formatted_date" => "Dec 15",
                        "date" => "2024-12-15",
                        "day" => "Sunday"
                    ]
                ],
                "datasets" => [
                    [
                        "label" => "Visitors",
                        "data" => [1, 0, 0, 0, 0, 0, 0]
                    ],
                    [
                        "label" => "Views",
                        "data" => [1, 0, 0, 0, 0, 0, 0]
                    ]
                ]
            ],
        ];


        $output = View::load('metabox/traffic-overview', ['data' => $data], true);

        return [
            'data'      => $data,
            'output'    => $output
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}