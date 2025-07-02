<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\ExclusionsModel;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class ExclusionsChartDataProvider extends AbstractChartDataProvider
{
    use LineChartResponseTrait;

    protected $exclusionsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args['group_by'] = ['date', 'reason'];

        $this->exclusionsModel = new ExclusionsModel();
    }

    public function getData()
    {
        // Init chart data
        $this->initChartData();

        $this->setChartData();

        return $this->getChartData();
    }

    protected function setChartData()
    {
        $period      = $this->args['date'] ?? DateRange::get();
        $periodDates = array_keys(TimeZone::getListDays($period));

        $parsedData   = [];
        $totalData    = array_fill_keys($periodDates, 0);

        // Set chart labels
        $this->setChartLabels(array_map(
            function ($date) {
                return [
                    'formatted_date'    =>DateTime::format($date, ['exclude_year' => true, 'short_month' => true]),
                    'date'              =>DateTime::format($date, ['date_format' => 'Y-m-d']),
                    'day'               =>DateTime::format($date, ['date_format' => 'l'])
                ];
            },
            $periodDates
        ));

        $data = $this->exclusionsModel->getExclusions($this->args);

        foreach ($data as $item) {
            $count = intval($item->count);
            $parsedData[$item->reason][$item->date] = $count;
            $totalData[$item->date]                 += $count;
        }

        // Sort data
        uasort($parsedData, function($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // Get top 3 exclusions
        $topData = array_slice($parsedData, 0, 3, true);

        foreach ($topData as $reason => $data) {
            // Fill out missing counts with 0
            $data = array_merge(array_fill_keys($periodDates, 0), $data);

            // Sort data by date
            ksort($data);

            // Add data as dataset
            $this->addChartDataset(
                ucfirst($reason),
                array_values($data)
            );
        }

        if (!empty($totalData)) {
            $this->addChartDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($totalData),
                'total'
            );
        }
    }
}