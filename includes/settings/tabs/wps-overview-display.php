<?php
if ( $wps_nonce_valid ) {
	$wps_option_list = array( 'wps_disable_map', 'wps_disable_dashboard', 'wps_disable_editor' );

	foreach ( $wps_option_list as $option ) {
		$new_option = str_replace( 'wps_', '', $option );

		if ( array_key_exists( $option, $_POST ) ) {
			$value = $_POST[ $option ];
		} else {
			$value = '';
		}

		$WP_Statistics->store_option( $new_option, $value );
	}
}

// Only display the global options if the user is an administrator.
if ( $wps_admin ) {
	?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Dashboard', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php _e( 'The following items are global to all users.', 'wp-statistics' ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php _e( 'Disable dashboard widgets', 'wp-statistics' ); ?>:</label>
            </th>

            <td>
                <input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo $WP_Statistics->get_option( 'disable_dashboard' ) == true ? "checked='checked'" : ''; ?>>
                <label for="disable-dashboard"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
                <p class="description"><?php _e( 'Disable the dashboard widgets.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Page/Post Editor', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php _e( 'The following items are global to all users.', 'wp-statistics' ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php _e( 'Disable post/page editor widget', 'wp-statistics' ); ?>:</label>
            </th>

            <td>
                <input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo $WP_Statistics->get_option( 'disable_editor' ) == true ? "checked='checked'" : ''; ?>>
                <label for="disable-editor"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
                <p class="description"><?php _e( 'Disable the page/post editor widget.', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'Map', 'wp-statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <td scope="row" colspan="2"><?php _e( 'The following items are global to all users.', 'wp-statistics' ); ?></td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="disable-map"><?php _e( 'Disable map', 'wp-statistics' ); ?>:</label>
            </th>

            <td>
                <input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo $WP_Statistics->get_option( 'disable_map' ) == true ? "checked='checked'" : ''; ?>>
                <label for="disable-map"><?php _e( 'Enable', 'wp-statistics' ); ?></label>
                <p class="description"><?php _e( 'Disable the map display', 'wp-statistics' ); ?></p>
            </td>
        </tr>

        </tbody>
    </table>
	<?php
}

submit_button( __( 'Update', 'wp-statistics' ), 'primary', 'submit' );

?>