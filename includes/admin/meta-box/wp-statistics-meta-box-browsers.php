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
        global $wpdb;

        // Set Default Params
        $defaults = array(
            'ago'     => 0,
            'from'    => '',
            'to'      => '',
            'browser' => 'all',
            'number'  => 10
        );
        $args     = wp_parse_args($args, $defaults);

        // Filter By Date
        self::filterByDate($args);

        // Get List Of Days
        $days_time_list = array_keys(self::$daysList);
        foreach (self::$daysList as $k => $v) {
            $date[]          = $v['format'];
            $total_daily[$k] = 0;
        }

        // Set Default Value
        $total         = $count = $top_ten = 0;
        $BrowserVisits = $lists_value = $lists_name = $lists_keys = $lists_logo = array();

        // Check Custom Browsers or ALL Browsers
        if ($args['browser'] == "all") {
            $Browsers = wp_statistics_ua_list();

            // Get List Of Browsers
            foreach ($Browsers as $Browser) {

                //Get List Of count Visitor By Agent
                if (empty($args['from']) and empty($args['to']) and $args['ago'] == "all") {

                    // IF All Time
                    $BrowserVisits[$Browser] = wp_statistics_useragent($Browser);
                } else {

                    // IF Custom Time
                    $BrowserVisits[$Browser] = wp_statistics_useragent($Browser, reset($days_time_list), end($days_time_list));
                }

                // Set All
                $total += $BrowserVisits[$Browser];
            }

            //Add Unknown Agent to total
            if (empty($args['from']) and empty($args['to']) and $args['ago'] == "all") {
                $total += $other_agent_count = $wpdb->get_var('SELECT COUNT(*) FROM `' . DB::table('visitor') . '` WHERE `agent` NOT IN (\'' . implode("','", $Browsers) . '\')');
            } else {
                $total += $other_agent_count = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `' . DB::table('visitor') . '` WHERE `agent` NOT IN (\'' . implode("','", $Browsers) . '\') AND `last_counter` BETWEEN %s AND %s', reset($days_time_list), end($days_time_list)));
            }

            //Sort Browser List By Visitor ASC
            arsort($BrowserVisits);

            // Get List Of Browser
            foreach ($BrowserVisits as $key => $value) {
                $top_ten += $value;
                $count++;
                if ($count > 9) { // Max 10 Browser
                    break;
                }

                //Get Browser name
                $browser_name  = UserAgent::BrowserList(strtolower($key));
                $lists_name[]  = $browser_name;
                $lists_value[] = (int)$value;
                $lists_keys[]  = strtolower($key);
                $lists_logo[]  = UserAgent::getBrowserLogo($key);
            }

            // Push Other Browser
            if ($lists_name and $lists_value and $other_agent_count > 0) {
                $lists_name[]  = __('Other', 'wp-statistics');
                $lists_value[] = (int)($total - $top_ten);
            }

        } else {

            // Set Browser info
            $lists_keys[] = strtolower($args['browser']);
            $lists_logo[] = UserAgent::getBrowserLogo($args['browser']);

            $sql = $wpdb->prepare("SELECT version, COUNT(*) as count FROM " . DB::table('visitor') . " WHERE agent = %s AND `last_counter` BETWEEN '" . reset($days_time_list) . "' AND '" . end($days_time_list) . "' GROUP BY version", $args['browser']);

            // Get List Of Version From Custom Browser
            $list = $wpdb->get_results($sql, ARRAY_A);

            // Sort By Count
            Helper::SortByKeyValue($list, 'count');

            // Get Last 20 Version that Max number
            $Browsers = array_slice($list, 0, $args['number']);

            // Push to array
            foreach ($Browsers as $l) {

                // Sanitize Version name
                $exp = explode(".", $l['version']);
                if (count($exp) > 2) {
                    $lists_name[] = $exp[0] . "." . $exp[1] . "." . substr($exp[2], 0, 3);
                } else {
                    $lists_name[] = $l['version'];
                }

                // Get List Count
                $lists_value[] = (int)$l['count'];

                // Add to Total
                $total += $l['count'];
            }
        }

        // Set Title
        $subtitle = ($args['browser'] == "all" ? __('Browser', 'wp-statistics') : UserAgent::BrowserList(strtolower($args['browser'])));
        if (end($days_time_list) == TimeZone::getCurrentDate("Y-m-d")) {
            $title = sprintf(__('%s Statistics in the last %s days', 'wp-statistics'), $subtitle, self::$countDays);
        } else {
            $title = sprintf(__('%s Statistics from %s to %s', 'wp-statistics'), $subtitle, $args['from'], $args['to']);
        }

        // Prepare Response
        $response = array(
            'title'          => $title,
            'browsers_name'  => $lists_name,
            'browsers_value' => $lists_value,
            'info'           => array(
                'visitor_page' => Menus::admin_url('visitors'),
                'agent'        => $lists_keys,
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