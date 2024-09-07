<?php

namespace WP_Statistics\Service\Charts\Traits;

trait MatrixChartResponseTrait
{
    private $chartData;


    /**
     * Initializes the chart data structure.
     *
     * @return void
     */
    protected function initChartData()
    {
        $this->chartData = [
            'datasets' => []
        ];
    }

    /**
     * Sets the chart datasets.
     *
     * @param array $data The chart data.
     * @return void
     */
    protected function setChartDatasets($data)
    {
        $this->chartData['datasets'] = $data;
    }

    /**
     * Retrieves the chart data.
     *
     * @return array The chart data.
     */
    protected function getChartData()
    {
        return $this->chartData;
    }
}
