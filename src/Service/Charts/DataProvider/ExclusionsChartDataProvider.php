<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class ExclusionsChartDataProvider extends AbstractChartDataProvider
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
                    'formatted_date' => DateTime::format($date, ['exclude_year' => true, 'short_month' => true]),
                    'date'           => DateTime::format($date, ['date_format' => 'Y-m-d']),
                    'day'            => DateTime::format($date, ['date_format' => 'D'])
                ];
            },
            $periodDates
        ));

        // Query exclusions data using AnalyticsQueryHandler
        $result = $this->queryHandler->handle([
            'sources'   => ['exclusions'],
            'group_by'  => ['exclusion_date', 'exclusion_reason'],
            'date_from' => $period['from'] ?? null,
            'date_to'   => $period['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $result['data']['rows'] ?? [];

        foreach ($data as $row) {
            $date   = $row['date'] ?? '';
            $reason = $row['reason'] ?? '';
            $count  = intval($row['exclusions'] ?? 0);

            if (!empty($reason) && !empty($date)) {
                $parsedData[$reason][$date] = $count;
                if (isset($totalData[$date])) {
                    $totalData[$date] += $count;
                }
            }
        }

        // Sort data
        uasort($parsedData, function ($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // Get top 3 exclusions
        $topData = array_slice($parsedData, 0, 3, true);

        foreach ($topData as $reason => $reasonData) {
            // Fill out missing counts with 0
            $reasonData = array_merge(array_fill_keys($periodDates, 0), $reasonData);

            // Sort data by date
            ksort($reasonData);

            // Add data as dataset
            $this->addChartDataset(
                ucfirst($reason),
                array_values($reasonData)
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
