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

        <tr valign="top">
            <th scope="row">
                <label for="check_online"><?php esc_html_e('Frequency of Online User Checks', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="number" class="code" id="check_online" name="wps_check_online" value="<?php echo esc_attr(WP_STATISTICS\Option::get('check_online')); ?>" style="width: 100px"/>
                <?php esc_html_e('Seconds', 'wp-statistics'); ?>
                <p class="description"><?php esc_html_e('Defines how often the plugin checks for online users. \'120 seconds\' means updates every 2 minutes.', 'wp-statistics') ?></p>
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

        <tr valign="top">
            <th scope="row">
                <label for="visits"><?php esc_html_e('Track Views', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="visits" type="checkbox" value="1" name="wps_visits" <?php echo WP_STATISTICS\Option::get('visits') == true ? "checked='checked'" : ''; ?>>
                <label for="visits"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Counts the number of times each page is visited.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="visitors_tr">
            <th scope="row">
                <label for="visitors"><?php esc_html_e('Monitor Unique Visitors', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="visitors" type="checkbox" value="1" name="wps_visitors" <?php echo WP_STATISTICS\Option::get('visitors') == true ? "checked='checked'" : ''; ?>>
                <label for="visitors"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Tracks individual users to determine how many unique visitors you have.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" data-view="visitors_log_tr" <?php echo(WP_STATISTICS\Option::get('visitors') == false ? 'style="display:none;"' : '') ?>>
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

        <tr valign="top" data-view="visitors_log_tr" <?php echo(WP_STATISTICS\Option::get('visitors') == false ? 'style="display:none;"' : '') ?>>
            <th scope="row">
                <label for="enable_user_column"><?php esc_html_e('Display User View Logs', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="enable_user_column" type="checkbox" value="1" name="wpsesc_html_enable_user_column" <?php echo WP_STATISTICS\Option::get('enable_user_column') == true ? "checked='checked'" : ''; ?>>
                <label for="enable_user_column"><?php esc_html_e('Show View Logs', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Adds a column in the WordPress admin\'s user list to display a log of user views.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Content Engagement Metrics', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="pages"><?php esc_html_e('Track Page Views', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="pages" type="checkbox" value="1" name="wps_pages" <?php echo WP_STATISTICS\Option::get('pages') == true ? "checked='checked'" : ''; ?>>
                <label for="pages"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Tracks how many times each individual page is visited.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="all_pages"><?php esc_html_e('Monitor All Content Types', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="all_pages" type="checkbox" value="1" name="wps_track_all_pages" <?php echo WP_STATISTICS\Option::get('track_all_pages') == true ? "checked='checked'" : ''; ?>>
                <label for="all_pages"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Tracks visitor data for custom post types in addition to standard posts and pages. To access more detailed statistics for custom post types, download the <a href="https://wp-statistics.com/product/wp-statistics-data-plus/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings">DataPlus add-on</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	 ?></p>
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

        <tr valign="top">
            <th scope="row">
                <label for="hit_post_metabox"><?php esc_html_e('Views Metabox', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="hit_post_metabox" type="checkbox" value="1" name="wps_hit_post_metabox" <?php echo WP_STATISTICS\Option::get('hit_post_metabox') == true ? "checked='checked'" : ''; ?>>
                <label for="hit_post_metabox"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Presents a snapshot of content views in the publish box for quick reference.', 'wp-statistics'); ?></p>
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

        <tr valign="top" <?php echo  WP_STATISTICS\Option::get('show_hits') ?   'style="display: table-row"' :   'style="display: none"' ?> id='wps_show_hits_option'>
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
            <th scope="row" colspan="2"><h3><?php esc_html_e('Cache Integration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="use_cache_plugin"><?php esc_html_e('Cache Compatibility Mode', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="use_cache_plugin" type="checkbox" value="1" name="wps_use_cache_plugin" <?php echo WP_STATISTICS\Option::get('use_cache_plugin') == true ? "checked='checked'" : ''; ?>>
                <label for="use_cache_plugin"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Ensures accurate statistics recording with caching plugins or services.', 'wp-statistics'); ?></p>
                <p class="description">
                <ul>
                    <li><?php echo esc_html(sprintf(__('Remember to update permalinks in the WP Statistics REST API settings and clear the cache for full integration.', 'wp-statistics'), WP_STATISTICS\RestAPI::$namespace, admin_url('options-permalink.php'))); ?></li>
                    <li>
                        <?php echo __('To keep Google from indexing REST API endpoints, add the specified code to your <strong>robots.txt</strong> file:', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?><br/>
                        <br/><code>User-Agent: * <?php echo PHP_EOL; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?></code>
                        <br/><code>Disallow: /wp-json</code>
                    </li>
                </ul>
                </p>
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
                <select name="wps_menu_bar" id="menu-bar">
                    <option value="1" <?php selected(WP_STATISTICS\Option::get('menu_bar'), '1'); ?>><?php esc_html_e('Yes', 'wp-statistics'); ?></option>
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('menu_bar'), '0'); ?>><?php esc_html_e('No', 'wp-statistics'); ?></option>
                </select>
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
            <th scope="row" colspan="2"><h3><?php esc_html_e('Search Engine Handling', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Search Engine Filters', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Select which search engines are permitted to gather and report usage data when visitors arrive at your site from these sources', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
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
                    <p class="description"><?php echo esc_attr(sprintf(__('Allow %s to collect and report data.', 'wp-statistics'), $se['name'])); ?></p>
                </td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Graphical Data Presentation', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="chart-totals"><?php esc_html_e('Include Totals in Charts', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="chart-totals" type="checkbox" value="1" name="wps_chart_totals" <?php echo WP_STATISTICS\Option::get('chart_totals') == true ? "checked='checked'" : ''; ?>>
                <label for="chart-totals"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('For charts that represent multiple values, show the combined amount of all items at the bottom.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='general-settings'")); ?>
