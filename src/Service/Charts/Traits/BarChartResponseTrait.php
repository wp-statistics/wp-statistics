<?php

namespace WP_Statistics\Service\Charts\Traits;

trait BarChartResponseTrait
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
            'data'      => [],
            'labels'    => [],
            'icons'     => []
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
     * Sets the chart icons.
     *
     * @param array $icons The chart icons.
     * @return void
     */
    protected function setChartIcons($icons)
    {
        $this->chartData['icons'] = $icons;
    }

    /**
     * Sets the chart data.
     *
     * @param array $data The chart data.
     * @return void
     */
    protected function setChartData($data)
    {
        $this->chartData['data'] = $data;
    }

    protected function getChartData()
    {
        return $this->chartData;
    }
}
