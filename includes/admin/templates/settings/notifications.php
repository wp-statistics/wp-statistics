<script type="text/javascript">
    function ToggleStatOptions() {
        jQuery('[id^="wps_stats_report_option"]').fadeToggle();
    }
</script>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Email Configuration', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="email-report"><?php esc_html_e('Recipient Email Addresses', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input dir="ltr" type="text" id="email_list" name="wps_email_list" size="30" value="<?php if (WP_STATISTICS\Option::get('email_list') == '') {
                    $wp_statistics_options['email_list'] = get_bloginfo('admin_email');
                }
                echo esc_textarea(WP_STATISTICS\Option::get('email_list')); ?>"/>
                <p class="description"><?php esc_html_e('Enter email addresses to receive reports. Use a comma to separate multiple addresses.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="stats-report"><?php esc_html_e('Automated Report Delivery', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="stats-report" type="checkbox" value="1" name="wps_stats_report" <?php echo WP_STATISTICS\Option::get('stats_report') == true ? "checked='checked'" : ''; ?> onClick='ToggleStatOptions();'>
                <label for="stats-report"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Set preferences for receiving automated, detailed statistical reports by email.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Database Notifications', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-report"><?php esc_html_e('GeoIP Update', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-report" type="checkbox" value="1" name="wps_geoip_report" <?php echo WP_STATISTICS\Option::get('geoip_report') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-report"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Receive notifications when the GeoIP database updates.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="prune-report"><?php esc_html_e('Database Pruning Alert', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="prune-report" type="checkbox" value="1" name="wps_prune_report" <?php echo WP_STATISTICS\Option::get('prune_report') == true ? "checked='checked'" : ''; ?>>
                <label for="prune-report"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Get notified when the database pruning occurs.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Admin Dashboard Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="admin-notices"><?php esc_html_e('Display All WP Statistics Notices', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="admin-notices" type="checkbox" value="1" name="wps_admin_notices" <?php echo WP_STATISTICS\Option::get('admin_notices') == true ? "checked='checked'" : ''; ?>>
                <label for="admin-notices"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('All notifications, alerts, and suggestions from WP Statistics appear in the admin dashboard. Without selection, only critical warnings or errors are shown for a streamlined dashboard view.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>
<?php if (WP_STATISTICS\Option::get('stats_report')) {
    $style = "";
} else {
    $style = "display: none;";
} ?>
<div class="postbox" style="<?php echo esc_attr($style); ?>" id='wps_stats_report_option'>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Advanced Reporting Options', 'wp-statistics'); ?></h3></th>
        </tr>
        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="time-report"><?php esc_html_e('Report Frequency', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_time_report" id="time-report">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('time_report'), '0'); ?>><?php esc_html_e('Please select', 'wp-statistics'); ?></option>
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
                <p class="description"><?php _e('Select the frequency of report deliveries. For custom schedules, more information can be found in our <a href="https://wp-statistics.com/resources/schedule-statistical-reports/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">documentation</a>.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="send-report"><?php esc_html_e('Delivery Method', 'wp-statistics'); ?></label>
            </th>

            <td>
                <select name="wps_send_report" id="send-report">
                    <option value="0" <?php selected(WP_STATISTICS\Option::get('send_report'), '0'); ?>><?php esc_html_e('Please select', 'wp-statistics'); ?></option>
                    <option value="mail" <?php selected(WP_STATISTICS\Option::get('send_report'), 'mail'); ?>><?php esc_html_e('Email', 'wp-statistics'); ?></option>
                    <?php if (is_plugin_active('wp-sms/wp-sms.php') || is_plugin_active('wp-sms-pro/wp-sms.php')) { ?>
                        <option value="sms" <?php selected(WP_STATISTICS\Option::get('send_report'), 'sms'); ?>><?php esc_html_e('SMS', 'wp-statistics'); ?></option>
                    <?php } ?>
                </select>

                <p class="description"><?php echo sprintf(__('Select your preferred method for receiving reports: via email or SMS (SMS notifications are sent using the %s Plugin to the Admin Mobile Number).', 'wp-statistics'), '<a href="https://wordpress.org/extend/plugins/wp-sms/" target="_blank">' . __('WP SMS', 'wp-statistics') . '</a>'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="content-report"><?php esc_html_e('Custom Report Builder', 'wp-statistics'); ?></label>
            </th>

            <td>
                <?php wp_editor(WP_STATISTICS\Option::get('content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5)); ?>
                <p class="description"><?php esc_html_e('Using WP Statistics shortcodes to display specific statistics.', 'wp-statistics'); ?></p>

                <p class="description data">
                    <?php esc_html_e('Insert any of the following shortcode examples to show corresponding data:', 'wp-statistics'); ?>
                    <br><br>
                    <?php esc_html_e('Today\'s Visitors', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=today]</code><br>
                    <?php esc_html_e('Today\'s Views', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=today]</code><br>
                    <?php esc_html_e('Yesterday\'s Visitors', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=yesterday]</code><br>
                    <?php esc_html_e('Yesterday\'s Views', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=yesterday]</code><br>
                    <?php esc_html_e('Total Visitors', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visitors time=total]</code><br>
                    <?php esc_html_e('Total Views', 'wp-statistics'); ?>:
                    <code>[wpstatistics stat=visits time=total]</code><br>
                </p>
                <p class="description"><?php _e('Refer to our complete <a href="https://wp-statistics.com/resources/shortcodes/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">shortcode guide</a> for more options.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row" style="vertical-align: top;">
                <label for="content-report"><?php esc_html_e('Enhanced Visual Report', 'wp-statistics'); ?></label>
            </th>
            <td>
                <div><?php _e('For graphical representations of your data, explore our <a href="https://wp-statistics.com/product/wp-statistics-advanced-reporting/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Advanced Reporting Add-on</a> for additional chart and graph options.', 'wp-statistics') // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	  ?></div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='notifications-settings'")); ?>
