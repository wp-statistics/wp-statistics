<?php
use WP_STATISTICS\Option;
?>
<h2 class="wps-settings-box__title">
    <span><?php esc_html_e('Display Options', 'wp-statistics'); ?></span>
    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources/display-options-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
</h2>

<div class="postbox">
    <table class="form-table">
        <tbody>
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Admin Interface', 'wp-statistics'); ?></h3></th>
            </tr>
            <tr data-id="visitors_stats_in_editor_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('View Stats in Editor', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo WP_STATISTICS\Option::get('disable_editor') == '1' ? '' : "checked='checked'"; ?>>
                    <label for="disable-editor"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Show a summary of content view statistics in the post editor.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="views_column_in_content_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Views Column in Content List', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <input id="disable_column" type="checkbox" value="1" name="wps_disable_column" <?php echo WP_STATISTICS\Option::get('disable_column') == '1' ? '' : "checked='checked'"; ?>>
                    <label for="disable_column"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display the "Views" column in the content list menus, showing the page view counts for content across all post types.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="views_column_user_list_tr" data-view="visitors_log_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Views Column in User List', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <input id="enable_user_column" type="checkbox" value="1" name="wps_enable_user_column" <?php echo WP_STATISTICS\Option::get('enable_user_column') == true ? "checked='checked'" : ''; ?>>
                    <label for="enable_user_column"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Display the "Views" column in the admin user list, showing the view count related to each logged-in WordPress user. Requires "Track Logged-In User Activity" to be enabled.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="show_stats_admin_menu_bar_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Show Stats in Admin Menu Bar', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="menu-bar" type="checkbox" value="1" name="wps_menu_bar" <?php echo WP_STATISTICS\Option::get('menu_bar') == true ? "checked='checked'" : ''; ?>>
                    <label for="menu-bar"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('View your site\'s statistics directly from the WordPress admin menu bar.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="previous_period_charts_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Previous Period in Charts', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="charts_previous_period" type="checkbox" value="1" name="wps_charts_previous_period" <?php checked(Option::get('charts_previous_period', 1)) ?>>
                    <label for="charts_previous_period"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Show data from the previous period in charts for comparison.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="statistics_widgets_dashboard_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == '1' ? "" : "checked='checked'"; ?>>
                    <label for="disable-dashboard"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('View WP Statistics widgets in the WordPress dashboard.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="notifications_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('WP Statistics Notifications', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="display-notifications" type="checkbox" value="1" name="wps_display_notifications" <?php checked(Option::get('display_notifications')); ?>>
                    <label for="display-notifications"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display important notifications inside the plugin, such as new version releases, feature updates, news, and special offers.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="disable_inactive_notices_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Disable Inactive Essential Feature Notices', 'wp-statistics'); ?></span>
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
            <tr class="wps-settings-box_head">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Frontend Display', 'wp-statistics'); ?></h3></th>
            </tr>

            <tr data-id="views_single_contents_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Views in Single Contents', 'wp-statistics'); ?></span>
                </th>

                <td>
                    <input id="wps_settings[show_hits]" type="checkbox" value="1" name="wps_show_hits" <?php echo WP_STATISTICS\Option::get('show_hits') ? "checked='checked'" : ''; ?> >
                    <label for="wps_settings[show_hits]"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Shows the view count on the content\'s page for visitor insight.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr data-id="display_position_tr" class="js-wps-show_if_show_hits_enabled" <?php echo WP_STATISTICS\Option::get('show_hits') ? 'style="display: table-row"' : 'style="display: none"' ?> id='wps_show_hits_option'>
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

<?php submit_button(__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='display-settings'")); ?>
