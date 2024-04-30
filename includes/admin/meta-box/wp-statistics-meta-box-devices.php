<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\DB;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
use WP_STATISTICS\TimeZone;

class devices extends MetaBoxAbstract
{
    /**
     * Get Devices Chart
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
        $arg = apply_filters('wp_statistics_meta_box_devices_args', $arg);

        global $wpdb;

        // Set Default Params
        $defaults = array(
            'ago'    => 0,
            'from'   => '',
            'to'     => '',
            'order'  => '',
            'number' => 10 // Get Max number of platform
        );
        $args     = wp_parse_args($arg, $defaults);

        // Filter By Date
        self::filterByDate($args);

        // Get List Of Days
        $days_time_list = array_keys(self::$daysList);
        foreach (self::$daysList as $k => $v) {
            $date[]          = $v['format'];
            $total_daily[$k] = 0;
        }

        // Set Default Value
        $total       = $count = 0;
        $lists_value = $lists_name = array();

        $order_by = '';
        if ($args['order'] and in_array($args['order'], array('DESC', 'ASC', 'desc', 'asc'))) {
            $order_by = "ORDER BY `count` " . esc_sql($args['order']);
        }

        // Get List All Operating Systems
        $list = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT device, COUNT(*) as count FROM `" . DB::table('visitor') . "` WHERE device != %s AND `last_counter` BETWEEN %s AND %s GROUP BY device {$order_by}",
                _x('Unknown', 'Device', 'wp-statistics'),
                reset($days_time_list),
                end($days_time_list)),
            ARRAY_A);

        // Sort By Count
        Helper::SortByKeyValue($list, 'count');

        // Get Last 10 Version that Max number
        $devices = array_slice($list, 0, $args['number']);

        // Push to array
        foreach ($devices as $l) {

            if (trim($l['device']) != "") {

                // Sanitize Version name
                $lists_name[] = sanitize_text_field($l['device']);

                // Get List Count
                $lists_value[] = (int)$l['count'];

                // Add to Total
                $total += $l['count'];
            }
        }

        // Set Title
        if (end($days_time_list) == TimeZone::getCurrentDate("Y-m-d")) {
            $title = sprintf(__('Statistics for %1$s in the Past %2$s Days', 'wp-statistics'), __('Devices', 'wp-statistics'), self::$countDays);
        } else {
            $title = sprintf(__('Statistics for %1$s Between %2$s and %3$s', 'wp-statistics'), __('Devices', 'wp-statistics'), $args['from'], $args['to']);
        }

        // Prepare Response
        $response = array(
            'device_name'  => $lists_name,
            'device_value' => $lists_value,
            'info'         => array(
                'visitor_page' => Menus::admin_url('visitors')
            ),
            'total'        => $total
        );

        // Check For No Data Meta Box
        if (count(array_filter($lists_value)) < 1 and !isset($args['no-data'])) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

}