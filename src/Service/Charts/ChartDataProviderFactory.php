<?php

namespace WP_Statistics\Service\Charts;

use WP_Statistics\Service\Charts\DataProvider\OsChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\MapChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\ModelChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\DeviceChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\BrowserChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\TrafficChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\PlatformChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\ExclusionsChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\PerformanceChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SocialMediaChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SearchEngineChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\UsersTrafficChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SourceCategoryChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\PublishOverviewChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\AuthorsPostViewsChartDataProvider;

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
     * Returns a new instance of BrowserChartDataProvider.
     *
     * @param array $args
     * @return BrowserChartDataProvider
     */
    public static function browserChart($args)
    {
        return new BrowserChartDataProvider($args);
    }

    /**
     * Returns a new instance of DeviceChartDataProvider.
     *
     * @param array $args
     * @return DeviceChartDataProvider
     */
    public static function deviceChart($args)
    {
        return new DeviceChartDataProvider($args);
    }

    /**
     * Returns a new instance of OsChartDataProvider.
     *
     * @param array $args
     * @return OsChartDataProvider
     */
    public static function osChart($args)
    {
        return new OsChartDataProvider($args);
    }

    /**
     * Returns a new instance of ModelChartDataProvider.
     *
     * @param array $args
     * @return ModelChartDataProvider
     */
    public static function modelChart($args)
    {
        return new ModelChartDataProvider($args);
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
     * Returns a new instance of SocialMediaChartDataProvider.
     *
     * @param array $args The arguments to pass to the SocialMediaChartDataProvider constructor.
     * @return SocialMediaChartDataProvider
     */
    public static function socialMediaChart($args)
    {
        return new SocialMediaChartDataProvider($args);
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
     * Returns a new instance of UsersTrafficChartDataProvider.
     *
     * @param array $args The arguments to pass to the UsersTrafficChartDataProvider constructor.
     * @return UsersTrafficChartDataProvider
     */
    public static function usersTrafficChart($args)
    {
        return new UsersTrafficChartDataProvider($args);
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

    /**
     * Returns a new instance of MapChartDataProvider.
     *
     * @param array $args
     * @return ExclusionsChartDataProvider
     */
    public static function exclusionsChart($args)
    {
        return new ExclusionsChartDataProvider($args);
    }

    /**
     * Returns a new instance of SourceCategoryChartDataProvider.
     *
     * @param array $args
     * @return SourceCategoryChartDataProvider
     */
    public static function sourceCategoryChart($args)
    {
        return new SourceCategoryChartDataProvider($args);
    }
}
