<?php

namespace WP_Statistics\Components;

use ErrorException;

class DateTime
{
    public static $defaultDateFormat = 'Y-m-d';
    public static $defaultTimeFormat = 'g:i a';

    /**
     * Gets the start of week string.
     *
     * This function returns the string value of the start of week day.
     *
     * @return string The start of week string (e.g. 'monday', 'tuesday', etc.)
     */
    public static function getStartOfWeek()
    {
        $startDay = intval(get_option('start_of_week', 0));

        switch ($startDay) {
            case 0:
                return 'sunday';
            case 1:
                return 'monday';
            case 2:
                return 'tuesday';
            case 3:
                return 'wednesday';
            case 4:
                return 'thursday';
            case 5:
                return 'friday';
            case 6:
                return 'saturday';
            default:
                return 'monday';
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
     * Formats a given datetime string according to WordPress settings and provided arguments.
     *
     * @param string|int $datetime The datetime string to format. If numeric, it is treated as a Unix timestamp.
     * @param array $args {
     *     Arguments to customize the formatting.
     *
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
    public static function format($datetime, $args = [])
    {
        $args = wp_parse_args($args, [
            'include_time'  => false,
            'exclude_year'  => false,
            'short_month'   => false,
            'separator'     => ' ',
            'date_format'   => self::getDateFormat(),
            'time_format'   => self::getTimeFormat()
        ]);

        $timestamp  = is_numeric($datetime) ? $datetime : strtotime($datetime);

        if ($timestamp === false) {
            throw new ErrorException(esc_html__('Invalid date passed as argument.', 'wp-statistics'));
        }

        if ($args['include_time'] === true) {
            $format = $args['date_format'] . $args['separator'] . $args['time_format'];
        } else {
            $format = $args['date_format'];
        }

        if ($args['exclude_year']) {
            $format = preg_replace('/(,\s?Y|Y\s?,|Y[, \/-]?|[, \/-]?Y)/i', '', $format);
        }

        if ($args['short_month']) {
            $format = str_replace('F', 'M', $format);
        }

        return date($format, $timestamp);
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