<?php

// Save Option
if ( $wps_nonce_valid and $wps_admin ) {

	$value = 'REMOTE_ADDR';
	if ( isset( $_POST['ip_method'] ) and ! empty( $_POST['ip_method'] ) ) {

		// Check Custom Header
		if ( $_POST['ip_method'] == "CUSTOM_HEADER" ) {
			if ( trim( $_POST['user_custom_header_ip_method'] ) != "" ) {
				$value = $_POST['user_custom_header_ip_method'];
			}
		} else {
			$value = $_POST['ip_method'];
		}
	}

	$WP_Statistics->update_option( 'ip_method', $value );
}

// Get IP Method
$ip_method = WP_Statistics::getIPMethod();
?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2" style="padding-bottom: 10px; font-weight: normal;line-height: 25px;"><?php _e( 'Please choose the basis for receiving users IP according to your site\'s server.', 'wp-statistics' ); ?>
                <br/> <?php _e( 'Your Real IP according to ipify.org API is :', 'wp-statistics' ); ?>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2">
                <code id="user_real_ip" style="padding: 15px;font-size: 30px;font-weight: 200; letter-spacing: 2px;font-family: 'Segoe UI',Roboto,Oxygen-Sans,Ubuntu,Cantarell,'Helvetica Neue',sans-serif;">
                    <script type="application/javascript">
                        function wp_statistics_get_user_ip(json) {
                            if (typeof json['ip'] != "undefined") {
                                jQuery("code#user_real_ip").html(json['ip']);
                            } else {
                                jQuery("code#user_real_ip").html("<?php _e( 'Error connection to server. Please check your internet connection and try again.', 'wp-statistics' ); ?>");
                            }
                        }
                    </script>
                    <script type="application/javascript" src="https://api.ipify.org?format=jsonp&callback=wp_statistics_get_user_ip"></script>
                </code><br/><br/></th>
        </tr>

		<?php
		foreach ( WP_Statistics::list_of_server_ip_variable() as $method ) {
			?>
            <tr valign="top">
                <th scope="row" colspan="2" style="padding-top: 8px;padding-bottom: 8px;">
                    <table>
                        <tr>
                            <td style="width: 10px; padding: 0px;">
                                <input type="radio" name="ip_method" style="vertical-align: -3px;" value="<?php echo $method; ?>"<?php if ( $ip_method == $method ) {
									echo " checked=\"checked\"";
								} ?>>
                            </td>
                            <td style="width: 250px;"> <?php printf( __( 'Use %1$s', 'wp-statistics' ), $method ); ?></td>
                            <td><code><?php if ( isset( $_SERVER[ $method ] ) and ! empty( $_SERVER[ $method ] ) ) {
										echo $_SERVER[ $method ];
									} else {
										_e( 'No available data.', 'wp-statistics' );
									} ?></code></td>
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
                            <input type="radio" name="ip_method" style="vertical-align: -3px;" value="CUSTOM_HEADER" <?php if ( ! in_array( $ip_method, WP_Statistics::list_of_server_ip_variable() ) ) {
								echo " checked=\"checked\"";
							} ?>>
                        </td>
                        <td style="width: 250px;"> <?php echo __( 'Use Custom Header', 'wp-statistics' ); ?></td>
                        <td style="padding-left: 0px;">
                            <input type="text" name="user_custom_header_ip_method" style="padding: 5px; width: 250px;height: 35px;" value="<?php if ( ! in_array( $ip_method, WP_Statistics::list_of_server_ip_variable() ) ) {
								echo $ip_method;
							} ?>">
                            <p class="description"><?php _e( 'if You are Using Custom $_SERVER in your site e.g. `HTTP_CF_CONNECTING_IP` for CloudFlare Service.', 'wp-statistics' ); ?></p>
                            <p class="description">
								<?php if ( ! in_array( $ip_method, WP_Statistics::list_of_server_ip_variable() ) ) {
									echo '<code>';
									if ( isset( $_SERVER[ $ip_method ] ) and ! empty( $_SERVER[ $ip_method ] ) ) {
										echo $_SERVER[ $ip_method ];
									} else {
										_e( 'No available data.', 'wp-statistics' );
									}
								}
								echo '</code>';
								?></p>
                        </td>
                    </tr>
                </table>
            </th>
        </tr>

        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );