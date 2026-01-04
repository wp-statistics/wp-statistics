<?php
namespace WP_Statistics\Service\Charts;

use WP_Statistics\Globals\Option;

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
        if (!empty($this->args['prev_data'])) {
            return true;
        }

        return Option::getValue('charts_previous_period', 1) ? true : false;
    }
}