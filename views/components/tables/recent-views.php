
<div class="inside">
    <?php if (!empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table wps-recent-views-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php esc_html_e('Date', 'wp-statistics') ?>
                    </th>
                    <th class="wps-pd-l start">
                        <?php esc_html_e('Page', 'wp-statistics') ?>
                    </th>
                </tr>
                </thead>

                <tbody>
                <?php foreach ($data as $author) : ?>
                    <tr>
                        <td class="wps-pd-l">March 10, 2024 4:15 pm</td>
                        <td class="wps-pd-l start">
                            <a target="_blank" href="" title="Privacy Policy" class="wps-link-arrow">
                                <span>Privacy Policy</span>
                            </a>
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