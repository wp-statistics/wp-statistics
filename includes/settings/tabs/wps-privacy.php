<?php
if ( $wps_nonce_valid ) {
	$wps_option_list = array(
		'wps_anonymize_ips',
		'wps_hash_ips',
		'wps_store_ua',
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
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Privacy and Data Protection', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php echo sprintf( __( 'If you want to delete visitor data, please <a href="%s">click here</a>.', 'wp-statistics' ), WP_Statistics_Admin_Pages::admin_url( 'optimization', array( 'tab' => 'purging' ) ) ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="anonymize_ips"><?php _e( 'Anonymize IP Addresses:', 'wp-statistics' ); ?></label>
            </th>

            <td>
                <input id="anonymize_ips" type="checkbox" value="1" name="wps_anonymize_ips" <?php echo $WP_Statistics->get_option( 'anonymize_ips' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="anonymize_ips"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php echo __( 'This option anonymizes the user IP address for GDPR compliance. For example, 888.888.888.888 > 888.888.888.000.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="hash_ips"><?php _e( 'Hash IP Addresses:', 'wp-statistics' ); ?></label>
            </th>

            <td>
                <input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo $WP_Statistics->get_option( 'hash_ips' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="hash_ips"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php echo __(
					                                  'This feature will not store IP addresses in the database but instead used a unique hash.',
					                                  'wp-statistics'
				                                  ) . ' ' . __(
					                                  'The "Store entire user agent string" setting will be disabled if this is selected.',
					                                  'wp-statistics'
				                                  ) . ' ' . __(
					                                  'You will not be able to recover the IP addresses in the future to recover location information if this is enabled.',
					                                  'wp-statistics'
				                                  ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="store_ua"><?php _e( 'Store entire user agent string:', 'wp-statistics' ); ?></label>
            </th>

            <td>
                <input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo $WP_Statistics->get_option( 'store_ua' ) == true
					? "checked='checked'" : ''; ?>>
                <label for="store_ua"><?php _e( 'Enable', 'wp-statistics' ); ?></label>

                <p class="description"><?php _e( 'Only enabled for debugging. (If the IP hash\'s are enabled, This option disabling automatically.)', 'wp-statistics' ); ?></p>
            </td>
        </tr>
        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );
