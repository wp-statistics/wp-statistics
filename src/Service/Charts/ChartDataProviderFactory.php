<?php

namespace WP_Statistics\Service\Charts;

use WP_Statistics\Service\Charts\DataProvider\PerformanceChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SearchEngineChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\TrafficChartDataProvider;

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
