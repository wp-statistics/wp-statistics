<?php
namespace WP_Statistics\Service\Admin\Metabox\Metaboxes;

use WP_Statistics\Components\View;
use WP_Statistics\Abstracts\BaseMetabox;
use WP_STATISTICS\Menus;

class SearchTraffic extends BaseMetabox
{
    protected $key = 'search-traffic';
    protected $context = 'normal';

    public function getName()
    {
        return esc_html__('Search Traffic', 'wp-statistics');
    }

    public function getDescription()
    {
        return esc_html__('', 'wp-statistics');
    }

    public function getOptions()
    {
        return [
            'datepicker'    => false,
        ];
    }

    public function getData()
    {
        $args = $this->getFilters();

        $data = [
            'data' => [
                'labels' => [
                    ['formatted_date' => 'Feb 4', 'date' => '2025-02-04', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Feb 5', 'date' => '2025-02-05', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Feb 6', 'date' => '2025-02-06', 'day' => 'Thursday'],
                    ['formatted_date' => 'Feb 7', 'date' => '2025-02-07', 'day' => 'Friday'],
                    ['formatted_date' => 'Feb 8', 'date' => '2025-02-08', 'day' => 'Saturday'],
                    ['formatted_date' => 'Feb 9', 'date' => '2025-02-09', 'day' => 'Sunday'],
                    ['formatted_date' => 'Feb 10', 'date' => '2025-02-10', 'day' => 'Monday'],
                    ['formatted_date' => 'Feb 11', 'date' => '2025-02-11', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Feb 12', 'date' => '2025-02-12', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Feb 13', 'date' => '2025-02-13', 'day' => 'Thursday'],
                    ['formatted_date' => 'Feb 14', 'date' => '2025-02-14', 'day' => 'Friday'],
                    ['formatted_date' => 'Feb 15', 'date' => '2025-02-15', 'day' => 'Saturday'],
                    ['formatted_date' => 'Feb 16', 'date' => '2025-02-16', 'day' => 'Sunday'],
                    ['formatted_date' => 'Feb 17', 'date' => '2025-02-17', 'day' => 'Monday'],
                    ['formatted_date' => 'Feb 18', 'date' => '2025-02-18', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Feb 19', 'date' => '2025-02-19', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Feb 20', 'date' => '2025-02-20', 'day' => 'Thursday'],
                    ['formatted_date' => 'Feb 21', 'date' => '2025-02-21', 'day' => 'Friday'],
                    ['formatted_date' => 'Feb 22', 'date' => '2025-02-22', 'day' => 'Saturday'],
                    ['formatted_date' => 'Feb 23', 'date' => '2025-02-23', 'day' => 'Sunday'],
                    ['formatted_date' => 'Feb 24', 'date' => '2025-02-24', 'day' => 'Monday'],
                    ['formatted_date' => 'Feb 25', 'date' => '2025-02-25', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Feb 26', 'date' => '2025-02-26', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Feb 27', 'date' => '2025-02-27', 'day' => 'Thursday'],
                    ['formatted_date' => 'Feb 28', 'date' => '2025-02-28', 'day' => 'Friday'],
                    ['formatted_date' => 'Mar 1', 'date' => '2025-03-01', 'day' => 'Saturday'],
                    ['formatted_date' => 'Mar 2', 'date' => '2025-03-02', 'day' => 'Sunday'],
                    ['formatted_date' => 'Mar 3', 'date' => '2025-03-03', 'day' => 'Monday'],
                    ['formatted_date' => 'Mar 4', 'date' => '2025-03-04', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Mar 5', 'date' => '2025-03-05', 'day' => 'Wednesday']
                ],
                'datasets' => [
                    [
                        'label' => 'Clicks',
                        'data' => [1200, 1500, 1800, 2000, 1900, 1700, 1600, 1400, 1300, 1500, 1800, 2100, 2300, 2500, 2400, 2200, 2000, 1800, 1600, 1500, 1400, 1600, 1800, 2000, 2200, 2300, 2100, 1900, 1700, 1500]
                    ],
                    [
                        'label' => 'Impressions',
                        'data' => [2000, 2300, 2500, 2800, 2700, 2600, 2400, 2200, 2100, 2300, 2600, 2900, 3000, 2800, 2700, 2500, 2300, 2100, 2000, 1900, 1800, 2000, 2200, 2400, 2600, 2700, 2500, 2300, 2100, 1900]
                    ]
                ]
            ],
            'previousData' => [
                'labels' => [
                    ['formatted_date' => 'Jan 5', 'date' => '2025-01-05', 'day' => 'Sunday'],
                    ['formatted_date' => 'Jan 6', 'date' => '2025-01-06', 'day' => 'Monday'],
                    ['formatted_date' => 'Jan 7', 'date' => '2025-01-07', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Jan 8', 'date' => '2025-01-08', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Jan 9', 'date' => '2025-01-09', 'day' => 'Thursday'],
                    ['formatted_date' => 'Jan 10', 'date' => '2025-01-10', 'day' => 'Friday'],
                    ['formatted_date' => 'Jan 11', 'date' => '2025-01-11', 'day' => 'Saturday'],
                    ['formatted_date' => 'Jan 12', 'date' => '2025-01-12', 'day' => 'Sunday'],
                    ['formatted_date' => 'Jan 13', 'date' => '2025-01-13', 'day' => 'Monday'],
                    ['formatted_date' => 'Jan 14', 'date' => '2025-01-14', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Jan 15', 'date' => '2025-01-15', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Jan 16', 'date' => '2025-01-16', 'day' => 'Thursday'],
                    ['formatted_date' => 'Jan 17', 'date' => '2025-01-17', 'day' => 'Friday'],
                    ['formatted_date' => 'Jan 18', 'date' => '2025-01-18', 'day' => 'Saturday'],
                    ['formatted_date' => 'Jan 19', 'date' => '2025-01-19', 'day' => 'Sunday'],
                    ['formatted_date' => 'Jan 20', 'date' => '2025-01-20', 'day' => 'Monday'],
                    ['formatted_date' => 'Jan 21', 'date' => '2025-01-21', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Jan 22', 'date' => '2025-01-22', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Jan 23', 'date' => '2025-01-23', 'day' => 'Thursday'],
                    ['formatted_date' => 'Jan 24', 'date' => '2025-01-24', 'day' => 'Friday'],
                    ['formatted_date' => 'Jan 25', 'date' => '2025-01-25', 'day' => 'Saturday'],
                    ['formatted_date' => 'Jan 26', 'date' => '2025-01-26', 'day' => 'Sunday'],
                    ['formatted_date' => 'Jan 27', 'date' => '2025-01-27', 'day' => 'Monday'],
                    ['formatted_date' => 'Jan 28', 'date' => '2025-01-28', 'day' => 'Tuesday'],
                    ['formatted_date' => 'Jan 29', 'date' => '2025-01-29', 'day' => 'Wednesday'],
                    ['formatted_date' => 'Jan 30', 'date' => '2025-01-30', 'day' => 'Thursday'],
                    ['formatted_date' => 'Jan 31', 'date' => '2025-01-31', 'day' => 'Friday'],
                    ['formatted_date' => 'Feb 1', 'date' => '2025-02-01', 'day' => 'Saturday'],
                    ['formatted_date' => 'Feb 2', 'date' => '2025-02-02', 'day' => 'Sunday'],
                    ['formatted_date' => 'Feb 3', 'date' => '2025-02-03', 'day' => 'Monday']
                ],
                'datasets' => [
                    [
                        'label' => 'Clicks',
                        'data' => [1100, 1400, 1700, 1900, 1800, 1600, 1500, 1300, 1200, 1400, 1700, 2000, 2200, 2400, 2300, 2100, 1900, 1700, 1500, 1400, 1300, 1500, 1700, 1900, 2100, 2200, 2000, 1800, 1600, 1400]
                    ],
                    [
                        'label' => 'Impressions',
                        'data' => [1900, 2200, 2400, 2700, 2600, 2500, 2300, 2100, 2000, 2200, 2500, 2800, 2900, 2700, 2600, 2400, 2200, 2000, 1900, 1800, 1700, 1900, 2100, 2300, 2500, 2600, 2400, 2200, 2000, 1800]
                    ]
                ]
            ]
        ];
        $output = View::load('metabox/search_traffic', [], true);

        return [
            'output'    => $output,
            'data'      => $data
        ];
    }

    public function render()
    {
        View::load('metabox/metabox-skeleton', []);
    }
}