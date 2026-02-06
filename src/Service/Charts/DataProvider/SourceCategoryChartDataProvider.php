<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Analytics\Referrals\SourceChannels;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class SourceCategoryChartDataProvider extends AbstractChartDataProvider
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
        $thisPeriod      = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $thisPeriodDates = array_keys(TimeZone::getListDays($thisPeriod));

        // This period data
        $thisParsedData  = [];
        $thisPeriodTotal = array_fill_keys($thisPeriodDates, 0);

        // Set chart labels
        $this->setChartLabels($this->generateChartLabels($thisPeriodDates));

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer_channel', 'date'],
            'date_from' => $thisPeriod['from'] ?? null,
            'date_to'   => $thisPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $result['data']['rows'] ?? [];

        foreach ($data as $row) {
            $visitors       = intval($row['visitors'] ?? 0);
            $sourceChannel  = $row['referrer_channel'] ?? '';
            $date           = $row['date'] ?? '';

            if (empty($sourceChannel) || empty($date)) {
                continue;
            }

            // Get display name for the channel
            $channelName = SourceChannels::getName($sourceChannel) ?: ucfirst($sourceChannel);

            $thisParsedData[$channelName][$date] = $visitors;
            $thisPeriodTotal[$date]              += $visitors;
        }

        // Sort data by total visitors number
        uasort($thisParsedData, function ($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // Get top 3
        $topCategories = array_slice($thisParsedData, 0, 3, true);

        foreach ($topCategories as $category => &$categoryData) {
            // Fill out missing visitors with 0
            $categoryData = array_merge(array_fill_keys($thisPeriodDates, 0), $categoryData);

            // Sort data by date
            ksort($categoryData);

            // Add category data as dataset
            $this->addChartDataset(
                $category,
                array_values($categoryData)
            );
        }

        if (!empty($thisPeriodTotal)) {
            $this->addChartDataset(
                esc_html__('Total', 'wp-statistics'),
                array_values($thisPeriodTotal),
                'total'
            );
        }
    }

    protected function setPrevPeriodData()
    {
        $thisPeriod = isset($this->args['date']) ? $this->args['date'] : DateRange::get();
        $prevPeriod = DateRange::getPrevPeriod($thisPeriod);

        $prevPeriodDates = array_keys(TimeZone::getListDays($prevPeriod));

        $this->setChartPreviousLabels($this->generateChartLabels($prevPeriodDates));

        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer_channel', 'date'],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $result['data']['rows'] ?? [];

        // Previous period data
        $prevPeriodTotal = array_fill_keys($prevPeriodDates, 0);

        foreach ($data as $row) {
            $visitors = intval($row['visitors'] ?? 0);
            $date     = $row['date'] ?? '';

            if (!empty($date) && isset($prevPeriodTotal[$date])) {
                $prevPeriodTotal[$date] += $visitors;
            }
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
                    'formatted_date' => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                    'date'           => date_i18n('Y-m-d', strtotime($date)),
                    'day'            => date_i18n('D', strtotime($date))
                ];
            },
            $dateRange
        );

        return $labels;
    }
}
