<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

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
                <?php if (!empty($data['data'])) : ?>
                    <div class="o-table-wrapper">
                        <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                            <thead>
                            <tr>
                                <th scope="col" class="wps-pd-l">
                                    <span><?php esc_html_e('Type', 'wp-statistics') ?></span>
                                </th>
                                <th scope="col" class="wps-pd-l wps-col-num">
                                    <span class="wps-order"><?php esc_html_e('Exclusions', 'wp-statistics') ?></span>
                                </th>
                                <th scope="col" class="wps-pd-l wps-col-share">
                                    <?php esc_html_e('Share', 'wp-statistics'); ?>
                                </th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data['data'] as $item) : ?>
                                <tr>
                                    <td class="wps-pd-l">
                                        <b><?php echo esc_html(\WP_STATISTICS\Exclusion::get_reason_label($item->reason)) ?></b>
                                    </td>
                                    <td class="wps-pd-l wps-col-num">
                                        <?php echo esc_html(number_format_i18n($item->count)); ?>
                                    </td>
                                    <td class="wps-pd-l wps-col-share">
                                        <?php View::load('components/objects/percent-bar', ['count' => $item->count, 'total' => $data['total'], 'label' => __('Share of all exclusions', 'wp-statistics')]); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else : ?>
                    <?php View::load('components/objects/empty-state'); ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>