<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Utils\Request;

class LoggedInUsersChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    public $args;
    protected $visitorsModel;
    protected $viewsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->args['date'] = $this->args['date'] ?? DateRange::get();

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        $this->initChartData();

        $this->prepareResult();

        return $this->getChartData();
    }

    protected function prepareResult()
    {
        $loggedInData   = $this->visitorsModel->countVisitors(array_merge($this->args, ['logged_in' => true, 'user_role' => Request::get('role', '')]));
        $anonymousData  = $this->visitorsModel->countVisitors(array_merge($this->args, ['user_id' => '0']));

        $data = [
            [
                'label' => esc_html__('User Visitors', 'wp-statistics'),
                'icon'  => WP_STATISTICS_URL . 'assets/images/user-visitor.svg',
                'value' => $loggedInData
            ],
            [
                'label' => esc_html__('Anonymous Visitors', 'wp-statistics'),
                'icon'  => WP_STATISTICS_URL . 'assets/images/anonymous.svg',
                'value' => $anonymousData
            ]
        ];

        usort($data, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        $this->setChartLabels(wp_list_pluck($data, 'label'));
        $this->setChartIcons(wp_list_pluck($data, 'icon'));
        $this->setChartData(wp_list_pluck($data, 'value'));
    }
}