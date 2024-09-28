<?php
namespace WP_Statistics\Service\Charts;

use WP_STATISTICS\Option;

abstract class AbstractChartDataProvider
{
    /**
     * Determines if previous data is enabled for charts.
     *
     * @return bool Returns true if previous data is enabled, false otherwise.
     */
    protected function isPreviousDataEnabled()
    {
        return Option::get('charts_previous_period', 1) ? true : false;
    }
}