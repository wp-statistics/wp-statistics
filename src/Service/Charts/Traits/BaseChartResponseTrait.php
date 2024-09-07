<?php

namespace WP_Statistics\Service\Charts\Traits;

trait BaseChartResponseTrait
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
            'labels'   => [],
            'datasets' => []
        ];
    }

    /**
     * Sets the chart labels.
     *
     * @param array $labels The chart labels.
     * @return void
     */
    protected function setChartLabels($labels)
    {
        $this->chartData['labels'] = $labels;
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
