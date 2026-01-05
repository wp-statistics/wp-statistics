<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;
use WP_Statistics\Utils\Request;

class LoggedInUsersChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    /**
     * @var AnalyticsQueryHandler
     */
    protected $queryHandler;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->queryHandler = new AnalyticsQueryHandler();
    }

    public function getData()
    {
        $this->initChartData();

        $data = $this->parseData();

        $this->setChartLabels($data['labels']);
        $this->setChartIcons($data['icons']);
        $this->setChartData($data['values']);

        return $this->getChartData();
    }

    protected function parseData()
    {
        // Build filters for logged-in users
        $loggedInFilters = ['logged_in' => '1'];
        $role = Request::get('role', '');
        if (!empty($role)) {
            $loggedInFilters['user_role'] = ['contains' => $role];
        }

        // Query logged-in visitors
        $loggedInResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'filters'   => $loggedInFilters,
            'format'    => 'table',
        ]);

        // Query anonymous visitors (user_id = 0)
        $anonymousResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'date_from' => $this->args['date']['from'] ?? null,
            'date_to'   => $this->args['date']['to'] ?? null,
            'filters'   => ['logged_in' => '0'],
            'format'    => 'table',
        ]);

        $loggedInCount  = intval($loggedInResult['data']['totals']['visitors'] ?? 0);
        $anonymousCount = intval($anonymousResult['data']['totals']['visitors'] ?? 0);

        $data = [
            [
                'label' => esc_html__('User Visitors', 'wp-statistics'),
                'icon'  => WP_STATISTICS_URL . 'public/images/user-visitor.svg',
                'value' => $loggedInCount
            ],
            [
                'label' => esc_html__('Anonymous Visitors', 'wp-statistics'),
                'icon'  => WP_STATISTICS_URL . 'public/images/anonymous.svg',
                'value' => $anonymousCount
            ]
        ];

        usort($data, function ($a, $b) {
            return $b['value'] <=> $a['value'];
        });

        return [
            'labels' => wp_list_pluck($data, 'label'),
            'icons'  => wp_list_pluck($data, 'icon'),
            'values' => wp_list_pluck($data, 'value'),
        ];
    }
}