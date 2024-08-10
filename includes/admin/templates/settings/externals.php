<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php esc_html_e('GeoIP Settings', 'wp-statistics'); ?></h3>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_geoip_license_type"><?php esc_html_e('GeoIP Database Update Source', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_license_type" id="geoip_license_type">
                    <option value="js-deliver" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'js-deliver'); ?>><?php esc_html_e('Use the JsDelivr', 'wp-statistics'); ?></option>
                    <option value="user-license" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'user-license'); ?>><?php esc_html_e('Use the MaxMind server with your own license key', 'wp-statistics'); ?></option>
                </select>

                <p class="description"><?php esc_html_e('Select a service that updates the GeoIP database, ensuring the geographic information displayed is accurate and up-to-date. It\'s only used for database updates, not for real-time location lookups.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="geoip_license_key_option">
            <th scope="row">
                <label for="geoip_license_key"><?php esc_html_e('GeoIP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="geoip_license_key" type="text" size="30" name="wps_geoip_license_key" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_license_key')); ?>">
                <p class="description"><?php echo esc_html__('Put your license key here and save settings to apply it.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-enable"><?php esc_html_e('Manual Update of GeoIP Database', 'wp-statistics'); ?></label>
            </th>

            <td>
                <label for="geoip-enable">
                    <?php submit_button(esc_html__('Update Now', 'wp-statistics'), "secondary", "update_geoip", false); ?>
                </label>

                <p class="description"><?php esc_html_e('Click here to update the GeoIP database immediately for the database.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-schedule"><?php esc_html_e('Schedule Monthly Update of GeoIP Database', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-schedule" type="checkbox" name="wps_schedule_geoip" <?php echo WP_STATISTICS\Option::get('schedule_geoip') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-schedule"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <?php
                if (WP_STATISTICS\Option::get('schedule_geoip')) {
                    echo '<p class="description">' . esc_html__('Next update will be', 'wp-statistics') . ': <code>';
                    $last_update = WP_STATISTICS\Option::get('last_geoip_dl');
                    $this_month  = strtotime('first Tuesday of this month');

                    if ($last_update > $this_month) {
                        $next_update = strtotime('first Tuesday of next month') + (86400 * 2);
                    } else {
                        $next_update = $this_month + (86400 * 2);
                    }

                    $next_schedule = wp_next_scheduled('wp_statistics_geoip_hook');
                    if ($next_schedule) {
                        echo \WP_STATISTICS\TimeZone::getLocalDate(get_option('date_format'), $next_update) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ' @ ' .
                            \WP_STATISTICS\TimeZone::getLocalDate(get_option('time_format'), $next_schedule); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    } else {
                        echo \WP_STATISTICS\TimeZone::getLocalDate(get_option('date_format'), $next_update) . // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ' @ ' .
                            \WP_STATISTICS\TimeZone::getLocalDate(get_option('time_format'), time()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }

                    echo '</code></p>';
                }
                ?>
                <p class="description"><?php esc_html_e('Automates monthly GeoIP database updates for the latest geographical data, occurring two days after the first Tuesday each month.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-schedule"><?php esc_html_e('Update Missing GeoIP Data', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-auto-pop" type="checkbox" name="wps_auto_pop" <?php echo WP_STATISTICS\Option::get('auto_pop') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-auto-pop"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Fills in any gaps in the GeoIP database following a new download.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-schedule"><?php esc_html_e('Country Code for Unlocatable IPs', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" size="3" id="geoip-private-country-code" name="wps_private_country_code" value="<?php echo esc_attr(WP_STATISTICS\Option::get('private_country_code', \WP_STATISTICS\GeoIP::$private_country)); ?>">
                <p class="description"><?php echo esc_html__('Assigns a default country code for private IP addresses that cannot be geographically located.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <script type="text/javascript">
            jQuery(document).ready(function () {

                // Show and hide user license input base on license type option
                function handle_geoip_license_key_field() {
                    console.log(jQuery("#geoip_license_type").val())
                    if (jQuery("#geoip_license_type").val() == "user-license") {
                        jQuery("#geoip_license_key_option").show();
                    } else {
                        jQuery("#geoip_license_key_option").hide();
                    }
                }

                handle_geoip_license_key_field();
                jQuery("#geoip_license_type").on('change', handle_geoip_license_key_field);

                // Ajax function for updating database
                jQuery("input[name = 'update_geoip']").click(function (event) {
                    event.preventDefault();
                    var geoip_clicked_button = this;

                    jQuery(".geoip-update-loading").remove();
                    jQuery(".update_geoip_result").remove();

                    jQuery(this).after("<img class='geoip-update-loading' src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            'action': 'wp_statistics_update_geoip_database',
                            'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>'
                        },
                        datatype: 'json',
                    }).success(function (result) {
                        jQuery(".geoip-update-loading").remove();
                        jQuery(geoip_clicked_button).after("<span class='update_geoip_result'>" + result + "</span>")
                    }).error(function (result) {
                        jQuery(".geoip-update-loading").remove();
                        jQuery(geoip_clicked_button).after("<span class='update_geoip_result'><?php _e('Oops! Something went wrong. Please try again. For more details, check the <b>PHP Error Log</b>.', 'wp-statistics'); ?></span>")
                    });
                });
            });
        </script>
        </tbody>
    </table>
</div>

<?php submit_button(esc_html__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='externals-settings'")); ?>