<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_STATISTICS\SearchEngine;
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
        $args     = wp_parse_args($arg, $defaults);

        // Set Default Params
        $date = $stats = $total_daily = $search_engine_list = array();

        // Filter By Date
        self::filterByDate($args);

        // Get List Of Days
        $days_time_list = array_keys(self::$daysList);
        foreach (self::$daysList as $k => $v) {
            $date[]          = $v['format'];
            $total_daily[$k] = 0;
        }

        // Set Title
        if (end($days_time_list) == TimeZone::getCurrentDate("Y-m-d")) {
            $title = sprintf(__('Referrals from Search Engines in the Past %s Days', 'wp-statistics'), self::$countDays);
        } else {
            $title = sprintf(__('Search Engine Referrals Between %1$s and %2$s', 'wp-statistics'), $args['from'], $args['to']);
        }

        //Check Chart total is activate
        $total_stats = Option::get('chart_totals');

        // Get List Of Search Engine
        $search_engines = SearchEngine::getList();

        // Push List to data
        foreach ($search_engines as $se) {

            // Get Search engine information
            $search_engine_list[] = $se;

            // Get Number Search every Days
            foreach ($days_time_list as $d) {
                $getStatic            = wp_statistics_searchengine($se['tag'], $d);
                $stats[$se['name']][] = $getStatic;
                $total_daily[$d]      = $total_daily[$d] + $getStatic;
            }
        }

        // Prepare Response
        $response = array(
            'title'         => $title,
            'date'          => $date,
            'stat'          => $stats,
            'search-engine' => $search_engine_list,
            'total'         => array(
                'active' => ($total_stats == 1 ? 1 : 0),
                'color'  => '180, 180, 180',
                'stat'   => array_values($total_daily)
            )
        );

        // Check For No Data Meta Box
        if (count(array_filter($total_daily)) < 1 and !isset($args['no-data'])) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }
}
