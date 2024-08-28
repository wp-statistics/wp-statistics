<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Option;
use WP_Statistics\Service\Admin\VisitorInsights\VisitorInsightsDataProvider;
use WP_STATISTICS\TimeZone;

class hits extends MetaBoxAbstract
{
    /**
     * Default Number day in Hits Chart
     *
     * @var int
     */
    public static $default_days_ago = 7;

    /**
     * Show Chart Hit
     *
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_hits_args', $args);

        // Check Number Days Or Between
        if (isset($args['from']) and isset($args['to'])) {
            $params = array('from' => $args['from'], 'to' => $args['to']);
        } else {
            $days   = (!empty($args['ago']) ? $args['ago'] : self::$default_days_ago);
            $params = array('ago' => $days);
        }

        // Prepare Response
        $response = self::HitsChart($params);

        // Check For No Data Meta Box
        if ((isset($response['visits']) and (!isset($args['no-data'])) and isset($response['visitors']) and count(array_filter($response['visits'])) < 0 and count(array_filter($response['visitors'])) < 0) || (isset($response['visits']) and !isset($response['visitors']) and count(array_filter($response['visits'])) < 0) || (!isset($response['visits']) and isset($response['visitors']) and count(array_filter($response['visitors'])) < 0)) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

    /**
     * Get Last Hits Chart
     *
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public static function HitsChart($args = array())
    {
        $args = wp_parse_args($args, [
            'ago'  => 0,
            'from' => '',
            'to'   => ''
        ]);
        self::filterByDate($args);
      
        $range = array_keys(self::$daysList);

        $visitorDataProvider = new VisitorInsightsDataProvider([
            'date' => [
                'from'  => reset($range),
                'to'    => end($range)
            ]
        ]);

        return $visitorDataProvider->getTrafficChartData();
    }
}
