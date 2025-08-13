<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Country;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_STATISTICS\Helper;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\MapChartResponseTrait;

class MapChartDataProvider extends AbstractChartDataProvider
{
    use MapChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'not_null' => 'location',
            'page'     => false,
            'per_page' => false
        ]);

        $this->visitorsModel = new VisitorsModel();
    }

    public function getData()
    {
        $this->initChartData();

        $data       = $this->visitorsModel->getVisitorsGeoData($this->args);
        $parsedData = $this->parseData($data);

        $labels  = wp_list_pluck($parsedData, 'label');
        $flags   = wp_list_pluck($parsedData, 'flag');
        $codes   = wp_list_pluck($parsedData, 'code');
        $data    = wp_list_pluck($parsedData, 'visitors');
        $rawData = wp_list_pluck($parsedData, 'visitors_raw');

        $this->setChartLabels($labels);
        $this->setChartFlags($flags);
        $this->setChartCountryCodes($codes);
        $this->setChartData($data);
        $this->setChartRawData($rawData);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        foreach ($data as $item) {
            if (empty($item->country)) continue;

            // Format the visitors count
            $formattedVisitors = number_format($item->visitors);

            $parsedData[] = [
                'label'        => Country::getName($item->country),
                'code'         => $item->country,
                'visitors'     => $formattedVisitors,
                'visitors_raw' => $item->visitors,
                'flag'         => Country::flag($item->country)
            ];
        }

        return $parsedData;
    }
}
