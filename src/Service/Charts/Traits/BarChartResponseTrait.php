<?php

namespace WP_Statistics\Service\Charts\Traits;

/**
 * @deprecated 15.0.0 Kept for backward compatibility with addons. Will be removed in a future version.
 */
trait BarChartResponseTrait
{
    private $chartData;

    protected function initChartData()
    {
        $this->chartData = [
            'data'   => [],
            'labels' => [],
            'icons'  => []
        ];
    }

    protected function setChartLabels($labels)
    {
        $this->chartData['labels'] = $labels;
    }

    protected function setChartIcons($icons)
    {
        $this->chartData['icons'] = $icons;
    }

    protected function setChartData($data)
    {
        $this->chartData['data'] = $data;
    }

    protected function getChartData()
    {
        return $this->chartData;
    }
}
