<?php

use WP_Statistics\Components\View;

?>

<div class="inside">
    <div class="o-table-wrapper">
        <table width="100%" class="o-table wps-new-table">
            <thead>
            <tr>
                <th class="wps-pd-l">
                    <?php esc_html_e('Entry Page', 'wp-statistics') ?>
                </th>
                <th class="wps-pd-l">
                    <span class="wps-order"><?php esc_html_e('Unique Entrances', 'wp-statistics') ?></span>
                </th>
                <th class="wps-pd-l">
                    <?php esc_html_e('Publish Date', 'wp-statistics') ?>
                </th>
                <th class="wps-pd-l"></th>
            </tr>
            </thead>

            <tbody>
            <tr>
                <td class="wps-pd-l">
                    <?php
                    View::load("components/objects/external-link", [
                        'url'   => '',
                        'title' => 'Services Overview',
                    ]);

                    ?>
                </td>

                <td class="wps-pd-l">
                    <a href="">720</a>
                </td>

                <td class="wps-pd-l">
                    November 17, 2024 at 10:34
                </td>

                <td class="wps-pd-l view-more view-more__arrow">
                    <a target="_blank" href="/"><?php esc_html_e('View Page', 'wp-statistics') ?></a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    <div class="o-wrap o-wrap--no-data wps-center">
        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
    </div>
</div>