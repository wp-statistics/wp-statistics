<?php

namespace WP_Statistics\Service\Charts;

use WP_Statistics\Service\Charts\DataProvider\SearchEngineChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SocialMediaChartDataProvider;
use WP_Statistics\Service\Charts\DataProvider\SourceCategoryChartDataProvider;

class ChartDataProviderFactory
{
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
