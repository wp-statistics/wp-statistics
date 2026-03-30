<?php

namespace WP_Statistics\Service\Charts\Traits;

/**
 * @deprecated 15.0.0 Kept for backward compatibility with addons. Will be removed in a future version.
 */
trait LineChartResponseTrait
{
    private $chartData;

    protected function initChartData($prevData = false)
    {
        $this->chartData = [
            'data' => [
                'labels'   => [],
                'datasets' => [],
            ]
        ];

        if ($prevData) {
            $this->chartData['previousData'] = [
                'labels'   => [],
                'datasets' => [],
            ];
        }
    }

    protected function setChartLabels($labels)
    {
        $this->chartData['data']['labels'] = $labels;
    }

    protected function setChartPreviousLabels($labels)
    {
        $this->chartData['previousData']['labels'] = $labels;
    }

    protected function addChartDataset($label, $data, $slug = null)
    {
        $this->chartData['data']['datasets'][] = [
            'label' => $label,
            'data'  => $data,
            'slug'  => $slug
        ];
    }

    protected function addChartPreviousDataset($label, $data, $slug = '')
    {
        $this->chartData['previousData']['datasets'][] = [
            'label' => $label,
            'data'  => $data,
            'slug'  => $slug
        ];
    }

    protected function getChartData()
    {
        return $this->chartData;
    }
}
