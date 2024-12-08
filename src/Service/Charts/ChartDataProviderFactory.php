<?php

namespace WP_Statistics\Service\Charts;

use WP_Statistics\Service\Charts\DataProvider\PerformanceChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SearchEngineChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\TrafficChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\PlatformChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\PublishOverviewChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\AuthorsPostViewsChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\MapChartDataProvider;

class ChartDataProviderFactory
{
    /**
     * Returns a new instance of PerformanceChartDataProvider.
     *
     * @param array $args The arguments to pass to the PerformanceChartDataProvider constructor.
     * @return PerformanceChartDataProvider
     */
    public static function performanceChart($args)
    {
        return new PerformanceChartDataProvider($args);
    }

    /**
     * Returns a new instance of SearchEngineChartDataProvider.
     *
     * @param array $args The arguments to pass to the SearchEngineChartDataProvider constructor.
     * @return SearchEngineChartDataProvider
     */
    public static function searchEngineChart($args)
    {
        return new SearchEngineChartDataProvider($args);
    }

    /**
     * Returns a new instance of TrafficChartDataProvider.
     *
     * @param array $args The arguments to pass to the TrafficChartDataProvider constructor.
     * @return TrafficChartDataProvider
     */
    public static function trafficChart($args)
    {
        return new TrafficChartDataProvider($args);
    }

    /**
     * Returns a new instance of PlatformChartDataProvider.
     *
     * @param array $args The arguments to pass to the PlatformChartDataProvider constructor.
     * @return PlatformChartDataProvider
     */
    public static function platformCharts($args)
    {
        return new PlatformChartDataProvider($args);
    }

    /**
     * Returns a new instance of PublishOverviewChartDataProvider.
     *
     * @param array $args The arguments to pass to the PublishOverviewChartDataProvider constructor.
     * @return PublishOverviewChartDataProvider
     */
    public static function publishOverview($args)
    {
        return new PublishOverviewChartDataProvider($args);
    }

    /**
     * Returns a new instance of AuthorsPostViewsChartDataProvider.
     *
     * @param array $args The arguments to pass to the AuthorsPostViewsChartDataProvider constructor.
     * @return AuthorsPostViewsChartDataProvider
     */
    public static function authorsPostViews($args)
    {
        return new AuthorsPostViewsChartDataProvider($args);
    }

    /**
     * Returns a new instance of MapChartDataProvider.
     *
     * @param array $args
     * @return MapChartDataProvider
     */
    public static function mapChart($args)
    {
        return new MapChartDataProvider($args);
    }
}
