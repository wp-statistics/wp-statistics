<?php

use WP_Statistics\Components\View;

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
            <?php

            use WP_STATISTICS\Admin_Template;
            use WP_Statistics\Decorators\ReferralDecorator;
            use WP_STATISTICS\Menus;
            use WP_Statistics\Service\Analytics\Referrals\SourceChannels;

            ?>

            <div class="inside">
                <div class="o-table-wrapper">
                    <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                        <thead>
                        <tr>
                            <th class="wps-pd-l">
                                <span><?php esc_html_e('Source Category', 'wp-statistics') ?></span>
                            </th>
                            <th class="wps-pd-l start">
                                <span class="wps-order"><?php esc_html_e('Number of Referrals', 'wp-statistics') ?></span>
                            </th>
                            <th class="wps-pd-l"></th>
                        </tr>
                        </thead>

                        <tbody>
                        <tr>
                            <td class="wps-pd-l">
                                Organic Search
                            </td>
                            <td class="wps-pd-l start">
                                <a href=""">
                                50
                                </a>
                            </td>

                            <td class="wps-pd-l">
                                33%
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div class="o-wrap o-wrap--no-data wps-center">
                    <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                </div>
            </div>
            <?php
            echo $pagination ?? ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            ?>
        </div>
    </div>
</div>