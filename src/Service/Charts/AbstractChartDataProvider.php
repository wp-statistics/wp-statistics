<?php
namespace WP_Statistics\Service\Charts;

abstract class AbstractChartDataProvider
{
    protected $args;

    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * Determines if previous data is enabled for charts.
     *
     * @return bool Returns true if previous data is enabled, false otherwise.
     */
    protected function isPreviousDataEnabled()
    {
        return !empty($this->args['prev_data']);
    }
}
