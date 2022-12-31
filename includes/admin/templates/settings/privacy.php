<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Privacy and Data Protection', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php echo sprintf(__('To delete visitor data, check out <b><a href="%s">Tools → Erase Personal Data</a></b>, and for delete all data, check out here <b><a href="%s">Optimization → Purging</a></b>.', 'wp-statistics'), admin_url('erase-personal-data.php'), esc_url(WP_STATISTICS\Menus::admin_url('optimization', array('tab' => 'purging')))); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="anonymize_ips"><?php _e('Anonymize IP Addresses:', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="anonymize_ips" type="checkbox" value="1" name="wps_anonymize_ips" <?php echo WP_STATISTICS\Option::get('anonymize_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="anonymize_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('This option anonymize the user IP address because of the data privacy & GDPR. For example, <code>888.888.888.888</code> -> <code>888.888.888.000</code>.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e('Hash IP Addresses:', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo WP_STATISTICS\Option::get('hash_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="hash_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('By enabling this option, you cannot recover the IP addresses in the future to find out location information, and IP addresses will not be stored in the database but instead used a unique hash.', 'wp-statistics') . ' ' . __('Also, it disables the "Store entire user agent string" setting.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="store_ua"><?php _e('Store Entire User Agent String:', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo WP_STATISTICS\Option::get('store_ua') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="store_ua"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Only enable it for debugging. If the IP hashes are enabled, this option will be disabled automatically.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="do_not_track"><?php _e('Do Not Track:', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="do_not_track" type="checkbox" value="1" name="wps_do_not_track" <?php echo WP_STATISTICS\Option::get('do_not_track') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="do_not_track"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e("Enabling the do not track mode will respect the user's browser settings for tracking protection. This means that the plugin will not collect or store any data about the user's visits to your website. Please note that this may impact the accuracy of your website's analytics.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>
