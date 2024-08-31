<?php

namespace WP_Statistics\Service\Admin\MiniChart;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\TimeZone;

class MiniChartHelper
{
    /**
     * An array of simple `Y-m-d` dates from `date_range` days ago to today.
     *
     * @var array
     */
    private static $chartDates = [];

    /**
     * Returns color of the chart.
     *
     * @return  string  Hex code.
     */
    public static function getChartColor()
    {
        return Option::getByAddon('chart_color', 'mini_chart', '#7362BF');
    }

    /**
     * Returns either 'Visitors' or 'Views' depending on the selected options.
     *
     * @return  string
     */
    public static function getTooltipLabel()
    {
        return Helper::checkMiniChartOption('metric', 'visitors', 'visitors') ? __('Visitors', 'wp-statistics') : __('Views', 'wp-statistics');
    }

    /**
     * Returns `chartDates` array (and creates the array if it's empty).
     *
     * @param   int     $forceDays  Ignore `date_range` option and send dates for the past x days.
     * @param   string  $minDate    Ignore all dates before this date (e.g. You can pass post publish date here). Format `Y-m-d`.
     *
     * @return  array               An array of simple `Y-m-d` dates from `date_range` days ago to today.
     */
    public static function getChartDates($forceDays = 0, $minDate = '')
    {
        // Return `$chartDates` if it already has data
        if (!empty(self::$chartDates) && !intval($forceDays) && empty($minDate)) {
            return self::$chartDates;
        }
        $chartDates = [];

        // Fill `$chartDates` in reveresed order (oldest date is at the beginning of the array)
        $daysAgoStartIndex = intval($forceDays) ? intval($forceDays) - 1 : intval(Option::getByAddon('date_range', 'mini_chart', '14')) - 1;
        for ($i = $daysAgoStartIndex; $i >= 0; $i--) {
            $date = TimeZone::getTimeAgo($i);
            // Add `$date` if `$minDate` is not passed or if `$minDate` is less than `$date`
            if (empty($minDate) || $minDate < $date) {
                $chartDates[] = $date;
            }
        }

        // Update class attribute only if the method was called without `$forceDays` or `$minDate`
        if (!intval($forceDays) && empty($minDate)) {
            self::$chartDates = $chartDates;
        }

        return $chartDates;
    }
}
