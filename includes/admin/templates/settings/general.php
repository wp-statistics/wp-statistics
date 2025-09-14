<?php

use WP_STATISTICS\Menus;

?>

<h2 class="wps-settings-box__title">
    <span><?php esc_html_e('General', 'wp-statistics'); ?></span>
    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources/general-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
</h2>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Tracking Options', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr data-id="monitor_online_visitors_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Monitor Online Visitors', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo WP_STATISTICS\Option::get('useronline') == true ? "checked='checked'" : ''; ?>>
                <label for="useronline"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Tracks and displays visitors currently online, including their activity duration. Disabling this option stops the online monitoring feature, but visitor tracking remains active.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr data-id="track_logged_in_user_activity_tr" data-view="visitors_log_tr">
            <th scope="row">
                <span class="wps-setting-label">
                    <span>
                        <?php esc_html_e('Track Logged-In User Activity', 'wp-statistics'); ?>
                        <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                            <a class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                        <?php endif ?>
                    </span>
                </span>
            </th>
            <td>
                <input id="visitors_log" type="checkbox" value="1" name="wps_visitors_log" <?php echo WP_STATISTICS\Option::get('visitors_log') == true ? "checked='checked'" : ''; ?>>
                <label for="visitors_log"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Tracks the activities of logged-in users, including page views, and records them with their WordPress User IDs for detailed insights into user behavior. If disabled, logged-in users are tracked anonymously, similar to other visitors.', 'wp-statistics'); ?></p>
                <p class="description"><?php __('Note: Compliance with GDPR and other privacy regulations is essential. Inform users about data collection and usage through your privacy policy. For details on data handling and privacy, visit <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
            </td>
        </tr>

        <tr data-id="store_ua_tr">
            <th scope="row">
                <span class="wps-setting-label">
                    <span>
                        <?php esc_html_e('Store Entire User Agent String', 'wp-statistics'); ?>
                        <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                            <a class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                        <?php endif ?>
                    </span>
                </span>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo WP_STATISTICS\Option::get('store_ua') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>>
                <label for="store_ua"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Records full details of visitors for diagnostic purposes. When \'Hash IP Addresses\' is operational, this feature is bypassed, and data collection is disabled to ensure privacy. Refer to our <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">avoiding PII data collection guide</a> for more information.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	  ?></p>
            </td>
        </tr>

        <tr data-id="attribution_model_tr">
            <th scope="row">
                <label for="attribution_model"><?php esc_html_e('Attribution Model', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select id="attribution_model" name="wps_attribution_model">
                    <option value="first-touch" <?php selected(WP_STATISTICS\Option::get('attribution_model', 'first-touch'), 'first-touch'); ?>>
                        <?php esc_html_e('First-Touch', 'wp-statistics'); ?>
                    </option>
                    <option value="last-touch" <?php selected(WP_STATISTICS\Option::get('attribution_model'), 'last-touch'); ?>>
                        <?php esc_html_e('Last-Touch', 'wp-statistics'); ?>
                    </option>
                </select>
                <p class="description"><?php _e('Select how conversions are attributed: First-Touch credits the first interaction, and Last-Touch credits the most recent. <a href="https://wp-statistics.com/resources/attribution-models/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Learn more</a>.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Tracker Configuration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr data-id="tracking_method_tr">
            <th scope="row">
                 <label for="wps_settings[use_cache_plugin]">
                     <span>
                         <?php esc_html_e('Tracking Method', 'wp-statistics'); ?>
                     </span>
                     <span class="wps-badge wps-badge--deprecated"><?php esc_html_e('DEPRECATED', 'wp-statistics'); ?></span>
                 </label>
            </th>

            <td>
                <select id="wps_settings[use_cache_plugin]" name="wps_use_cache_plugin">
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

        <tr class="js-wps-show_if_use_cache_plugin_equal_1" data-id="bypass_ad_blockers_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Bypass Ad Blockers', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input id="bypass_ad_blockers" type="checkbox" value="1" name="wps_bypass_ad_blockers" <?php echo WP_STATISTICS\Option::get('bypass_ad_blockers') == true ? "checked='checked'" : ''; ?>>
                <label for="bypass_ad_blockers"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Dynamically load the tracking script with a unique name and address to bypass ad blockers.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr class="js-wps-show_if_use_cache_plugin_equal_1" data-id="tracker_debugger_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Tracker Debugger', 'wp-statistics'); ?></span>
            </th>

            <td>
                <a class=" wps-button wps-button--default" href="<?php echo esc_url(Menus::admin_url('wps_tracker-debugger_page')); ?>"><?php esc_html_e('Open Debugger', 'wp-statistics'); ?></a>
                <p class="description"><?php esc_html_e('Use the Tracker Debugger to inspect and troubleshoot your tracking script, ensuring accurate data collection.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('id' => 'general_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='general-settings'")); ?>
