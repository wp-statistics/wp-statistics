<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Country;
use WP_STATISTICS\DB;
use WP_STATISTICS\Menus;

class countries extends MetaBoxAbstract
{

    public static function get($args = array())
    {
        global $wpdb;

        // Set Default Params
        $defaults = array(
            'ago'   => 0,
            'from'  => '',
            'to'    => '',
            'limit' => 10
        );
        $args     = wp_parse_args($args, $defaults);

        // Load List Country Code
        $ISOCountryCode = Country::getList();

        // Filter By Date
        self::filterByDate($args);

        $days_time_list = array_keys(self::$daysList);

        // Get List From DB
        $list = array();

        // Get Result
        $limitQuery = (isset($args['limit']) and $args['limit'] > 0) ? $wpdb->prepare("LIMIT %d", $args['limit']) : '';
        $sqlQuery   = $wpdb->prepare("SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND %s GROUP BY `location` ORDER BY `count` DESC", reset($days_time_list), end($days_time_list));
        $result     = $wpdb->get_results($sqlQuery . " " . $limitQuery);
        foreach ($result as $item) {
            $item->location = strtoupper($item->location);
            $list[]         = array(
                'location' => $item->location,
                'name'     => $ISOCountryCode[$item->location],
                'flag'     => Country::flag($item->location),
                'link'     => Menus::admin_url('visitors', array('location' => $item->location)),
                'number'   => $item->count
            );
        }

        $response = array('countries' => $list);

        // Check For No Data Meta Box
        if (count($response) < 1) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

}