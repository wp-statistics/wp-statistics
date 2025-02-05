<?php

namespace WP_STATISTICS;

use DateTimeZone;
use WP_Statistics\Components\DateRange;

class TimeZone
{
    /**
     * Get Current timeStamp
     *
     * @return bool|string
     */
    public static function getCurrentTimestamp()
    {
        return apply_filters('wp_statistics_current_timestamp', self::getCurrentDate('U'));
    }

    /**
     * Set WordPress TimeZone offset
     */
    public static function set_timezone()
    {
        if (get_option('timezone_string')) {
            return timezone_offset_get(timezone_open(get_option('timezone_string')), new \DateTime());
        } elseif (get_option('gmt_offset')) {
            return get_option('gmt_offset') * 60 * 60;
        }

        return 0;
    }

    /**
     * Adds the timezone offset to the given time string
     *
     * @param $timestring
     *
     * @return int
     */
    public static function strtotimetz($timestring)
    {
        return strtotime($timestring) + self::set_timezone();
    }

    /**
     * Adds current time to timezone offset
     *
     * @return int
     */
    public static function timetz()
    {
        return time() + self::set_timezone();
    }

    /**
     * Returns a date string in the desired format with a passed in timestamp.
     *
     * @param $format
     * @param $timestamp
     * @return bool|string
     */
    public static function getLocalDate($format, $timestamp)
    {
        return date($format, $timestamp + self::set_timezone()); // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
    }

    /**
     * @param string $format
     * @param null $strtotime
     * @param null $relative
     *
     * @return bool|string
     */
    public static function getCurrentDate($format = 'Y-m-d H:i:s', $strtotime = null, $relative = null)
    {
        if ($strtotime) {
            if ($relative) {
                return date($format, strtotime("{$strtotime} day", $relative) + self::set_timezone());  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            } else {
                return date($format, strtotime("{$strtotime} day") + self::set_timezone());  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
            }
        } else {
            return date($format, time() + self::set_timezone());  // phpcs:ignore WordPress.DateTime.RestrictedFuncitons.date_date
        }
    }

    /**
     * Returns a date string in the desired format.
     *
     * @param string $format
     * @param null $strtotime
     * @param null $relative
     *
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
     * @param string $format
     * @param null $strtotime
     * @param string $day
     *
     * @return string
     */
    public static function getCurrentDate_i18n($format = 'Y-m-d H:i:s', $strtotime = null, $day = ' day')
    {
        if ($strtotime) {
            return date_i18n($format, strtotime("{$strtotime}{$day}") + self::set_timezone());
        } else {
            return date_i18n($format, time() + self::set_timezone());
        }
    }

    /**
     * Check is Valid date
     *
     * @param $date
     * @return bool
     */
    public static function isValidDate($date)
    {
        if (empty($date)) {
            return false;
        }

        if (preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $date) && strtotime($date) !== false) {
            return true;
        }
        return false;
    }

    /**
     * Get List Of days from ago Days
     *
     * @param int $ago_days
     * @param string $format
     * @return false|string
     */
    public static function getTimeAgo($ago_days = 1, $format = 'Y-m-d')
    {
        return date($format, strtotime("- " . $ago_days . " day", self::getCurrentTimestamp()));  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
    }

    /**
     * Get Number Days From Two Days
     *
     * @param $from
     * @param bool $to
     * @return float
     * @example 2019-05-18, 2019-05-22 -> 5 days
     */
    public static function getNumberDayBetween($from, $to = false)
    {
        $to        = ($to === false ? self::getCurrentTimestamp() : strtotime($to));
        $from      = strtotime($from);
        $date_diff = $to - $from;

        return ceil($date_diff / (60 * 60 * 24));
    }

    /**
     * Get List Of Two Days
     *
     * @param array $args
     * @return array
     * @throws \Exception
     */
    public static function getListDays($args = array())
    {

        // Get Default
        $defaults = array(
            'from'   => '',
            'to'     => false,
            'format' => "j M"
        );
        $args     = wp_parse_args($args, $defaults);
        $list     = array();

        // Check Now Date
        $args['to'] = ($args['to'] === false ? self::getCurrentDate() : $args['to']);

        // Get List Of Day
        $period = new \DatePeriod(new \DateTime($args['from']), new \DateInterval('P1D'), new \DateTime(date('Y-m-d', strtotime("+1 day", strtotime($args['to']))))); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        foreach ($period as $key => $value) {
            $list[$value->format('Y-m-d')] = array(
                'timestamp' => $value->format('U'),
                'format'    => $value->format(apply_filters('wp_statistics_request_days_format', $args['format']))
            );
        }

        return $list;
    }

    /**
     * Returns an array of date filters.
     *
     * @deprecated 14.11 Use WP_Statistics/DateRange::getPeriods() instead.
     * @return array
     */
    public static function getDateFilters()
    {
        return [
            'today'      => [
                'from' => self::getTimeAgo(0),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            'yesterday'  => [
                'from' => self::getTimeAgo(1),
                'to'   => self::getTimeAgo(1)
            ],
            'this_week' => DateRange::get('this_week'),
            'last_week' => DateRange::get('last_week'),
            'this_month' => [
                'from' => date('Y-m-d', strtotime('first day of this month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of this month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            'last_month' => [
                'from' => date('Y-m-d', strtotime('first day of previous month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of previous month')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            '2months_ago' => [
                'from' => date('Y-m-d', strtotime('first day of -2 months')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => date('Y-m-d', strtotime('last day of -2 months')),  // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
            ],
            '7days'      => [
                'from' => self::getTimeAgo(6),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '14days'     => [
                'from' => self::getTimeAgo(13),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '30days'     => [
                'from' => self::getTimeAgo(29),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '60days'     => [
                'from' => self::getTimeAgo(59),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '90days'     => [
                'from' => self::getTimeAgo(89),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '120days'    => [
                'from' => self::getTimeAgo(119),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            '6months'    => [
                'from' => date('Y-m-d', strtotime('-6 months')), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            'year'       => [
                'from' => date('Y-m-d', strtotime('-12 months')), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            'this_year'       => [
                'from' => self::getCurrentDate("Y-01-01"),
                'to'   => self::getCurrentDate("Y-m-d")
            ],
            'last_year'       => [
                'from' => self::getTimeAgo(365, "Y-01-01"),
                'to'   => self::getTimeAgo(365, "Y-12-30")
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
     * @param $timezone like: 'Europe/London'
     * @return string
     */
    public static function getCountry($timezone)
    {
        $countryCode = false;
        $timezones   = timezone_identifiers_list();

        if (in_array($timezone, $timezones)) {
            $location    = timezone_location_get(new DateTimeZone($timezone));
            $countryCode = $location['country_code'];
        }

        return $countryCode;
    }

    /**
     * Convert timestamp to "time ago" format
     *
     * @param string   $currentDate Current date and time
     * @param DateTime $visitDate Visit date and time
     * @param string   $originalDate Formatted original date to display if difference is more than 24 hours
     * 
     * @return string Formatted time difference
     */
    public static function getElapsedTime($currentDate, $visitDate, $originalDate)
    {
        if (!($currentDate instanceof \DateTime)) {
            $currentDate = new \DateTime($currentDate);
        }

        $diffMinutes = round(($currentDate->getTimestamp() - $visitDate->getTimestamp()) / 60);

        if ($diffMinutes < 1) {
            return esc_html__('Now', 'wp-statistics');
        }

        if ($diffMinutes >= 1440) {
            return $originalDate;
        }

        if ($diffMinutes >= 60) {
            $hours = floor($diffMinutes / 60);
            $minutes = $diffMinutes % 60;
            if ($minutes > 0) {
                return sprintf(
                    esc_html(
                        /* translators: 1: number of hours, 2: number of minutes */
                        _n(
                            '%1$d hour %2$d minute ago',
                            '%1$d hours %2$d minutes ago',
                            absint($hours),
                            'wp-statistics'
                        )
                    ),
                    absint($hours),
                    absint($minutes)
                );
            }

            return sprintf(
                esc_html(
                    /* translators: %d: number of hours */
                    _n(
                        '%d hour ago',
                        '%d hours ago',
                        absint($hours),
                        'wp-statistics'
                    )
                ),
                absint($hours)
            );
        }
        return sprintf(
            esc_html(
                /* translators: %d: number of minutes */
                _n(
                    '%d minute ago',
                    '%d minutes ago',
                    absint($diffMinutes),
                    'wp-statistics'
                )
            ),
            absint($diffMinutes)
        );
    }
}
