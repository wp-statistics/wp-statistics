<?php

namespace WP_Statistics\Service\Admin\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\PostsModel;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;

class TrafficChartDataProvider extends AbstractChartDataProvider
{
    public $args;
    protected $visitorsModel;
    protected $viewsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();
    }

    public function getData()
    {
        $result = [
            'data'          => ['labels' => [], 'datasets' => []],
            'previousData'  => ['labels' => [], 'datasets' => []]
        ];

        // If range is set, use it, otherwise, get from usermeta
        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

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

        $parsedData = [];
        foreach ($currentDates as $date) {
            $parsedData['labels'][]   = [
                'date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'  => date_i18n('l', strtotime($date))
            ];
            $parsedData['visitors'][] = isset($currentVisitors[$date]) ? intval($currentVisitors[$date]) : 0;
            $parsedData['views'][]    = isset($currentViews[$date]) ? intval($currentViews[$date]) : 0;
        }

        $prevParsedData = [];
        foreach ($prevDates as $date) {
            $prevParsedData['labels'][]   = [
                'date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'  => date_i18n('l', strtotime($date))
            ];
            $prevParsedData['visitors'][] = isset($prevVisitors[$date]) ? intval($prevVisitors[$date]) : 0;
            $prevParsedData['views'][]    = isset($prevViews[$date]) ? intval($prevViews[$date]) : 0;
        }

        // Add parsed data to dataset
        $result['data']['labels']       = $parsedData['labels'];

        $result['data']['datasets'][]   = [
            'label' => __('Visitors', 'wp-statistics'),
            'data'  => $parsedData['visitors'],
        ];

        $result['data']['datasets'][]   = [
            'label' => __('Views', 'wp-statistics'),
            'data'  => $parsedData['views'],
        ];


        // Add previous parsed data to dataset
        $result['previousData']['labels']       = $prevParsedData['labels'];

        $result['previousData']['datasets'][]   = [
            'label' => __('Visitors', 'wp-statistics'),
            'data'  => $prevParsedData['visitors']
        ];

        $result['previousData']['datasets'][]   = [
            'label' => __('Views', 'wp-statistics'),
            'data'  => $prevParsedData['views']
        ];

        return $result;
    }
}
