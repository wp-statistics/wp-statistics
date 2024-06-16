<?php

use WP_STATISTICS\Menus;

?>
<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['visitors'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php echo esc_html($firstColTitle); ?>
                                            <span class="wps-tooltip" title="<?php echo esc_html($firstColTooltip); ?>"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics'); ?>
                                            <span class="wps-tooltip" title="<?php echo esc_html_e('Visitor Count Tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Percent Share', 'wp-statistics'); ?>
                                            <span class="wps-tooltip" title="<?php echo esc_html_e('Percent Share Tooltip', 'wp-statistics'); ?>"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($data['visitors'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <span title="<?php echo esc_attr($item->version); ?>" class="wps-single-name">
                                                    <?php echo esc_html($item->version); ?>
                                                </span>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo intval($item->views); ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo number_format((intval($item->views) / intval($data['total']->views_sum)) * 100, 2); ?>%
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else : ?>
                        <div class="o-wrap o-wrap--no-data wps-center">
                            <?php esc_html_e('No recent data available.', 'wp-statistics'); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped 
                ?>
            </div>
        </div>
    </div>
</div>