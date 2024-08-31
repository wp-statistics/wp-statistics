<?php

namespace WP_Statistics\Service\Admin\MiniChart;

use WP_STATISTICS\Helper;
use WP_STATISTICS\Option;
use WP_STATISTICS\TimeZone;

class MiniChartHelper
{
    /**
     * Is Mini-chart add-on active?
     *
     * @var bool
     */
    private $isMiniChartActive = false;

    /**
     * An array of simple `Y-m-d` dates from `date_range` days ago to today.
     *
     * @var array
     */
    private $chartDates = [];

    public function __construct()
    {
        $this->isMiniChartActive = Helper::isAddOnActive('mini-chart');
    }

    /**
     * Returns Mini-chart add-on's active status.
     *
     * @return bool
     */
    public function isMiniChartActive()
    {
        return $this->isMiniChartActive;
    }

    /**
     * Returns color of the chart.
     *
     * @return  string  Hex code.
     */
    public function getChartColor()
    {
        if (!$this->isMiniChartActive()) {
            return '#7362BF';
        }

        return Option::getByAddon('chart_color', 'mini_chart', '#7362BF');
    }

    /**
     * Returns either 'Visitors' or 'Views' depending on the selected options.
     *
     * @return  string
     */
    public function getTooltipLabel()
    {
        return Helper::checkMiniChartOption('metric', 'views', 'visitors') ? __('Views', 'wp-statistics') : __('Visitors', 'wp-statistics');
    }

    /**
     * Returns `chartDates` array (and creates the array if it's empty).
     *
     * @param   int     $forceDays  Ignore `date_range` option and send dates for the past x days.
     * @param   string  $minDate    Ignore all dates before this date (e.g. You can pass post publish date here). Format `Y-m-d`.
     *
     * @return  array               An array of simple `Y-m-d` dates from `date_range` days ago to today.
     */
    public function getChartDates($forceDays = 0, $minDate = '')
    {
        // Return `$chartDates` if it already has data (and `$forceDays` and `$minDate` are empty)
        if (!empty($this->chartDates) && !intval($forceDays) && empty($minDate)) {
            return $this->chartDates;
        }
        $chartDates = [];

        // Fill `$chartDates` in reveresed order (oldest date is at the beginning of the array)
        $defaultDaysCount  = $this->isMiniChartActive() ? intval(Option::getByAddon('date_range', 'mini_chart', '14')) : 14;
        $daysAgoStartIndex = intval($forceDays) ? intval($forceDays) - 1 : $defaultDaysCount - 1;
        for ($i = $daysAgoStartIndex; $i >= 0; $i--) {
            $date = TimeZone::getTimeAgo($i);
            // Add `$date` if `$minDate` is not passed or if `$minDate` is less than `$date`
            if (empty($minDate) || $minDate < $date) {
                $chartDates[] = $date;
            }
        }

        // Cache `$chartDates` as a class attribute only if the method was called without `$forceDays` or `$minDate`
        if (!intval($forceDays) && empty($minDate)) {
            $this->chartDates = $chartDates;
        }

        return $chartDates;
    }
}
