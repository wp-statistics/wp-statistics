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

    public $args;
    protected $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

        $data       = $this->visitorsModel->getSearchEngineReferrals($this->args);
        $prevData   = $this->visitorsModel->getSearchEngineReferrals(array_merge($this->args, ['date' => $prevPeriod]));

        $result     = $this->prepareResult($data, $prevData);

        return $result;
    }

    protected function prepareResult($data, $prevData)
    {
        $parsedData = [
            'data'          => [],
            'previousData'  => []
        ];

        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

        $thisPeriodDates = array_keys(TimeZone::getListDays($thisPeriod));
        $prevPeriodDates = array_keys(TimeZone::getListDays($prevPeriod));

        // This period data
        $thisParsedData     = [];
        $thisPeriodTotal    = array_fill_keys($thisPeriodDates, 0);

        // Init chart data
        $this->initChartData(true);

        // Set chart labels
        $this->setChartLabels($this->generateChartLabels($thisPeriodDates));
        $this->setChartPreviousLabels($this->generateChartLabels($prevPeriodDates));

        foreach ($data as $item) {
            $visitors = intval($item->visitors);
            $thisParsedData[$item->engine][$item->date] = $visitors;
            $thisPeriodTotal[$item->date]               += $visitors;
        }

        $topSearchEngines = $this->getTopSearchEngines($thisParsedData);

        foreach ($thisParsedData as $searchEngine => &$data) {
            if (!in_array($searchEngine, $topSearchEngines)) continue;

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

        usort($parsedData['data'], function($a, $b) {
            return array_sum($b['data']) - array_sum($a['data']);
        });

        if (!empty($thisPeriodTotal)) {
            $this->addChartDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($thisPeriodTotal)
            );
        }

        // Previous period data
        $prevPeriodTotal = array_fill_keys($prevPeriodDates, 0);

        foreach ($prevData as $item) {
            $prevPeriodTotal[$item->date] += intval($item->visitors);
        }

        if (!empty($prevPeriodTotal)) {
            $this->addChartPreviousDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($prevPeriodTotal)
            );
        }

        return $this->getChartData();
    }

    protected function generateChartLabels($dateRange)
    {
        $labels = array_map(
            function ($date) {
                return [
                    'date'  => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                    'day'   => date_i18n('l', strtotime($date)),
                ];
            },
            $dateRange
        );

        return $labels;
    }

    protected function getTopSearchEngines($data)
    {
        // Create an array of top search engines
        $topSearchEngines = array_map(function($item) {
            return array_sum($item);
        }, $data);

        // Get the top 3 items
        arsort($topSearchEngines);
        $topSearchEngines = array_slice($topSearchEngines, 0, 3, true);
        $topSearchEngines = array_keys($topSearchEngines);

        return $topSearchEngines;
    }
}
