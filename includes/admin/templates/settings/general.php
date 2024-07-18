<script type="text/javascript">
    function ToggleShowHitsOptions() {
        jQuery('[id^="wps_show_hits_option"]').fadeToggle();
    }
</script>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('User Presence Monitoring', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="useronline"><?php esc_html_e('Display Online Users', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo WP_STATISTICS\Option::get('useronline') == true ? "checked='checked'" : ''; ?>>
                <label for="useronline"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Shows current online users on the site.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Visitor Analytics', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top" data-view="visitors_log_tr">
            <th scope="row">
                <label for="visitors_log">
                    <?php esc_html_e('Record User Page Views', 'wp-statistics'); ?>
                </label>
                <?php if (\WP_STATISTICS\Option::get('privacy_audit')): ?>
                    <a href="#" class="wps-tooltip" title="<?php esc_html_e('Privacy Impact - This setting affects user privacy. Adjust with caution to ensure compliance with privacy standards. For more details, visit the Privacy Audit page.', 'wp-statistics') ?>"><i class="wps-tooltip-icon privacy"></i></a>
                <?php endif ?>
            </th>
            <td>
                <input id="visitors_log" type="checkbox" value="1" name="wps_visitors_log" <?php echo WP_STATISTICS\Option::get('visitors_log') == true ? "checked='checked'" : ''; ?>>
                <label for="visitors_log"><?php esc_html_e('Track User Activity', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Logs each visit made by signed-in users, along with their user IDs, to provide a detailed view of page traffic and user engagement.', 'wp-statistics'); ?></p>
                <p class="description"><?php __('Note: Compliance with GDPR and other privacy regulations is essential. Inform users about data collection and usage through your privacy policy. For details on data handling and privacy, visit <a href="https://wp-statistics.com/resources/avoiding-pii-data-collection/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Avoiding PII Data Collection</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
            </td>
        </tr>

        <tr>
            <th scope="row">
                <label for="use_cache_plugin"><?php esc_html_e('Tracking Method', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select id="use_cache_plugin" name="wps_use_cache_plugin">
                    <option value="1" <?php echo WP_STATISTICS\Option::get('use_cache_plugin') ? "selected='selected'" : ''; ?>>
                        <?php esc_html_e('Client Side Tracking (Recommended)', 'wp-statistics'); ?>
                    </option>
                    <option value="0" <?php echo !WP_STATISTICS\Option::get('use_cache_plugin') ? "selected='selected'" : ''; ?>>
                        <?php esc_html_e('Server Side Tracking (Deprecated)', 'wp-statistics'); ?>
                    </option>
                </select>
                <p class="description"><?php esc_html_e('Client Side Tracking uses the visitor’s browser for better accuracy and compatibility with caching methods and plugins. Server Side Tracking is less accurate and will be deprecated. Client Side Tracking is strongly recommended.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
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
            <th scope="row" colspan="2"><h3><?php esc_html_e('User Interface Preferences', 'wp-statistics'); ?></h3></th>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="disable-editor"><?php esc_html_e('Chart Metabox Views', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo WP_STATISTICS\Option::get('disable_editor') == '1' ? '' : "checked='checked'"; ?>>
                <label for="disable-editor"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Shows content view statistics in a graphical format when editing.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable_column"><?php esc_html_e('Views Column', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="disable_column" type="checkbox" value="1" name="wps_disable_column" <?php echo WP_STATISTICS\Option::get('disable_column') == '1' ? '' : "checked='checked'"; ?>>
                <label for="disable_column"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Displays the number of views for each content item in your content list.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" data-view="visitors_log_tr">
            <th scope="row">
                <label for="enable_user_column"><?php esc_html_e('Show User Views', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="enable_user_column" type="checkbox" value="1" name="wps_enable_user_column" <?php echo WP_STATISTICS\Option::get('enable_user_column') == true ? "checked='checked'" : ''; ?>>
                <label for="enable_user_column"><?php esc_html_e('Show View Logs', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Displays the number of page views for each WordPress user in the admin user list. Requires "Track User Activity" to be enabled.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="show_hits"><?php esc_html_e('Views in Single Contents', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="show_hits" type="checkbox" value="1" name="wps_show_hits" <?php echo WP_STATISTICS\Option::get('show_hits') ? "checked='checked'" : ''; ?> onClick='ToggleShowHitsOptions();'>
                <label for="show_hits"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Shows the view count on the content\'s page for visitor insight.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php esc_html_e('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == '1' ? "" : "checked='checked'"; ?>>
                <label for="disable-dashboard"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('View WP Statistics widgets in the WordPress dashboard.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" <?php echo WP_STATISTICS\Option::get('show_hits') ? 'style="display: table-row"' : 'style="display: none"' ?> id='wps_show_hits_option'>
            <th scope="row" style="vertical-align: top;">
                <label for="display_hits_position"><?php esc_html_e('Display position', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_display_hits_position" id="display_hits_position">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), '0'); ?>><?php esc_html_e('Please select', 'wp-statistics'); ?></option>
                    <option value="before_content" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), 'before_content'); ?>><?php esc_html_e('Before Content', 'wp-statistics'); ?></option>
                    <option value="after_content" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), 'after_content'); ?>><?php esc_html_e('After Content', 'wp-statistics'); ?></option>
                </select>
                <p class="description"><?php esc_html_e('Choose the position to show views.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Admin Interface Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="menu-bar"><?php esc_html_e('Show Stats in Admin Menu Bar', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="menu-bar" type="checkbox" value="1" name="wps_menu_bar" <?php echo WP_STATISTICS\Option::get('menu_bar') == true ? "checked='checked'" : ''; ?>>
                <label for="menu-bar"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('View your site\'s statistics directly from the WordPress admin menu bar.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hide_notices"><?php esc_html_e('Disable Inactive Essential Feature Notices', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="hide_notices" type="checkbox" value="1" name="wps_hide_notices" <?php echo WP_STATISTICS\Option::get('hide_notices') == true ? "checked='checked'" : ''; ?>>
                <label for="hide_notices"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Stops displaying messages for essential features that are currently switched off.', 'wp-statistics'); ?></p>
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
