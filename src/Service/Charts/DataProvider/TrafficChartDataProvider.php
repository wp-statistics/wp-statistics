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
    }

    public function getData()
    {
        // Get and parse current data
        $currentPeriod  = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $currentDates   = array_keys(TimeZone::getListDays($currentPeriod));

        $visitors       = $this->visitorsModel->countDailyVisitors($this->args);
        $views          = $this->viewsModel->countDailyViews(array_merge($this->args, ['ignore_post_type' => true]));
        $parsedData     = $this->parseData($currentDates, ['visitors' => $visitors, 'views' => $views]);

        // Get and parse previous data
        $prevPeriod     = DateRange::getPrevPeriod($currentPeriod);
        $prevDates      = array_keys(TimeZone::getListDays($prevPeriod));

        $prevVisitors   = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['date' => $prevPeriod]));
        $prevViews      = $this->viewsModel->countDailyViews(array_merge($this->args, ['date' => $prevPeriod]));
        $prevParsedData = $this->parseData($prevDates, ['visitors' => $prevVisitors, 'views' => $prevViews]);

        // Prepare data
        $result = $this->prepareResult($parsedData, $prevParsedData);

        return $result;
    }

    protected function parseData($dates, $data)
    {
        $visitors = wp_list_pluck($data['visitors'], 'visitors', 'date');
        $views    = wp_list_pluck($data['views'], 'views', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]   = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('l', strtotime($date))
            ];
            $parsedData['visitors'][] = isset($visitors[$date]) ? intval($visitors[$date]) : 0;
            $parsedData['views'][]    = isset($views[$date]) ? intval($views[$date]) : 0;
        }

        return $parsedData;
    }

    protected function prepareResult($data, $prevData)
    {
        // Init chart response with previous data
        $this->initChartData(true);

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
