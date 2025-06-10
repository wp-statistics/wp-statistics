<?php

use WP_STATISTICS\Menus;
use WP_Statistics\Components\View;

?>
<div class="wps-postbox-two-col">


    <!--    Top Browsers-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top OS', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Browser', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <span title="Chrome" class="wps-browser-name">
                                <img alt="Chrome" src="http://statistics.localhost/wp-content/plugins/wp-statistics/assets/images/browser/chrome.svg" title="Chrome" class="log-tools wps-flag">
                                Chrome
                            </span>
                        </td>
                        <td class="wps-pd-l">
                            32,521
                        </td>
                        <td class="wps-pd-l">
                            25.18%
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'browsers'], Menus::admin_url('devices')),
            'title' => __('View Browsers', 'wp-statistics'),
        ]); ?>
    </div>


    <!--    Top OS-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Top OS', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('OS', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <span title="Android" class="wps-platform-name">
                                <img alt="Android" src="/assets/images/operating-system/android.svg" title="Android" class="log-tools wps-flag">
                                Android
                            </span>
                        </td>
                        <td class="wps-pd-l">
                            32,521
                        </td>
                        <td class="wps-pd-l">
                            25.18%
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'browsers'], Menus::admin_url('platforms')),
            'title' => __('View OS', 'wp-statistics'),
        ]); ?>
    </div>


    <!--    Device Models-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Device Models', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Model', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <span title="Google Nexus" class="wps-model-name">
                                Google Nexus
                            </span>
                        </td>
                        <td class="wps-pd-l">
                            32,521
                        </td>
                        <td class="wps-pd-l">
                            25.18%
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'models'], Menus::admin_url('devices')),
            'title' => __('View Device Models', 'wp-statistics'),
        ]); ?>
    </div>


    <!--    Device Categories-->
    <div class="postbox">
        <?php
        View::load("components/objects/card-header", [
            'title' => __('Device Categories', 'wp-statistics'),
        ]);
        ?>
        <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <?php esc_html_e('Category', 'wp-statistics'); ?>
                        </th>
                        <th class="wps-pd-l">
                            <span class="wps-order"><?php esc_html_e('Visitors', 'wp-statistics'); ?></span>
                        </th>
                        <th class="wps-pd-l">
                            %
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="wps-pd-l">
                            <span title="Smartphone" class="wps-model-name">
                                Smartphone
                            </span>
                        </td>
                        <td class="wps-pd-l">
                            32,521
                        </td>
                        <td class="wps-pd-l">
                            25.18%
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="o-wrap o-wrap--no-data wps-center">
                <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
            </div>
        </div>
        <?php
        View::load("components/objects/card-footer", [
            'href'  => add_query_arg(['tab' => 'categories'], Menus::admin_url('devices')),
            'title' => __('View Device Categories', 'wp-statistics'),
        ]); ?>
    </div>
</div>
