<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ViewsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class TrafficChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    public $args;
    protected $visitorsModel;
    protected $viewsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel    = new VisitorsModel();
        $this->viewsModel       = new ViewsModel();

        $this->initChartData(true);
    }

    public function getData()
    {
        $thisPeriod     = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod     = DateRange::getPrevPeriod($thisPeriod);
        $currentDates   = array_keys(TimeZone::getListDays($thisPeriod));
        $prevDates      = array_keys(TimeZone::getListDays($prevPeriod));

        // Get current data from database
        $visitors       = $this->visitorsModel->countDailyVisitors($this->args);
        $views          = $this->viewsModel->countDailyViews(array_merge($this->args, ['ignore_post_type' => true]));

        // Get previous data from database
        $prevVisitors   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['date' => $prevPeriod]));
        $prevViews      = $this->viewsModel->countDailyViews(array_merge($this->args, ['date' => $prevPeriod]));

        // Parse data
        $parsedData     = $this->parseData($currentDates, ['visitors' => $visitors, 'views' => $views]);
        $prevParsedData = $this->parseData($prevDates, ['visitors' => $prevVisitors, 'views' => $prevViews]);

        // Prepare data
        $result = $this->prepareResult($parsedData, $prevParsedData);

        return $result;
    }

    public function parseData($dates, $data)
    {
        $currentVisitors = wp_list_pluck($data['visitors'], 'visitors', 'date');
        $currentViews    = wp_list_pluck($data['views'], 'views', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]   = [
                'date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'day'  => date_i18n('l', strtotime($date))
            ];
            $parsedData['visitors'][] = isset($currentVisitors[$date]) ? intval($currentVisitors[$date]) : 0;
            $parsedData['views'][]    = isset($currentViews[$date]) ? intval($currentViews[$date]) : 0;
        }

        return $parsedData;
    }

    public function prepareResult($data, $prevData)
    {
        // Current Data
        $this->setChartLabels($data['labels']);
        $this->addChartDataset(esc_html__('Visitors', 'wp-statistics'), $data['visitors']);
        $this->addChartDataset(esc_html__('Views', 'wp-statistics'), $data['views']);

        // Previous Data
        $this->setChartPreviousLabels($prevData['labels']);
        $this->addChartPreviousDataset(esc_html__('Visitors', 'wp-statistics'), $prevData['visitors']);
        $this->addChartPreviousDataset(esc_html__('Views', 'wp-statistics'), $prevData['views']);

        return $this->getChartData();
    }
}
