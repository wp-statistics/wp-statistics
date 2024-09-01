<?php

namespace WP_Statistics\Service\Admin\Charts;

use WP_Statistics\Service\Admin\Charts\DataProvider\PerformanceChartDataProvider;

class ChartDataProviderFactory
{
    public static function performanceChart($args)
    {
        return new PerformanceChartDataProvider($args);
    }
}
