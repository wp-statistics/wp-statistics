<?php
if ( $wps_nonce_valid ) {

	if ( array_key_exists( 'wps_remove_plugin', $_POST ) ) {
		if ( is_super_admin() ) {
			update_option( 'wp_statistics_removal', 'true' );

			// We need to reload the page after we reset the options but it's too late to do it through a HTTP redirect so do a 
			// JavaScript redirect instead.
			echo '<script type="text/javascript">window.location.href="' . admin_url() . 'plugins.php";</script>';
		}
	}

	if ( array_key_exists( 'wps_reset_plugin', $_POST ) ) {
		if ( is_super_admin() ) {
			GLOBAL $wpdb, $WP_Statistics;

			$default_options = $WP_Statistics->Default_Options();

			// Handle multi site implementations
			if ( is_multisite() ) {
				// Loop through each of the sites.
				$sites = $WP_Statistics->get_wp_sites_list();
				foreach ( $sites as $blog_id ) {

					switch_to_blog( $blog_id );

					// Delete the wp_statistics option.
					update_option( 'wp_statistics', array() );
					// Delete the user options.
					$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

					$WP_Statistics->load_options();

					// Set some intelligent defaults.
					foreach ( $default_options as $key => $value ) {
						if ( ! in_array( $key, $excluded_defaults ) && false === $WP_Statistics->get_option( $key ) ) {
							$WP_Statistics->store_option( $key, $value );
						}
					}

					$WP_Statistics->save_options();
				}

				restore_current_blog();
			} else {
				// Delete the wp_statistics option.
				update_option( 'wp_statistics', array() );
				// Delete the user options.
				$wpdb->query( "DELETE FROM {$wpdb->prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'" );

				$WP_Statistics->load_options();

				// Set some intelligent defaults.
				foreach ( $default_options as $key => $value ) {
					if ( ! in_array( $key, $excluded_defaults ) && false === $WP_Statistics->get_option( $key ) ) {
						$WP_Statistics->store_option( $key, $value );
					}
				}

				$WP_Statistics->save_options();
			}

			// We need to reload the page after we reset the options but it's too late to do it through a HTTP redirect so do a 
			// JavaScript redirect instead.
			echo '<script type="text/javascript">window.location.href="' . admin_url() . 'admin.php?page=wps_settings_page";</script>';
		}
	}
}

?>
    <table class="form-table">
        <tbody>
        <tr valign="top">
            <th scope="row" colspan="2"><h3><?php _e( 'WP Statisitcs Removal', 'wp_statistics' ); ?></h3></th>
        </tr>

        <tr valign="top">
            <th scope="row" colspan="2">
				<?php _e( 'Uninstalling WP Statistics will not remove the data and settings, you can use this option to remove the WP Statistics data from your install before uninstalling the plugin.', 'wp_statistics' ); ?>
                <br>
                <br>
				<?php _e( 'Once you submit this form the settings will be deleted during the page load, however WP Statistics will still show up in your Admin menu until another page load is executed.', 'wp_statistics' ); ?>
            </th>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="reset-plugin"><?php _e( 'Reset options', 'wp_statistics' ); ?>:</label>
            </th>

            <td>
                <input id="reset-plugin" type="checkbox" name="wps_reset_plugin">
                <label for="reset-plugin"><?php _e( 'Reset', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Reset the plugin options to the defaults.  This will remove all user and global settings but will keep all other data.  This action cannot be undone.  Note: For multi-site installs this will reset all sites to the defaults.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        <tr valign="top">
            <th scope="row">
                <label for="remove-plugin"><?php _e( 'Remove data and settings', 'wp_statistics' ); ?>:</label>
            </th>

            <td>
                <input id="remove-plugin" type="checkbox" name="wps_remove_plugin">
                <label for="remove-plugin"><?php _e( 'Remove', 'wp_statistics' ); ?></label>
                <p class="description"><?php _e( 'Remove data and settings, this action cannot be undone.', 'wp_statistics' ); ?></p>
            </td>
        </tr>

        </tbody>
    </table>

<?php submit_button( __( 'Update', 'wp_statistics' ), 'primary', 'submit' ); ?>