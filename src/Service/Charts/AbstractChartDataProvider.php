<?php
namespace WP_Statistics\Service\Charts;

use WP_STATISTICS\Option;

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

        return Option::get('charts_previous_period', 1) ? true : false;
    }


    /**
     * Checks if any of the filterable parameters are set.
     *
     * @return bool Returns true if any of the filterable parameters are set, false otherwise.
     */
    protected function isFilterApplied()
    {
        $args = array_filter($this->args);

        if (array_intersect(['resource_type', 'resource_id', 'query_param', 'post_type', 'author_id', 'post_id', 'taxonomy', 'term', 'event_name', 'event_target', 'utm_source', 'utm_medium', 'utm_campaign'], array_keys($args))) {
            return true;
        }

        return false;
    }
}