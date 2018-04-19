<?php
if ( $wps_nonce_valid ) {
	$wps_option_list = array(
		'wps_store_ua',
		'wps_hash_ips',
		'wps_all_online',
	);

	// If the IP hash's are enabled, disable storing the complete user agent.
	if ( array_key_exists( 'wps_hash_ips', $_POST ) ) {
		$_POST['wps_store_ua'] = '';
	}

	foreach ( $wps_option_list as $option ) {
		if ( array_key_exists( $option, $_POST ) ) {
			$value = $_POST[ $option ];
		} else {
			$value = '';
		}
		$new_option = str_replace( "wps_", "", $option );
		$WP_Statistics->store_option( $new_option, $value );
	}
}
?>
    <script type="text/javascript">
        function ToggleShowHitsOptions() {
            jQuery('[id^="wps_show_hits_option"]').fadeToggle();
        }
    </script>

    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'IP Addresses', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e( 'Hash IP Addresses', 'wp-statistics' ); ?>:</label>
            </th>

            <td>
                <input id="hash_ips" type="checkbox" value="1"
                       name="wps_hash_ips" <?php echo $WP_Statistics->get_option( 'hash_ips' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="hash_ips"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php _e(
						'This feature will not store IP addresses in the database but instead used a unique hash.  The "Store entire user agent string" setting will be disabled if this is selected.  You will not be able to recover the IP addresses in the future to recover location information if this is enabled.',
						'wp-statistics'
					); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="store_ua"><?php _e( 'Store entire user agent string', 'wp-statistics' ); ?>:</label>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1"
                       name="wps_store_ua" <?php echo $WP_Statistics->get_option( 'store_ua' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="store_ua"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php _e( 'Only enabled for debugging', 'wp-statistics' ); ?></p>
            </td>
        </tr>
        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );