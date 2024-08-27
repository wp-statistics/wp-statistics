<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\TimeZone;
use WP_STATISTICS\UserAgent;

class browsers extends MetaBoxAbstract
{
    /**
     * Get Browser ar Chart
     *
     * @param array $arg
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
        $args = apply_filters('wp_statistics_meta_box_browsers_args', $args);

        global $wpdb;


        // Set Default Params
        $defaults = array(
            'ago'     => 0,
            'from'    => '',
            'to'      => '',
            'number'  => 4
        );
        $args     = wp_parse_args($args, $defaults);

        // Filter By Date
        self::filterByDate($args);

        // Get List Of Days
        $days_time_list = array_keys(self::$daysList);

        // Set Default Value
        $total       = $count = 0;
        $lists_value = $lists_name = $lists_logo = array();
        
        $order_by = '';
        if (isset($args['order']) and in_array($args['order'], array('DESC', 'ASC', 'desc', 'asc'))) {
            $order_by = "ORDER BY `count` " . esc_sql($args['order']);
        }

        // Get List All Operating Systems
        $list = $wpdb->get_results(
            $wpdb->prepare("SELECT agent, COUNT(*) as count FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND %s GROUP BY agent {$order_by}", reset($days_time_list), end($days_time_list)),
            ARRAY_A
        );

        // Sort By Count
        Helper::SortByKeyValue($list, 'count');

        // Get Last 4 Version that Max number
        $agents = array_slice($list, 0, $args['number']);

        // Push to array
        foreach ($agents as $l) {
            if (empty(trim($l['agent']))) continue;

            // Sanitize Version name
            $lists_name[] = sanitize_text_field($l['agent']);

            $lists_logo[] = UserAgent::getBrowserLogo($l['agent']);

            // Get List Count
            $lists_value[] = (int)$l['count'];

            // Add to Total
            $total += $l['count'];
        }

        $others = array_slice($list, $args['number']);
        if (!empty($others)) {
            $lists_name[]   = __('Others', 'wp-statistics');
            $lists_value[]  = array_sum(array_column($others, 'count'));
            $total          += array_sum(array_column($others, 'count'));
        }
        

        // Prepare Response
        $response = array(
            'browsers_logos' => $lists_logo,
            'browsers_name'  => $lists_name,
            'browsers_value' => $lists_value,
            'info'           => array(
                'visitor_page' => Menus::admin_url('visitors'),
                'logo'         => $lists_logo
            ),
            'total'          => $total
        );

        // Check For No Data Meta Box
        if (count(array_filter($lists_value)) < 1 and !isset($args['no-data'])) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

}