<?php

namespace WP_Statistics\Service\Admin\Charts;

use WP_Statistics\Service\Admin\Charts\DataProvider\PerformanceChartDataProvider;
use WP_Statistics\Service\Admin\Charts\DataProvider\SearchEngineChartDataProvider;
use WP_Statistics\Service\Admin\Charts\DataProvider\TrafficChartDataProvider;

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

    public static function trafficChart($args)
    {
        return new TrafficChartDataProvider($args);
    }
}
