<?php
// Only display the global options if the user is an administrator.
if ($wps_admin) {
    ?>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('Dashboard Widgets', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="disable-map"><?php _e('Display WP Statistics Widgets', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == true ? "checked='checked'" : ''; ?>>
                    <label for="disable-dashboard"><?php _e('Disable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Toggle this setting to enable or disable the display of WP Statistics widgets on your WordPress dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="disable-map"><?php _e('Enable Geographic Map', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo WP_STATISTICS\Option::get('disable_map') == true ? "checked='checked'" : ''; ?>>
                    <label for="disable-map"><?php _e('Disable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Activate this setting to display a geographic map on your dashboard, providing insights into your visitors\' locations.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            </tbody>
        </table>
    </div>
    <?php
}

submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='overview-display-settings'")); ?>
