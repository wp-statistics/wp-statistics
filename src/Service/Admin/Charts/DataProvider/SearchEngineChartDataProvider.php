<?php

namespace WP_Statistics\Service\Admin\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;

class SearchEngineChartDataProvider extends AbstractChartDataProvider
{
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

        $thisPeriodDates = array_keys(TimeZone::getListDays($thisPeriod));
        $prevPeriodDates = array_keys(TimeZone::getListDays($prevPeriod));

        $result = [
            'data' => [
                'labels'   => array_map(
                    function ($date) {
                        return [
                            'date'  => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                            'day'   => date_i18n('l', strtotime($date)),
                        ];
                    },
                    $thisPeriodDates
                ),
                'datasets' => []
            ],
            'previousData' => [
                'labels'   => array_map(
                    function ($date) {
                        return [
                            'date'  => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                            'day'   => date_i18n('l', strtotime($date)),
                        ];
                    },
                    $prevPeriodDates
                ),
                'datasets' => []
            ],
        ];

        // This period data
        $thisParsedData     = [];
        $thisPeriodData     = $this->visitorsModel->getSearchEngineReferrals($this->args);
        $thisPeriodTotal    = array_fill_keys($thisPeriodDates, 0);

        foreach ($thisPeriodData as $item) {
            $visitors = intval($item->visitors);
            $thisParsedData[$item->engine][$item->date] = $visitors;
            $thisPeriodTotal[$item->date]               += $visitors;
        }

        // Create an array of top search engines
        $topEngines = array_map(function($item) {
            return array_sum($item);
        }, $thisParsedData);

        // Sort top search engines in descending order
        arsort($topEngines);

        // Get the top 3 items
        $topEngines = array_slice($topEngines, 0, 3, true);

        foreach ($thisParsedData as $searchEngine => &$data) {
            if (!in_array($searchEngine, array_keys($topEngines))) continue;

            // Fill out missing visitors with 0
            $data = array_merge(array_fill_keys($thisPeriodDates, 0), $data);

            // Sort data by date
            ksort($data);

            // Generate dataset
            $result['data']['datasets'][] = [
                'label' => ucfirst($searchEngine),
                'data'  => array_values($data)
            ];
        }

        usort($result['data']['datasets'], function($a, $b) {
            return array_sum($b['data']) - array_sum($a['data']);
        });

        if (!empty($thisPeriodTotal)) {
            $result['data']['datasets'][] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'data'  => array_values($thisPeriodTotal)
            ];
        }

        // Previous period data
        $prevPeriodData     = $this->visitorsModel->getSearchEngineReferrals(array_merge($this->args, ['date' => $prevPeriod]));
        $prevPeriodTotal    = array_fill_keys($prevPeriodDates, 0);

        foreach ($prevPeriodData as $item) {
            $prevPeriodTotal[$item->date] += intval($item->visitors);
        }

        if (!empty($prevPeriodTotal)) {
            $result['previousData']['datasets'][] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'data'  => array_values($prevPeriodTotal)
            ];
        }

        return $result;
    }
}
