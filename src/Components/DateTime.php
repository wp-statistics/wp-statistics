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
        if (is_numeric($date)) {
            $date = "@$date";
        }

        $dateTime = new \DateTime($date, self::getTimezone());
        return $dateTime->format($format);
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
     * @type bool $include_time Whether to include the time in the formatted string. Default false.
     * @type bool $exclude_year Whether to exclude the year from the formatted string. Default false.
     * @type bool $short_month Whether to use a short month name (e.g. 'Jan' instead of 'January'). Default false.
     * @type string $separator The separator to use between date and time. Default ' '.
     * @type string $date_format The format string to use for the date. Default is the WordPress option 'date_format'.
     * @type string $time_format The format string to use for the time. Default is the WordPress option 'time_format'.
     * }
     *
     * @return string The formatted datetime string.
     *
     * @throws ErrorException If the provided datetime string is invalid.
     */
    public static function format($date, $args = [])
    {
        $args = wp_parse_args($args, [
            'include_time' => false,
            'exclude_year' => false,
            'short_month'  => false,
            'separator'    => ' ',
            'date_format'  => self::getDateFormat(),
            'time_format'  => self::getTimeFormat()
        ]);

        // If the date is numeric, treat it as a Unix timestamp
        if (is_numeric($date)) {
            $dateTime = new \DateTime('@' . $date, new \DateTimeZone('UTC'));
            $dateTime->setTimezone(self::getTimezone());
        } else {
            $dateTime = new \DateTime($date, self::getTimezone());
        }

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

    /**
     * Checks if the given date is today or in the future.
     *
     * @param string $date
     *
     * @return bool
     */
    public static function isTodayOrFutureDate($date)
    {
        $today = date('Y-m-d');

        if (!$date || strtotime($date) === false) {
            return false;
        }

        $inputDate = date('Y-m-d', strtotime($date));

        return ($inputDate >= $today);
    }

    /**
     * Build a default date‑format pattern string based on WordPress settings.
     *
     * @param bool $withTime Include the time portion.
     * @param bool $excludeYear Omit the year from the pattern.
     * @param bool $shortMonth Use a short month name (e.g. 'Jan').
     * @param string $separator Separator between date and time parts.
     *
     * @return string Date‑format pattern.
     */
    public static function getDefaultDateFormat($withTime = false, $excludeYear = false, $shortMonth = false, $separator = ' ')
    {
        $dateFormat = self::getDateFormat();
        $timeFormat = self::getTimeFormat();

        if ($withTime) {
            $dateFormat = trim($dateFormat . $separator . $timeFormat);
        }

        if ($excludeYear) {
            $dateFormat = preg_replace('/(,\s?Y|Y\s?,|Y[, \\/-]?|[, \\/-]?Y)/i', '', $dateFormat);
        }

        if ($shortMonth) {
            $dateFormat = str_replace('F', 'M', $dateFormat);
        }

        return $dateFormat;
    }

    /**
     * Calculate the difference between two dates.
     *
     * @param string|int $startDate A date string or Unix timestamp.
     * @param string|int $endDate A date string or Unix timestamp. Defaults to 'now'.
     * @param string $unit Unit to return: 'days', 'hours', or 'minutes'.
     *
     * @return int Difference expressed in the requested unit; zero on failure.
     */
    public static function calculateDateDifference($fromDate, $toDate = 'now')
    {
        $fromDateTime = new \DateTime($fromDate);
        $toDateTime   = new \DateTime($toDate);

        $interval = $fromDateTime->diff($toDateTime);

        if ($interval->y > 0) {
            return _n('a year', sprintf('%d years', $interval->y), $interval->y, 'wp-statistics');
        }

        if ($interval->m > 0) {
            return _n('a month', sprintf('%d months', $interval->m), $interval->m, 'wp-statistics');
        }

        if ($interval->d >= 7) {
            $weekCount = (int)floor($interval->d / 7);
            return _n('a week', sprintf('%d weeks', $weekCount), $weekCount, 'wp-statistics');
        }

        return _n('a day', sprintf('%d days', $interval->d), $interval->d, 'wp-statistics');
    }

    /**
     * Get the UTC offset in seconds.
     *
     * @return int
     */
    public static function getUtcOffset()
    {
        $timezone  = get_option('timezone_string');
        $gmtOffset = get_option('gmt_offset');

        if ($timezone) {
            return timezone_offset_get(timezone_open($timezone), new \DateTime());
        }

        if ($gmtOffset) {
            return $gmtOffset * 60 * 60;
        }

        return 0;
    }

    /**
     * Get current timestamp with timezone offset applied
     *
     * This method returns a timestamp that includes the site's timezone offset,
     * maintaining compatibility with the original TimeZone class implementation.
     *
     * @return int|string Current timestamp with timezone offset
     */
    public static function getCurrentTimestamp()
    {
        $dateTime  = new \DateTime('now', self::getTimezone());
        $timestamp = $dateTime->format('U');

        return apply_filters('wp_statistics_current_timestamp', $timestamp);
    }

    /**
     * Convert UTC datetime to site's timezone.
     *
     * @param string $utcDateTime UTC datetime string
     * @param string|bool $format Optional. Format string or boolean flag (default: true)
     *                           If true: uses WordPress default format with i18n
     *                           If false: returns Y-m-d H:i:s without i18n
     *                           If string: uses that as date format with i18n
     * @return string|null Formatted datetime in site's timezone
     * @since 15.0.0
     */
    public static function convertUtc($utcDateTime, $format = true)
    {
        if (empty($utcDateTime)) {
            return null;
        }

        // Convert UTC timestamp to site's timezone
        $datetime = new \DateTime($utcDateTime, new \DateTimeZone('UTC'));
        $datetime->setTimezone(self::getTimezone());

        // Handle format based on input type
        if (is_string($format)) {
            // Use custom format with i18n
            return date_i18n($format, strtotime($datetime->format('Y-m-d H:i:s')));
        }

        if ($format === true) {
            // Use WordPress default format with i18n
            return date_i18n(
                self::getDefaultDateFormat(true, true),
                strtotime($datetime->format('Y-m-d H:i:s'))
            );
        }

        // Return standard format without i18n
        return $datetime->format('Y-m-d H:i:s');
    }

    /**
     * Get current date in UTC timezone.
     *
     * @param string $format The format string to use for the date. Default is 'Y-m-d H:i:s'.
     * @param string|null $strtotime Optional. A string parsable by strtotime (e.g. '+1 day', '-2 weeks')
     * @return string Formatted date string in UTC
     * @since 15.0.0
     */
    public static function getUtc($format = 'Y-m-d H:i:s', $strtotime = null)
    {
        $datetime = new \DateTime('now', new \DateTimeZone('UTC'));

        if ($strtotime) {
            $datetime->modify($strtotime);
        }

        return $datetime->format($format);
    }

    public static function isValid($date)
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
     * Build a site's‑local calendar "day" window and return the UTC boundaries (DST‑safe).
     *
     * @param string|int|array|\DateTimeInterface $date  Human string like 'yesterday'/'2025-09-30',
     *                                                   a unix timestamp, a DateTimeInterface, or
     *                                                   an array with key 'from'.
     * @param string $format  UTC datetime format to return. Default 'Y-m-d H:i:s'.
     *
     * @return array{startUtc:string,endUtc:string,labelDate:string}
     * @since 15.0.0
     */
    public static function getUtcRangeForLocalDate($date = 'yesterday', $format = 'Y-m-d H:i:s')
    {
        // Resolve the site's timezone (same as wp_timezone()).
        $timeZone = self::getTimezone();

        // Normalize input into a DateTimeImmutable in the site's timezone.
        if ($date instanceof \DateTimeInterface) {
            $localDay = (new \DateTimeImmutable('@' . $date->getTimestamp()))->setTimezone($timeZone);
        } elseif (is_array($date)) {
            $seed = $date['from'] ?? 'yesterday';
            $localDay = new \DateTimeImmutable(is_numeric($seed) ? ('@' . $seed) : $seed, $timeZone);
        } else {
            $localDay = new \DateTimeImmutable(is_numeric($date) ? ('@' . $date) : $date, $timeZone);
        }

        // Local midnight boundaries for the target day (DST-safe).
        $localStart = $localDay->setTime(0, 0);
        $localEnd   = $localStart->modify('+1 day');

        $startUtc  = $localStart->setTimezone(new \DateTimeZone('UTC'))->format($format);
        $endUtc    = $localEnd  ->setTimezone(new \DateTimeZone('UTC'))->format($format);
        $labelDate = $localStart->format('Y-m-d');

        return [
            'startUtc'  => $startUtc,
            'endUtc'    => $endUtc,
            'labelDate' => $labelDate,
        ];
    }
}