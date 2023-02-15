<?php

namespace WP_STATISTICS;

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
     * @return array|bool|string
     */
    public static function getList()
    {
        global $WP_Statistics;

        # Load From global
        if (isset($WP_Statistics->country_codes)) {
            return $WP_Statistics->country_codes;
        }

        # Load From file
        include WP_STATISTICS_DIR . "includes/defines/country-codes.php";
        if (isset($ISOCountryCode)) {
            return $ISOCountryCode;
        }

        return array();
    }

    /**
     * Get Country flag
     *
     * @param $location
     * @return string
     */
    public static function flag($location)
    {
        $list_country = self::getList();
        if (!array_key_exists($location, $list_country)) {
            $location = self::$unknown_location;
        }
        return WP_STATISTICS_URL . 'assets/images/flags/' . strtolower($location) . '.svg';
    }

    /**
     * Get Country name by Code
     *
     * @param $code
     * @return mixed
     */
    public static function getName($code)
    {
        $list_country = self::getList();
        if (array_key_exists($code, $list_country)) {
            return $list_country[$code];
        }

        return $list_country[self::$unknown_location];
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
        $ISOCountryCode = Country::getList();

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
        $result     = $wpdb->get_results($sqlQuery . " " . $limitQuery);
        foreach ($result as $item) {
            $item->location = strtoupper($item->location);
            $list[]         = array(
                'location' => $item->location,
                'name'     => $ISOCountryCode[$item->location],
                'flag'     => self::flag($item->location),
                'link'     => Menus::admin_url('visitors', array('location' => $item->location)),
                'number'   => $item->count
            );
        }

        return $list;
    }

}