<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\LineChartResponseTrait;
use WP_STATISTICS\TimeZone;

class SearchEngineChartDataProvider extends AbstractChartDataProvider
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

    /**
     * Get the channel filter based on external filters.
     *
     * @return array Channel filter for the query.
     */
    protected function getChannelFilter()
    {
        $externalFilters = $this->args['filters'] ?? [];

        // Check if referrer_channel is specified in external filters
        if (isset($externalFilters['referrer_channel']) && is_array($externalFilters['referrer_channel'])) {
            $channelFilter = $externalFilters['referrer_channel'];

            // Handle 'in' operator: ['in' => ['search', 'paid']]
            if (isset($channelFilter['in'])) {
                return $channelFilter;
            }

            // Handle 'is' operator: ['is' => 'search'] or ['is' => 'paid']
            if (isset($channelFilter['is'])) {
                return ['in' => [$channelFilter['is']]];
            }
        }

        // Default: both search and paid channels
        return ['in' => ['search', 'paid']];
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

        // Query for search engine referrals with channel filter from args
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer', 'date'],
            'filters'   => [
                'referrer_channel' => $this->getChannelFilter(),
            ],
            'date_from' => $thisPeriod['from'] ?? null,
            'date_to'   => $thisPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $result['data']['rows'] ?? [];

        foreach ($data as $row) {
            $dateKey      = $row['date'] ?? '';
            $referrerName = $row['referrer_name'] ?? '';
            $visitors     = intval($row['visitors'] ?? 0);

            if (!empty($referrerName) && !empty($dateKey)) {
                $thisParsedData[$referrerName][$dateKey] = ($thisParsedData[$referrerName][$dateKey] ?? 0) + $visitors;
                $thisPeriodTotal[$dateKey]               = ($thisPeriodTotal[$dateKey] ?? 0) + $visitors;
            }
        }

        // Sort data by search engine referrals number
        uasort($thisParsedData, function ($a, $b) {
            return array_sum($b) - array_sum($a);
        });

        // Get top 3 search engines
        $topSearchEngines = array_slice($thisParsedData, 0, 3, true);

        foreach ($topSearchEngines as $searchEngine => &$searchEngineData) {
            // Fill out missing visitors with 0
            $searchEngineData = array_merge(array_fill_keys($thisPeriodDates, 0), $searchEngineData);

            // Sort data by date
            ksort($searchEngineData);

            // Add search engine data as dataset
            $this->addChartDataset(
                ucfirst($searchEngine),
                array_values($searchEngineData)
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

        // Query for previous period search engine referrals with same channel filter
        $result = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['date'],
            'filters'   => [
                'referrer_channel' => $this->getChannelFilter(),
            ],
            'date_from' => $prevPeriod['from'] ?? null,
            'date_to'   => $prevPeriod['to'] ?? null,
            'format'    => 'table',
            'per_page'  => 1000,
        ]);

        $data = $result['data']['rows'] ?? [];

        // Previous period data
        $prevPeriodTotal = array_fill_keys($prevPeriodDates, 0);

        foreach ($data as $row) {
            $dateKey  = $row['date'] ?? '';
            $visitors = intval($row['visitors'] ?? 0);

            if (!empty($dateKey) && isset($prevPeriodTotal[$dateKey])) {
                $prevPeriodTotal[$dateKey] += $visitors;
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
                    'formatted_date'    => date_i18n(Helper::getDefaultDateFormat(false, true, true), strtotime($date)),
                    'date'              => date_i18n('Y-m-d', strtotime($date)),
                    'day'               => date_i18n('D', strtotime($date))
                ];
            },
            $dateRange
        );

        return $labels;
    }
}