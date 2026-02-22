<?php

namespace WP_Statistics\Service\Charts;

/**
 * @deprecated 15.0.0 Kept for backward compatibility with addons. Will be removed in a future version.
 */
abstract class AbstractChartDataProvider
{
    protected $args;

    public function __construct($args = [])
    {
        $this->args = $args;
    }

    protected function isPreviousDataEnabled()
    {
        return !empty($this->args['prev_data']);
    }
}
