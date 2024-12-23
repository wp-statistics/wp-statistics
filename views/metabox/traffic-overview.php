<?php

use WP_STATISTICS\Menus;
use WP_STATISTICS\Option;
use WP_Statistics\Components\View;

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
                            <span>75K</span>
                            <span class="current-data-percent current-data-percent__success">2%</span>
                        </div>
                    </div>
                </div>
                <div class="wps-postbox-chart--item">
                    <span><span class="wps-postbox-chart--item--color"></span><?php esc_html_e('Views', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span>75K</span>
                            <span class="current-data-percent current-data-percent__danger">45%</span>
                        </div>
                    </div>
                </div>
                <div class="wps-postbox-chart--item wps-postbox-chart--item__active">
                    <span><?php esc_html_e('Active now', 'wp-statistics'); ?></span>
                    <div>
                        <div class="current-data">
                            <span class="dot"></span>
                            <span>15</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="wps-postbox-chart--container">
            <canvas id="wps_traffic_overview_meta_chart" height="166"></canvas>
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
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Yesterday', 'wp-statistics'); ?></b></td>
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Last 7 days', 'wp-statistics'); ?></b></td>
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Last 30 days', 'wp-statistics'); ?></b></td>
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('This year (Jan-Today)', 'wp-statistics'); ?></b></td>
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
                <tr>
                    <td><b><?php esc_html_e('Total', 'wp-statistics'); ?></b></td>
                    <td><a href=""><span class="quickstats-values">1</span></a></td>
                    <td><a href=""><span class="quickstats-values">2</span></a></td>
                </tr>
            </tbody>
        </table>
    </div>

    <?php if (!Option::get('time_report')) {
        View::load("components/meta-box/enable-mail", ['url' => Menus::admin_url('settings')]);
    } ?>
</div>