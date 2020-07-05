<table class="form-table">
    <tbody>
    <tr valign="top">
        <th scope="row" colspan="2"><h3><?php _e('WP Statisitcs Reset Options', 'wp-statistics'); ?></h3></th>
    </tr>

    <tr valign="top">
        <th scope="row">
            <label for="reset-plugin"><?php _e('Reset options:', 'wp-statistics'); ?></label>
        </th>

        <td>
            <input id="reset-plugin" type="checkbox" name="wps_reset_plugin">
            <label for="reset-plugin"><?php _e('Reset', 'wp-statistics'); ?></label>
            <p class="description"><?php _e('Reset the plugin options to the defaults. This will remove all user and global settings but will keep all other data. This action cannot be undone. Note: For multisite installs this will reset all sites to the defaults.', 'wp-statistics'); ?></p>
        </td>
    </tr>

    </tbody>
</table>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit'); ?>