<?php 

namespace WP_Statistics\Service\Admin\VisitorInsights;

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\OnlineModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\Helper;

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
            'traffic_chart_data' => $this->getTrafficChartData()
        ];
    }

    public function getTrafficChartData()
    {
        $result = [
            'data'          => ['labels' => [], 'visitors' => [], 'views' => []],
            'previousData'  => ['labels' => [], 'visitors' => [], 'views' => []]
        ];

        // If range is set, use it, otherwise, get from usermeta
        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = isset($this->args['date']) ? DateRange::getPrevPeriod($this->args['date']) : DateRange::getPrevPeriod();

        $currentDates   = array_keys(TimeZone::getListDays($thisPeriod));
        $prevDates      = array_keys(TimeZone::getListDays($prevPeriod));

        $currentVisitors = $this->visitorsModel->countDailyVisitors($this->args);
        $currentVisitors = wp_list_pluck($currentVisitors, 'visitors', 'date');
        $currentViews    = $this->viewsModel->countDailyViews(array_merge($this->args, ['ignore_post_type' => true]));
        $currentViews    = wp_list_pluck($currentViews, 'views', 'date');
        
        $prevVisitors   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['date' => $prevPeriod]));
        $prevVisitors   = wp_list_pluck($prevVisitors, 'visitors', 'date');
        $prevViews      = $this->viewsModel->countDailyViews(array_merge($this->args, ['date' => $prevPeriod]));
        $prevViews      = wp_list_pluck($prevViews, 'views', 'date');

        foreach ($currentDates as $date) {
            $result['data']['labels'][]   = [
                'date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'  => date_i18n('l', strtotime($date))
            ];
            $result['data']['visitors'][] = isset($currentVisitors[$date]) ? intval($currentVisitors[$date]) : 0;
            $result['data']['views'][]    = isset($currentViews[$date]) ? intval($currentViews[$date]) : 0;
        }

        foreach ($prevDates as $date) {
            $result['previousData']['labels'][]   = [
                'date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'  => date_i18n('l', strtotime($date))
            ];
            $result['previousData']['visitors'][] = isset($prevVisitors[$date]) ? intval($prevVisitors[$date]) : 0;
            $result['previousData']['views'][]    = isset($prevViews[$date]) ? intval($prevViews[$date]) : 0;
        }

        return $result;
    }

    public function getVisitorsData()
    {
        return [
            'data'  => $this->visitorsModel->getVisitorsData(array_merge($this->args, [
                'page_info' => true,
                'user_info' => true,
                'order_by'  => 'date',
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
                'page_info' => true,
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
        $userInfo       = !empty($visitorInfo->user_id) ? new \WP_User($visitorInfo->user_id) : [];
        $visitorJourney = $this->visitorsModel->getVisitorJourney($this->args);

        return [
            'visitor_info'      => $visitorInfo,
            'visitor_journey'   => $visitorJourney,
            'user_info'         => $userInfo
        ];
    }
}