<?php

namespace WP_STATISTICS;

use DateTimeZone;
use WP_Statistics\Components\DateRange;
use WP_Statistics\Components\DateTime;

/**
 * Legacy TimeZone class for backward compatibility.
 *
 * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime instead.
 * @see \WP_Statistics\Components\DateTime
 *
 * This class is maintained for backward compatibility with add-ons.
 * New code should use the DateTime component from the v15 architecture.
 *
 * Migration guide:
 * - TimeZone::getCurrentDate()      -> DateTime::get()
 * - TimeZone::getCurrentTimestamp() -> DateTime::getCurrentTimestamp()
 * - TimeZone::getDateFilters()      -> DateRange::getPeriods()
 * - TimeZone::set_timezone()        -> DateTime::getUtcOffset()
 * - TimeZone::isValidDate()         -> DateTime::isValidDate()
 * - TimeZone::getTimeAgo()          -> DateTime::getTimeAgo()
 * - TimeZone::getNumberDayBetween() -> DateTime::getNumberDayBetween()
 * - TimeZone::getListDays()         -> DateTime::getListDays()
 * - TimeZone::getElapsedTime()      -> DateTime::getElapsedTime()
 * - TimeZone::getCountry()          -> DateTime::getCountryFromTimezone()
 */
class TimeZone
{
    /**
     * Get Current timeStamp
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getCurrentTimestamp() instead.
     * @return bool|string
     */
    public static function getCurrentTimestamp()
    {
        return DateTime::getCurrentTimestamp();
    }

    /**
     * Set WordPress TimeZone offset
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getUtcOffset() instead.
     * @return int
     */
    public static function set_timezone()
    {
        return DateTime::getUtcOffset();
    }

    /**
     * Adds the timezone offset to the given time string
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::get() instead.
     * @param string $timestring
     * @return int
     */
    public static function strtotimetz($timestring)
    {
        return strtotime($timestring) + DateTime::getUtcOffset();
    }

    /**
     * Adds current time to timezone offset
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getCurrentTimestamp() instead.
     * @return int
     */
    public static function timetz()
    {
        return time() + DateTime::getUtcOffset();
    }

    /**
     * Returns a date string in the desired format with a passed in timestamp.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::format() instead.
     * @param string $format
     * @param int $timestamp
     * @return bool|string
     */
    public static function getLocalDate($format, $timestamp)
    {
        return date($format, $timestamp + DateTime::getUtcOffset()); // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
    }

    /**
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::get() instead.
     * @param string $format
     * @param null $strtotime
     * @param null $relative
     * @return bool|string
     */
    public static function getCurrentDate($format = 'Y-m-d H:i:s', $strtotime = null, $relative = null)
    {
        $offset = DateTime::getUtcOffset();
        if ($strtotime) {
            if ($relative) {
                return date($format, strtotime("{$strtotime} day", $relative) + $offset);  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            } else {
                return date($format, strtotime("{$strtotime} day") + $offset);  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            }
        } else {
            return date($format, time() + $offset);  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
        }
    }

    /**
     * Returns a date string in the desired format (without timezone offset).
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::get() instead.
     * @param string $format
     * @param null $strtotime
     * @param null $relative
     * @return bool|string
     */
    public static function getRealCurrentDate($format = 'Y-m-d H:i:s', $strtotime = null, $relative = null)
    {
        if ($strtotime) {
            if ($relative) {
                return date($format, strtotime("{$strtotime} day", $relative));  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            } else {
                return date($format, strtotime("{$strtotime} day"));  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            }
        } else {
            return date($format, time());  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
        }
    }

    /**
     * Returns an internationalized date string in the desired format.
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::format() instead.
     * @param string $format
     * @param null $strtotime
     * @param string $day
     * @return string
     */
    public static function getCurrentDate_i18n($format = 'Y-m-d H:i:s', $strtotime = null, $day = ' day')
    {
        $offset = DateTime::getUtcOffset();
        if ($strtotime) {
            return date_i18n($format, strtotime("{$strtotime}{$day}") + $offset);
        } else {
            return date_i18n($format, time() + $offset);
        }
    }

    /**
     * Check is Valid date
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::isValidDate() instead.
     * @param string $date
     * @return bool
     */
    public static function isValidDate($date)
    {
        return DateTime::isValidDate($date);
    }

    /**
     * Get List Of days from ago Days
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getTimeAgo() instead.
     * @param int $ago_days
     * @param string $format
     * @return false|string
     */
    public static function getTimeAgo($ago_days = 1, $format = 'Y-m-d')
    {
        return DateTime::getTimeAgo($ago_days, $format);
    }

    /**
     * Get Number Days From Two Days
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getNumberDayBetween() instead.
     * @param string $from
     * @param bool $to
     * @return float|int
     * @example 2019-05-18, 2019-05-22 -> 5 days
     */
    public static function getNumberDayBetween($from, $to = false)
    {
        return DateTime::getNumberDayBetween($from, $to);
    }

    /**
     * Get List Of Two Days
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getListDays() instead.
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public static function getListDays($args = array())
    {
        return DateTime::getListDays($args);
    }

    /**
     * Returns an array of date filters.
     *
     * @deprecated 14.11 Use \WP_Statistics\Components\DateRange::getPeriods() instead.
     * @return array
     */
    public static function getDateFilters()
    {
        return [
            'today'       => [
                'from' => DateTime::getTimeAgo(0),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            'yesterday'   => [
                'from' => DateTime::getTimeAgo(1),
                'to'   => DateTime::getTimeAgo(1)
            ],
            'this_week'   => DateRange::get('this_week'),
            'last_week'   => DateRange::get('last_week'),
            'this_month'  => [
                'from' => date('Y-m-d', strtotime('first day of this month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of this month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            'last_month'  => [
                'from' => date('Y-m-d', strtotime('first day of previous month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of previous month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            '2months_ago' => [
                'from' => date('Y-m-d', strtotime('first day of -2 months')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of -2 months')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            '7days'       => [
                'from' => DateTime::getTimeAgo(6),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '14days'      => [
                'from' => DateTime::getTimeAgo(13),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '30days'      => [
                'from' => DateTime::getTimeAgo(29),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '60days'      => [
                'from' => DateTime::getTimeAgo(59),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '90days'      => [
                'from' => DateTime::getTimeAgo(89),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '120days'     => [
                'from' => DateTime::getTimeAgo(119),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            '6months'     => [
                'from' => date('Y-m-d', strtotime('-6 months')), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            'year'        => [
                'from' => date('Y-m-d', strtotime('-12 months')), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            'this_year'   => [
                'from' => DateTime::get('now', 'Y-01-01'),
                'to'   => DateTime::get('now', 'Y-m-d')
            ],
            'last_year'   => [
                'from' => DateTime::getTimeAgo(365, 'Y-01-01'),
                'to'   => DateTime::getTimeAgo(365, 'Y-12-30')
            ]
        ];
    }

    /**
     * Calculates the date filter by given date filter string.
     *
     * @deprecated 14.11 Use WP_Statistics/DateRange::get() instead.
     *
     * @param string $dateFilter Date filter string.
     *
     * @return array
     */
    public static function calculateDateFilter($dateFilter = false)
    {
        $dateFilters = self::getDateFilters();

        if (!empty($dateFilters[$dateFilter])) {
            return $dateFilters[$dateFilter];
        }

        return $dateFilters['30days'];
    }

    /**
     * Retrieve the country of a given timezone
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getCountryFromTimezone() instead.
     * @param string $timezone like: 'Europe/London'
     * @return string|false
     */
    public static function getCountry($timezone)
    {
        return DateTime::getCountryFromTimezone($timezone);
    }

    /**
     * Convert timestamp to "time ago" format
     *
     * @deprecated 15.0.0 Use \WP_Statistics\Components\DateTime::getElapsedTime() instead.
     * @param string|\DateTime $currentDate Current date and time
     * @param \DateTime        $visitDate Visit date and time
     * @param string           $originalDate Formatted original date to display if difference is more than 24 hours
     * @return string Formatted time difference
     */
    public static function getElapsedTime($currentDate, $visitDate, $originalDate)
    {
        return DateTime::getElapsedTime($currentDate, $visitDate, $originalDate);
    }
}
