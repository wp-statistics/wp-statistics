<?php
use WP_Statistics\Components\View;
use WP_STATISTICS\Helper;
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
                <?php if (!empty($data['data'])) : ?>
                    <div class="o-table-wrapper">
                        <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                            <thead>
                                <tr>
                                    <th scope="col" class="wps-pd-l">
                                        <span><?php esc_html_e('Type', 'wp-statistics') ?></span>
                                    </th>
                                    <th scope="col" class="wps-pd-l start">
                                        <span class="wps-order"><?php esc_html_e('Exclusions', 'wp-statistics') ?></span>
                                    </th>
                                    <th scope="col" class="wps-pd-l">
                                        <span class="screen-reader-text"><?php esc_html_e('View excluded percentage', 'wp-statistics'); ?></span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data['data'] as $item) : ?>
                                    <tr>
                                        <td class="wps-pd-l">
                                            <b><?php echo esc_html(ucwords($item->reason)) ?></b>
                                        </td>
                                        <td class="wps-pd-l start">
                                            <?php echo esc_html(number_format_i18n($item->count)); ?>
                                        </td>
                                        <td class="wps-pd-l">
                                            <?php echo esc_html(Helper::calculatePercentage($item->count, $data['total']) . '%') ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <div class="o-wrap o-wrap--no-data wps-center">
                        <?php esc_html_e('No recent data available.', 'wp-statistics') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>