<?php

namespace WP_Statistics\Service\Admin\Geographic;

use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\TimeZone;

class GeographicDataProvider
{
    protected $args;
    protected $visitorsModel;


    public function __construct($args)
    {
        $this->args = $args;

        $this->visitorsModel = new VisitorsModel();
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
        $visitorsGeoData = $this->visitorsModel->getVisitorsGeoData($this->args);
        $stats           = reset($visitorsGeoData);

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
            'stats'     => [
                'visitors' => !empty($stats) ? $stats->visitors : 0,
                'views'    => !empty($stats) ? $stats->views : 0
            ],
            'regions'   => $regions,
            'cities'    => [
                'data'  => $cities,
                'total' => $citiesTotal
            ],
            'referrers' => $referrers,
        ];
    }

    public function getSingleCountryChartsData()
    {
        $platformData = $this->visitorsModel->getVisitorsPlatformData($this->args);

        return [
            'search_engine_chart_data' => $this->visitorsModel->getSearchEnginesChartData($this->args),
            'os_chart_data'         => [
                'labels'    => wp_list_pluck($platformData['platform'], 'label'),
                'data'      => wp_list_pluck($platformData['platform'], 'visitors'),
                'icons'     => wp_list_pluck($platformData['platform'], 'icon'),
            ],
            'browser_chart_data'    => [
                'labels'    => wp_list_pluck($platformData['agent'], 'label'), 
                'data'      => wp_list_pluck($platformData['agent'], 'visitors'),
                'icons'     => wp_list_pluck($platformData['agent'], 'icon')
            ],
            'device_chart_data'        => [
                'labels' => wp_list_pluck($platformData['device'], 'label'),
                'data'   => wp_list_pluck($platformData['device'], 'visitors')
            ],
            'model_chart_data'         => [
                'labels' => wp_list_pluck($platformData['model'], 'label'),
                'data'   => wp_list_pluck($platformData['model'], 'visitors')
            ],
        ];
    }
}