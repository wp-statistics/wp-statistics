<?php

use WP_STATISTICS\Country;

?>

<div class="wps-card">
    <div class="wps-card__title">
        <h2>
            <?php echo esc_html__('Top Cities', 'wp-statistics') ?>
        </h2>
    </div>
    <div class="inside">
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php esc_html_e('City', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Region', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Country', 'wp-statistics') ?>
                    </th>

                    <th class="wps-pd-l">
                        <span class="wps-order">
                            <?php esc_html_e('Visitors', 'wp-statistics') ?>
                        </span>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Views', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="wps-pd-l">
                        <span class="truncate" title="Paris">Paris</span>
                    </td>
                    <td class="wps-pd-l">
                        <span class="truncate" title="Île-de-France">Île-de-France</span>
                    </td>
                    <td class="wps-pd-l">
                        <div class="wps-country-name">
                            <img class="wps-flag" src="" alt="">
                            <span class="truncate" title="Russian Federation">Russian Federation</span>
                        </div>
                    </td>
                    <td class="wps-pd-l">
                        <span>45,452</span>
                    </td>
                    <td class="wps-pd-l">
                        <span>45,452</span>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="o-wrap o-wrap--no-data wps-center">
            <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
        </div>
    </div>
    <div class="wps-card__footer">
        <div class="wps-card__footer__more">
            <a class="wps-card__footer__more__link" href="">
                <?php esc_html_e('View Cities', 'wp-statistics') ?>
            </a>
        </div>
    </div>
</div>