<script type="text/javascript">
    function ToggleShowHitsOptions() {
        jQuery('[id^="wps_show_hits_option"]').toggle();
    }

    function ToggleBypassAdBlockers() {
        var trackingMethod = jQuery('#use_cache_plugin').val();
        var bypassAdBlockersRow = jQuery('#bypass_ad_blockers_row');
        var bypassAdBlockersCheckbox = jQuery('#bypass_ad_blockers');

        if (trackingMethod === '0') {
            bypassAdBlockersRow.hide();
            bypassAdBlockersCheckbox.prop('checked', false);
        } else {
            bypassAdBlockersRow.show();
        }
    }

    jQuery(document).ready(function() {
        jQuery('#use_cache_plugin').on('change', ToggleBypassAdBlockers);
        ToggleBypassAdBlockers(); // Initial check
    });
</script>


<div class="postbox">
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Tracking Options', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="useronline"><?php esc_html_e('Monitor Online Visitors', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo WP_STATISTICS\Option::get('useronline') == true ? "checked='checked'" : ''; ?>>
                    <label for="useronline"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Tracks and displays visitors currently online, including their activity duration. Disabling this option stops the online monitoring feature, but visitor tracking remains active.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top" data-view="visitors_log_tr">
                <th scope="row">
                    <label for="visitors_log">
                        <?php esc_html_e('Track Logged-In User Activity', 'wp-statistics'); ?>
                    </label>
                    <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                        <a href="#" class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                    <?php endif ?>
                </th>
                <td>
                    <input id="visitors_log" type="checkbox" value="1" name="wps_visitors_log" <?php echo WP_STATISTICS\Option::get('visitors_log') == true ? "checked='checked'" : ''; ?>>
                    <label for="visitors_log"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Tracks the activities of logged-in users, including page views, and records them with their WordPress User IDs for detailed insights into user behavior. If disabled, logged-in users are tracked anonymously, similar to other visitors.', 'wp-statistics'); ?></p>
                    <p class="description"><?php __('Note: Compliance with GDPR and other privacy regulations is essential. Inform users about data collection and usage through your privacy policy. For details on data handling and privacy, visit <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="store_ua"><?php esc_html_e('Store Entire User Agent String', 'wp-statistics'); ?></label>
                    <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                        <a href="#" class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                    <?php endif ?>
                </th>

                <td>
                    <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo WP_STATISTICS\Option::get('store_ua') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                    <label for="store_ua"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Records full details of visitors for diagnostic purposes. When \'Hash IP Addresses\' is operational, this feature is bypassed, and data collection is disabled to ensure privacy. Refer to our <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">avoiding PII data collection guide</a> for more information.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	  ?></p>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Tracker Configuration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr>
            <th scope="row">
                <label for="use_cache_plugin"><?php esc_html_e('Tracking Method', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select id="use_cache_plugin" name="wps_use_cache_plugin" onClick="ToggleBypassAdBlockers()">
                    <option value="1" <?php echo WP_STATISTICS\Option::get('use_cache_plugin') ? "selected='selected'" : ''; ?>>
                        <?php esc_html_e('Client Side Tracking (Recommended)', 'wp-statistics'); ?>
                    </option>
                    <option value="0" <?php echo !WP_STATISTICS\Option::get('use_cache_plugin') ? "selected='selected'" : ''; ?>>
                        <?php esc_html_e('Server Side Tracking (Deprecated)', 'wp-statistics'); ?>
                    </option>
                </select>
                <p class="description"><?php _e('Client Side Tracking uses the visitor’s browser for better accuracy and <b>caching compatibility</b>. Server Side Tracking is less accurate and will be deprecated. Client Side Tracking is strongly recommended. <a href="https://wp-statistics.com/2024/07/24/deprecating-server-side-tracking-in-wp-statistics-15/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Learn more</a>', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="bypass_ad_blockers_row">
            <th scope="row">
                <label for="bypass_ad_blockers"><?php esc_html_e('Bypass Ad Blockers', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="bypass_ad_blockers" type="checkbox" value="1" name="wps_bypass_ad_blockers" <?php echo WP_STATISTICS\Option::get('bypass_ad_blockers') == true ? "checked='checked'" : ''; ?>>
                <label for="bypass_ad_blockers"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Search Engine Tracking', 'wp-statistics'); ?></h3></th>
        </tr>

        <?php
        $se_option_list = '';

        foreach ($selist as $se) {
            $option_name    = 'wps_disable_se_' . $se['tag'];
            $store_name     = 'disable_se_' . $se['tag'];
            $se_option_list .= $option_name . ',';
            ?>

            <tr valign="top">
                <th scope="row">
                    <label for="<?php echo esc_attr($option_name); ?>"><?php echo esc_attr($se['name']); ?></label>
                </th>
                <td>
                    <input id="<?php echo esc_attr($option_name); ?>" type="checkbox" value="1" name="<?php echo esc_attr($option_name); ?>" <?php echo WP_STATISTICS\Option::get($store_name) == '1' ? '' : "checked='checked'"; ?>><label for="<?php echo esc_attr($option_name); ?>"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php echo esc_attr(sprintf(__('Track and report visits referred from %s.', 'wp-statistics'), $se['name'])); ?></p>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='general-settings'")); ?>
