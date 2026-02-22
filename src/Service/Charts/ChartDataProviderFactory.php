<?php

namespace WP_Statistics\Service\Charts;

/**
 * @deprecated 15.0.0 Chart data providers have been removed. Factory methods return empty data.
 *             Addons should migrate to the AnalyticsQuery API.
 */
class ChartDataProviderFactory
{
    /**
     * @deprecated 15.0.0
     */
    public static function performanceChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function browserChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function platformCharts($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function eventActivityChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function topSourceCategories($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function searchEngineChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function socialMediaChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function sourceCategoryChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function trafficChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function deviceChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function osChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function modelChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function countryChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function continentChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function mapChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function exclusionsChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function usersTrafficChart($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function publishOverview($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function authorsPostViews($args = [])
    {
        return new NullChartDataProvider($args);
    }

    /**
     * @deprecated 15.0.0
     */
    public static function loggedInUsers($args = [])
    {
        return new NullChartDataProvider($args);
    }
}
