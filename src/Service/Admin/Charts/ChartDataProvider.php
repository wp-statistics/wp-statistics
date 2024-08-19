<?php

namespace WP_Statistics\Service\Admin\Charts;

use WP_STATISTICS\Option;

class ChartDataProvider
{
    /**
     * Returns color of the chart.
     *
     * @return  string  Hex code.
     */
    public function getChartColor()
    {
        return Option::getByAddon('chart_color', 'mini_chart', '#7362BF');
    }

    /**
     * Returns border color of the chart.
     *
     * @return  string  Hex code.
     */
    public function getBorderColor()
    {
        return Option::getByAddon('chart_border_color', 'mini_chart', '#0D0725');
    }

    /**
     * Returns either 'Visitors' or 'Views' depending on the selected options.
     *
     * @return  string
     */
    public function getTooltipLabel($isTotal = false)
    {
        return Option::getByAddon('metric', 'mini_chart', 'visitors') === 'visitors' ? __('Visitors', 'wp-statistics') : __('Views', 'wp-statistics');
    }
}
