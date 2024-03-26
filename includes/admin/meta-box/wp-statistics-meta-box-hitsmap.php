<?php

namespace WP_STATISTICS\MetaBox;

use WP_STATISTICS\Country;
use WP_STATISTICS\DB;
use WP_STATISTICS\GeoIP;
use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_STATISTICS\UserAgent;

class hitsmap extends MetaBoxAbstract
{

    private static $response = array(
        "country"           =>  array(),
        "total_country"     => array(),
        "visitor"          => array(),
        "color"             =>  array()
    );

    public static function get($args = array())
    {
        /**
         * Filters the args used from metabox for query stats
         *
         * @param array $args The args passed to query stats
         * @since 14.2.1
         *
         */
        $args = apply_filters('wp_statistics_meta_box_hitsmap_args', $args);

        global $wpdb;



        // Get List Country Code
        $CountryCode = Country::getList();

        // Filter By Date
        self::filterByDate($args);

        $days_time_list = array_keys(self::$daysList);

        $sql    = $wpdb->prepare(
            "SELECT location, COUNT(`location`) as count FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN '%s' AND %s GROUP BY `location`",
            reset($days_time_list),
            end($days_time_list),
        );
        $locationCount =  $wpdb->get_results($sql, OBJECT_K);

        $chunk = 1000;
        $total = 0;
        $offset = 0;
        $result = self::getData($days_time_list, $chunk, $offset);

        while ($result) {
            foreach (Helper::yieldARow($result) as $country) {
                // Check User is Unknown IP
                if ($country->location == GeoIP::$private_country) {
                    continue;
                }

                $locationLower = strtolower($country->location);

                // $final_result[strtolower($new_country->location)][] = $new_country;
                if (!array_key_exists($locationLower, self::$response['total_country'])) {
                    if (array_key_exists($country->location, $locationCount)) {
                        self::$response['total_country'][$locationLower] = $locationCount[$country->location]->count;
                    }
                }


                if (array_key_exists($locationLower, self::$response['visitor'])) {
                    if (count(self::$response['visitor'][$locationLower]) <= 6) {
                        self::$response['visitor'][$locationLower][] = static::getVisitor($country);
                    }
                } else {
                    self::$response['visitor'][$locationLower][] = static::getVisitor($country);
                }

                // Set Country information
                if (!array_key_exists($locationLower, self::$response['country'])) {
                    self::$response['country'][$locationLower] = array(
                        'location' => $country->location,
                        'name' => $CountryCode[$country->location],
                        'flag' => Country::flag($country->location)
                    );
                }

                $total++;
            }

            $offset += $chunk;
            $result = self::getData($days_time_list, $chunk, $offset);
        }

        // Default Color for Country Map
        $startColor = array(200, 238, 255);
        $endColor   = array(0, 100, 145);

        reset(self::$response['country']);
        while ($country = current(self::$response['country'])) {

            $locationLower = strtolower($country['location']);
            // Set Color For Country
            if (!array_key_exists($locationLower, self::$response['color'])) {
                $devided = self::$response['total_country'][$locationLower] / $total;
                self::$response['color'][$locationLower] = sprintf(
                    "#%02X%02X%02X",
                    round($startColor[0] + ($endColor[0] - $startColor[0]) * $devided),
                    round($startColor[1] + ($endColor[1] - $startColor[1]) * $devided),
                    round($startColor[2] + ($endColor[2] - $startColor[2]) * $devided)
                );
            }

            next(self::$response["country"]);
        }

        return self::response(self::$response);
    }


    private static function getData($days, $limit, $offset)
    {
        global $wpdb;
        // Get List Country Of Visitors
        $sql    = $wpdb->prepare(
            "SELECT location, hits, agent, ip FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN '%s' AND %s LIMIT %d OFFSET %d",
            reset($days),
            end($days),
            $limit,
            $offset
        );
        return $wpdb->get_results($sql, OBJECT);
    }


    private static function getVisitor($country)
    {
        // Push Browser
        $visitor['browser'] = array(
            'name' => $country->agent,
            'logo' => UserAgent::getBrowserLogo($country->agent)
        );

        // Push IP
        if (IP::IsHashIP($country->ip)) {
            $visitor['ip'] = IP::$hash_ip_prefix;
        } else {
            $visitor['ip'] = $country->ip;
        }

        // Push City
        if (GeoIP::active('city')) {
            try {
                $visitor['city'] = GeoIP::getCity($country->ip);
            } catch (\Exception $e) {
                $visitor['city'] = '';
            }
        }

        return $visitor;
    }
}
