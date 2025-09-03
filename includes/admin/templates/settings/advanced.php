<?php

use WP_Statistics\Components\DateTime;
use WP_Statistics\Components\Event;
use WP_STATISTICS\Option;
use WP_STATISTICS\IP;
use WP_STATISTICS\Helper;
use WP_Statistics\Service\Admin\Posts\WordCountService;
use WP_STATISTICS\TimeZone;
use WP_Statistics\Service\Geolocation\GeolocationFactory;
use WP_Statistics\Service\Geolocation\Provider\CloudflareGeolocationProvider;
use WP_Statistics\Service\Admin\ModalHandler\Modal;

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
                <b><?php esc_html_e('Key', 'wp-statistics'); ?></b></td>
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

<h2 class="wps-settings-box__title">
    <span><?php esc_html_e('Advanced Options', 'wp-statistics'); ?></span>
    <a href="<?php echo esc_url(WP_STATISTICS_SITE_URL . '/resources/advanced-options-settings/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings') ?>" target="_blank"><?php esc_html_e('View Guide', 'wp-statistics'); ?></a>
</h2>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Your IP Information', 'wp-statistics'); ?></h3></th>
        </tr>

        <?php if (apply_filters('wp_statistics_ip_detection_preview', $ip_method)) : ?>
            <tr data-id="ipify_org_ip_tr">
                <th scope="row">
                    <span class="wps-setting-label"><?php esc_html_e('Ipify.org IP', 'wp-statistics'); ?></span>
                </th>
                <td>
                    <input type="text" aria-label="<?php esc_html_e('Ipify.org IP', 'wp-statistics'); ?>" readonly id="js-ipService" class="regular-text"/>
                    <p class="description">
                        <?php esc_html_e('Your IP address as detected by the Ipify.org service', 'wp-statistics'); ?>
                    </p>
                </td>
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
                            jQuery("#js-ipService").val('<?php _e('Loading...', 'wp-statistics'); ?>');
                        },
                        error: function (jqXHR) {
                            if (jqXHR.status == 0) {
                                jQuery("#js-ipService").val("<?php esc_html_e('Unable to retrieve some IP data. Ensure your internet connection is active and retry.', 'wp-statistics'); ?>");
                            }
                        },
                        success: function (json) {
                            jQuery("#js-ipService").val(json['ip']);
                        }
                    });
                });
            </script>
        <?php endif; ?>

        <tr data-id="wp_statistics_ip_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('WP Statistics', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input type="text" aria-label="<?php esc_html_e('WP Statistics', 'wp-statistics'); ?>" id="wp_statistics_ip" readonly value="<?php echo $ip_address ?>" class="regular-text"/>
                <p class="description">
                    <?php esc_html_e('Your IP address as detected by the current WP Statistics settings', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>

        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Main IP Detection Method', 'wp-statistics'); ?> <span class="wps-tooltip" title="<?php esc_html_e('Select the preferred method for determining the visitor\'s IP address. The method should correspond to the way your server and network infrastructure relay IP information. Choose the option that reflects the correct IP in your server environment.', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></span></h3></th>
        </tr>

        <tr data-id="detection_method_tr">
            <th scope="row">
                <label for="wps_settings[ip_method]"><?php esc_html_e('Detection Method', 'wp-statistics'); ?></label>
            </th>
            <td>
                <select id="wps_settings[ip_method]" name="ip_method">
                    <option value="sequential" <?php echo WP_STATISTICS\Option::get('ip_method') ? "sequential='selected'" : ''; ?>>
                        <?php esc_html_e('Sequential IP Detection (Recommended)', 'wp-statistics'); ?>
                    </option>
                    <option value="CUSTOM_HEADER" <?php echo in_array(WP_STATISTICS\Option::get('ip_method'), $ip_options) ? 'selected' : ''; ?>>
                        <?php esc_html_e('Specify a Custom Header for IP Detection', 'wp-statistics'); ?>
                    </option>
                </select>
                <div class="js-wps-show_if_ip_method_equal_sequential">
                    <p class="description">
                        <?php _e('Automatically detects the user\'s IP address by checking a sequence of server variables. The detection order is: <code>HTTP_X_FORWARDED_FOR</code>, <code>HTTP_X_FORWARDED</code>, <code>HTTP_FORWARDED_FOR</code>, <code>HTTP_FORWARDED</code>, <code>REMOTE_ADDR</code>, <code>HTTP_CLIENT_IP</code>, <code>HTTP_X_CLUSTER_CLIENT_IP</code>, <code>HTTP_X_REAL_IP</code>, <code>HTTP_INCAP_CLIENT_IP</code>. Stops at the first valid IP found.', 'wp-statistics') ?>
                    </p>
                </div>

                <div class="js-wps-show_if_ip_method_equal_CUSTOM_HEADER">
                    <div style="display: flex; align-items: center; gap: 10px;" class="description">
                        <input aria-label="<?php esc_html_e('Specify a Custom Header for IP Detection', 'wp-statistics'); ?>" type="text" name="user_custom_header_ip_method" autocomplete="off" value="<?php echo in_array($ip_method, $ip_options) ? esc_attr($ip_method) : '' ?>">
                    </div>

                    <p class="description">
                        <?php _e('If your server uses a custom key in <code>$_SERVER</code> for IP detection (e.g., <code>HTTP_CF_CONNECTING_IP</code> for CloudFlare), specify it here.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?>
                        <a aria-label="<?php esc_attr_e('Open modal to view available headers on your server', 'wp-statistics'); ?>" href="#TB_inline?&width=950&height=600&inlineId=list-of-php-server" class="thickbox"><?php _e('View available headers on your server.', 'wp-statistics');   // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?></a>
                    </p>
                    <p class="description"><?php _e('Refer to our <a href="https://wp-statistics.com/resources/how-to-configure-ip-detection-in-wp-statistics-for-accurate-visitor-tracking/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">Documentation</a> for more info and how to configure IP Detection properly.', 'wp-statistics');  // phpcs:ignore WordPress.Security.EscapeOutput.UnsafePrintingFunction  ?></p>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2">
                <h3><?php esc_html_e('Geolocation Settings', 'wp-statistics'); ?></h3>
            </th>
        </tr>
        <tr data-id="location_detection_method_tr">
            <th scope="row"><label for="wps_settings[geoip_location_detection_method]"><?php esc_html_e('Location Detection Method', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_location_detection_method" id="wps_settings[geoip_location_detection_method]">
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

        <tr id="geoip_license_type_option" class="js-wps-show_if_geoip_location_detection_method_equal_maxmind js-wps-show_if_or js-wps-show_if_geoip_location_detection_method_equal_dbip" data-id="geolocation_database_update_source_tr">
            <th scope="row"><label for="wps_settings[geoip_license_type]"><?php esc_html_e('Geolocation Database Update Source', 'wp-statistics'); ?></label></th>
            <td>
                <select name="wps_geoip_license_type" id="wps_settings[geoip_license_type]">
                    <option value="js-deliver" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'js-deliver'); ?>><?php esc_html_e('Use the JsDelivr', 'wp-statistics'); ?></option>
                    <option value="user-license" <?php selected(WP_STATISTICS\Option::get('geoip_license_type'), 'user-license'); ?> >
                    <?php esc_html_e('Use the MaxMind server with your own license key', 'wp-statistics'); ?>
                    </option>
                 </select>

                <p class="description"><?php esc_html_e('Select the source for updating the Geolocation database. If using a premium database, updates will be downloaded automatically using the provided license key.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr id="geoip_license_key_option" class="js-wps-show_if_geoip_location_detection_method_equal_maxmind js-wps-show_if_geoip_license_type_equal_user-license" data-id="geoip_license_key_tr">
            <th scope="row">
                <label for="geoip_license_key"><?php esc_html_e('GeoIP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <div class="wps-input-group wps-input-group__action">
                    <input id="geoip_license_key" class="wps-input-group__field" type="text" size="30" name="wps_geoip_license_key" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_license_key')); ?>">
                    <button type="button" class="button has-icon wps-input-group__label wps-input-group__copy" style="margin: 0; "><?php esc_html_e('Copy', 'wp-statistics'); ?></button>
                </div>
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

        <tr id="geoip_dbip_license_key_option" class="js-wps-show_if_geoip_location_detection_method_equal_dbip js-wps-show_if_geoip_license_type_equal_user-license" data-id="db_ip_license_key_tr">
            <th scope="row">
                <label for="geoip_dbip_license_key_option"><?php esc_html_e('DB-IP License Key', 'wp-statistics'); ?></label>
            </th>
            <td>
                <div class="wps-input-group wps-input-group__action">
                    <input id="geoip_dbip_license_key_option" type="text" size="30" name="wps_geoip_dbip_license_key_option" class="regular-text wps-input-group__field" value="<?php echo esc_attr(WP_STATISTICS\Option::get('geoip_dbip_license_key_option', '')); ?>">
                    <button type="button" class="button has-icon wps-input-group__label wps-input-group__copy" style="margin: 0; "><?php esc_html_e('Copy', 'wp-statistics'); ?></button>
                </div>
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

        <tr id="enable_geoip_option" class="js-wps-show_if_geoip_location_detection_method_equal_maxmind js-wps-show_if_or js-wps-show_if_geoip_location_detection_method_equal_dbip" data-id="manual_update_of_geolocation_database_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Manual Update of Geolocation Database', 'wp-statistics'); ?></span>
            </th>

            <td>
                <div>
                    <button type="submit" name="update_geoip" aria-label="<?php esc_html_e('Manual Update of Geolocation Database', 'wp-statistics'); ?>" class="wps-button wps-button--default">
                        <?php esc_html_e('Update Now', 'wp-statistics'); ?>
                    </button>
                </div>

                <p class="description"><?php esc_html_e('Click here to update the Geolocation database immediately for the database.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr id="schedule_geoip_option" class="js-wps-show_if_geoip_location_detection_method_equal_maxmind js-wps-show_if_or js-wps-show_if_geoip_location_detection_method_equal_dbip" data-id="schedule_monthly_update_of_geolocation_database_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Schedule Monthly Update of Geolocation Database', 'wp-statistics'); ?></span>
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
                <p class="description"><?php esc_html_e('Automates monthly Geolocation database updates for the latest geographical data.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr id="geoip_auto_pop_option" class="js-wps-show_if_geoip_location_detection_method_equal_maxmind js-wps-show_if_or js-wps-show_if_geoip_location_detection_method_equal_dbip" data-id="update_missing_geolocation_data_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Update Missing Geolocation Data', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input id="geoip-auto-pop" type="checkbox" name="wps_auto_pop" <?php echo WP_STATISTICS\Option::get('auto_pop') == true ? "checked='checked'" : ''; ?>>
                <label for="geoip-auto-pop"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Fills in any gaps in the Geolocation database following a new download.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <tr data-id="country_code_for_private_ips_tr">
            <th scope="row">
                <label for="geoip-private-country-code"><?php esc_html_e('Country Code for Private IPs', 'wp-statistics'); ?></label>
            </th>

            <td>
                <input type="text" size="3" id="geoip-private-country-code" name="wps_private_country_code" value="<?php echo esc_attr(WP_STATISTICS\Option::get('private_country_code', GeolocationFactory::getProviderInstance()->getDefaultPrivateCountryCode())); ?>">
                <p class="description"><?php echo esc_html__('Assigns a default country code for private IP addresses that cannot be geographically located.', 'wp-statistics'); ?></p>
            </td>
        </tr>

        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                const locationMethodElement =  document.querySelector('#wps_settings\\[geoip_location_detection_method\\]');

                // Update the license type option text based on selection
                function updateLicenseTypeText() {
                    const method = locationMethodElement.value;
                    const licenseTypeOption = document.querySelector('#wps_settings\\[geoip_license_type\\] option[value="user-license"]');

                    if (method === 'maxmind') {
                        licenseTypeOption.textContent = "<?php esc_html_e('Use the MaxMind server with your own license key', 'wp-statistics'); ?>";
                    } else if (method === 'dbip') {
                        licenseTypeOption.textContent = "<?php esc_html_e('Use the DB-IP server with your own license key', 'wp-statistics'); ?>";
                    }
                    const licenseSelect = jQuery('#wps_settings\\[geoip_license_type\\]');
                    licenseSelect.trigger('change.select2');
                    licenseSelect.select2({
                        dropdownCssClass: 'wps-setting-input__dropdown',
                        minimumResultsForSearch: Infinity,
                    });
                }

                // Add event listeners
                if (locationMethodElement) {
                    jQuery(locationMethodElement).on('change select2:select', updateLicenseTypeText);
                    updateLicenseTypeText();
                }

                // Ajax function for updating database
                document.querySelector("button[name='update_geoip']")?.addEventListener('click', function(event) {
                    event.preventDefault();
                    const geoipClickedButton = this;
                    geoipClickedButton.classList.add('wps-loading-button');

                    document.querySelectorAll(".wps-alert-box").forEach(el => el.remove());
                    const selectedLocationMethod = document.querySelector('#wps_settings\\[geoip_location_detection_method\\]').value || 'maxmind';

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
                        geoip_clicked_button.classList.remove('wps-loading-button')
                        jQuery(geoip_clicked_button).after("<div class='wps-alert wps-alert-box wps-alert__success'><span>" + result + "</span></div>")
                    }).error(function (result) {
                        geoip_clicked_button.classList.remove('wps-loading-button')
                        jQuery(geoip_clicked_button).after("<div class='wps-alert wps-alert-box wps-alert__danger'><span>" + _e('Oops! Something went wrong. Please try again. For more details, check the <b>PHP Error Log</b>.', 'wp-statistics') + "</span></div>")
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
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2">
                <h3><?php esc_html_e('Content Analytics', 'wp-statistics'); ?></h3>
            </th>
        </tr>

        <tr>
            <th scope="row"><span class="wps-setting-label"><?php esc_html_e('Word Count Analytics', 'wp-statistics'); ?></span></th>
            <td>
                <input id="word_count_analytics" type="checkbox" name="wps_word_count_analytics" <?php checked(WordCountService::isActive()) ?>>
                <label for="word_count_analytics"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>

                <p class="description">
                    <?php esc_html_e('Provides word count data for content and author analytics reports. Turning off this option will remove all word count-related reports.', 'wp-statistics'); ?>
                </p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" class="wps-sm-pb-0"><h3><?php esc_html_e('Purge Old Data Daily', 'wp-statistics'); ?></h3></th>
            <td class="wps-sm-pt-0">
                <div>
                    <?php $storedDays = Option::get('schedule_dbmaint_days', 180); ?>

                    <?php if ($storedDays != 0): ?>
                        <div class="alert alert-success"><span><?php esc_html_e('Next cleanup scheduled for', 'wp-statistics'); ?> <b><?php echo esc_html(DateTime::format('tomorrow midnight', ['exclude_year' => true, 'include_time' => true])) ?></b></span></div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>

        <tr data-id="purge_data_older_than_tr">
            <th scope="row">
                <label for="wps_settings[wps_schedule_dbmaint_days_select]"><?php esc_html_e('Automatically Aggregate Old Data', 'wp-statistics'); ?></label>
            </th>
            <td>
                <?php
                $presets        = [30, 60, 90, 180, 365, 730, 0]; // 0 for Forever
                $selectedOption = in_array($storedDays, $presets) ? $storedDays : 'custom';
                ?>
                <div class="wps-input-group">
                    <select id="wps_settings[wps_schedule_dbmaint_days_select]" name="wps_schedule_dbmaint_days_select" class="wps-input-group__field">
                        <option value="30" <?php selected($selectedOption, 30); ?>><?php esc_html_e('Keep details for 30 days', 'wp-statistics'); ?></option>
                        <option value="60" <?php selected($selectedOption, 60); ?>><?php esc_html_e('Keep details for 60 days', 'wp-statistics'); ?></option>
                        <option value="90" <?php selected($selectedOption, 90); ?>><?php esc_html_e('Keep details for 90 days', 'wp-statistics'); ?></option>
                        <option value="180" <?php selected($selectedOption, 180); ?>><?php esc_html_e('Keep details for 180 days', 'wp-statistics'); ?></option>
                        <option value="365" <?php selected($selectedOption, 365); ?>><?php esc_html_e('Keep details for 1 year', 'wp-statistics'); ?></option>
                        <option value="730" <?php selected($selectedOption, 730); ?>><?php esc_html_e('Keep details for 2 years', 'wp-statistics'); ?></option>
                        <option value="0" <?php selected($selectedOption, 0); ?>><?php esc_html_e('Keep data forever', 'wp-statistics'); ?></option>
                        <option value="custom" <?php selected($selectedOption, 'custom'); ?>><?php esc_html_e('Custom...', 'wp-statistics'); ?></option>
                    </select>
                </div>
                <div class="js-wps-show_if_wps_schedule_dbmaint_days_select_equal_custom">
                    <div class="wps-input-group wps-input-group__small description">
                        <input aria-label="<?php esc_html_e('Custom data retention period in days', 'wp-statistics'); ?>"
                               type="number" class="wps-input-group__field wps-input-group__field--small" id="wps_schedule_dbmaint_days_custom" min="30" max="3650" step="1"
                               value="<?php echo $selectedOption === 'custom' ? esc_attr($storedDays) : ''; ?>">
                        <span class="wps-input-group__label wps-input-group__label-side"><?php esc_html_e('Days', 'wp-statistics'); ?></span>
                    </div>
                </div>
                <input type="hidden" id="wps_schedule_dbmaint_days" name="wps_schedule_dbmaint_days" value="<?php echo esc_attr($storedDays); ?>">
                <p class="description">
                    <?php
                    echo sprintf(
                        esc_html__(
                            'Reduce database size and speed up reports. Each night at 00:00 (your WordPress timezone), data older than the period below is aggregated: we keep total Visitors and Views for the whole site and for each page, and delete the detailed rows. This cannot be undone. All-time reports still show Visitors/Views. %s',
                            'wp-statistics'
                        ),
                        '<a href="' . esc_url( WP_STATISTICS_SITE_URL . '/resources/aggregation-and-data-retention/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings' ) . '" target="_blank">' . esc_html__( 'Learn more in the Aggregation guide', 'wp-statistics' ) . '</a>'
                    );
                    ?>
                </p>

            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php
Modal::render('setting-confirmation', [
    'title'                => __('Confirmation', 'wp-statistics'),
    'description'          => __('This will permanently delete data from the database each day, are you sure you want to enable this option?', 'wp-statistics'),
    'primaryButtonText'    => __('Yes , Enable', 'wp-statistics'),
    'primaryButtonStyle'   => 'danger',
    'secondaryButtonText'  => __('Cancel', 'wp-statistics'),
    'secondaryButtonStyle' => 'cancel',
    'showCloseButton'      => true,
    'actions'              => [
        'primary'   => 'enable',
        'secondary' => 'closeModal',
    ],
]);


Modal::render('enable-automatic-data-deletion');
?>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Anonymous Usage Data', 'wp-statistics'); ?></h3></th>
        </tr>
        <tr data-id="share_anonymous_data_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Share Anonymous Data', 'wp-statistics'); ?></span>
            </th>
            <td>
                <input id="wps_share_anonymous_data" type="checkbox" name="wps_share_anonymous_data" <?php echo WP_STATISTICS\Option::get('share_anonymous_data') == true ? "checked='checked'" : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>'>
                <label for="wps_share_anonymous_data"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php printf(esc_html__('Sends non-personal, anonymized data to help us improve WP Statistics. No personal or identifying information is collected or shared. %s', 'wp-statistics'), '<a href="https://wp-statistics.com/resources/sharing-your-data-with-us/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings" target="_blank">' . esc_html__('Learn more.', 'wp-statistics') . '</a>'); ?></p>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Restore Default Settings', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr data-id="reset_options_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Reset Options', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input id="reset-plugin" type="checkbox" name="wps_reset_plugin">
                <label for="reset-plugin"><?php esc_html_e('Reset', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Revert all user-specific and global configurations to the WP Statistics default settings, preserving your existing data.', 'wp-statistics'); ?></p>
                <div class="wps-alert wps-alert__danger">
                    <?php echo sprintf(('<div class="wps-g-0"><b>%s</b>%s</div>'), __('For multisite users', 'wp-statistics'), __('Every site within the network will return to the default settings.', 'wp-statistics')); ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr class="wps-settings-box_head">
            <th scope="row" colspan="2"><h3><?php esc_html_e('Danger Zone', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr data-id="delete_all_data_on_plugin_deletion_tr">
            <th scope="row">
                <span class="wps-setting-label"><?php esc_html_e('Delete All Data on Plugin Deletion', 'wp-statistics'); ?></span>
            </th>

            <td>
                <input id="delete-data-on-uninstall" type="checkbox" name="wps_delete_data_on_uninstall" <?php checked(WP_STATISTICS\Option::get('delete_data_on_uninstall')) ?>>
                <label for="delete-data-on-uninstall"><?php esc_html_e('Enable', 'wp-statistics'); ?></label>
                <p class="description"><?php esc_html_e('Enable this option to automatically delete all WP Statistics data from your database when the plugin is deleted.', 'wp-statistics'); ?></p>
                <div class="wps-alert wps-alert__danger">
                    <?php esc_html_e('This action is permanent and cannot be undone. Make sure to back up your data before enabling this option.', 'wp-statistics'); ?>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
</div>

<?php submit_button(esc_html__('Update', 'wp-statistics'), 'wps-button wps-button--primary', 'submit', '', array('id' => 'advanced_submit', 'OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='advanced-settings'")); ?>
