<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Country;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class GeographicDataProvider
{
    protected $args;
    protected $visitorsModel;

    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
    }

    public function getOverviewData()
    {
        $args = array_merge($this->args, ['per_page' => 5, 'page' => 1]);

        $countries      = $this->visitorsModel->getVisitorsGeoData($args);
        $cities         = $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['group_by' => ['city'], 'not_null' => 'visitor.city', 'count_field' => 'city']));
        $countryRegions = $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['country' => Helper::getTimezoneCountry(), 'group_by' => ['country', 'region'], 'count_field' => 'region', 'not_null' => 'visitor.region']));
        $globalRegions  = $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['group_by' => ['region'], 'count_field' => 'region', 'not_null' => 'visitor.region', 'per_page' => 1]));
        $states         = $this->visitorsModel->getVisitorsGeoData(array_merge($args, ['country' => 'US', 'continent' => 'North America', 'group_by' => ['region'], 'count_field' => 'region', 'not_null' => 'visitor.region']));

        $summary   = [
            'country'   => !empty($countries[0]->country) ? Country::getName($countries[0]->country) : '',
            'region'    => $globalRegions[0]->region ?? '',
            'city'      => $cities[0]->city ?? '',
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
            'map_chart_data'        => $mapData,
            'europe_chart_data'     => $europeData,
            'continent_chart_data'  => $continentsData
        ];
    }

    public function getCountriesData()
    {
        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($this->args),
            'total'     => $this->visitorsModel->countGeoData($this->args)
        ];
    }

    public function getCitiesData()
    {
        $args = array_merge(
            $this->args,
            [
                'group_by'    => ['city'],
                'not_null'    => 'visitor.city',
                'count_field' => 'city'
            ]
        );

        return [
            'cities' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'  => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getEuropeData()
    {
        $args = array_merge(
            $this->args,
            ['continent' => 'Europe']
        );

        return [
            'countries' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'     => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getUsData()
    {
        $args = array_merge(
            $this->args,
            [
                'country'     => 'US',
                'continent'   => 'North America',
                'group_by'    => ['region'],
                'count_field' => 'region',
                'not_null'    => 'visitor.region'
            ]
        );

        return [
            'states' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'  => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getRegionsData()
    {
        $countryCode = Helper::getTimezoneCountry();

        $args = array_merge(
            $this->args,
            [
                'country'     => $countryCode,
                'group_by'    => ['country', 'region'],
                'count_field' => 'region',
                'not_null'    => 'visitor.region'
            ]
        );

        return [
            'regions' => $this->visitorsModel->getVisitorsGeoData($args),
            'total'   => $this->visitorsModel->countGeoData($args)
        ];
    }

    public function getSingleCountryData()
    {
        $geoData = $this->visitorsModel->getVisitorsGeoData($this->args);
        $stats   = reset($geoData);

        $prevGeoData = $this->visitorsModel->getVisitorsGeoData(array_merge($this->args, ['date' => DateRange::getPrevPeriod()]));
        $prevStats   = reset($prevGeoData);

        $visitors     = !empty($stats) ? $stats->visitors : 0;
        $prevVisitors = !empty($prevStats) ? $prevStats->visitors : 0;

        $views     = !empty($stats) ? $stats->views : 0;
        $prevViews = !empty($prevStats) ? $prevStats->views : 0;

        $regions = $this->visitorsModel->getVisitorsGeoData(array_merge(
            $this->args,
            [
                'group_by'    => ['region'],
                'count_field' => 'region',
                'not_null'    => 'visitor.region',
                'per_page'    => 20
            ]
        ));

        $cities = $this->visitorsModel->getVisitorsGeoData(array_merge(
            $this->args,
            [
                'group_by'    => ['city'],
                'count_field' => 'city',
                'not_null'    => 'visitor.city',
                'per_page'    => 10,
                'page'        => !empty($this->args['cities_page']) ? $this->args['cities_page'] : 1
            ]
        ));

        $citiesTotal = $this->visitorsModel->countGeoData(array_merge(
            $this->args,
            [
                'group_by'    => ['city'],
                'count_field' => 'city',
                'not_null'    => 'visitor.city',
            ]
        ));

        $referrers = $this->visitorsModel->getReferrers($this->args);

        return [
            'glance'     => [
                'visitors' => [
                    'value'  => $visitors,
                    'change' => Helper::calculatePercentageChange($prevVisitors, $visitors)
                ],
                'views'    => [
                    'value'  => $views,
                    'change' => Helper::calculatePercentageChange($prevViews, $views)
                ],
                'region' => [
                    'value' => !empty($regions) ? $regions[0]->region : ''
                ],
                'city' => [
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
        $platformDataProvider       = ChartDataProviderFactory::platformCharts($this->args);
        $searchEngineDataProvider   = ChartDataProviderFactory::searchEngineChart($this->args);
        $trafficTrendsDataProvider  = ChartDataProviderFactory::trafficChart($this->args);

        return [
            'search_engine_chart_data'  => $searchEngineDataProvider->getData(),
            'os_chart_data'             => $platformDataProvider->getOsData(),
            'browser_chart_data'        => $platformDataProvider->getBrowserData(),
            'device_chart_data'         => $platformDataProvider->getDeviceData(),
            'model_chart_data'          => $platformDataProvider->getModelData(),
            'traffic_chart_data'        => $trafficTrendsDataProvider->getData()
        ];
    }
}