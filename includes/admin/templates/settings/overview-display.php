<?php
// Only display the global options if the user is an administrator.
if ($wps_admin) {
    ?>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Dashboard Widgets', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="disable-map"><?php esc_html_e('Display WP Statistics Widgets', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == '1' ? "" : "checked='checked'"; ?>>
                    <label for="disable-dashboard"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('View WP Statistics widgets in the WordPress dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="disable-map"><?php esc_html_e('Display Global Visitor', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo WP_STATISTICS\Option::get('disable_map') == '1' ? "" : "checked='checked'"; ?>>
                    <label for="disable-map"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('View the \'Global Visitor Distribution\' widget in the overviews.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
    <?php
}

submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='overview-display-settings'")); ?>
