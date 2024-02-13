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
                <p class="description"><?php echo __('Enable this setting to anonymize user IP addresses by masking the last part of the IP. This modification is crucial for privacy preservation and is a requirement under the GDPR for protecting user data. Anonymization helps ensure that a user’s full IP address is not stored or processed, thus maintaining their anonymity.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e('Hash IP Addresses', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo WP_STATISTICS\Option::get('hash_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="hash_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('When activated, this feature will hash IP addresses using a secure algorithm before they are stored. Hashing is a form of pseudonymization that transforms the IP address into a unique string of characters, which cannot be reversed to reveal the original IP. This process provides an additional layer of security and is in line with data protection regulations such as GDPR, which encourage the use of pseudonymization to protect personal data.', 'wp-statistics') . ' ' . __('Also, it disables the "Store entire user agent string" setting.', 'wp-statistics'); ?></p>
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
                <p class="description"><?php _e('This setting is intended for use in troubleshooting and should only be enabled temporarily for debugging. It records the full user agent string, which contains details about the user’s browser and operating system. Please note that if “Hash IP Addresses” is enabled to protect user privacy, this setting will be turned off to prevent the storage of potentially identifiable information.', 'wp-statistics'); ?></p>
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
                <label for="do_not_track"><?php _e('Do Not Track (DNT)', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="do_not_track" type="checkbox" value="1" name="wps_do_not_track" <?php echo WP_STATISTICS\Option::get('do_not_track') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="do_not_track"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e("If this option is on, the plugin will not track visitors who set their browser to “Do Not Track”. This isn’t required by privacy laws like GDPR, but it’s a way to show that we value privacy. Keep in mind that turning this on means you won’t get information from visitors who don’t want to be tracked.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>
