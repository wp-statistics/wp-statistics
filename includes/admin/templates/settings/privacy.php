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
                <p class="description"><?php echo __('Masks the last segment of a user\'s IP address for privacy, complying with GDPR and preventing the full IP from being stored. More details can be found at <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection.</a>', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e('Hash IP Addresses', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo WP_STATISTICS\Option::get('hash_ips') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="hash_ips"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php echo __('Transforms IP addresses into a unique, non-reversible string using a secure algorithm, enhancing privacy protection and complying with data privacy regulations. For an in-depth explanation, refer to <a href="https://wp-statistics.com/resources/counting-unique-visitors-without-cookies/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="blank">Counting Unique Visitors Without Cookies</a>.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Debugging & Advanced Options', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="store_ua"><?php _e('Store Entire User Agent String', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo WP_STATISTICS\Option::get('store_ua') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="store_ua"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Records full details of visitors for diagnostic purposes. When \'Hash IP Addresses\' is operational, this feature is bypassed, and data collection is disabled to ensure privacy. Refer to our <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">avoiding PII data collection guide</a> for more information.', 'wp-statistics'); ?></p>
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
                <p class="description"><?php _e("Respects the visitor's browser setting to not track their web activity. Privacy laws like GDPR do not mandate this feature, but activating it demonstrates a commitment to privacy. Be aware that with DNT respected, information from visitors preferring not to be tracked will not be collected.", 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='privacy-settings'")); ?>
