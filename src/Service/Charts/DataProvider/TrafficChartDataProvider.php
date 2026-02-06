<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class TrafficChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

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
        // Init chart data
        $this->initChartData($this->isPreviousDataEnabled());

        $this->setThisPeriodData();

        // Get previous data only if comparison is enabled
        if ($this->isPreviousDataEnabled()) {
            $this->setPrevPeriodData();
        }

        return $this->getChartData();
    }

    protected function setThisPeriodData()
    {
        $currentPeriod = $this->args['date'] ?? DateRange::get();
        $currentDates  = array_keys(TimeZone::getListDays($currentPeriod));

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => $currentPeriod['from'] ?? null,
            'date_to'   => $currentPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseData($currentDates, $result['data']['rows'] ?? []);

        $this->setChartLabels($data['labels']);
        $this->addChartDataset(esc_html__('Visitors', 'wp-statistics'), $data['visitors'], 'visitors');
        $this->addChartDataset(esc_html__('Views', 'wp-statistics'), $data['views'], 'views');
    }

    protected function setPrevPeriodData()
    {
        $currentPeriod = $this->args['date'] ?? DateRange::get();
        $prevPeriod    = DateRange::getPrevPeriod($currentPeriod);
        $prevDates     = array_keys(TimeZone::getListDays($prevPeriod));

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $this->parseData($prevDates, $result['data']['rows'] ?? []);

        $this->setChartPreviousLabels($data['labels']);
        $this->addChartPreviousDataset(esc_html__('Visitors', 'wp-statistics'), $data['visitors']);
        $this->addChartPreviousDataset(esc_html__('Views', 'wp-statistics'), $data['views']);
    }

    protected function parseData($dates, $data)
    {
        $visitors = wp_list_pluck($data, 'visitors', 'date');
        $views    = wp_list_pluck($data, 'views', 'date');

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