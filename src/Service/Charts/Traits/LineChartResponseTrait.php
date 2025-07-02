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
     * @return void
     */
    protected function setChartLabels($labels)
    {
        $this->chartData['data']['labels'] = $labels;
    }

    /**
     * Sets the previous chart labels.
     *
     * @param array $labels The chart labels.
     * @return void
     */
    protected function setChartPreviousLabels($labels)
    {
        $this->chartData['previousData']['labels'] = $labels;
    }

    /**
     * Adds a dataset to the chart data.
     *
     * @param string $label The label for the dataset.
     * @param array $data The data for the dataset.
     * @param string $slug The slug for the dataset
     * @return void
     */
    protected function addChartDataset($label, $data, $slug = null)
    {
        $this->chartData['data']['datasets'][] = [
            'label' => $label,
            'data'  => $data,
            'slug'  => $slug
        ];
    }

    /**
     * Adds a dataset to the previous chart data.
     *
     * @param string $label The label for the dataset.
     * @param array $data The data for the dataset.
     * @return void
     */
    protected function addChartPreviousDataset($label, $data)
    {
        $this->chartData['previousData']['datasets'][] = [
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