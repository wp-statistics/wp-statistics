<?php
use WP_STATISTICS\Admin_Template;
?>

    <div class="inside">
            <div class="o-table-wrapper">
                <table width="100%" class="o-table wps-new-table wps-new-table--referrers">
                    <thead>
                    <tr>
                        <th class="wps-pd-l">
                            <span><?php esc_html_e('Domain Address', 'wp-statistics') ?></span>
                        </th>
                        <?php if ($show_source_category && $show_source_category !== null) : ?>
                            <th class="wps-pd-l">
                                <?php esc_html_e('Source Category', 'wp-statistics') ?>
                            </th>
                        <?php endif; ?>
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
                            <?php if ($show_source_category && $show_source_category !== null) : ?>
                                <td class="wps-pd-l">
                                    <div class="wps-ellipsis-parent">
                                        <span class="wps-ellipsis-text" title="Organic Search">Organic Search</span>
                                    </div>
                                </td>
                            <?php endif; ?>
                            <td class="wps-pd-l start">
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
<?php echo isset($pagination) ? $pagination : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
?>