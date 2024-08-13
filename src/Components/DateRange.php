<?php

namespace WP_Statistics\Components;

use WP_STATISTICS\TimeZone;
use WP_STATISTICS\User;

class DateRange
{
    const USER_DATE_RANGE_META_KEY = 'wp_statistics_user_date_range';

    public static function getDefault()
    {
        $date = [
            'from'  => date('Y-m-d', strtotime('-29 days')),
            'to'    => date('Y-m-d')
        ];
        return apply_filters('wp_statistics_default_date_range', $date);
    }

    /**
     * Stores the given date range in the user's meta data.
     *
     * @param array $range An array containing 'from' and 'to' date strings.
     * @return void
     */
    public static function store($range)
    {
        $isFromValid = isset($range['from']) ? TimeZone::isValidDate($range['from']) : false;
        $isToValid   = isset($range['to']) ? TimeZone::isValidDate($range['to']) : false;

        if ($isFromValid && $isToValid) {
            User::saveMeta(self::USER_DATE_RANGE_META_KEY, $range);
        }
    }

    /**
     * Retrieves the date range stored in the user's meta data. Returns default date range if not set.
     *
     * @return array
     */
    public static function retrieve()
    {
        $storedRange = User::getMeta(self::USER_DATE_RANGE_META_KEY, true);

        return !empty($storedRange) ? $storedRange : self::getDefault();
    }

    /**
     * @todo Add complete list of needed string dates such as today, yesterday, month, last-month, etc 
     */
    public function get($item = false)
    {
        $items = [
            
        ];

        if (!isset($items[$item])) {
            throw new \ErrorException(esc_html__('Invalid date range.'));
        }

        return !empty($item) ? $items[$item] : $items;
    }
}