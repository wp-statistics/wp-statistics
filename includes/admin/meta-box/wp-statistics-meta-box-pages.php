<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Admin_Template;
use WP_STATISTICS\DB;
use WP_STATISTICS\Menus;
use WP_STATISTICS\TimeZone;

class pages extends MetaBoxAbstract
{
    /**
     * Get MetaBox Rest API Data
     *
     * @param array $args
     * @return array
     */
    public static function get($args = array())
    {
        global $wpdb;

        // Define the array of defaults
        $defaults = array(
            'per_page' => 10,
            'paged'    => 1,
            'from'     => '',
            'to'       => '',
            'ago'      => '',
        );

        $args = wp_parse_args($args, $defaults);

        // Filter By Date
        self::filterByDate($args);

        // Get List Of Days
        $days_time_list = array_keys(self::$daysList);

        $response['pages'] = \WP_STATISTICS\Pages::getTop([
            'per_page' => $args['per_page'],
            'paged'    => $args['paged'],
            'from'     => reset($days_time_list),
            'to'       => end($days_time_list),
        ]);

//        // Date Time SQL
//        $DateTimeSql = "WHERE (`pages`.`date` BETWEEN '" . reset($days_time_list) . "' AND '" . end($days_time_list) . "')";
//
//        // Generate SQL
//        $sql = "SELECT `pages`.`date`,`pages`.`uri`,`pages`.`id`,`pages`.`type`, SUM(`pages`.`count`) + IFNULL(`historical`.`value`, 0) AS `count_sum` FROM `" . DB::table('pages') . "` `pages` LEFT JOIN `" . DB::table('historical') . "` `historical` ON `pages`.`uri`=`historical`.`uri` AND `historical`.`category`='uri' {$DateTimeSql} GROUP BY `uri` ORDER BY `count_sum` DESC";
//
//        // Get List Of Pages
//        $response          = array();
//        $response['pages'] = array();
//
//        $result = $wpdb->get_results($sql . $wpdb->prepare(" LIMIT %d, %d", ($args['paged'] - 1) * $args['per_page'], $args['per_page']));
//        foreach ($result as $item) {
//
//            // Lookup the post title.
//            $page_info = \WP_STATISTICS\Pages::get_page_info($item->id, $item->type);
//
//            // Push to list
//            $response['pages'][] = array(
//                'title'     => $page_info['title'],
//                'link'      => $page_info['link'],
//                'str_url'   => urldecode($item->uri),
//                'hits_page' => Menus::admin_url('pages', array('ID' => $item->id, 'type' => $item->type)),
//                'number'    => number_format_i18n($item->count_sum)
//            );
//        }

        // Check For No Data Meta Box
        if (count($response) < 1) {
            $response['no_data'] = 1;
        }

        // Response
        return self::response($response);
    }

}