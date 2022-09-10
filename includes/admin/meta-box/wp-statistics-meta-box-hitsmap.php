<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Country;
use WP_STATISTICS\DB;
use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_STATISTICS\Timezone;
use WP_STATISTICS\UserAgent;

class hitsmap
{

    public static function get($args = array())
    {
        global $wpdb;

        // Set Default Unknown Country
        $final_result[GeoIP::$private_country] = array();

        // Get List Country Code
        $CountryCode = Country::getList();

        if (empty($args['from']) and empty($args['to'])) {
            if (array_key_exists($args['ago'], TimeZone::getDateFilters())) {
                $dateFilter   = TimeZone::calculateDateFilter($args['ago']);
                $args['from'] = $dateFilter['from'];
                $args['to']   = $dateFilter['to'];
            }
        }

        // Prepare Count Day
        if (!empty($args['from']) and !empty($args['to'])) {
            $count_day = TimeZone::getNumberDayBetween($args['from'], $args['to']);
        } else {
            if (is_numeric($args['ago']) and $args['ago'] > 0) {
                $count_day = $args['ago'];
            } else {
                $count_day = 1;
            }
        }

        // Get time ago Days Or Between Two Days
        if (!empty($args['from']) and !empty($args['to'])) {
            $days_list = TimeZone::getListDays(array('from' => $args['from'], 'to' => $args['to']));
        } else {
            if (is_numeric($args['ago']) and $args['ago'] > 0) {
                $days_list = TimeZone::getListDays(array('from' => TimeZone::getTimeAgo($args['ago'])));
            } else {
                $days_list = TimeZone::getListDays(array('from' => TimeZone::getTimeAgo($count_day)));
            }
        }

        $days_time_list = array_keys($days_list);

        // Get List Country Of Visitors
        $result = $wpdb->get_results("SELECT * FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN '" . reset($days_time_list) . "' AND '" . end($days_time_list) . "'");
        if ($result) {
            foreach ($result as $new_country) {
                $final_result[strtolower($new_country->location)][] = $new_country;
            }
        }
        $final_total = count($result) - count($final_result[GeoIP::$private_country]);
        unset($final_result[GeoIP::$private_country]);

        // Default Color for Country Map
        $startColor = array(200, 238, 255);
        $endColor   = array(0, 100, 145);

        // Get Every Country
        foreach ($final_result as $items) {

            // Get Visitors Row
            foreach ($items as $markets) {

                // Check User is Unknown IP
                if ($markets->location == GeoIP::$private_country) {
                    continue;
                }

                // Push Browser
                $visitor['browser'] = array(
                    'name' => $markets->agent,
                    'logo' => UserAgent::getBrowserLogo($markets->agent)
                );

                // Push IP
                if (IP::IsHashIP($markets->ip)) {
                    $visitor['ip'] = IP::$hash_ip_prefix;
                } else {
                    $visitor['ip'] = $markets->ip;
                }

                // Push City
                if (GeoIP::active('city')) {
                    try {
                        $visitor['city'] = GeoIP::getCity($markets->ip);
                    } catch (\Exception $e) {
                        $visitor['city'] = '';
                    }
                }

                $get_ipp[$markets->location][] = $visitor;
            }

            // Check Exist Visitor in Same Country
            if (isset($get_ipp) and isset($markets) and array_key_exists($markets->location, $get_ipp)) {

                // Show Only Last Five User
                $market_total = count($get_ipp[$markets->location]);

                // Set Country information
                $response['country'][strtolower($markets->location)] = array('location' => $markets->location, 'name' => $CountryCode[$markets->location], 'flag' => Country::flag($markets->location));

                // Set Visitor List
                $response['visitor'][strtolower($markets->location)] = array_slice($get_ipp[$markets->location], 0, 6); # We only Six number User from every Country

                // Set Color For Country
                $response['color'][strtolower($markets->location)] = sprintf("#%02X%02X%02X", round($startColor[0] + ($endColor[0] - $startColor[0]) * $market_total / $final_total), round($startColor[1] + ($endColor[1] - $startColor[1]) * $market_total / $final_total), round($startColor[2] + ($endColor[2] - $startColor[2]) * $market_total / $final_total));

                // Set total Every Country
                $response['total_country'][strtolower($markets->location)] = $market_total;
            }
        }

        // Set Total
        $response['total'] = $final_total;

        return $response;
    }

}