<?php

namespace WP_Statistics\Service\Charts\DataProvider;

use WP_STATISTICS\Country;
use WP_Statistics\Models\VisitorsModel;
use WP_Statistics\Service\Charts\AbstractChartDataProvider;
use WP_Statistics\Service\Charts\Traits\BarChartResponseTrait;

class ContinentChartDataProvider extends AbstractChartDataProvider
{
    use BarChartResponseTrait;

    protected $visitorsModel;

    public function __construct($args)
    {
        parent::__construct($args);

        $this->args = array_merge($this->args, [
            'not_null'  => 'continent',
            'order_by'  => 'visitors',
            'group_by'  => 'continent',
            'page'      => 1,
            'per_page'  => 5
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

        return $this->getChartData();
    }

    protected function parseData($data)
    {
        $parsedData = [
            'labels'    => [],
            'visitors'  => []
        ];

        foreach ($data as $item) {
            $parsedData['labels'][]     = $item->continent;
            $parsedData['visitors'][]   = intval($item->visitors);
        }

        return $parsedData;
    }
}
