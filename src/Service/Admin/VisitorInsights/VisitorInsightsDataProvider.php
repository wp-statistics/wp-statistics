<?php

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Option;
use WP_STATISTICS\Admin_Template;
use WP_Statistics\Utils\Request;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class VisitorInsightsDataProvider
{
    protected $args;
    protected $visitorsModel;
    protected $onlineModel;
    protected $viewsModel;

    protected $isTrackLoggedInUsersEnabled;

    protected $chartData;

    public function __construct($args)
    {
        $this->args = $args;

        $this->isTrackLoggedInUsersEnabled = Option::get('visitors_log') ? true : false;

        $this->visitorsModel = new VisitorsModel();
        $this->onlineModel   = new OnlineModel();
        $this->viewsModel    = new ViewsModel();
    }

    public function getOverviewData()
    {
        $overviewChartData = $this->getOverviewChartsData();

        $visitors       = $this->visitorsModel->countVisitors();
        $prevVisitors   = $this->visitorsModel->countVisitors(['date' => DateRange::getPrevPeriod()]);
        $views          = $this->visitorsModel->countHits();
        $prevViews      = $this->visitorsModel->countHits(['date' => DateRange::getPrevPeriod()]);

        $loggedIn           = $this->visitorsModel->countVisitors(['logged_in' => true]);
        $prevLoggedIn       = $this->visitorsModel->countVisitors(['logged_in' => true, 'date' => DateRange::getPrevPeriod()]);
        $loggedInShare      = Helper::calculatePercentage($loggedIn, $visitors);
        $prevLoggedInShare  = Helper::calculatePercentage($prevLoggedIn, $prevVisitors);

        $referrers      = $this->visitorsModel->getReferrers(['decorate' => true, 'per_page' => 5]);
        $topVisitors    = $this->visitorsModel->getVisitorsData(['order_by' => 'hits', 'order' => 'DESC', 'page' => 1, 'per_page' => 5]);
        $entryPages     = $this->visitorsModel->getEntryPages(['per_page' => 5]);

        $glance = [
            'visitors'  => [
                'value'     => $visitors,
                'change'    => Helper::calculatePercentageChange($prevVisitors, $visitors)
            ],
            'views'     => [
                'value'     => $views,
                'change'    => Helper::calculatePercentageChange($prevViews, $views)
            ],
            'country'   => $overviewChartData['countries']['labels'][0] ?? '',
            'referrer'  => isset($referrers[0]) ? $referrers[0]->getRawReferrer() : '',
        ];

        if ($this->isTrackLoggedInUsersEnabled) {
            $glance['logged_in'] = [
                'value'     => $loggedInShare . '%',
                'change'    => $loggedInShare - $prevLoggedInShare
            ];
        }

        $summary = [
            'online'    => $this->onlineModel->countOnlines(),
            'visitors'  => [
                'today'     => $this->visitorsModel->countVisitors(['date' => DateRange::get('today')]),
                'yesterday' => $this->visitorsModel->countVisitors(['date' => DateRange::get('yesterday')]),
                '7days'     => $this->visitorsModel->countVisitors(['date' => DateRange::get('7days', true)]),
            ],
            'views'     => [
                'today'     => $this->visitorsModel->countHits(['date' => DateRange::get('today')]),
                'yesterday' => $this->visitorsModel->countHits(['date' => DateRange::get('yesterday')]),
                '7days'     => $this->visitorsModel->countHits(['date' => DateRange::get('7days', true)]),
            ],
        ];

        return [
            'glance'        => $glance,
            'summary'       => $summary,
            'referrers'     => $referrers,
            'entry_pages'   => $entryPages,
            'map_chart'     => $overviewChartData['map'],
            'visitors'      => $topVisitors,
        ];
    }

    public function getOverviewChartsData()
    {
        if (!empty($this->chartData)) {
            return $this->chartData;
        }

        $platformsChart = ChartDataProviderFactory::platformCharts();
        $countryChart   = ChartDataProviderFactory::countryChart();
        $trafficChart   = ChartDataProviderFactory::trafficChart();
        $mapChart       = ChartDataProviderFactory::mapChart();

        $this->chartData = [
            'devices'   => $platformsChart->getDeviceData(),
            'browsers'  => $platformsChart->getBrowserData(),
            'countries' => $countryChart->getData(),
            'traffic'   => $trafficChart->getData(),
            'map'       => $mapChart->getData()
        ];

        if ($this->isTrackLoggedInUsersEnabled) {
            $this->chartData['logged_in_users'] = ChartDataProviderFactory::loggedInUsers()->getData();
        }

        return $this->chartData;
    }

    public function getViewsChartsData()
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

    public function getViewsData()
    {
        return [
            'data'  => $this->viewsModel->getViewsData(array_merge($this->args, [
                'page'      => Admin_Template::getCurrentPaged(),
                'per_page'  => Admin_Template::$item_per_page,
            ])),
            'total' => $this->viewsModel->countViewRecords($this->args)
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
        $visitorInfo    = $this->visitorsModel->getVisitorData($this->args);
        $visitorJourney = $this->visitorsModel->getVisitorJourney($this->args);

        return [
            'visitor'           => $visitorInfo,
            'visitor_journey'   => $visitorJourney
        ];
    }

    public function getLoggedInUsersData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'user_role' => Request::get('role', ''),
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