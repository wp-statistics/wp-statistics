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
$userOnline     = new \WP_STATISTICS\UserOnline();
?>

<div class="wps-meta-traffic-summary">
    <div class="o-wrap">
        <div class="wps-postbox-chart--data">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--items">
                <div class="wps-postbox-chart--item__data">
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($visitors, 1)) ?></span>
                                <span class="current-data-percent
                                    <?php
                                    if ($visitors == 0) {
                                        echo 'current-data-percent__neutral';
                                    } else {
                                        echo ($visitors > $prevVisitors) ? 'current-data-percent__success' : 'current-data-percent__danger';
                                    }
                                    ?>">
                                    <?php echo esc_html(Helper::calculatePercentageChange($prevVisitors, $visitors)) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($views, 1)) ?></span>
                                <span class="current-data-percent
                                    <?php
                                        if ($views == 0) {
                                            echo 'current-data-percent__neutral';
                                        } else {
                                            echo ($views > $prevViews) ? 'current-data-percent__success' : 'current-data-percent__danger';
                                        }
                                        ?>">
                                    <?php echo esc_html(Helper::calculatePercentageChange($prevViews, $views)) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if ($userOnline::active()) : ?>
                <div class="wps-postbox-chart--item wps-postbox-chart--item__active">
                    <span><?php esc_html_e('Online Visitors', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span class="dot"></span>
                            <span><?php echo esc_html($data['online']); ?></span>
                        </div>
                    </div>
                </div>
                <?php endif ?>
            </div>
        </div>
        <div class="wps-postbox-chart--container">
            <p class="screen-reader-text">
                <?php echo esc_html__('Traffic overview chart', 'wp-statistics') ?>
            </p>
            <canvas id="wp-statistics-quickstats-widget-chart" aria-labelledby="Traffic overview chart" role="img" height="166"></canvas>
        </div>
    </div>
    <div class="o-table-wrapper">
        <table width="100%" class="o-table o-table--wps-summary-stats">
            <thead>
                <tr>
                    <th width="50%" scope="col">
                        <span class="screen-reader-text"><?php esc_html_e('Date range', 'wp-statistics'); ?></span>
                    </th>
                    <th scope="col"><?php esc_html_e('Visitors', 'wp-statistics'); ?></th>
                    <th scope="col"><?php esc_html_e('Views', 'wp-statistics'); ?></th>
                </tr>
            </thead>
            <tbody>
            <tr>
                <td><b><?php esc_html_e('Today', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('today'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['today']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['today']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('today'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['today']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['today']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('Yesterday', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('yesterday'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['yesterday']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['yesterday']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('yesterday'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['yesterday']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['yesterday']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('Last 7 days', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('7days'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['7days']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['7days']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('7days'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['7days']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['7days']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('Last 30 days', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('30days'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['30days']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['30days']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('30days'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['30days']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['30days']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('This year (Jan-Today)', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('this_year'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['this_year']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['this_year']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('this_year'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['this_year']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['this_year']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><b><?php esc_html_e('Total', 'wp-statistics'); ?></b></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('total'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['visitors']['total']['visitors'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['total']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('total'))) ?>"
                       title="<?php echo esc_attr(Helper::formatNumberWithUnit($data['hits']['total']['hits'], 1)) ?>">
                        <span class="quickstats-values"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['total']['hits'], 1)) ?></span></a></td>
            </tr>
            </tbody>
        </table>
    </div>

    <?php
        if (!Option::get('time_report') && !in_array('enable_email_metabox_notice', get_option('wp_statistics_dismissed_notices', [])))  {
            View::load("components/meta-box/enable-mail");
        }
    ?>
</div>