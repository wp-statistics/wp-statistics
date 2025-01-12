<?php

namespace WP_Statistics\Components;

use ErrorException;

class DateTime
{
    public static $defaultDateFormat = 'Y-m-d';
    public static $defaultTimeFormat = 'g:i a';


    /**
     * Returns a formatted date string.
     *
     * @param string $date Human readable date string passed to strtotime() function. Defaults to 'now'
     * @param string $format The format string to use for the date. Default is 'Y-m-d'.
     *
     * @return string The formatted date string.
     */
    public static function get($date = 'now', $format = 'Y-m-d')
    {
        return DateTime::format($date, ['date_format' => $format]);
    }

    /**
     * Returns the name of the day of the week used as the start of the week on the calendar.
     *
     * @param string $return Whether to return the name of the day, the number of the day, or both.
     * @return mixed
     */
    public static function getStartOfWeek($return = 'name')
    {
        $dayNumber = intval(get_option('start_of_week', 0));
        $weekDays  = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $dayName = $weekDays[$dayNumber] ?? 'Monday';

        // Return the name of the day, the number of the day, or both.
        switch ($return) {
            case 'number':
                return $dayNumber;
            case 'name':
                return $dayName;
            default:
                return ['number' => $dayNumber, 'name' => $dayName];
        }
    }

    /**
     * Gets the date format string from WordPress settings.
     *
     * @return string
     */
    public static function getDateFormat()
    {
        return get_option('date_format', self::$defaultDateFormat);
    }

    /**
     * Gets the time format string from WordPress settings.
     *
     * @return string
     */
    public static function getTimeFormat()
    {
        return get_option('time_format', self::$defaultTimeFormat);
    }

    /**
     * Returns the timezone object based on the current WordPress setting.
     *
     * @return \DateTimeZone
     */
    public static function getTimezone()
    {
        return new \DateTimeZone(wp_timezone_string());
    }

    /**
     * Gets the date and time format string from WordPress settings.
     *
     * @param string $separator (optional) The separator to use between date and time.
     * @return string
     */
    public static function getDateTimeFormat($separator = ' ')
    {
        return self::getDateFormat() . $separator . self::getTimeFormat();
    }

    /**
     * Subtract a given number of days from a date string.
     *
     * @param string|int $date The date string to subtract from.
     * @param int $days The number of days to subtract.
     * @param string $format The format to use for the returned date string. Default 'Y-m-d'.
     * @return string The date string with the specified number of days subtracted.
     */
    public static function subtract($date, $days, $format = 'Y-m-d')
    {
        return date($format, strtotime("-$days day", strtotime($date)));
    }

    /**
     * Formats a given date string according to WordPress settings and provided arguments.
     *
     * @param string|int $date The date string to format. If numeric, it is treated as a Unix timestamp.
     * @param array $args {
     *     @type bool $include_time Whether to include the time in the formatted string. Default false.
     *     @type bool $exclude_year Whether to exclude the year from the formatted string. Default false.
     *     @type bool $short_month Whether to use a short month name (e.g. 'Jan' instead of 'January'). Default false.
     *     @type string $separator The separator to use between date and time. Default ' '.
     *     @type string $date_format The format string to use for the date. Default is the WordPress option 'date_format'.
     *     @type string $time_format The format string to use for the time. Default is the WordPress option 'time_format'.
     * }
     *
     * @return string The formatted datetime string.
     *
     * @throws ErrorException If the provided datetime string is invalid.
     */
    public static function format($date, $args = [])
    {
        $args = wp_parse_args($args, [
            'include_time'  => false,
            'exclude_year'  => false,
            'short_month'   => false,
            'separator'     => ' ',
            'date_format'   => self::getDateFormat(),
            'time_format'   => self::getTimeFormat()
        ]);

        // If the date is numeric, treat it as a Unix timestamp
        if (is_numeric($date)) {
            $date = "@$date";
        }

        $dateTime = new \DateTime($date, self::getTimezone());

        $format = $args['date_format'];
        if ($args['include_time'] === true) {
            $format = $args['date_format'] . $args['separator'] . $args['time_format'];
        }

        if ($args['exclude_year']) {
            $format = preg_replace('/(,\s?Y|Y\s?,|Y[, \/-]?|[, \/-]?Y)/i', '', $format);
        }

        if ($args['short_month']) {
            $format = str_replace('F', 'M', $format);
        }

        return $dateTime->format($format);
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
}