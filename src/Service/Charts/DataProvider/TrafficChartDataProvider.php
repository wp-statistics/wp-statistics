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

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        // Init chart data
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        // Get previous data only if previous chart data option is enabled
        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $currentPeriod  = $this->args['date'] ?? DateRange::get();
        $currentDates   = array_keys(TimeZone::getListDays($currentPeriod));

        $data = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['include_hits' => true]));
        $data = $this->parseData($currentDates, $data);

        $this->setChartLabels($data['labels']);
        $this->addChartDataset(esc_html__('Visitors', 'wp-statistics'), $data['visitors'], 'visitors');
        $this->addChartDataset(esc_html__('Views', 'wp-statistics'), $data['views'], 'views');
    }

    protected function setPrevPeriodData()
    {
        $currentPeriod  = $this->args['date'] ?? DateRange::get();
        $prevPeriod     = DateRange::getPrevPeriod($currentPeriod);
        $prevDates      = array_keys(TimeZone::getListDays($prevPeriod));

        $data = $this->visitorsModel->countDailyVisitors(array_merge($this->args, ['include_hits' => true, 'date' => $prevPeriod]));
        $data = $this->parseData($prevDates, $data);

        $this->setChartPreviousLabels($data['labels']);
        $this->addChartPreviousDataset(esc_html__('Visitors', 'wp-statistics'), $data['visitors']);
        $this->addChartPreviousDataset(esc_html__('Views', 'wp-statistics'), $data['views']);
    }

    protected function parseData($dates, $data)
    {
        $visitors = wp_list_pluck($data, 'visitors', 'date');
        $views    = wp_list_pluck($data, 'hits', 'date');

        $parsedData = [];
        foreach ($dates as $date) {
            $parsedData['labels'][]   = [
                'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                'date'              => date_i18n('Y-m-d', strtotime($date)),
                'day'               => date_i18n('D', strtotime($date))
            ];
            $parsedData['visitors'][] = isset($visitors[$date]) ? intval($visitors[$date]) : 0;
            $parsedData['views'][]    = isset($views[$date]) ? intval($views[$date]) : 0;
        }

        return $parsedData;
    }
}