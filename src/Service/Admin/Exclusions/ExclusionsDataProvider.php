<?php
namespace WP_Statistics\Service\Admin\Exclusions;

use WP_Statistics\Models\ExclusionsModel;
use WP_Statistics\Service\Charts\ChartDataProviderFactory;

class ExclusionsDataProvider
{
    private $args;
    private $exclusionsModel;

    public function __construct($args = [])
    {
        $this->args = $args;

        $this->exclusionsModel  = new ExclusionsModel();
    }

    public function getChartData()
    {
        return  [
            'exclusions_chart_data' => ChartDataProviderFactory::exclusionsChart($this->args)->getData()
        ];
    }

    public function getExclusionsData()
    {
        $data   = $this->exclusionsModel->getExclusions($this->args);
        $total  = array_sum(array_column($data, 'count'));

        return [
            'data'  => $data,
            'total' => $total
        ];
    }
}