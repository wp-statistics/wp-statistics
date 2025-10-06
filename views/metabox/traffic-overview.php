<?php

use WP_Statistics\Components\DateRange;
use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;

$visitors     = $data['total']['visitors']['current'];
$prevVisitors = $data['total']['visitors']['prev'];
$views        = $data['total']['views']['current'];
$prevViews    = $data['total']['views']['prev'];
$userOnline   = new \WP_STATISTICS\UserOnline();
?>

<div class="wps-meta-traffic-summary">

    <?php if ($userOnline::active()) : ?>
        <div class="c-live">
            <div>
                <span class="c-live__status"></span>
                <span class="c-live__title"><?php esc_html_e('Online Users', 'wp-statistics'); ?></span>
            </div>
            <div class="c-live__online">
                <span class="c-live__online--value"><?php echo esc_html($data['online']) ?></span>
                <a class="c-live__value" href="<?php echo Menus::admin_url('visitors', ['tab' => 'online']) ?>" aria-label="<?php esc_attr_e('View online visitors', 'wp-statistics'); ?>"><span class="c-live__online--arrow"></span></a>
            </div>
        </div>
    <?php endif ?>

    <div class="o-wrap">
        <div class="wps-postbox-chart--title">
            <span class="wps-chart--title"><?php esc_html_e('Last 7 days (exclude today)', 'wp-statistics'); ?></span>
            <div class="wps-postbox-chart--info">
                <div class="wps-postbox-chart--previousPeriod">
                    <?php esc_html_e('Previous period', 'wp-statistics') ?>
                </div>
            </div>
        </div>

        <div class="wps-postbox-chart--data">
            <div class="wps-postbox-chart--items"></div>
            <div class="wps-postbox-chart--items">
                <div class="wps-postbox-chart--item__data">
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($visitors, 1)) ?></span>
                                <span class="current-data-percent diffs__change <?php echo ($visitors > $prevVisitors) ? 'plus' : 'minus' ?>">
                                    <span class="diffs__change__direction">
                                        <?php echo esc_html(Helper::calculatePercentageChange($prevVisitors, $visitors)) ?>%
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="wps-postbox-chart--item">
                        <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                        <div>
                            <div class="current-data">
                                <span><?php echo esc_html(Helper::formatNumberWithUnit($views, 1)) ?></span>
                                <span class="current-data-percent diffs__change <?php echo ($views > $prevViews) ? 'plus' : 'minus' ?>">
                                    <span class="diffs__change__direction">
                                        <?php echo esc_html(Helper::calculatePercentageChange($prevViews, $views)) ?>%
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
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
                <td><?php esc_html_e('Today', 'wp-statistics'); ?></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('today'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['visitors']['today']['visitors']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['today']['visitors'], 1)) ?></span></a></td>
                <td><a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('today'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['hits']['today']['hits']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['today']['hits'], 1)) ?></span></a></td>
            </tr>
            <tr>
                <td><?php esc_html_e('Yesterday', 'wp-statistics'); ?></td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('yesterday'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['visitors']['yesterday']['visitors']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['yesterday']['visitors'], 1)) ?></span></a>
                        <div class="diffs__change plus">
                            <span class="diffs__change__direction">32%</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('yesterday'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['hits']['yesterday']['hits']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['yesterday']['hits'], 1)) ?></span></a>
                        <div class="diffs__change plus">
                            <span class="diffs__change__direction">32%</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e('Last 7 days', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Totals from the last 7 complete days (excludes today).', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('7days'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['visitors']['7days']['visitors']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['7days']['visitors'], 1)) ?></span></a>
                        <div class="diffs__change plus">
                            <span class="diffs__change__direction">32%</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('7days'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['hits']['7days']['hits']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['7days']['hits'], 1)) ?></span></a>
                        <div class="diffs__change plus">
                            <span class="diffs__change__direction">32%</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <?php esc_html_e('Last 28 days', 'wp-statistics'); ?>
                    <span class="wps-tooltip" title="<?php esc_html_e('Totals from the last 28 complete days (excludes today).', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                </td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'visitors'], DateRange::get('30days'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['visitors']['30days']['visitors']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['visitors']['30days']['visitors'], 1)) ?></span></a>
                        <div class="diffs__change">
                            <span class="diffs__change__direction">0%</span>
                        </div>
                    </div>
                </td>
                <td>
                    <div>
                        <a href="<?php echo Menus::admin_url('visitors', array_merge(['tab' => 'views'], DateRange::get('30days'))) ?>"><span class="quickstats-values" title="<?php echo esc_attr($data['hits']['30days']['hits']); ?>"><?php echo esc_html(Helper::formatNumberWithUnit($data['hits']['30days']['hits'], 1)) ?></span></a>
                        <div class="diffs__change minus">
                            <span class="diffs__change__direction">0%</span>
                        </div>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <?php
    if (!Option::get('time_report') && !in_array('enable_email_metabox_notice', get_option('wp_statistics_dismissed_notices', []))) {
        View::load("components/meta-box/enable-mail");
    }
    ?>
</div>