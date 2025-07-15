<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;
use WP_Statistics\Utils\Request;

class VisitorInsightsDataProvider
{
    protected $args;
    protected $visitorsModel;
    protected $onlineModel;
    protected $viewsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
        $this->onlineModel   = new OnlineModel();
        $this->viewsModel    = new ViewsModel();
    }

    public function getChartsData()
    {
        return [
            'traffic_chart_data' => ChartDataProviderFactory::trafficChart($this->args)->getData()
        ];
    }

    public function getLoggedInChartsData()
    {
        return [
            'logged_in_chart_data' => ChartDataProviderFactory::usersTrafficChart($this->args)->getData()
        ];
    }

    public function getVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_info' => true,
                'order_by'  => 'visitor.ID',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getOnlineVisitorsData()
    {
        return [
            'data'  => $this->onlineModel->getOnlineVisitorsData(array_merge($this->args, [
                'order_by'  => 'date',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page
            ])),
            'total' => $this->onlineModel->countOnlines($this->args)
        ];
    }

    public function getTopVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_info' => true,
                'order_by'  => 'hits',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors($this->args)
        ];
    }

    public function getVisitorData()
    {
        $args = $this->args;

        $visitor = $this->visitorsModel->getVisitorData($args);

        if ($visitor->getUserId()) {
            // Get visitor journey by user ID if it's set
            $args = ['user_id' => $visitor->getUserId()];
        } elseif (!$visitor->isHashedIP() && !$visitor->isIpAnonymized()) {
            // Get visitor journey by IP if IP is not hashed or anonymized
            $args = ['ip' => $visitor->getIP()];
        }

        $visits = $this->visitorsModel->getVisitorJourney(array_merge($args, ['visitor_info' => true]));

        // Group data by date
        $data = [];
        foreach ($visits as $visit) {
            $page = ['page_id' => $visit->page_id, 'date' => $visit->date];

            if (!empty($data[$visit->last_counter])) {
                $data[$visit->last_counter]['journey'][] = $page;
                continue;
            }

            $data[$visit->last_counter] = [
                'session' => new VisitorDecorator($visit),
                'journey' => [$page]
            ];
        }

        return [
            'visitor'  => $visitor,
            'sessions' => $data
        ];
    }

    public function getLoggedInUsersData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_role' => Request::get('role', ''),
                'user_info' => true,
                'logged_in' => true,
                'order_by'  => 'visitor.ID',
                'order'     => 'DESC',
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->visitorsModel->countVisitors(array_merge($this->args, [
                'logged_in' => true,
                'user_role' => Request::get('role', '')
            ]))
        ];
    }
}