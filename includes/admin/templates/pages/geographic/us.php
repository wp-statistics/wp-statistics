<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use WP_STATISTICS\Admin_Template;
use WP_Statistics\Components\View;

?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['states'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                <tr>
                                    <th scope="col" class="wps-pd-l">
                                        <?php esc_html_e('State', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-num">
                                        <?php esc_html_e('Visitors', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-num">
                                        <?php esc_html_e('Views', 'wp-statistics') ?>
                                    </th>
                                    <th scope="col" class="wps-pd-l wps-col-share">
                                        <?php esc_html_e('Share of US', 'wp-statistics') ?>
                                    </th>
                                </tr>
                                </thead>

                                <tbody>

                                <?php foreach ($data['states'] as $item) : ?>
                                    <tr>
                                        <td class="wps-pd-l">
                                            <?php echo esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->region)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->visitors)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-num">
                                            <?php echo esc_html(number_format_i18n($item->views)) ?>
                                        </td>
                                        <td class="wps-pd-l wps-col-share">
                                            <?php View::load('components/objects/percent-bar', ['count' => $item->visitors, 'total' => $data['visitors'] ?? 0, 'label' => __('Share of US visitors with a known state', 'wp-statistics')]); ?>
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
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>