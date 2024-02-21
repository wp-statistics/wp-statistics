<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php _e('GeoIP Settings', 'wp-statistics'); ?></h3>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row"><label for="wps_geoip_license_type"><?php _e('GeoIP Database Update Source', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_license_type" id="geoip_license_type">
                    <option value="js-deliver" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'js-deliver'); ?>><?php _e('Use the JsDelivr', 'wp-statistics'); ?></option>
                    <option value="user-license" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'user-license'); ?>><?php _e('Use the MaxMind server with your own license key', 'wp-statistics'); ?></option>
                </select>

                <p class="description"><?php _e('Select a service that updates the GeoIP database, ensuring the geographic information displayed is accurate and up-to-date. It\'s only used for database updates, not for real-time location lookups.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="geoip_license_key_option">
            <th scope="row">
                <label for="geoip_license_key"><?php _e('GeoIP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="geoip_license_key" type="text" size="30" name="wps_geoip_license_key" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_license_key')); ?>">
                <p class="description"><?php echo __('Put your license key here and save settings to apply it.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <?php if (WP_STATISTICS\GeoIP::IsSupport()) { ?>
            <tr valign="top">
                <th scope="row">
                    <label for="geoip-enable"><?php _e('GeoIP Collection', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="geoip-enable" type="checkbox" name="wps_geoip" <?php echo(WP_STATISTICS\Option::get('geoip') === 'on' ? "checked='checked'" : ''); ?>>
                    <label for="geoip-enable">
                        <?php _e('Enable', 'wp-statistics'); ?>
                        <input type="hidden" name="geoip_name" value="country">

                        <?php
                        if (WP_STATISTICS\Option::get('geoip')) {
                            submit_button(__("Update Database", 'wp-statistics'), "secondary", "update_geoip", false);
                        }
                        ?>
                    </label>

                    <p class="description"><?php _e('Gather and display geographic information (country) related to your visitors.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-city"><?php _e('GeoIP City', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="geoip-city" type="checkbox" name="wps_geoip_city" <?php echo(WP_STATISTICS\Option::get('geoip_city') == 'on' ? "checked='checked'" : ''); ?>>
                    <label for="geoip-city">
                        <?php _e('Enable', 'wp-statistics'); ?>
                        <input type="hidden" name="geoip_name" value="city">

                        <?php
                        if (WP_STATISTICS\Option::get('geoip_city')) {
                            submit_button(__("Update Database", 'wp-statistics'), "secondary", "update_geoip", false);
                        }
                        ?>
                    </label>
                    <p class="description"><?php _e('Provides city-level geographic details for website visitor analysis.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e('Schedule Monthly Update of GeoIP Database', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="geoip-schedule" type="checkbox" name="wps_schedule_geoip" <?php echo WP_STATISTICS\Option::get('schedule_geoip') == true ? "checked='checked'" : ''; ?>>
                    <label for="geoip-schedule"><?php _e('Enable', 'wp-statistics'); ?></label>
                    <?php
                    if (WP_STATISTICS\Option::get('schedule_geoip')) {
                        echo '<p class="description">' . __('Next update will be', 'wp-statistics') . ': <code>';
                        $last_update = WP_STATISTICS\Option::get('last_geoip_dl');
                        $this_month  = strtotime(__('First Tuesday of this month', 'wp-statistics'));

                        if ($last_update > $this_month) {
                            $next_update = strtotime(__('First Tuesday of next month', 'wp-statistics')) + (86400 * 2);
                        } else {
                            $next_update = $this_month + (86400 * 2);
                        }

                        $next_schedule = wp_next_scheduled('wp_statistics_geoip_hook');
                        if ($next_schedule) {
                            echo \WP_STATISTICS\TimeZone::getLocalDate(get_option('date_format'), $next_update) .
                                ' @ ' .
                                \WP_STATISTICS\TimeZone::getLocalDate(get_option('time_format'), $next_schedule);
                        } else {
                            echo \WP_STATISTICS\TimeZone::getLocalDate(get_option('date_format'), $next_update) .
                                ' @ ' .
                                \WP_STATISTICS\TimeZone::getLocalDate(get_option('time_format'), time());
                        }

                        echo '</code></p>';
                    }
                    ?>
                    <p class="description"><?php _e('Automates monthly GeoIP database updates for the latest geographical data, occurring two days after the first Tuesday each month.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e('Update Missing GeoIP Data', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input id="geoip-auto-pop" type="checkbox" name="wps_auto_pop" <?php echo WP_STATISTICS\Option::get('auto_pop') == true ? "checked='checked'" : ''; ?>>
                    <label for="geoip-auto-pop"><?php _e('Enable', 'wp-statistics'); ?></label>
                    <p class="description"><?php _e('Fills in any gaps in the GeoIP database following a new download.', 'wp-statistics'); ?></p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    <label for="geoip-schedule"><?php _e('Country Code for Private IP Addresses', 'wp-statistics'); ?></label>
                </th>

                <td>
                    <input type="text" size="3" id="geoip-private-country-code" name="wps_private_country_code" value="<?php echo esc_attr(WP_STATISTICS\Option::get('private_country_code', \WP_STATISTICS\GeoIP::$private_country)); ?>">
                    <p class="description"><?php echo __('Assigns a default country code for private IP addresses that cannot be geographically located.', 'wp-statistics'); ?></p>
                </td>
            </tr>
        <?php } else { ?>
            <tr valign="top">
                <th scope="row" colspan="2">
                    <?php
                    echo __('GeoIP collection is disabled due to the following reasons:', 'wp-statistics') . '<br><br>';

                    if (!function_exists('curl_init')) {
                        echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
                        _e('GeoIP collection requires the cURL PHP extension and it is not loaded on your version of PHP!', 'wp-statistics');
                        echo '<br>';
                    }

                    if (!function_exists('bcadd')) {
                        echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
                        _e('GeoIP collection requires the BC Math PHP extension and it is not loaded on your version of PHP!', 'wp-statistics');
                        echo '<br>';
                    }

                    if (ini_get('safe_mode')) {
                        echo '<br>&nbsp;&nbsp;&nbsp;&nbsp;* ';
                        _e('PHP safe mode detected! GeoIP collection is not supported with PHP\'s safe mode enabled!', 'wp-statistics');
                        echo '<br>';
                    }
                    ?>
                </th>
            </tr>
        <?php } ?>

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

                    var geoip_action = jQuery(this).prev().val();
                    jQuery(".geoip-update-loading").remove();
                    jQuery(".update_geoip_result").remove();

                    jQuery(this).after("<img class='geoip-update-loading' src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            'action': 'wp_statistics_update_geoip_database',
                            'update_action': geoip_action,
                            'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); ?>'
                        },
                        datatype: 'json',
                    })
                        .always(function (result) {
                            jQuery(".geoip-update-loading").remove();
                            jQuery(geoip_clicked_button).after("<span class='update_geoip_result'>" + result + "</span>")
                        });
                });
            });
        </script>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php _e('Matomo Referrer Spam Blacklist Settings', 'wp-statistics'); ?></h3>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="referrerspam-enable"><?php _e('Referrer Spam Blacklist', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="referrerspam-enable" type="checkbox" name="wps_referrerspam" <?php echo WP_STATISTICS\Option::get('referrerspam') == true ? "checked='checked'" : ''; ?>>
                <label for="referrerspam-enable"><?php _e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php _e('Integrates with Matomo’s Referrer Spam Blacklist to exclude known spam referrers from site statistics. For more details on the blacklist source, visit <a href="https://github.com/matomo-org/referrer-spam-blacklist" target="_blank">Matomo\'s Referrer Spam Blacklist</a>.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" class="referrerspam_field" <?php if (!WP_STATISTICS\Option::get('referrerspam')) {
            echo ' style="display:none;"';
        } ?>>
            <th scope="row">
                <label for="geoip-update"><?php _e('Refresh Blacklist Data', 'wp-statistics'); ?></label>
            </th>

            <td>
                <button type="submit" name="update-referrer-spam" value="1" class="button"><?php _e('Update', 'wp-staitsitcs'); ?></button>
                <!--                <a href="--><?php //echo WP_STATISTICS\Menus::admin_url('settings', array('tab' => 'externals-settings', 'update-referrer-spam' => 'yes'))
                ?><!--" class="button">--><?php //_e('Update', 'wp-staitsitcs');
                ?><!--</a>-->
                <p class="description"><?php _e('Click here to manually download the latest set of referrer spam filters from Matomo.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" class="referrerspam_field" <?php if (!WP_STATISTICS\Option::get('referrerspam')) {
            echo ' style="display:none;"';
        } ?>>
            <th scope="row">
                <label for="referrerspam-schedule"><?php _e('Automate Blacklist Updates', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="referrerspam-schedule" type="checkbox" name="wps_schedule_referrerspam" <?php echo WP_STATISTICS\Option::get('schedule_referrerspam') == true ? "checked='checked'" : ''; ?>>
                <label for="referrerspam-schedule"><?php _e('Weekly Auto-Update', 'wp-statistics'); ?></label>
                <?php
                if (WP_STATISTICS\Option::get('schedule_referrerspam')) {
                    echo '<p class="description">' . __('Next update will be', 'wp-statistics') . ': <code>';
                    $next_schedule = wp_next_scheduled('wp_statistics_referrerspam_hook');

                    if ($next_schedule) {
                        echo esc_attr(date(get_option('date_format'), $next_schedule) . ' @ ' . date(get_option('time_format'), $next_schedule));
                    } else {
                        $next_update = time() + (86400 * 7);
                        echo esc_attr(date(get_option('date_format'), $next_update) . ' @ ' . date(get_option('time_format'), time()));
                    }

                    echo '</code></p>';
                }
                ?>
                <p class="description"><?php _e('Check this to automatically download updates to the Matomo Referrer Spam Blacklist every week, ensuring continuous protection.'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='externals-settings'")); ?>