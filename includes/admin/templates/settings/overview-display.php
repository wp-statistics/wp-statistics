<?php
// Only display the global options if the user is an administrator.
if ($wps_admin) {
    ?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Dashboard', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php _e('The following items are global to all users.', 'wp-statistics'); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php _e('Disable dashboard widgets:', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == true ? "checked='checked'" : ''; ?>>
                <label for="disable-dashboard"><?php _e('Disable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Disable the dashboard widgets.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Map', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php _e('The following items are global to all users.', 'wp-statistics'); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php _e('Disable map:', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo WP_STATISTICS\Option::get('disable_map') == true ? "checked='checked'" : ''; ?>>
                <label for="disable-map"><?php _e('Disable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Disable the map display', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
    <?php
}

submit_button(__('Update', 'wp-statistics'), 'primary', 'submit');
