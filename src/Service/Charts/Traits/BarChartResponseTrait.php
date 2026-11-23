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
            'data'        => [],
            'labels'      => [],
            'icons'       => [],
            'percentages' => [],
        ];
    }

    /**
     * Sets the real whole the rows are a share of (e.g. all visitors), so the
     * front end can divide by it instead of by the rows shown.
     *
     * @param int|float $total
     * @return void
     */
    protected function setChartTotal($total)
    {
        $this->chartData['total'] = $total;
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

    /**
     * Sets the chart percentages.
     *
     * @param array $percentages The chart percentages.
     * @return void
     */
    protected function setChartPercentages($percentages)
    {
        $this->chartData['percentages'] = $percentages;
    }
}
