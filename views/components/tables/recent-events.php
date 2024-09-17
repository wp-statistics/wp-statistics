
<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-recent-events-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Date', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Link URL', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Link ID', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Link Class', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Link Text', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Page', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Action', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $author) : ?>
                    <tr>
                        <td class="wps-pd-l">March 25, 2024 09:07 PM</td>
                        <td class="wps-pd-l">
                            <a target="_blank" href="" title="veronalabs.com/privacy-policy/" class="wps-link-arrow">
                                <span>veronalabs.com/privacy-policy/</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent">
                                <span title="footer-nav">footer-nav</span>
                                <span class="wps-ellipsis-text--disable">N/A</span>
                            </div>

                        </td>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent">
                                <span title=".c-footer__social--link">.c-footer__social--link</span>
                            </div>
                        </td>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent">
                                <span title="wp-statistics.com">wp-statistics.com</span>
                            </div>
                        </td>
                        <td class="wps-pd-l">
                            <a target="_blank" href="" title="Home Page" class="wps-link-arrow">
                                <span>Home Page</span>
                            </a>
                        </td>
                        <td class="wps-pd-l">
                            <div class="wps-ellipsis-parent">
                                <span title="Download">Download</span>
                            </div>
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