<?php

namespace WP_Statistics\Service\Charts\Traits;

trait MapChartResponseTrait
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
            'labels'    => [],
            'codes'     => [],
            'flags'     => [],
            'data'      => []
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
     * Sets the chart country code.
     *
     * @param array $codes The chart country code.
     * @return void
     */
    protected function setChartCountryCodes($codes)
    {
        $this->chartData['codes'] = $codes;
    }

    /**
     * Sets the chart flags.
     *
     * @param array $flags The chart flags.
     * @return void
     */
    protected function setChartFlags($flags)
    {
        $this->chartData['flags'] = $flags;
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

    /**
     * Sets the chart raw data
     *
     * @param array $rawData The chart raw data
     * @return void
     */
    protected function setChartRawData($rawData) {
        $this->chartData['raw_data'] = $rawData;
    }

    protected function getChartData()
    {
        return $this->chartData;
    }
}
