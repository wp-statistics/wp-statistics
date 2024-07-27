<?php

use WP_STATISTICS\IP;

// Get IP Method
$ip_method  = IP::getIpMethod();
$ip_address = IP::getIP();
$ip_version = IP::getIpVersion();

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
                            <input id="custom-header" type="radio" name="ip_method" style="vertical-align: -3px;" value="CUSTOM_HEADER" <?php echo in_array($ip_method, IP::getIpOptions()) ? checked(true) : '' ?>>
                        </td>
                        <td style="width: 250px;">
                            <label for="custom-header"><?php esc_html_e('Specify a Custom Header for IP Detection', 'wp-statistics'); ?></label>
                        </td>
                        <td style="padding-left: 0px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <input type="text" name="user_custom_header_ip_method" autocomplete="off" style="padding: 5px; width: 250px;height: 35px;" value="<?php echo in_array($ip_method, IP::getIpOptions()) ? esc_attr($ip_method) : '' ?>">
                                <?php if (in_array($ip_method, IP::getIpOptions()) && empty($_SERVER[$ip_method])) {
                                    _e('<code>Result: No IP detected</code>', 'wp-statistics');
                                } ?>
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

<?php submit_button(esc_html__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='ip-configuration-settings'")); ?>
