<?php 
use WP_STATISTICS\Admin_Template;
?>

<div class="postbox-container wps-postbox-full">
    <div class="metabox-holder">
        <div class="meta-box-sortables">
            <div class="postbox">
                <div class="inside">
                    <?php if (!empty($data['regions'])) : ?>
                        <div class="o-table-wrapper">
                            <table width="100%" class="o-table wps-new-table">
                                <thead>
                                    <tr>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Region', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('Visitor Count', 'wp-statistics') ?>
                                        </th>
                                        <th class="wps-pd-l">
                                            <?php esc_html_e('View Count', 'wp-statistics') ?>
                                        </th>
                                    </tr>
                                </thead>
    
                                <tbody>

                                    <?php foreach ($data['regions'] as $item) : ?>
                                        <tr>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(\WP_STATISTICS\Admin_Template::unknownToNotSet($item->region)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->visitors)) ?>
                                            </td>
                                            <td class="wps-pd-l">
                                                <?php echo esc_html(number_format($item->views)) ?>
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