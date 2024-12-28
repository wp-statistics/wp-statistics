<?php

use WP_Statistics\Components\View;
use WP_Statistics\Decorators\ReferralDecorator;
use WP_STATISTICS\Helper;
use WP_STATISTICS\Menus;
?>

<div class="postbox-container wps-postbox-full">
    <div class="meta-box-sortables">
        <div class="postbox mb-8">
            <div class="postbox-header--table-title">
                <h2>
                    <?php esc_html_e('Income Visitor Chart', 'wp-statistics'); ?>
                </h2>
            </div>
            <div class="inside">
                <?php View::load("components/charts/source-categories"); ?>
            </div>
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
                                    <span><?php esc_html_e('Domain Address', 'wp-statistics') ?></span>
                                </th>
                                <th class="wps-pd-l start">
                                    <span><?php esc_html_e('Source Name', 'wp-statistics') ?></span>
                                </th>
                                <th class="wps-pd-l start">
                                    <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                                </th>
                            </tr>
                            </thead>

                            <tbody>
                                 <tr>
                                    <td class="wps-pd-l">
                                        <a href="" title="google.com" target="_blank" class="wps-link-arrow">
                                            <span>google.com</span>
                                        </a>
                                    </td>

                                    <td class="wps-pd-l start">
                                        Instagram
                                     </td>

                                    <td class=" wps-pd-l start">
                                        <a href="">
                                            13
                                        </a>
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