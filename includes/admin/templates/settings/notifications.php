<script type="text/javascript">
    function ToggleStatOptions() {
        jQuery('[id^="wps_stats_report_option"]').fadeToggle();
    }
</script>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Email Configuration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="email-report"><?php _e('Recipient Email Addresses', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input dir="ltr" type="text" id="email_list" name="wps_email_list" size="30" value="<?php if (WP_STATISTICS\Option::get('email_list') == '') {
                    $wp_statistics_options['email_list'] = get_bloginfo('admin_email');
                }
                echo esc_textarea(WP_STATISTICS\Option::get('email_list')); ?>"/>
                <p class="description"><?php _e('Specify the email addresses that should receive the reports. Separate multiple addresses with a comma.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="stats-report"><?php _e('Email Statistical Reports', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="stats-report" type="checkbox" value="1" name="wps_stats_report" <?php echo WP_STATISTICS\Option::get('stats_report') == true ? "checked='checked'" : ''; ?> onClick='ToggleStatOptions();'>
                <label for="stats-report"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Receive regular statistical reports in your inbox.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Database Notifications', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-report"><?php _e('GeoIP Update', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-report" type="checkbox" value="1" name="wps_geoip_report" <?php echo WP_STATISTICS\Option::get('geoip_report') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-report"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Receive notifications when the GeoIP database updates.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="prune-report"><?php _e('Database Pruning Alert', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="prune-report" type="checkbox" value="1" name="wps_prune_report" <?php echo WP_STATISTICS\Option::get('prune_report') == true ? "checked='checked'" : ''; ?>>
                <label for="prune-report"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Get notified when the database pruning occurs.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Admin Dashboard Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="admin-notices"><?php _e('Display All WP Statistics Notices', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="admin-notices" type="checkbox" value="1" name="wps_admin_notices" <?php echo WP_STATISTICS\Option::get('admin_notices') == true ? "checked='checked'" : ''; ?>>
                <label for="admin-notices"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Enable this to see all notifications, alerts, and suggestions from WP Statistics in your admin dashboard. If disabled, only critical warnings or errors will be shown, keeping your dashboard less cluttered.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<?php if (WP_STATISTICS\Option::get('stats_report')) {
    $hidden = "";
} else {
    $hidden = " style='display: none;'";
} ?>
<div class="postbox"<?php echo $hidden; ?> id='wps_stats_report_option'>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Advanced Reporting Options', 'wp-statistics'); ?></h3></th>
        </tr>
        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="time-report"><?php _e('Reporting Schedule', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_time_report" id="time-report">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('time_report'), '0'); ?>><?php _e('Please select', 'wp-statistics'); ?></option>
                    <?php
                    function wp_statistics_schedule_sort($a, $b)
                    {
                        if ($a['interval'] == $b['interval']) {
                            return 0;
                        }
                        return ($a['interval'] < $b['interval']) ? -1 : 1;
                    }

                    //Get List Of Schedules Wordpress
                    $schedules = wp_get_schedules();
                    uasort($schedules, 'wp_statistics_schedule_sort');
                    $schedules_item = array();

                    foreach ($schedules as $key => $value) {
                        if (!in_array($value, $schedules_item)) {
                            echo '<option value="' . esc_attr($key) . '" ' . selected(WP_STATISTICS\Option::get('time_report'), $key) . '>' . esc_attr($value['display']) . '</option>';
                            $schedules_item[] = $value;
                        }
                    }
                    ?>
                </select>
                <p class="description"><?php _e('Report Timing: Choose how frequently you want your statistical reports to be compiled and sent. Options range from hourly to monthly.', 'wp-statistics'); ?></p>
                <p class="description"><?php echo sprintf(__('For setting up a custom schedule, please refer to our <a href="%s" target="_blank">documentation</a>.', 'wp-statistics'), 'https://wp-statistics.com/resources/schedule-statistical-reports/'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="send-report"><?php _e('Delivery Method', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_send_report" id="send-report">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('send_report'), '0'); ?>><?php _e('Please select', 'wp-statistics'); ?></option>
                    <option value="mail" <?php selected(WP_STATISTICS\Option::get('send_report'), 'mail'); ?>><?php _e('Email', 'wp-statistics'); ?></option>
                    <?php if (is_plugin_active('wp-sms/wp-sms.php') || is_plugin_active('wp-sms-pro/wp-sms.php')) { ?>
                        <option value="sms" <?php selected(WP_STATISTICS\Option::get('send_report'), 'sms'); ?>><?php _e('SMS', 'wp-statistics'); ?></option>
                    <?php } ?>
                </select>

                <p class="description"><?php _e('Delivery Channel: Select how you would like to receive the generated reports. Currently, reports can be sent to you via email or downloaded directly from the plugin.', 'wp-statistics'); ?></p>
                <?php if (!is_plugin_active('wp-sms/wp-sms.php')) { ?>
                    <p class="description">
                        <span class="wps-note"><?php _e('Note:', 'wp-statistics'); ?></span>
                        <?php echo sprintf(__('To send SMS text messages please install the %s plugin.', 'wp-statistics'), '<a href="http://wordpress.org/extend/plugins/wp-sms/" target="_blank">' . __('WP SMS', 'wp-statistics') . '</a>'); ?>
                    </p>
                <?php } ?>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="content-report"><?php _e('Report Composition', 'wp-statistics'); ?></label>
            </th>

            <td>
                <?php wp_editor(WP_STATISTICS\Option::get('content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5)); ?>
                <p class="description"><?php _e('Report Content: Customize the content of your reports by using WP Statistics shortcodes to display various statistics.', 'wp-statistics'); ?></p>

                <p class="description data">
                    <?php _e('Insert any of the following shortcode examples to show corresponding data:', 'wp-statistics'); ?>
                    <br><br>
                    <?php _e('Current Online Users', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=usersonline]</code><br>
                    <?php _e('Today\'s Visits', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=today]</code><br>
                    <?php _e('Today\'s Visits', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=today]</code><br>
                    <?php _e('Yesterday\'s Visitors', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=yesterday]</code><br>
                    <?php _e('Yesterday\'s Visits', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=yesterday]</code><br>
                    <?php _e('Total Visitors', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=total]</code><br>
                    <?php _e('Total Visits', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=total]</code><br>
                </p>
                <p class="description"><?php echo sprintf(__('Refer to our complete  <a href="%s" target="_blank">shortcode guide</a> for more options.', 'wp-statistics'), 'https://wp-statistics.com/resources/shortcodes/'); ?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="content-report"><?php _e('Visual Reporting', 'wp-statistics'); ?></label>
            </th>
            <td>
                <div>Graphical Reports: Interested in visual representations of your data? Explore our <a target="_blank" href="https://wp-statistics.com/product/wp-statistics-advanced-reporting?utm_source=wp_statistics&utm_medium=display&utm_campaign=wordpress">Advanced Reporting Add-on</a> for chart and graph options.</div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='notifications-settings'")); ?>
