<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class SearchEngineChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        // Set default values
        $this->args = wp_parse_args($args, [
            'source_channel' => ['search', 'paid_search'],
        ]);

        // Group by source_name
        $this->args['group_by'] = ['source_name', 'last_counter'];

        // Rest per_page to get all results
        $this->args['per_page'] = false;

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
        $thisPeriod      = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $thisPeriodDates = array_keys(TimeZone::getListDays($thisPeriod));

        // This period data
        $thisParsedData     = [];
        $thisPeriodTotal    = array_fill_keys($thisPeriodDates, 0);

        // Set chart labels
        $this->setChartLabels($this->generateChartLabels($thisPeriodDates));

        $data = $this->visitorsModel->getReferrers($this->args);

        foreach ($data as $item) {
            $visitors = intval($item->visitors);
            $thisParsedData[$item->source_name][$item->last_counter] = $visitors;
            $thisPeriodTotal[$item->last_counter]                    += $visitors;
        }

        // Sort data by search engine referrals number
        uasort($thisParsedData, function($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // Get top 3 search engines
        $topSearchEngines = array_slice($thisParsedData, 0, 3, true);

        foreach ($topSearchEngines as $searchEngine => &$data) {
            // Fill out missing visitors with 0
            $data = array_merge(array_fill_keys($thisPeriodDates, 0), $data);

            // Sort data by date
            ksort($data);

            // Add search engine data as dataset
            $this->addChartDataset(
                ucfirst($searchEngine),
                array_values($data)
            );
        }

        if (!empty($thisPeriodTotal)) {
            $this->addChartDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($thisPeriodTotal)
            );
        }
    }

    protected function setPrevPeriodData()
    {
        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

        $data = $this->visitorsModel->getReferrers(array_merge($this->args, ['date' => $prevPeriod]));

        $prevPeriodDates = array_keys(TimeZone::getListDays($prevPeriod));

        $this->setChartPreviousLabels($this->generateChartLabels($prevPeriodDates));

        // Previous period data
        $prevPeriodTotal = array_fill_keys($prevPeriodDates, 0);

        foreach ($data as $item) {
            $prevPeriodTotal[$item->last_counter] += intval($item->visitors);
        }

        if (!empty($prevPeriodTotal)) {
            $this->addChartPreviousDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($prevPeriodTotal)
            );
        }
    }

    protected function generateChartLabels($dateRange)
    {
        $labels = array_map(
            function ($date) {
                return [
                    'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                    'date'              => date_i18n('Y-m-d', strtotime($date)),
                    'day'               => date_i18n('l', strtotime($date))
                ];
            },
            $dateRange
        );

        return $labels;
    }
}
