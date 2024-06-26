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
                'per_page'    => 10
            ]
        ));

        $cities = $this->visitorsModel->getVisitorsGeoData(array_merge(
            $this->args,
            [
                'group_by'    => ['city'],
                'count_field' => 'city',
                'not_null'    => 'visitor.city',
                'per_page'    => 10
            ]
        ));

        $referrers = $this->visitorsModel->getReferrers($this->args);

        return [
            'stats'     => $stats,
            'regions'   => $regions,
            'cities'    => $cities,
            'referrers' => $referrers,
        ];
    }

    public function getChartsData()
    {
        $chartData = $this->visitorsModel->getParsedVisitorsData($this->args);

        return [
            'search_engine_chart_data' => $this->getSearchEnginesChartData(),
            'os_chart_data'            => [
                'labels' => array_keys($chartData['platform']),
                'data'   => array_values($chartData['platform'])
            ],
            'browser_chart_data'       => [
                'labels' => array_keys($chartData['agent']),
                'data'   => array_values($chartData['agent'])
            ],
            'device_chart_data'        => [
                'labels' => array_keys($chartData['device']),
                'data'   => array_values($chartData['device'])
            ],
            'model_chart_data'         => [
                'labels' => array_keys($chartData['model']),
                'data'   => array_values($chartData['model'])
            ],
        ];
    }

    public function getSearchEnginesChartData()
    {

        // Get results up to 30 days
        $args = [];
        $days = TimeZone::getNumberDayBetween($this->args['date']['from'], $this->args['date']['to']);
        if ($days > 30) {
            $args = [
                'date' => [
                    'from' => date('Y-m-d', strtotime("-30 days", strtotime($this->args['date']['to']))),
                    'to'   => $this->args['date']['to']
                ]
            ];
        }

        $args = array_merge($this->args, $args);

        $datesList = TimeZone::getListDays($args['date']);
        $datesList = array_keys($datesList);

        $result = [
            'labels'   => array_map(function ($date) {
                return date_i18n('j M', strtotime($date));
            }, $datesList),
            'datasets' => []
        ];

        $data       = $this->visitorsModel->getSearchEngineReferrals($args);
        $parsedData = [];
        $totalData  = array_fill_keys($datesList, 0);

        // Format and parse data
        foreach ($data as $item) {
            $parsedData[$item->engine][$item->date] = $item->visitors;
            $totalData[$item->date]                 += $item->visitors;
        }

        foreach ($parsedData as $searchEngine => &$data) {
            // Fill out missing visitors with 0
            $data = array_merge(array_fill_keys($datesList, 0), $data);

            // Sort data by date
            ksort($data);

            // Generate dataset
            $result['datasets'][] = [
                'label' => ucfirst($searchEngine),
                'data'  => array_values($data)
            ];
        }

        if (!empty($result['datasets'])) {
            $result['datasets'][] = [
                'label' => esc_html__('Total', 'wp-statistics'),
                'data'  => array_values($totalData)
            ];
        }

        return $result;
    }
}