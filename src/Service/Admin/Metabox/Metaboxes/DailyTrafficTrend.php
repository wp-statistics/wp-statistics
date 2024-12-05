<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class DailyTrafficTrend extends BaseMetabox
{
    protected $key = 'daily_traffic_trend';
    protected $priority = 'normal';

    public function getName()
    {
        return esc_html__('Daily Traffic Trend', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => true,
            'button'        => View::load('metabox/action-button',['link'=> Menus::admin_url('visitors', ['tab' => 'views']),'title'=>'Daily Traffic Trend Report'],true)
        ];
    }

    public function getData()
    {
        $args = [
            'ignore_date' => true
        ];

        // @todo    Dynamic data
        $data = [
            "data" => [
                "labels" => [
                    [
                        "formatted_date" => "Dec 13",
                        "date" => "2024-12-13",
                        "day" => "Friday"
                    ],
                    [
                        "formatted_date" => "Dec 14",
                        "date" => "2024-12-14",
                        "day" => "Saturday"
                    ]
                ],
                "datasets" => [
                    [
                        "label" => "Visitors",
                        "data" => [2, 3]
                    ],
                    [
                        "label" => "Views",
                        "data" => [1, 0]
                    ]
                ]
            ],
            "previousData" => [
                "labels" => [
                    [
                        "formatted_date" => "Dec 11",
                        "date" => "2024-12-11",
                        "day" => "Wednesday"
                    ],
                    [
                        "formatted_date" => "Dec 12",
                        "date" => "2024-12-12",
                        "day" => "Thursday"
                    ]
                ],
                "datasets" => [
                    [
                        "label" => "Visitors",
                        "data" => [0, 0]
                    ],
                    [
                        "label" => "Views",
                        "data" => [0, 5]
                    ]
                ]
            ]
        ];

        $output = View::load('metabox/daily-traffic-trend', ['data' => $data], true);

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