<?php

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;

$visitors       = $data['total']['visitors']['current'];
$prevVisitors   = $data['total']['visitors']['prev'];
$views          = $data['total']['views']['current'];
$prevViews      = $data['total']['views']['prev'];
?>

<div class="wps-meta-traffic-summary">
    <div class="o-wrap">
        <div class="wps-postbox-chart--data">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--items">
                <div class="wps-postbox-chart--item">
                    <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span><?php echo esc_html(Helper::formatNumberWithUnit($visitors, 1)) ?></span>
                            <span class="current-data-percent <?php echo ($visitors > $prevVisitors) ? 'current-data-percent__success' : 'current-data-percent__danger' ?>"><?php echo esc_html(Helper::calculatePercentageChange($prevVisitors, $visitors)) ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="wps-postbox-chart--item">
                    <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span><?php echo esc_html(Helper::formatNumberWithUnit($views, 1)) ?></span>
                            <span class="current-data-percent <?php echo ($views > $prevViews) ? 'current-data-percent__success' : 'current-data-percent__danger' ?>"><?php echo esc_html(Helper::calculatePercentageChange($prevViews, $views)) ?>%</span>
                        </div>
                    </div>
                </div>
                <div class="wps-postbox-chart--item wps-postbox-chart--item__active">
                    <span><?php esc_html_e('Active now', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span class="dot"></span>
                            <span><?php echo esc_html($data['online']); ?></span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="wps-postbox-chart--container">
            <canvas id="wp-statistics-quickstats-widget-chart" height="166"></canvas>
        </div>
    </div>

    <div class="o-table-wrapper">
        <table width="100%" class="o-table o-table--wps-summary-stats">
            <thead>
                <tr>
                    <th width="50%"></th>
                    <th><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                    <th><?php esc_html_e('Views', 'wp-statistics'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><b><?php esc_html_e('Today', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('today'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['today']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('today'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['today']['views'], 1)) ?></span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Yesterday', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('yesterday'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['yesterday']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('yesterday'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['yesterday']['views'], 1)) ?></span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Last 7 days', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('7days'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['7days']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('7days'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['7days']['views'], 1)) ?></span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Last 30 days', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('30days'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['30days']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('30days'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['30days']['views'], 1)) ?></span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('This year (Jan-Today)', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('this_year'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['this_year']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('this_year'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['this_year']['views'], 1)) ?></span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Total', 'wp-statistics'); ?></b></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('total'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['total']['visitors'], 1)) ?></span></a></td>
                    <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('total'))) ?>"><span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['views']['total']['views'], 1)) ?></span></a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if (!Option::get('time_report')) {
        View::load("components/meta-box/enable-mail", ['url' => Menus::admin_url('settings')]);
    } ?>
</div>