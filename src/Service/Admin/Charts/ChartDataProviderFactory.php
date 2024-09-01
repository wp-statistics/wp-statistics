<?php

namespace WP_Statistics\Service\Admin\Charts;

use WP_Statistics\Service\Admin\Charts\DataProvider\PerformanceChartDataProvider;
use WP_Statistics\Service\Admin\Charts\DataProvider\SearchEngineChartDataProvider;

class ChartDataProviderFactory
{
    public static function performanceChart($args)
    {
        return new PerformanceChartDataProvider($args);
    }

    public static function searchEngineChart($args)
    {
        return new SearchEngineChartDataProvider($args);
    }
}
