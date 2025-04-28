<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Country;
use WP_Statistics\Decorators\VisitorDecorator;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Analytics\DeviceDetection\DeviceHelper;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class CountryChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'per_page' => 5,
            'not_null' => 'location'
        ]);

        $this->visitorsModel = new VisitorsModel();
    }


    public function getData()
    {
        $this->initChartData();

        $data = $this->visitorsModel->getVisitorsGeoData($this->args);

        $data = $this->parseData($data);

        $this->setChartLabels($data['labels']);
        $this->setChartData($data['visitors']);
        $this->setChartIcons($data['icons']);

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [];

        foreach ($data as $item) {
            $parsedData['labels'][] = Country::getName($item->country);
            $parsedData['visitors'][] = number_format_i18n($item->visitors);
            $parsedData['icons'][] = Country::flag($item->country);
        }

        return $parsedData;
    }
}
