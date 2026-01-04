<?php

namespace WP_STATISTICS;

use WP_Statistics\Components\Country as V15Country;

/**
 * Legacy Country class for backward compatibility.
 *
 * @deprecated 15.0.0 Use \WP_Statistics\Components\Country instead.
 * @see \WP_Statistics\Components\Country
 *
 * This class is maintained for backward compatibility with add-ons.
 * New code should use the v15 Country component.
 *
 * Migration guide:
 * - Country::getList()    -> V15Country::getAll()
 * - Country::getName()    -> V15Country::getName()
 * - Country::flag()       -> V15Country::getFlag()
 * - Country::isValid()    -> V15Country::isValid()
 */
class Country
{
    /**
     * Default Unknown flag
     *
     * @var string
     */
    public static $unknown_location = '000';

    /**
     * Get country codes
     *
     * @return array
     */
    public static function getList()
    {
        return V15Country::getAll();
    }

    /**
     * Get Country flag
     *
     * @param string $location
     * @return string
     */
    public static function flag($location)
    {
        return V15Country::getFlag($location);
    }

    /**
     * Get Country name by Code
     *
     * @param string $code
     * @return string
     */
    public static function getName($code)
    {
        return V15Country::getName($code);
    }

    /**
     * Check if a country code is valid.
     *
     * @param string $code
     * @return bool
     */
    public static function isValid($code)
    {
        return V15Country::isValid($code);
    }

    /**
     * get Top Country List
     *
     * @param array $args
     * @return array
     */
    public static function getTop($args = array())
    {
        global $wpdb;

        // Load List Country Code
        $ISOCountryCode = V15Country::getAll();

        // Get List From DB
        $list = array();

        // Check Default
        if (empty($args['from']) and empty($args['to'])) {
            if (array_key_exists($args['ago'], TimeZone::getDateFilters())) {
                $dateFilter   = TimeZone::calculateDateFilter($args['ago']);
                $args['from'] = $dateFilter['from'];
                $args['to']   = $dateFilter['to'];
            }
        }

        // Check Custom Date
        if (!empty($args['from']) and !empty($args['to'])) {
            $count_day = TimeZone::getNumberDayBetween($args['from'], $args['to']);
        } else {
            if (is_numeric($args['ago']) and $args['ago'] > 0) {
                $count_day = $args['ago'];
            } else {
                $first_day = Helper::get_date_install_plugin();
                $count_day = TimeZone::getNumberDayBetween($first_day);
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

        // Get Result
        $limitQuery = (isset($args['limit']) and $args['limit'] > 0) ? $wpdb->prepare("LIMIT %d", $args['limit']) : '';
        $sqlQuery   = $wpdb->prepare("SELECT `location`, COUNT(`location`) AS `count` FROM `" . DB::table('visitor') . "` WHERE `last_counter` BETWEEN %s AND %s GROUP BY location ORDER BY `count` DESC", reset($days_time_list), end($days_time_list));
        $result     = $wpdb->get_results($sqlQuery . " " . $limitQuery); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        foreach ($result as $item) {
            $item->location = strtoupper($item->location);
            $list[]         = array(
                'location' => $item->location,
                'name'     => $ISOCountryCode[$item->location] ?? V15Country::getName($item->location),
                'flag'     => self::flag($item->location),
                'link'     => Menus::admin_url('visitors', array('location' => $item->location)),
                'number'   => $item->count
            );
        }

        return $list;
    }

}
