<?php
	if( $wps_nonce_valid ) {
		$wps_option_list = array('wps_disable_map','wps_google_coordinates','wps_map_type','wps_disable_dashboard','wps_disable_editor');
		
		foreach( $wps_option_list as $option ) {
			$new_option = str_replace( "wps_", "", $option );

			if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }

			$WP_Statistics->store_option($new_option, $value);
		}
	}

// Only display the global options if the user is an administrator.
if( $wps_admin ) {
?>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Dashboard', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php _e('The following items are global to all users.', 'wp_statistics');?></td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="disable-map"><?php _e('Disable dashboard widgets', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="disable-dashboard" type="checkbox" value="1" name="wps_disable_dashboard" <?php echo $WP_Statistics->get_option('disable_dashboard')==true? "checked='checked'":'';?>>
				<label for="disable-dashboard"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Disable the dashboard widgets.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Page/Post Editor', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php _e('The following items are global to all users.', 'wp_statistics');?></td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="disable-map"><?php _e('Disable post/page editor widget', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="disable-editor" type="checkbox" value="1" name="wps_disable_editor" <?php echo $WP_Statistics->get_option('disable_editor')==true? "checked='checked'":'';?>>
				<label for="disable-editor"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Disable the page/post editor widget.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Map', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<td scope="row" colspan="2"><?php _e('The following items are global to all users.', 'wp_statistics');?></td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="wps_map_type"><?php _e('Map type', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<select name="wps_map_type">
				<?php
					foreach( array( __('Google', 'wp_statistics') => 'google', __('JQVMap', 'wp_statistics') => 'jqvmap') as $key => $value ) {
						echo "<option value=\"$value\"";
						if( $WP_Statistics->get_option('map_type') == $value ) { echo ' SELECTED'; }
						echo ">$key</option>";
					}
				?>
				</select>
				<p class="description"><?php _e('The "Google" option will use Google\'s mapping service to plot the recent visitors (requires access to Google).', 'wp_statistics'); ?></p>
				<p class="description"><?php _e('The "JQVMap" option will use JQVMap javascript mapping library to plot the recent visitors (requires no extenral services).', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="disable-map"><?php _e('Disable map', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo $WP_Statistics->get_option('disable_map')==true? "checked='checked'":'';?>>
				<label for="disable-map"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Disable the map display', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="google-coordinates"><?php _e('Get country location from Google', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="google-coordinates" type="checkbox" value="1" name="wps_google_coordinates" <?php echo $WP_Statistics->get_option('google_coordinates')==true? "checked='checked'":'';?>>
				<label for="google-coordinates"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('This feature may cause a performance degradation when viewing statistics and is only valid if the map type is set to "Google".', 'wp_statistics'); ?></p>
			</td>
		</tr>

	</tbody>
</table>	
<?php 
	}

submit_button(__('Update', 'wp_statistics'), 'primary', 'submit'); 

?>