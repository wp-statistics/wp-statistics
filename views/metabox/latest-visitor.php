<?php

use WP_Statistics\Components\View;

?>

<div class="o-table-wrapper">
    <table width="100%" class="o-table wps-new-table">
        <thead>
        <tr>
            <th class="wps-pd-l">
                <span class="wps-order"><?php esc_html_e('Last View', 'wp-statistics'); ?></span>
            </th>
            <th class="wps-pd-l">
                <?php esc_html_e('Visitor Info', 'wp-statistics'); ?>
            </th>
            <th class="wps-pd-l">
                <?php esc_html_e('Location', 'wp-statistics'); ?>
            </th>
            <th class="wps-pd-l">
                <?php esc_html_e('Referrer', 'wp-statistics'); ?>
            </th>
            <th class="wps-pd-l">
                <?php esc_html_e('Latest Page', 'wp-statistics'); ?>
            </th>
            <th class="wps-pd-l">
                <?php esc_html_e('Views', 'wp-statistics'); ?>
            </th>
        </tr>
        </thead>

        <tbody>
        <tr>
            <td class="wps-pd-l">
                 December 8, 11:04 am
            </td>

            <td class="wps-pd-l">
                <!--                                --><?php //View::load("components/visitor-information", ['visitor' => $visitor]); ?>
            </td>

            <td class="wps-pd-l">
                <div class="wps-country-flag wps-ellipsis-parent">
                    <a target="" href="" class="wps-tooltip" title="">
                        <img src="" alt="getCountryName" width="15" height="15">
                    </a>
                    <span class="wps-ellipsis-text" title="Hesse, Frankfurt am Main">Hesse, Frankfurt am Main</span>
                </div>
            </td>

            <td class="wps-pd-l">
                <?php
                View::load("components/objects/external-link", ['url' => '', 'title' => '']);
                ?>
            </td>

            <td class="wps-pd-l">
                <a target="_blank" href="" title="" class="wps-link-arrow"><span>Home Page: Home</span></a>
            </td>

            <td class="wps-pd-l">
                <a href="">13</a>
            </td>
        </tr>
        </tbody>
    </table>
</div>

