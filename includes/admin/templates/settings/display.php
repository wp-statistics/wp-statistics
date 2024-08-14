<div class="postbox">
    <table class="form-table">
        <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php esc_html_e('Admin Interface', 'wp-statistics'); ?></h3></th>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="disable-editor"><?php esc_html_e('View Stats in Editor', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo WP_STATISTICS\Option::get('disable_editor') == '1' ? '' : "checked='checked'"; ?>>
                    <label for="disable-editor"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Show a summary of content view statistics in the post editor.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="disable_column"><?php esc_html_e('Views Column in Content List', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <input id="disable_column" type="checkbox" value="1" name="wps_disable_column" <?php echo WP_STATISTICS\Option::get('disable_column') == '1' ? '' : "checked='checked'"; ?>>
                    <label for="disable_column"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('Display the "Views" column in the content list menus, showing the view counts for content across all post types.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top" data-view="visitors_log_tr">
                <th scope="row">
                    <label for="enable_user_column"><?php esc_html_e('Views Column in User List', 'wp-statistics'); ?></label>
                </th>
                <td>
                    <input id="enable_user_column" type="checkbox" value="1" name="wps_enable_user_column" <?php echo WP_STATISTICS\Option::get('enable_user_column') == true ? "checked='checked'" : ''; ?>>
                    <label for="enable_user_column"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Display the "Views" column in the admin user list, showing the page view counts associated with each WordPress user. Requires "<b>Track Logged-In User Activity</b>" to be enabled.', 'wp-statistics'); ?></p>
                </td>
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
                    <label for="disable-map"><?php esc_html_e('WP Statistics Widgets in the WordPress dashboard', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo WP_STATISTICS\Option::get('disable_dashboard') == '1' ? "" : "checked='checked'"; ?>>
                    <label for="disable-dashboard"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php esc_html_e('View WP Statistics widgets in the WordPress dashboard.', 'wp-statistics'); ?></p>
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
                <th scope="row" colspan="2"><h3><?php esc_html_e('Frontend Display', 'wp-statistics'); ?></h3></th>
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

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='display-settings'")); ?>
