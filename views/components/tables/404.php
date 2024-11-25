<div class="inside">
    <?php if (empty($data)) : ?>
        <div class="o-table-wrapper">
            <table width="100%" class="o-table wps-new-table">
                <thead>
                <tr>
                    <th class="wps-pd-l">
                        <?php esc_html_e('URL', 'wp-statistics'); ?>
                    </th>
                    <th class="wps-pd-l">
                        <a href="" class="sort desc">
                            <?php esc_html_e('Views', 'wp-statistics') ?>
                        </a>
                    </th>
                </tr>
                </thead>

                <tbody>
                <tr>
                    <td class="wps-pd-l">
                        <a class="wps-link-arrow wps-link-arrow--404-page" href="" target="_blank" title="/documentation/data-plus">
                            <span>/documentation/data-plus</span>
                        </a>
                    </td>
                    <td class="wps-pd-l">11,563</td>
                </tr>
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