<?php 
use WP_STATISTICS\Admin_Template;
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
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('State', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="Country Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="Visitor Count Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                            <span class="wps-tooltip" title="View Count Tooltip"><i class="wps-tooltip-icon info"></i></span>
                                        </th>
                                    </tr>
                                </thead>
    
                                <tbody>

                                    <?php foreach ($data['states'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->region) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->visitors) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html($item->views) ?>
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
                <?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
        </div>
    </div>
</div>