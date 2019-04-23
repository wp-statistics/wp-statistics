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

// Add TickBox
add_thickbox();

?>
    <!-- Show Help $_SERVER -->
    <div id="list-of-php-server" style="display:none;">
        <table>
            <tr>
                <td width="330" style="color: #3238fb; border-bottom: 1px solid #bcbeff;padding-top:10px;padding-bottom:10px;">
                    <b><?php _e('$_SERVER', 'wp-statistics'); ?></b></td>
                <td style="color: #3238fb; border-bottom: 1px solid #bcbeff;padding-top:10px;padding-bottom:10px;"> <?php _e('Value', 'wp-statistics'); ?></td>
            </tr>
			<?php
			foreach ( $_SERVER as $key => $value ) {
				?>
                <tr>
                    <td width="330" style="padding-top:10px;padding-bottom:10px;">
                        <b><?php echo $key; ?></b></td>
                    <td style="padding-top:10px;padding-bottom:10px;"> <?php echo ( $value == "" ? "-" : substr( str_replace( array( "\n", "\r" ), '', trim( $value ) ), 0, 200 ) ) . ( strlen( $value ) > 200 ? '..' : '' ); ?></td>
                </tr>
				<?php
			}
			?>
        </table>
    </div>
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
                        jQuery(document).ready(function () {
                            jQuery.ajax({
                                url: "https://api.ipify.org?format=json",
                                dataType: 'json',
                                error: function (jqXHR) {
                                    if (jqXHR.status == 0) {
                                        jQuery("code#user_real_ip").html("<?php _e( 'Please check your internet connection and try again.', 'wp-statistics' ); ?>");
                                    }
                                },
                                success: function (json) {
                                    jQuery("code#user_real_ip").html(json['ip']);
                                }
                            });
                        });
                    </script>
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
                            <input type="text" name="user_custom_header_ip_method" autocomplete="off" style="padding: 5px; width: 250px;height: 35px;" value="<?php if ( ! in_array( $ip_method, WP_Statistics::list_of_server_ip_variable() ) ) {
								echo $ip_method;
							} ?>">

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
                            <p class="description"><?php _e( 'if You are Using Custom $_SERVER in your site e.g. `HTTP_CF_CONNECTING_IP` for CloudFlare Service.', 'wp-statistics' ); ?></p>
                            <p class="description"><a href="#TB_inline?&width=850&height=600&inlineId=list-of-php-server" class="thickbox"><?php _e( 'Show all $_SERVER in Your Server.', 'wp-statistics' ); ?></a></p>
                        </td>
                    </tr>
                </table>
            </th>
        </tr>

        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );