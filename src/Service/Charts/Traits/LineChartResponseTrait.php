<?php

namespace WP_Statistics\Service\Charts\Traits;

trait LineChartResponseTrait
{
    private $chartData;


    /**
     * Initializes the chart data structure.
     *
     * @param bool $prevData Whether to include previous data in the structure.
     * @return void
     */
    protected function initChartData($prevData = false)
    {
        $this->chartData = [
            'data' => [
                'labels'    => [],
                'datasets'  => [],
            ]
        ];

        if ($prevData) {
            $this->chartData['previousData'] = [
                'labels'    => [],
                'datasets'  => [],
            ];
        }
    }


    /**
     * Sets the chart labels.
     *
     * @param array $labels The chart labels.
     * @param bool $prevData Whether to set the labels for previous data or not.
     * @return void
     */
    protected function setChartLabels($labels, $prevData = false)
    {
        $key = $prevData ? 'previousData' : 'data';
        $this->chartData[$key]['labels'] = $labels;
    }


    /**
     * Adds a dataset to the chart data.
     *
     * @param string $label The label for the dataset.
     * @param array $data The data for the dataset.
     * @param bool $prevData (optional) Whether to add the dataset to the previous data or not. Defaults to false.
     * @return void
     */
    protected function addChartDataset($label, $data, $prevData = false)
    {
        $key = $prevData ? 'previousData' : 'data';

        $this->chartData[$key]['datasets'][] = [
            'label' => $label,
            'data'  => $data
        ];
    }

    /**
     * Get the complete response data for the chart.
     *
     * @return array
     */
    protected function getChartData()
    {
        return $this->chartData;
    }
}
