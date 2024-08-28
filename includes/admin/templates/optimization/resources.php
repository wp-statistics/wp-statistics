<div class="wrap wps-wrap">
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php use WP_STATISTICS\GeoIP;

                        esc_html_e('Resources/Information', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Current PHP Memory Consumption', 'wp-statistics'); ?>
                </th>
                <td>
                    <strong><?php echo esc_html(size_format(memory_get_usage(), 3)); ?></strong>
                    <p class="description"><?php esc_html_e('Displays the amount of memory currently being used by PHP on your server.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <?php esc_html_e('Maximum Allowed PHP Memory', 'wp-statistics'); ?>
                </th>

                <td>
                    <strong><?php echo esc_html(ini_get('memory_limit')); ?></strong>
                    <p class="description"><?php esc_html_e('This is the maximum amount of memory PHP can use on your server. Increasing this value might improve performance but ensure you don\'t exceed your server\'s limits.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <?php
            foreach ($result as $table_name => $data) {
                ?>
                <tr valign="top">
                    <th scope="row">
                        <?php echo sprintf(esc_html__('Number of rows in the %s table', 'wp-statistics'), '<code>' . esc_attr($table_name) . '</code>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped   ?>
                    </th>
                    <td>
                        <strong><?php echo esc_html(number_format_i18n($data['rows'])); ?></strong> <?php echo esc_html(_n('Row', 'Rows', number_format_i18n($data['rows']), 'wp-statistics')); ?>
                        <p class="description"><?php echo wp_kses_data($data['desc']) ?></p>
                    </td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>
