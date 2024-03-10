<?php
use WP_STATISTICS\Country;
use WP_STATISTICS\GeoIP;

// Get IP Method
$ip_method = \WP_STATISTICS\IP::getIPMethod();

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
                <b><?php _e('$_SERVER', 'wp-statistics'); ?></b></td>
            <td style="border-bottom: 1px solid #ccc;padding-top:10px;padding-bottom:10px;"><b><?php _e('Value', 'wp-statistics'); ?></b></td>
        </tr>
        <?php
        foreach ($_SERVER as $key => $value) {
            // Check Value is Array
            if (is_array($value)) {
                $value = json_encode($value);
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
            <th scope="row" colspan="2"><h3><?php _e('Your IP Information', 'wp-statistics'); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2" style="padding-bottom: 10px; font-weight: normal;line-height: 25px;">
                <?php _e('Your IP address as detected by the Ipify.org service:', 'wp-statistics'); ?>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2">
                <code id="user_real_ip" style="display: inline-block; padding: 15px; font-family: 'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">
                    <script type="application/javascript">
                        jQuery(document).ready(function () {
                            jQuery.ajax({
                                url: "https://api.ipify.org?format=json",
                                dataType: 'json',
                                beforeSend: function () {
                                    jQuery("code#user_real_ip").html('Loading...');
                                },
                                error: function (jqXHR) {
                                    if (jqXHR.status == 0) {
                                        jQuery("code#user_real_ip").html("<?php _e('Unable to retrieve some IP data. Ensure your internet connection is active and retry.', 'wp-statistics'); ?>");
                                    }
                                },
                                success: function (json) {
                                    jQuery("code#user_real_ip").html(json['ip']);
                                }
                            });
                        });
                    </script>
                </code></th>
        </tr>

        </tbody>
    </table>
</div>

<div class="postbox">
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e('Main IP Detection Method', 'wp-statistics'); ?> <a href="#" class="wps-tooltip" title="<?php _e('Select the preferred method for determining the visitor\'s IP address. The method should correspond to the way your server and network infrastructure relay IP information. Choose the option that reflects the correct IP in your server environment.', 'wp-statistics'); ?>"><i class="wps-tooltip-icon"></i></a></h3></th>
        </tr>

        <?php
        foreach (\WP_STATISTICS\IP::$ip_methods_server as $method) {
            ?>
            <tr valign="top">
                <th scope="row" colspan="2" style="padding-top: 8px;padding-bottom: 8px;">
                    <table>
                        <tr>
                            <td style="width: 10px; padding: 0px;">
                                <input type="radio" name="ip_method" style="vertical-align: -3px;" value="<?php echo esc_attr($method); ?>"<?php if ($ip_method == $method) {
                                    echo " checked=\"checked\"";
                                } ?>>
                            </td>
                            <td style="width: 250px;"> <?php printf(__('Use <code>%1$s</code>', 'wp-statistics'), esc_attr($method)); ?></td>
                            <td><code><?php
                                    if (isset($_SERVER[$method]) and !empty($_SERVER[$method])) {
                                        echo esc_attr(wp_unslash($_SERVER[$method]));
                                    } else {
                                        _e('No available data.', 'wp-statistics');
                                    } ?>
                                </code>

                                <?php 
                                    if (!empty($_SERVER[$method]) && GeoIP::active()) { 
                                        $countryCode = GeoIP::getCountry(wp_unslash($_SERVER[$method]));
                                        $countryFlag = Country::flag($countryCode);
                                        $countryName = Country::getName($countryCode);
                                        
                                        ?><img src="<?php echo esc_url($countryFlag) ?>" alt="<?php echo esc_attr($countryName) ?>" title="<?php echo esc_attr($countryName) ?>" class="wps-flag" style="margin-left: 5px; vertical-align: top;"/><?php
                                    } 
                                ?>

                                <?php
                                if (isset($_SERVER[$method]) and !empty($_SERVER[$method]) and \WP_STATISTICS\IP::check_sanitize_ip($_SERVER[$method]) === false) {
                                    echo ' &nbsp;&nbsp;<a href="https://wp-statistics.com/sanitize-user-ip/" style="color: #d04f4f;" target="_blank" title="' . __('Your value required to sanitize user IP', 'wp-statistics') . '"><span class="dashicons dashicons-warning"></span></a>';
                                }
                                ?>
                            </td>
                        </tr>
                    </table>
                </th>
            </tr>
            <?php
        }
        ?>

        <!-- Custom Header -->
        <tr valign="top">
            <th scope="row" colspan="2" style="padding-top: 0px;padding-bottom: 0px;">
                <table>
                    <tr>
                        <td style="width: 10px; padding: 0px;">
                            <input type="radio" name="ip_method" style="vertical-align: -3px;" value="CUSTOM_HEADER" <?php if (!in_array($ip_method, \WP_STATISTICS\IP::$ip_methods_server)) {
                                echo " checked=\"checked\"";
                            } ?>>
                        </td>
                        <td style="width: 250px;"> <?php echo __('Specify a Custom Header for IP Detection', 'wp-statistics'); ?></td>
                        <td style="padding-left: 0px;">
                            <input type="text" name="user_custom_header_ip_method" autocomplete="off" style="padding: 5px; width: 250px;height: 35px;" value="<?php if (!in_array($ip_method, \WP_STATISTICS\IP::$ip_methods_server)) {
                                echo esc_attr($ip_method);
                            } ?>">

                            <p class="description">
                                <?php if (!in_array($ip_method, \WP_STATISTICS\IP::$ip_methods_server)) {
                                    echo '<code>';
                                    if (isset($_SERVER[$ip_method]) and !empty($_SERVER[$ip_method])) {
                                        echo sanitize_text_field(wp_unslash($_SERVER[$ip_method]));
                                    } else {
                                        _e('No available data.', 'wp-statistics');
                                    }
                                }
                                echo '</code>';
                                if (!in_array($ip_method, \WP_STATISTICS\IP::$ip_methods_server) and isset($_SERVER[$ip_method]) and !empty($_SERVER[$ip_method]) and \WP_STATISTICS\IP::check_sanitize_ip($_SERVER[$ip_method]) === false) {
                                    echo ' &nbsp;&nbsp;<a href="https://wp-statistics.com/sanitize-user-ip/" style="color: #d04f4f;" target="_blank" title="' . __('Your value required to sanitize user IP', 'wp-statistics') . '"><span class="dashicons dashicons-warning"></span></a>';
                                }
                                ?></p>
                            <p class="description"><?php _e('If your server uses a custom key in <code>$_SERVER</code> for IP detection (e.g., <code>HTTP_CF_CONNECTING_IP</code> for CloudFlare), specify it here.', 'wp-statistics'); ?></p>
                            <p class="description">
                                <a href="#TB_inline?&width=850&height=600&inlineId=list-of-php-server" class="thickbox"><?php _e('View <code>$_SERVER</code> in your server.', 'wp-statistics'); ?></a>
                            </p>
                            <p class="description"><?php echo sprintf(__('Refer to our <a href="%s" target="_blank">Documentation</a> for more info and how to configure IP Detection properly.', 'wp-statistics'), 'https://wp-statistics.com/resources/how-to-configure-ip-detection-in-wp-statistics-for-accurate-visitor-tracking/?utm_source=wp-statistics&utm_medium=link&utm_campaign=settings'); ?></p>
                        </td>
                    </tr>
                </table>
            </th>
        </tr>

        </tbody>
    </table>
</div>

<?php submit_button(__('Update', 'wp-statistics'), 'primary', 'submit', '', array('OnClick' => "var wpsCurrentTab = getElementById('wps_current_tab'); wpsCurrentTab.value='ip-configuration-settings'")); ?>
