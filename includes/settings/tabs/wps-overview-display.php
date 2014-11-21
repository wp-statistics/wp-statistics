<?php
	if( !$WP_Statistics->isset_user_option('overview_display') ) {
		$WP_Statistics->store_user_option('overview_display', array(
			'A' => array(
				1 => 'summary', 
				2 => 'browsers',
				3 => 'referring',
				4 => 'countries',
				5 => 'about' 
			),
			'B' => 	array( 
				1 => 'map',
				2 => 'hits',
				3 => 'search',
				4 => 'words',
				5 => 'pages',
				6 => 'recent' 
			),
		));
	}
	
	$column_a_list = array(
		'none'		=> __('None', 'wp_statistics'),
		'summary' 	=> __('Summary Statistics', 'wp_statistics'),
		'browsers' 	=> __('Browsers', 'wp_statistics'),
		'referring' => __('Top Referring Sites', 'wp_statistics'),
		'countries' => __('Top 10 Countries', 'wp_statistics'),
		'about' 	=> __('About', 'wp_statistics'),
	);
	
	$column_b_list = array(
		'none'		=> __('None', 'wp_statistics'),
		'map' 		=> __('Map', 'wp_statistics'),
		'hits' 		=> __('Hits Statistical Chart', 'wp_statistics'),
		'search' 	=> __('Search Engine Referrers Statistical Chart', 'wp_statistics'),
		'words' 	=> __('Latest Search Words', 'wp_statistics'),
		'pages' 	=> __('Top Pages Visited', 'wp_statistics'),
		'recent' 	=> __('Recent Visitors', 'wp_statistics'),
	);
	
	if( $wps_nonce_valid ) {
		$wps_option_list = array("wps_disable_map","wps_google_coordinates","wps_map_type","wps_disable_dashboard");
		
		foreach( $wps_option_list as $option ) {
			$new_option = str_replace( "wps_", "", $option );

			if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }

			$WP_Statistics->store_option($new_option, $value);
		}
		
		for( $i = 1; $i < 6; $i++ ) {
			$display_array['A'][$i] = '';
			if( array_key_exists( $_POST['wps_display']['A'][$i], $column_a_list) ) { $display_array['A'][$i] = $_POST['wps_display']['A'][$i]; }
		}
		
		for( $i = 1; $i < 7; $i++) {
			$display_array['B'][$i] = '';
			if( array_key_exists( $_POST['wps_display']['B'][$i], $column_b_list) ) { $display_array['B'][$i] = $_POST['wps_display']['B'][$i]; }
		}
		
		$WP_Statistics->store_user_option('overview_display', $display_array );

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
<?php } ?>

<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Overview Widgets to Display', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<td scope="row" colspan="3"><?php _e('The following items are unique to each user.  If you do not select the \'About\' widget it will automatically be displayed in the last positon of column A.', 'wp_statistics');?></td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot', 'wp_statistics'); ?>
			</th>
			
			<th>
				<?php _e('Column A', 'wp_statistics'); ?>
			</th>
			
			<th>
				<?php _e('Column B', 'wp_statistics'); ?>
			</th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 1', 'wp_statistics'); ?>
			</th>
			
			<td>
				<select name="wps_display[A][1]">
				<?php
					foreach( $column_a_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['A'][1] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
			
			<td>
				<select name="wps_display[B][1]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][1] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 2', 'wp_statistics'); ?>
			</th>
			
			<td>
				<select name="wps_display[A][2]">
				<?php
					foreach( $column_a_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['A'][2] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
			
			<td>
				<select name="wps_display[B][2]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][2] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 3', 'wp_statistics'); ?>
			</th>
			
			<td>
				<select name="wps_display[A][3]">
				<?php
					foreach( $column_a_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['A'][3] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
			
			<td>
				<select name="wps_display[B][3]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][3] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 4', 'wp_statistics'); ?>
			</th>
			
			<td>
				<select name="wps_display[A][4]">
				<?php
					foreach( $column_a_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['A'][4] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
			
			<td>
				<select name="wps_display[B][4]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][4] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 5', 'wp_statistics'); ?>
			</th>
			
			<td>
				<select name="wps_display[A][5]">
				<?php
					foreach( $column_a_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['A'][5] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
			
			<td>
				<select name="wps_display[B][5]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][5] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<?php _e('Slot 6', 'wp_statistics'); ?>
			</th>
			
			<td>
				<?php _e('N/A', 'wp_statistics');?>
			</td>
			
			<td>
				<select name="wps_display[B][6]">
				<?php
					foreach( $column_b_list as $key => $value ) {
						echo "<option value=\"$key\"";
						if( $WP_Statistics->user_options['overview_display']['B'][6] == $key ) { echo ' SELECTED'; }
						echo ">$value</option>";
					}
				?>
				</select>
			</td>
		</tr>
	</tbody>
</table>