<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Data Protection', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php _e('Ensure your website adheres to data protection standards.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="anonymize_ips"><?php _e('Anonymize IP Addresses', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="anonymize_ips" type="checkbox" value="1" name="wps_anonymize_ips" <?php echo WP_STATISTICS\Option::get('anonymize_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="anonymize_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('By enabling this option, the user IP address will be anonymized (e.g., <code>888.888.888.***</code>). This is especially useful for GDPR compliance.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e('Hash IP Addresses', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo WP_STATISTICS\Option::get('hash_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="hash_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('By enabling this feature, IP addresses will be hashed before being stored, preventing future access to the original address. This will also disable the "Store Entire User Agent String" setting.', 'wp-statistics') . ' ' . __('Also, it disables the "Store entire user agent string" setting.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Debugging & Advanced Options', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php _e('Advanced settings for website administrators.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="store_ua"><?php _e('Store Entire User Agent String', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo WP_STATISTICS\Option::get('store_ua') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="store_ua"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('This option is recommended for debugging purposes only. If "Hash IP Addresses" is active, this setting will be automatically disabled.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('User Preferences', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php _e('Respect and prioritize your website visitors\' browsing preferences.', 'wp-statistics') ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="do_not_track"><?php _e('Do Not Track', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="do_not_track" type="checkbox" value="1" name="wps_do_not_track" <?php echo WP_STATISTICS\Option::get('do_not_track') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="do_not_track"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e("Enabling this will ensure that the plugin doesn't collect or store any data from users who have enabled the \"Do Not Track\" setting in their browsers. Note: This may affect the accuracy of your website's analytics.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>
