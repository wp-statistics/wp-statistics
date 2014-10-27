<?php 
if( $wps_nonce_valid ) {

	$wps_option_list = array("wps_browscap","wps_update_browscap","wps_schedule_browscap");
	
	foreach( $wps_option_list as $option ) {
		$new_option = str_replace( "wps_", "", $option );
		if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }
		$WP_Statistics->store_option($new_option, $value);
	}
	
	// If we're focing the download of the browscap.ini file, make sure to flush the last download time from the options.
	if( array_key_exists( 'wps_update_browscap', $_POST ) ) {
		$WP_Statistics->store_option('last_browscap_dl', 0);
	}
}

?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('browscap settings', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="browscap-enable"><?php _e('browscap usage', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="browscap-enable" type="checkbox" name="wps_browscap" <?php echo $WP_Statistics->get_option('browscap')==true? "checked='checked'":'';?>>
				<label for="browscap-enable"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('The browscap database will be downloaded and used to detect robots.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="geoip-update"><?php _e('Update browscap Info', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="browscap-update" type="checkbox" name="wps_update_browscap" <?php echo $WP_Statistics->get_option('update_browscap')==true? "checked='checked'":'';?>>
				<label for="browscap-update"><?php _e('Download browscap Database', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Save changes on this page to download the update.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="browscap-schedule"><?php _e('Schedule weekly update of browscap DB', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="browscap-schedule" type="checkbox" name="wps_schedule_browscap" <?php echo $WP_Statistics->get_option('schedule_browscap')==true? "checked='checked'":'';?>>
				<label for="browscap-schedule"><?php _e('Active', 'wp_statistics'); ?></label>
				<?php 
					if( $WP_Statistics->get_option('schedule_browscap') ) {
						echo '<p class="description">' . __('Next update will be') .': <code>';
						$last_update = $WP_Statistics->get_option('last_browscap_dl');
						$next_update = $last_update + (86400 * 7);
						
						$next_schedule = wp_next_scheduled('wp_statistics_browscap_hook');
						
						if( $next_schedule ) {
							echo date( get_option('date_format'), $next_update ) . ' @ ' . date( get_option('time_format'), $next_schedule );
						} else {
							echo date( get_option('date_format'), $next_update ) . ' @ ' . date( get_option('time_format'), time() );
						}
						
						echo '</code></p>';
					}
				?>
				<p class="description"><?php _e('Download of the browscap database will be scheduled for once a week.', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
	</tbody>
</table>