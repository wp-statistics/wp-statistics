<?php

use WP_STATISTICS\Helper;
use WP_STATISTICS\IP;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\CloudflareGeolocationProvider;
use WP_STATISTICS\TimeZone;

// Get IP Method
$ip_method  = IP::getIpMethod();
$ip_address = IP::getIP();
$ip_version = IP::getIpVersion();
$ip_options = IP::getIpOptions();

// Add TickBox
add_thickbox();
?>
<!-- Show Help $_SERVER -->
<style>
    #TB_window {
        direction: ltr;
    }
</style>
<div id="list-of-php-server" style="display:none;">
    <table style="direction: ltr;">
        <tr>
            <td width="330" style="border-bottom: 1px solid #ccc;padding-top:10px;padding-bottom:10px;">
                <b><?php esc_html_e('$_SERVER', 'wp-statistics'); ?></b></td>
            <td style="border-bottom: 1px solid #ccc;padding-top:10px;padding-bottom:10px;"><b><?php esc_html_e('Value', 'wp-statistics'); ?></b></td>
        </tr>
        <?php
        foreach ($_SERVER as $key => $value) {
            // Check Value is Array
            if (is_array($value)) {
                $value = wp_json_encode($value);
            }
            ?>
            <tr>
                <td width="330" style="padding-top:10px;padding-bottom:10px;">
                    <b><?php echo esc_attr($key); ?></b>
                </td>
                <td style="padding-top:10px;padding-bottom:10px;"><?php echo esc_attr(($value == "" ? "-" : substr(str_replace(array("\n", "\r"), '', trim($value)), 0, 200)) . (strlen($value) > 200 ? '..' : '')); ?></td>
            </tr>
            <?php
        }
        ?>
    </table>
</div>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Your IP Information', 'wp-statistics'); ?></h3></th>
        </tr>

        <?php if (apply_filters('wp_statistics_ip_detection_preview', $ip_method)) : ?>
            <tr valign="top">
                <th scope="row" colspan="2" style="padding-bottom: 10px; font-weight: normal;line-height: 25px;">
                    <?php printf(esc_html__('Your IP address as detected by the Ipify.org service: %s', 'wp-statistics'), '<b id="js-ipService" style="display: inline-block;"></b>'); ?>
                </th>
            </tr>
            <script type="application/javascript">
                jQuery(document).ready(function () {
                    jQuery.ajax({
                        <?php if($ip_version == 'IPv4') : ?>
                        url: "https://api.ipify.org?format=json",
                        <?php else : ?>
                        url: "https://api64.ipify.org/?format=json",
                        <?php endif; ?>
                        dataType: 'json',
                        beforeSend: function () {
                            jQuery("#js-ipService").html('<?php _e('Loading...', 'wp-statistics'); ?>');
                        },
                        error: function (jqXHR) {
                            if (jqXHR.status == 0) {
                                jQuery("#js-ipService").html("<?php esc_html_e('Unable to retrieve some IP data. Ensure your internet connection is active and retry.', 'wp-statistics'); ?>");
                            }
                        },
                        success: function (json) {
                            jQuery("#js-ipService").html(json['ip']);
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <tr valign="top">
            <th scope="row" colspan="2" style="padding-bottom: 10px; font-weight: normal;line-height: 25px;">
                <?php printf(esc_html__('Your IP address as detected by the current WP Statistics settings is: %s', 'wp-statistics'), '<b style="display: inline-block;">' . esc_html($ip_address) . '</b>'); ?>
            </th>
        </tr>

        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Main IP Detection Method', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php esc_html_e('Select the preferred method for determining the visitor\'s IP address. The method should correspond to the way your server and network infrastructure relay IP information. Choose the option that reflects the correct IP in your server environment.', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <!-- Sequential IP Detection -->
        <tr valign="top">
            <th scope="row" colspan="2" style="padding-top: 0px;padding-bottom: 0px;">
                <table>
                    <tr>
                        <td style="width: 10px; padding: 0px;">
                            <input id="sequential" type="radio" name="ip_method" style="vertical-align: -3px;" value="sequential" <?php checked($ip_method, 'sequential') ?>>
                        </td>
                        <td style="width: 250px;">
                            <label for="sequential"><?php esc_html_e('Sequential IP Detection (Recommended)', 'wp-statistics'); ?></label>
                        </td>
                        <td style="padding-left: 0px;">
                            <p class="description"><?php _e('Automatically detects the user\'s IP address by checking a sequence of server variables. The detection order is: <code>HTTP_X_FORWARDED_FOR</code>, <code>HTTP_X_FORWARDED</code>, <code>HTTP_FORWARDED_FOR</code>, <code>HTTP_FORWARDED</code>, <code>REMOTE_ADDR</code>, <code>HTTP_CLIENT_IP</code>, <code>HTTP_X_CLUSTER_CLIENT_IP</code>, <code>HTTP_X_REAL_IP</code>, <code>HTTP_INCAP_CLIENT_IP</code>. Stops at the first valid IP found.', 'wp-statistics') ?></p>
                        </td>
                    </tr>
                </table>
            </th>
        </tr>

        <!-- Custom IP Detection -->
        <tr valign="top">
            <th scope="row" colspan="2" style="padding-top: 0px;padding-bottom: 0px;">
                <table>
                    <tr>
                        <td style="width: 10px; padding: 0px;">
                            <input id="custom-header" type="radio" name="ip_method" style="vertical-align: -3px;" value="CUSTOM_HEADER" <?php echo in_array($ip_method, $ip_options) ? checked(true) : '' ?>>
                        </td>
                        <td style="width: 250px;">
                            <label for="custom-header"><?php esc_html_e('Specify a Custom Header for IP Detection', 'wp-statistics'); ?></label>
                        </td>
                        <td style="padding-left: 0px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" name="user_custom_header_ip_method" autocomplete="off" style="padding: 5px; width: 250px;height: 35px;" value="<?php echo in_array($ip_method, $ip_options) ? esc_attr($ip_method) : '' ?>">
                            </div>

                            <p class="description">
                                <?php _e('If your server uses a custom key in <code>$_SERVER</code> for IP detection (e.g., <code>HTTP_CF_CONNECTING_IP</code> for CloudFlare), specify it here.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?>
                                <a href="#TB_inline?&width=950&height=600&inlineId=list-of-php-server" class="thickbox"><?php _e('View <code>$_SERVER</code> in your server.', 'wp-statistics');   // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?></a>
                            </p>
                            <p class="description"><?php _e('Refer to our <a href="https://wp-statistics.com/resources/how-to-configure-ip-detection-in-wp-statistics-for-accurate-visitor-tracking/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Documentation</a> for more info and how to configure IP Detection properly.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?></p>
                        </td>
                    </tr>
                </table>
            </th>
        </tr>

        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2">
                <h3><?php esc_html_e('GeoIP Settings', 'wp-statistics'); ?></h3>
            </th>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="wps_geoip_location_detection_method"><?php esc_html_e('Location Detection Method', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_location_detection_method" id="geoip_location_detection_method">
                    <option value="cf" <?php selected(WP_STATISTICS\Option::get('geoip_location_detection_method', 'maxmind'), 'cf'); ?><?php echo CloudflareGeolocationProvider::isBehindCloudflare() ? '' : 'disabled'; ?>><?php esc_html_e('Cloudflare IP Geolocation', 'wp-statistics'); ?></option>
                    <option value="maxmind" <?php selected(WP_STATISTICS\Option::get('geoip_location_detection_method', 'maxmind'), 'maxmind'); ?>><?php esc_html_e('MaxMind GeoIP', 'wp-statistics'); ?></option>
                    <option value="dbip" <?php selected(WP_STATISTICS\Option::get('geoip_location_detection_method', 'maxmind'), 'dbip'); ?>><?php esc_html_e('DB-IP', 'wp-statistics'); ?></option>
                </select>

                <p class="description">
                    <?php
                        echo sprintf(
                            /* translators: %s: Link to learn about detection method */
                            esc_html__('Select the method to detect location data for visitors. You can choose between MaxMind GeoIP, Cloudflare Geolocation, and DB-IP. MaxMind and DB-IP provide database-based geolocation, while Cloudflare Geolocation relies on IP headers from Cloudflare. For optimal performance, we recommend using Cloudflare Geolocation if your site is on Cloudflare. %s', 'wp-statistics'),
                            '<a href="https://wp-statistics.com/resources/location-detection-methods-in-wp-statistics/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" class="wps-text-decoration-underline" target="_blank">' . esc_html__('Learn more about location detection methods.', 'wp-statistics') . '</a>'
                        );
                    ?>
                </p>
            </td>
        </tr>

        <tr valign="top" id="geoip_license_type_option">
            <th scope="row"><label for="wps_geoip_license_type"><?php esc_html_e('GeoIP Database Update Source', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_license_type" id="geoip_license_type">
                    <option value="js-deliver" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'js-deliver'); ?>><?php esc_html_e('Use the JsDelivr', 'wp-statistics'); ?></option>
                    <option value="user-license" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'user-license'); ?>><?php esc_html_e('Use the MaxMind server with your own license key', 'wp-statistics'); ?></option>
                </select>

                <p class="description"><?php esc_html_e('Select the source for updating the GeoIP database. If using a premium database, updates will be downloaded automatically using the provided license key.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="geoip_license_key_option">
            <th scope="row">
                <label for="geoip_license_key"><?php esc_html_e('GeoIP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="geoip_license_key" type="text" size="30" name="wps_geoip_license_key" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_license_key')); ?>">
                <p class="description">
                    <?php
                        /* translators: %s: Link to maxmind */
                        echo sprintf(
                            wp_kses(
                                __('Enter your <strong>MaxMind license key</strong> to enable the <strong>premium MaxMind GeoIP database</strong>, which provides more precise location data. The plugin uses the free database by default. %s', 'wp-statistics'),
                               [
                                'strong' => [],
                                'a'      => [
                                    'href'   => [],
                                    'class'  => [],
                                    'target' => [],
                                ]
                               ]
                            ),
                            '<a href="https://www.maxmind.com/en/geoip-databases" class="wps-text-decoration-underline" target="_blank">' . esc_html__('Get MaxMind Premium.', 'wp-statistics') . '</a>'
                        );
                    ?>
                </p>
            </td>
        </tr>

        <tr valign="top" id="geoip_dbip_license_key_option">
            <th scope="row">
                <label for="geoip_dbip_license_key_option"><?php esc_html_e('GeoIP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <input id="geoip_dbip_license_key_option" type="text" size="30" name="wps_geoip_dbip_license_key_option" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_dbip_license_key_option', '')); ?>">
                <p class="description">
                    <?php
                        /* translators: %s: Link to dbip */
                        echo sprintf(
                            wp_kses(
                                __('Enter your DB-IP license key to enable the premium DB-IP database, replacing the free version with a more detailed dataset.<br /> The premium DB-IP database is <strong>1.1GB</strong> in size. Make sure your server has enough storage space before enabling it, as the plugin downloads and stores this database. %s', 'wp-statistics'),
                                [
                                    'br'     => [],
                                    'strong' => [],
                                    'a'      => [
                                        'href'   => [],
                                        'class'  => [],
                                        'target' => [],
                                    ]
                                ]
                            ),
                            '<a href="https://db-ip.com/db/?refid=vrn" class="wps-text-decoration-underline" target="_blank">' . esc_html__('Get DB-IP Premium.', 'wp-statistics') . '</a>'
                        );
                    ?>
                </p>
            </td>
        </tr>

        <tr valign="top" id="enable_geoip_option">
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

        <tr valign="top" id="schedule_geoip_option">
            <th scope="row">
                <label for="geoip-schedule"><?php esc_html_e('Schedule Monthly Update of GeoIP Database', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-schedule" type="checkbox" name="wps_schedule_geoip" <?php echo WP_STATISTICS\Option::get('schedule_geoip') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-schedule"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <?php
                if (WP_STATISTICS\Option::get('schedule_geoip')) {
                    echo '<p class="description">' . esc_html__('Next update will be', 'wp-statistics') . ': <code>';
                    $event = wp_get_scheduled_event('wp_statistics_geoip_hook');

                    if ($event) {
                        echo TimeZone::getLocalDate(get_option('date_format'), $event->timestamp) . ' ' . __('at', 'wp-statistics') . ' ' . TimeZone::getLocalDate(get_option('time_format'), $event->timestamp); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                    }

                    echo '</code></p>';
                }
                ?>
                <p class="description"><?php esc_html_e('Automates monthly GeoIP database updates for the latest geographical data.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top" id="geoip_auto_pop_option">
            <th scope="row">
                <label for="geoip-auto-pop"><?php esc_html_e('Update Missing GeoIP Data', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="geoip-auto-pop" type="checkbox" name="wps_auto_pop" <?php echo WP_STATISTICS\Option::get('auto_pop') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-auto-pop"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Fills in any gaps in the GeoIP database following a new download.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="geoip-private-country-code"><?php esc_html_e('Country Code for Private IPs', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" size="3" id="geoip-private-country-code" name="wps_private_country_code" value="<?php echo esc_attr(WP_STATISTICS\Option::get('private_country_code', GeolocationFactory::getProviderInstance()->getDefaultPrivateCountryCode())); ?>">
                <p class="description"><?php echo esc_html__('Assigns a default country code for private IP addresses that cannot be geographically located.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                function handle_geoip_fields() {
                    var method = jQuery("#geoip_location_detection_method").val();
                    var isMaxmind = (method === "maxmind");
                    var isDBIP    = (method === "dbip");
                    var isUserLicense = (jQuery("#geoip_license_type").val() === "user-license");

                    jQuery("#geoip_license_type_option, #enable_geoip_option, #schedule_geoip_option, #geoip_auto_pop_option")
                        .toggle(isMaxmind || isDBIP);

                    if (isMaxmind) {
                        // For MaxMind, show its license key field only if "user-license" is selected.
                        jQuery("#geoip_license_key_option").toggle(isUserLicense);
                        jQuery("#geoip_dbip_license_key_option").hide();
                        jQuery("#geoip_license_type option[value='user-license']")
                            .text("<?php esc_html_e('Use the MaxMind server with your own license key', 'wp-statistics'); ?>");
                    } else if (isDBIP) {
                        // For DB-IP, show its license key field only if "user-license" is selected.
                        jQuery("#geoip_dbip_license_key_option").toggle(isUserLicense);
                        jQuery("#geoip_license_key_option").hide();
                        jQuery("#geoip_license_type option[value='user-license']")
                            .text("<?php esc_html_e('Use the DB-IP server with your own license key', 'wp-statistics'); ?>");
                    } else {
                        // Hide both license key fields if neither MaxMind nor DB-IP is selected.
                        jQuery("#geoip_license_key_option, #geoip_dbip_license_key_option").hide();
                    }
                }

                handle_geoip_fields();
                jQuery("#geoip_location_detection_method").on('change', handle_geoip_fields);
                jQuery("#geoip_license_type").on('change', handle_geoip_fields);

                // Ajax function for updating database
                jQuery("input[name = 'update_geoip']").click(function (event) {
                    event.preventDefault();
                    var geoip_clicked_button = this;

                    jQuery(".geoip-update-loading").remove();
                    jQuery(".update_geoip_result").remove();

                    jQuery(this).after("<img class='geoip-update-loading' src='<?php echo esc_url(plugins_url('wp-statistics')); ?>/assets/images/loading.gif'/>");

                    var selectedLocationMethod = jQuery("#geoip_location_detection_method").val();

                    if (!selectedLocationMethod) {
                        selectedLocationMethod = 'maxmind';
                    }

                    jQuery.ajax({
                        url: ajaxurl,
                        type: 'post',
                        data: {
                            'action': 'wp_statistics_update_geoip_database',
                            'wps_nonce': '<?php echo wp_create_nonce('wp_rest'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped	 ?>',
                            'geoip_location_detection_method': selectedLocationMethod,
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

<script type="text/javascript">
    function DBMaintWarning() {
        const checkbox = jQuery('#wps_schedule_dbmaint');
        if (checkbox.prop('checked')) {
            if (!confirm('<?php esc_html_e('This will permanently delete data from the database each day, are you sure you want to enable this option?', 'wp-statistics'); ?>')) {
                checkbox.prop('checked', false);
            }
        }
    }
</script>
<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Purge Old Data Daily', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint"><?php esc_html_e('Automatic Cleanup', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="wps_schedule_dbmaint" type="checkbox" name="wps_schedule_dbmaint" <?php echo WP_STATISTICS\Option::get('schedule_dbmaint') == true ? "checked='checked'" : ''; ?> onchange='DBMaintWarning();'>
                <label for="wps_schedule_dbmaint"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Automatic deletion of data entries that are more than a specified number of days old to keep the database optimized. The process runs the following day.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="wps_schedule_dbmaint_days"><?php esc_html_e('Purge Data Older Than', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" class="small-text code" id="wps_schedule_dbmaint_days" name="wps_schedule_dbmaint_days" value="<?php echo esc_attr(WP_STATISTICS\Option::get('schedule_dbmaint_days', "365")); ?>"/>
                <?php esc_html_e('Days', 'wp-statistics'); ?>
                <p class="description"><?php echo esc_html__('Sets the age threshold for deleting data entries. Data exceeding the specified age in days will be removed. The minimum setting is 30 days.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Restore Default Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="reset-plugin"><?php esc_html_e('Reset Options', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="reset-plugin" type="checkbox" name="wps_reset_plugin">
                <label for="reset-plugin"><?php esc_html_e('Reset', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Revert all user-specific and global configurations to the WP Statistics default settings, preserving your existing data.', 'wp-statistics'); ?></p>
                <p class="description"><span class="wps-note"><?php esc_html_e('Caution', 'wp-statistics'); ?>:</span> <?php esc_html_e('This change is irreversible.', 'wp-statistics'); ?></p>
                <p class="description"><?php _e('<b>For multisite users</b>: Every site within the network will return to the default settings.', 'wp-statistics'); // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction	?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Danger Zone', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="delete-data-on-uninstall"><?php esc_html_e('Delete All Data on Plugin Deletion', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input id="delete-data-on-uninstall" type="checkbox" name="wps_delete_data_on_uninstall" <?php checked(WP_STATISTICS\Option::get('delete_data_on_uninstall')) ?>>
                <label for="delete-data-on-uninstall"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Enable this option to automatically delete all WP Statistics data from your database when the plugin is deleted.', 'wp-statistics'); ?></p>
                <p class="description"><span class="wps-note"><?php esc_html_e('Warning', 'wp-statistics'); ?>:</span> <?php esc_html_e('This action is permanent and cannot be undone. Make sure to back up your data before enabling this option.', 'wp-statistics'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(esc_html__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='advanced-settings'")); ?>
