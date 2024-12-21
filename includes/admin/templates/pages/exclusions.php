<?php
use WP_Statistics\Components\View;
?>

<div class="postbox-container wps-postbox-full">
    <div class="postbox mb-8">
        <div class="postbox-header--table-title">
            <h2>
                <?php esc_html_e('Exclusions Over Time', 'wp-statistics'); ?>
            </h2>
        </div>
        <div class="inside">
            <?php View::load("components/charts/exclusions"); ?>
        </div>
    </div>
</div>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox">
            <div class="inside">
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                        <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <span><?php esc_html_e('Type', 'wp-statistics') ?></span>
                            </th>
                            <th class="wps-pd-l start">
                                <span class="wps-order"><?php esc_html_e('Exclusions', 'wp-statistics') ?></span>
                            </th>
                            <th class="wps-pd-l"></th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td class="wps-pd-l">
                                <b>User Role</b>
                            </td>
                            <td class="wps-pd-l start">
                               160
                            </td>
                            <td class="wps-pd-l">
                                60.1%
                            </td>
                        </tr>
                        <tr>
                            <td class="wps-pd-l">
                                <b>User Role</b>
                            </td>
                            <td class="wps-pd-l start">
                                160
                            </td>
                            <td class="wps-pd-l">
                                60.1%
                            </td>
                        </tr>
                        <tr>
                            <td class="wps-pd-l">
                                <b>User Role</b>
                            </td>
                            <td class="wps-pd-l start">
                                160
                            </td>
                            <td class="wps-pd-l">
                                60.1%
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                </div>
            </div>
        </div>
    </div>
</div>