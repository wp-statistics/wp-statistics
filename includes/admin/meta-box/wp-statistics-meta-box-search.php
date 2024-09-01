<?php

namespace WP_STATISTICS\MetaBox;

use WP_Statistics\Models\VisitorsModel;
use WP_STATISTICS\Option;
use WP_STATISTICS\SearchEngine;
use WP_Statistics\Service\Admin\Charts\ChartDataProviderFactory;
use WP_STATISTICS\TimeZone;

class search extends MetaBoxAbstract
{

    /**
     * Get Search Engine Chart
     *
     * @param array $arg
     * @return array
     * @throws \Exception
     */
    public static function get($arg = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $arg = apply_filters('wp_statistics_meta_box_search_args', $arg);

        // Set Default Params
        $defaults = array(
            'ago'  => 0,
            'from' => '',
            'to'   => ''
        );
        $args = wp_parse_args($arg, $defaults);

        // Filter By Date
        self::filterByDate($args);

        $range  = array_keys(self::$daysList);
        $from   = reset($range);
        $to     = end($range);

        $searchEngineChartData = ChartDataProviderFactory::searchEngineChart(['data' => ['from' => $from, 'to' => $to]])->getData();

        return self::response($searchEngineChartData);
    }
}
