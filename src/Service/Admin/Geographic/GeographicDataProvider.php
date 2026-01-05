<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\Helper;
use WP_Statistics\Components\Country;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Service\AnalyticsQuery\AnalyticsQueryHandler;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class GeographicDataProvider
{
    protected $args;
    protected $queryHandler;

    public function __construct($args)
    {
        $this->args = $args;

        $this->queryHandler = new AnalyticsQueryHandler();
    }

    /**
     * Build date filters for AnalyticsQueryHandler.
     *
     * @return array
     */
    protected function buildDateFilters()
    {
        $filters = [];

        if (!empty($this->args['date']['from'])) {
            $filters['date_from'] = $this->args['date']['from'];
        }

        if (!empty($this->args['date']['to'])) {
            $filters['date_to'] = $this->args['date']['to'];
        }

        return $filters;
    }

    /**
     * Get date range from args.
     *
     * @return array
     */
    protected function getDateRange()
    {
        return [
            'from' => $this->args['date']['from'] ?? null,
            'to'   => $this->args['date']['to'] ?? null,
        ];
    }

    /**
     * Get previous period date range.
     *
     * @return array
     */
    protected function getPrevDateRange()
    {
        $prevPeriod = DateRange::getPrevPeriod();

        return [
            'from' => $prevPeriod['from'] ?? null,
            'to'   => $prevPeriod['to'] ?? null,
        ];
    }

    public function getOverviewData()
    {
        $dateRange = $this->getDateRange();

        // Get countries (top 5)
        $countriesResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['country'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 5,
            'page'      => 1,
        ]);
        $countries = $this->parseCountryData($countriesResult['data']['rows'] ?? []);

        // Get cities (top 5)
        $citiesResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['city'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 5,
            'page'      => 1,
        ]);
        $cities = $this->parseCityData($citiesResult['data']['rows'] ?? []);

        // Get regions for user's country (top 5)
        $countryCode = Helper::getTimezoneCountry();
        $countryRegionsResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['region'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 5,
            'page'      => 1,
        ]);
        $countryRegions = $this->parseRegionData($countryRegionsResult['data']['rows'] ?? []);

        // Get top region globally (for summary)
        $globalRegionsResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['region'],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
            'page'      => 1,
        ]);
        $globalRegions = $this->parseRegionData($globalRegionsResult['data']['rows'] ?? []);

        // Get US states (top 5)
        $statesResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['region'],
            'filters'   => [
                'country'   => 'US',
                'continent' => 'NA',
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 5,
            'page'      => 1,
        ]);
        $states = $this->parseRegionData($statesResult['data']['rows'] ?? []);

        $summary = [
            'country' => !empty($countries[0]->country) ? Country::getName($countries[0]->country) : '',
            'region'  => $globalRegions[0]->region ?? '',
            'city'    => $cities[0]->city ?? '',
        ];

        return [
            'summary'   => $summary,
            'countries' => $countries,
            'cities'    => $cities,
            'regions'   => $countryRegions,
            'states'    => $states,
        ];
    }

    public function getOverviewChartData()
    {
        $mapData        = ChartDataProviderFactory::mapChart()->getData();
        $europeData     = ChartDataProviderFactory::countryChart(['continent' => 'Europe'])->getData();
        $continentsData = ChartDataProviderFactory::continentChart()->getData();

        return [
            'map_chart_data'       => $mapData,
            'europe_chart_data'    => $europeData,
            'continent_chart_data' => $continentsData
        ];
    }

    public function getCountriesData()
    {
        $dateRange = $this->getDateRange();
        $perPage   = $this->args['per_page'] ?? 10;
        $page      = $this->args['page'] ?? 1;

        $result = $this->queryHandler->handle([
            'sources'     => ['visitors', 'views'],
            'group_by'    => ['country'],
            'date_from'   => $dateRange['from'],
            'date_to'     => $dateRange['to'],
            'format'      => 'table',
            'per_page'    => $perPage,
            'page'        => $page,
            'show_totals' => false,
        ]);

        return [
            'countries' => $this->parseCountryData($result['data']['rows'] ?? []),
            'total'     => $result['meta']['total'] ?? 0
        ];
    }

    public function getCitiesData()
    {
        $dateRange = $this->getDateRange();
        $perPage   = $this->args['per_page'] ?? 10;
        $page      = $this->args['page'] ?? 1;

        $result = $this->queryHandler->handle([
            'sources'     => ['visitors', 'views'],
            'group_by'    => ['city'],
            'date_from'   => $dateRange['from'],
            'date_to'     => $dateRange['to'],
            'format'      => 'table',
            'per_page'    => $perPage,
            'page'        => $page,
            'show_totals' => false,
        ]);

        return [
            'cities' => $this->parseCityData($result['data']['rows'] ?? []),
            'total'  => $result['meta']['total'] ?? 0
        ];
    }

    public function getEuropeData()
    {
        $dateRange = $this->getDateRange();
        $perPage   = $this->args['per_page'] ?? 10;
        $page      = $this->args['page'] ?? 1;

        $result = $this->queryHandler->handle([
            'sources'     => ['visitors', 'views'],
            'group_by'    => ['country'],
            'filters'     => [
                'continent' => 'EU',
            ],
            'date_from'   => $dateRange['from'],
            'date_to'     => $dateRange['to'],
            'format'      => 'table',
            'per_page'    => $perPage,
            'page'        => $page,
            'show_totals' => false,
        ]);

        return [
            'countries' => $this->parseCountryData($result['data']['rows'] ?? []),
            'total'     => $result['meta']['total'] ?? 0
        ];
    }

    public function getUsData()
    {
        $dateRange = $this->getDateRange();
        $perPage   = $this->args['per_page'] ?? 10;
        $page      = $this->args['page'] ?? 1;

        $result = $this->queryHandler->handle([
            'sources'     => ['visitors', 'views'],
            'group_by'    => ['region'],
            'filters'     => [
                'country'   => 'US',
                'continent' => 'NA',
            ],
            'date_from'   => $dateRange['from'],
            'date_to'     => $dateRange['to'],
            'format'      => 'table',
            'per_page'    => $perPage,
            'page'        => $page,
            'show_totals' => false,
        ]);

        return [
            'states' => $this->parseRegionData($result['data']['rows'] ?? []),
            'total'  => $result['meta']['total'] ?? 0
        ];
    }

    public function getRegionsData()
    {
        $countryCode = Helper::getTimezoneCountry();
        $dateRange   = $this->getDateRange();
        $perPage     = $this->args['per_page'] ?? 10;
        $page        = $this->args['page'] ?? 1;

        $result = $this->queryHandler->handle([
            'sources'     => ['visitors', 'views'],
            'group_by'    => ['region'],
            'filters'     => [
                'country' => $countryCode,
            ],
            'date_from'   => $dateRange['from'],
            'date_to'     => $dateRange['to'],
            'format'      => 'table',
            'per_page'    => $perPage,
            'page'        => $page,
            'show_totals' => false,
        ]);

        return [
            'regions' => $this->parseRegionData($result['data']['rows'] ?? []),
            'total'   => $result['meta']['total'] ?? 0
        ];
    }

    public function getSingleCountryData()
    {
        $dateRange     = $this->getDateRange();
        $prevDateRange = $this->getPrevDateRange();
        $countryCode   = $this->args['country'] ?? '';

        // Get current period stats
        $statsResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['country'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $stats = $statsResult['data']['rows'][0] ?? [];

        // Get previous period stats
        $prevStatsResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['country'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $prevDateRange['from'],
            'date_to'   => $prevDateRange['to'],
            'format'    => 'table',
            'per_page'  => 1,
        ]);
        $prevStats = $prevStatsResult['data']['rows'][0] ?? [];

        $visitors     = !empty($stats) ? intval($stats['visitors'] ?? 0) : 0;
        $prevVisitors = !empty($prevStats) ? intval($prevStats['visitors'] ?? 0) : 0;

        $views     = !empty($stats) ? intval($stats['views'] ?? 0) : 0;
        $prevViews = !empty($prevStats) ? intval($prevStats['views'] ?? 0) : 0;

        // Get regions (top 20)
        $regionsResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['region'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 20,
        ]);
        $regions = $this->parseRegionData($regionsResult['data']['rows'] ?? []);

        // Get cities (paginated)
        $citiesPage = !empty($this->args['cities_page']) ? intval($this->args['cities_page']) : 1;
        $citiesResult = $this->queryHandler->handle([
            'sources'   => ['visitors', 'views'],
            'group_by'  => ['city'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 10,
            'page'      => $citiesPage,
        ]);
        $cities      = $this->parseCityData($citiesResult['data']['rows'] ?? []);
        $citiesTotal = $citiesResult['meta']['total'] ?? 0;

        // Get referrers
        $referrersResult = $this->queryHandler->handle([
            'sources'   => ['visitors'],
            'group_by'  => ['referrer'],
            'filters'   => [
                'country' => $countryCode,
            ],
            'date_from' => $dateRange['from'],
            'date_to'   => $dateRange['to'],
            'format'    => 'table',
            'per_page'  => 10,
        ]);
        $referrers = $this->parseReferrerData($referrersResult['data']['rows'] ?? []);

        return [
            'glance'    => [
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'views'    => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'region'   => [
                    'value' => !empty($regions) ? $regions[0]->region : ''
                ],
                'city'     => [
                    'value' => !empty($cities) ? $cities[0]->city : ''
                ],
                'referrer' => [
                    'value' => !empty($referrers) ? $referrers[0]->referred : ''
                ]
            ],
            'regions'   => $regions,
            'cities'    => [
                'data'  => $cities,
                'total' => $citiesTotal
            ],
            'referrers' => $referrers
        ];
    }

    public function getSingleCountryChartsData()
    {
        $platformDataProvider      = ChartDataProviderFactory::platformCharts($this->args);
        $searchEngineDataProvider  = ChartDataProviderFactory::searchEngineChart($this->args);
        $trafficTrendsDataProvider = ChartDataProviderFactory::trafficChart($this->args);

        return [
            'search_engine_chart_data' => $searchEngineDataProvider->getData(),
            'os_chart_data'            => $platformDataProvider->getOsData(),
            'browser_chart_data'       => $platformDataProvider->getBrowserData(),
            'device_chart_data'        => $platformDataProvider->getDeviceData(),
            'model_chart_data'         => $platformDataProvider->getModelData(),
            'traffic_chart_data'       => $trafficTrendsDataProvider->getData()
        ];
    }

    /**
     * Parse country data from AnalyticsQueryHandler result to legacy object format.
     *
     * @param array $rows
     * @return array
     */
    protected function parseCountryData($rows)
    {
        $result = [];

        foreach ($rows as $row) {
            $item = new \stdClass();
            $item->country   = $row['country_code'] ?? '';
            $item->visitors  = intval($row['visitors'] ?? 0);
            $item->views     = intval($row['views'] ?? 0);
            $item->continent = $row['country_continent'] ?? '';
            $item->region    = '';
            $item->city      = '';
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Parse city data from AnalyticsQueryHandler result to legacy object format.
     *
     * @param array $rows
     * @return array
     */
    protected function parseCityData($rows)
    {
        $result = [];

        foreach ($rows as $row) {
            $item = new \stdClass();
            $item->city      = $row['city_name'] ?? '';
            $item->country   = $row['country_code'] ?? '';
            $item->region    = $row['city_region_name'] ?? '';
            $item->visitors  = intval($row['visitors'] ?? 0);
            $item->views     = intval($row['views'] ?? 0);
            $item->continent = '';
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Parse region data from AnalyticsQueryHandler result to legacy object format.
     *
     * @param array $rows
     * @return array
     */
    protected function parseRegionData($rows)
    {
        $result = [];

        foreach ($rows as $row) {
            $item = new \stdClass();
            $item->region    = $row['region_name'] ?? '';
            $item->country   = $row['country_code'] ?? '';
            $item->visitors  = intval($row['visitors'] ?? 0);
            $item->views     = intval($row['views'] ?? 0);
            $item->city      = '';
            $item->continent = '';
            $result[] = $item;
        }

        return $result;
    }

    /**
     * Parse referrer data from AnalyticsQueryHandler result to legacy object format.
     *
     * @param array $rows
     * @return array
     */
    protected function parseReferrerData($rows)
    {
        $result = [];

        foreach ($rows as $row) {
            $item = new \stdClass();
            $item->referred       = $row['referrer_domain'] ?? '';
            $item->visitors       = intval($row['visitors'] ?? 0);
            $item->source_channel = $row['referrer_channel'] ?? '';
            $item->source_name    = $row['referrer_name'] ?? '';
            $result[] = $item;
        }

        return $result;
    }
}
