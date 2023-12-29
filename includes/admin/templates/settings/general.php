<script type="text/javascript">
    function ToggleShowHitsOptions() {
        jQuery('[id^="wps_show_hits_option"]').fadeToggle();
    }
</script>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('User Presence Monitoring', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="useronline"><?php _e('Display Online Users', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo WP_STATISTICS\Option::get('useronline') == true ? "checked='checked'" : ''; ?>>
                <label for="useronline"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Enables the feature to display current online users on the site.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="check_online"><?php _e('Frequency of Online User Checks', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="small-text code" id="check_online" name="wps_check_online" value="<?php echo esc_attr(WP_STATISTICS\Option::get('check_online')); ?>"/>
                <?php _e('Seconds', 'wp-statistics'); ?>
                <p class="description"><?php _e('Defines how often the plugin checks for online users. For example, 120 seconds means it checks every 2 minutes.', 'wp-statistics') ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="allonline"><?php _e('Record All Traffic', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="allonline" type="checkbox" value="1" name="wps_all_online" <?php echo WP_STATISTICS\Option::get('all_online') == true ? "checked='checked'" : ''; ?>>
                <label for="allonline"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Use this to troubleshoot or get a full visitor count. May include bots or duplicate users.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Visitor Analytics', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="visits"><?php _e('Track Page Views', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="visits" type="checkbox" value="1" name="wps_visits" <?php echo WP_STATISTICS\Option::get('visits') == true ? "checked='checked'" : ''; ?>>
                <label for="visits"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Enable to keep track of how many times each page on your site is visited.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="visitors_tr">
            <th scope="row">
                <label for="visitors"><?php _e('Monitor Unique Visitors', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="visitors" type="checkbox" value="1" name="wps_visitors" <?php echo WP_STATISTICS\Option::get('visitors') == true ? "checked='checked'" : ''; ?>>
                <label for="visitors"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Tracks individual users to determine how many unique visitors you have.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" data-view="visitors_log_tr" <?php echo(WP_STATISTICS\Option::get('visitors') == false ? 'style="display:none;"' : '') ?>>
            <th scope="row">
                <label for="visitors_log"><?php _e('Track Page Visits', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="visitors_log" type="checkbox" value="1" name="wps_visitors_log" <?php echo WP_STATISTICS\Option::get('visitors_log') == true ? "checked='checked'" : ''; ?>>
                <label for="visitors_log"><?php _e('Enable Tracking', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Switch on to track and generate reports on individual user page visits, providing insights into user engagement on your site.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" data-view="visitors_log_tr" <?php echo(WP_STATISTICS\Option::get('visitors') == false ? 'style="display:none;"' : '') ?>>
            <th scope="row">
                <label for="enable_user_column"><?php _e('Display User Visit Logs', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="enable_user_column" type="checkbox" value="1" name="wps_enable_user_column" <?php echo WP_STATISTICS\Option::get('enable_user_column') == true ? "checked='checked'" : ''; ?>>
                <label for="enable_user_column"><?php _e('Show Visit Logs', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Enable to add a column in the WordPress admin\'s user list, displaying a log of user visits for easy access and review.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Content Engagement Metrics', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="pages"><?php _e('Count Page Visits', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="pages" type="checkbox" value="1" name="wps_pages" <?php echo WP_STATISTICS\Option::get('pages') == true ? "checked='checked'" : ''; ?>>
                <label for="pages"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Activate to get a count of visits for each individual page.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="all_pages"><?php _e('Monitor All Content Types', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="all_pages" type="checkbox" value="1" name="wps_track_all_pages" <?php echo WP_STATISTICS\Option::get('track_all_pages') == true ? "checked='checked'" : ''; ?>>
                <label for="all_pages"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('This setting captures data from every content type, not just standard posts and pages.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>
<?php
if (!$disable_strip_uri_parameters) {
    ?>
    <div class="postbox">
        <table class="form-table">
            <tbody>
            <tr valign="top">
                <th scope="row" colspan="2"><h3><?php _e('URL Simplification', 'wp-statistics'); ?></h3></th>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="strip_uri_parameters"><?php _e('Strip URL Parameters', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="strip_uri_parameters" type="checkbox" value="1" name="wps_strip_uri_parameters" <?php echo WP_STATISTICS\Option::get('strip_uri_parameters') == true ? "checked='checked'" : ''; ?>>
                    <label for="strip_uri_parameters"><?php _e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Activating this will remove any parameters after \'?\' in URLs, simplifying your statistics.', 'wp-statistics'); ?></p>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
    <?php
}
?>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('User Interface Preferences', 'wp-statistics'); ?></h3></th>
        </tr>
        <tr valign="top">
            <th scope="row">
                <label for="disable-editor"><?php _e('Visits Chart Metabox', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo WP_STATISTICS\Option::get('disable_editor') == true ? "checked='checked'" : ''; ?>>
                <label for="disable-editor"><?php _e('Disable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Provides a graphical representation of hits for each content when editing.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable_column"><?php _e('Visits Column', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="disable_column" type="checkbox" value="1" name="wps_disable_column" <?php echo WP_STATISTICS\Option::get('disable_column') == true ? "checked='checked'" : ''; ?>>
                <label for="disable_column"><?php _e('Disable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('This will display how many times each content has been viewed directly in your content list.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hit_post_metabox"><?php _e('Visits in Publish Metabox', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="hit_post_metabox" type="checkbox" value="1" name="wps_hit_post_metabox" <?php echo WP_STATISTICS\Option::get('hit_post_metabox') == true ? "checked='checked'" : ''; ?>>
                <label for="hit_post_metabox"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Provides a quick view of hits right in the publish box of each content.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="show_hits"><?php _e('Visits in Single Pages', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="show_hits" type="checkbox" value="1" name="wps_show_hits" <?php echo WP_STATISTICS\Option::get('show_hits') == true ? "checked='checked'" : ''; ?> onClick='ToggleShowHitsOptions();'>
                <label for="show_hits"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('This will show the number of views directly within your content for visitors to see.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <?php if (WP_STATISTICS\Option::get('show_hits')) {
            $hidden = "";
        } else {
            $hidden = " style='display: none;'";
        } ?>
        <tr valign="top"<?php echo $hidden; ?> id='wps_show_hits_option'>
            <th scope="row" style="vertical-align: top;">
                <label for="display_hits_position"><?php _e('Display position', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_display_hits_position" id="display_hits_position">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), '0'); ?>><?php _e('Please select', 'wp-statistics'); ?></option>
                    <option value="before_content" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), 'before_content'); ?>><?php _e('Before Content', 'wp-statistics'); ?></option>
                    <option value="after_content" <?php selected(WP_STATISTICS\Option::get('display_hits_position'), 'after_content'); ?>><?php _e('After Content', 'wp-statistics'); ?></option>
                </select>
                <p class="description"><?php _e('Choose the position to show Visits.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Cache Integration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="use_cache_plugin"><?php _e('Cache Compatibility Mode', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="use_cache_plugin" type="checkbox" value="1" name="wps_use_cache_plugin" <?php echo WP_STATISTICS\Option::get('use_cache_plugin') == true ? "checked='checked'" : ''; ?>>
                <label for="use_cache_plugin"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('If you\'re using a caching plugin or service, enable this to ensure WP Statistics records data correctly.', 'wp-statistics'); ?></p>
                <p class="description">
                <ul>
                    <li><?php echo sprintf(__('To register WP Statistics REST API endpoint ( %s ) , go to the <a href="%s">Permalink page</a> and update the permalink by pressing Save Changes and then clear the cache.', 'wp-statistics'), WP_STATISTICS\RestAPI::$namespace, admin_url('options-permalink.php')); ?></li>
                    <li>
                        <?php echo __('To prevent Google index the REST API endpoints, add the below code in <strong>robots.txt</strong>', 'wp-statistics'); ?><br/>
                        <br/><code>User-Agent: * <?php echo PHP_EOL; ?></code>
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
            <th scope="row" colspan="2"><h3><?php _e('Admin Interface Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="menu-bar"><?php _e('Show Stats in Admin Menu Bar', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_menu_bar" id="menu-bar">
                    <option value="1" <?php selected(WP_STATISTICS\Option::get('menu_bar'), '1'); ?>><?php _e('Yes', 'wp-statistics'); ?></option>
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('menu_bar'), '0'); ?>><?php _e('No', 'wp-statistics'); ?></option>
                </select>
                <p class="description"><?php _e('This will add a quick-access link to your statistics in the WordPress admin menu bar.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hide_notices"><?php _e('Hide Admin Notices About Non-active Features', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="hide_notices" type="checkbox" value="1" name="wps_hide_notices" <?php echo WP_STATISTICS\Option::get('hide_notices') == true ? "checked='checked'" : ''; ?>>
                <label for="hide_notices"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('If a feature is turned off, this will prevent notifications about it.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Search Engine Handling', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="addsearchwords"><?php _e('Add Page Title to Empty Search Words', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="addsearchwords" type="checkbox" value="1" name="wps_addsearchwords" <?php echo WP_STATISTICS\Option::get('addsearchwords') == true ? "checked='checked'" : ''; ?>>
                <label for="addsearchwords"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('If a visitor arrives from a search engine without a clear query, this title will be used instead.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Search Engine Filters', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php _e('Exclusions allow you to stop tracking data from specific search engines. Here are the search engines you can exclude:', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
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
                    <input id="<?php echo esc_attr($option_name); ?>" type="checkbox" value="1" name="<?php echo esc_attr($option_name); ?>" <?php echo WP_STATISTICS\Option::get($store_name) == true ? "checked='checked'" : ''; ?>><label for="<?php echo esc_attr($option_name); ?>"><?php _e('Disable', 'wp-statistics'); ?></label>
                    <p class="description"><?php echo sprintf(__('Disable %s from data collection and reporting.', 'wp-statistics'), esc_attr($se['name'])); ?></p>
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
            <th scope="row" colspan="2"><h3><?php _e('Graphical Data Presentation', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="chart-totals"><?php _e('Include Totals in Charts', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="chart-totals" type="checkbox" value="1" name="wps_chart_totals" <?php echo WP_STATISTICS\Option::get('chart_totals') == true ? "checked='checked'" : ''; ?>>
                <label for="chart-totals"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('For charts that represent multiple values, this will include a total sum at the bottom.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='general-settings'")); ?>
