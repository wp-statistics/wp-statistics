<?php 
$selist = wp_statistics_searchengine_list( true );

if( $wps_nonce_valid ) {

	// We need to handle a change in the report schedule manually, so check to see it has been set.
	if( array_key_exists( 'wps_time_report', $_POST ) ) {
		// If the report has been changed, we need to update the schedule.
		if( $WP_Statistics->get_option('time_report') != $_POST['wps_time_report'] ) {
			// Remove the old schedule if it exists.
			if( wp_next_scheduled('report_hook') ) {
				wp_unschedule_event(wp_next_scheduled('report_hook'), 'report_hook');
			}

			// Setup the new schedule, we could just let this fall through and let the code in schedule.php deal with it
			// but that would require an extra page load to start the schedule so do it here instead.
			wp_schedule_event(time(), $_POST['wps_time_report'], 'report_hook');
		}
	}

	$wps_option_list = array("wps_stats_report","wps_time_report","wps_send_report","wps_content_report","wps_email_list","wps_browscap_report","wps_geoip_report","wps_prune_report","wps_upgrade_report");
	
	foreach( $wps_option_list as $option ) {
		if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }

		if(get_magic_quotes_gpc())
			$value = stripslashes($value);

		$new_option = str_replace( "wps_", "", $option );
		$WP_Statistics->store_option($new_option, $value);
	}
}

?>
<script type="text/javascript">
	function ToggleStatOptions() {
		jQuery('[id^="wps_stats_report_option"]').fadeToggle();	
	}
</script>

<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Common Report Options', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<td scope="row" style="vertical-align: top;">
				<label for="email-report"><?php _e('E-mail addresses', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input type="text" id="email_list" name="wps_email_list" size="30" value="<?php if( $WP_Statistics->get_option('email_list') == '' ) { $WP_Statistics->store_option('email_list', get_bloginfo('admin_email')); } echo $WP_Statistics->get_option('email_list'); ?>"/>
				<p class="description"><?php _e('A comma separated list of e-mail addresses to send reports to.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Update Reports', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<td scope="row">
				<label for="browscap-report"><?php _e('Browscap', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input id="browscap-report" type="checkbox" value="1" name="wps_browscap_report" <?php echo $WP_Statistics->get_option('browscap_report')==true? "checked='checked'":'';?>>
				<label for="browscap-report"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Send a report whenever the browscap.ini is updated.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<td scope="row">
				<label for="geoip-report"><?php _e('GeoIP', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input id="geoip-report" type="checkbox" value="1" name="wps_geoip_report" <?php echo $WP_Statistics->get_option('geoip_report')==true? "checked='checked'":'';?>>
				<label for="geoip-report"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Send a report whenever the GeoIP database is updated.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<td scope="row">
				<label for="prune-report"><?php _e('Pruning', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input id="prune-report" type="checkbox" value="1" name="wps_prune_report" <?php echo $WP_Statistics->get_option('prune_report')==true? "checked='checked'":'';?>>
				<label for="prune-report"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Send a report whenever the pruning of database is run.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<td scope="row">
				<label for="upgrade-report"><?php _e('Upgrade', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input id="upgrade-report" type="checkbox" value="1" name="wps_upgrade_report" <?php echo $WP_Statistics->get_option('upgrade_report')==true? "checked='checked'":'';?>>
				<label for="upgrade-report"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Send a report whenever the plugin is upgraded.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Statistical reporting', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="stats-report"><?php _e('Statistical reporting', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="stats-report" type="checkbox" value="1" name="wps_stats_report" <?php echo $WP_Statistics->get_option('stats_report')==true? "checked='checked'":'';?> onClick='ToggleStatOptions();'>
				<label for="stats-report"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<?php if( $WP_Statistics->get_option('stats_report') ) { $hidden=""; } else { $hidden=" style='display: none;'"; }?>
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row" style="vertical-align: top;">
				<label for="time-report"><?php _e('Schedule', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<select name="wps_time_report" id="time-report">
					<option value="0" <?php selected($WP_Statistics->get_option('time_report'), '0'); ?>><?php _e('Please select', 'wp_statistics'); ?></option>
<?php
					function wp_statistics_schedule_sort( $a, $b ) {
						if ($a['interval'] == $b['interval']) {
							return 0;
							}
							
						return ($a['interval'] < $b['interval']) ? -1 : 1;
					}
					
					$schedules = wp_get_schedules();
					
					uasort( $schedules, 'wp_statistics_schedule_sort' );
					
					foreach( $schedules as $key => $value ) {
						echo '					<option value="' . $key . '" ' . selected($WP_Statistics->get_option('time_report'), $key) . '>' . $value['display'] . '</option>';
					}
?>					
				</select>
				<p class="description"><?php _e('Select how often to receive statistical report.', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row" style="vertical-align: top;">
				<label for="send-report"><?php _e('Send reports via', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<select name="wps_send_report" id="send-report">
					<option value="0" <?php selected($WP_Statistics->get_option('send_report'), '0'); ?>><?php _e('Please select', 'wp_statistics'); ?></option>
					<option value="mail" <?php selected($WP_Statistics->get_option('send_report'), 'mail'); ?>><?php _e('Email', 'wp_statistics'); ?></option>
				<?php if( is_plugin_active('wp-sms/wp-sms.php') ) { ?>
					<option value="sms" <?php selected($WP_Statistics->get_option('send_report'), 'sms'); ?>><?php _e('SMS', 'wp_statistics'); ?></option>
				<?php } ?>
				</select>
				<p class="description"><?php _e('Select delivery method for statistical report.', 'wp_statistics'); ?></p>
				
				<?php if( !is_plugin_active('wp-sms/wp-sms.php') ) { ?>
					<p class="description note"><?php echo sprintf(__('Note: To send SMS text messages please install the %s plugin.', 'wp_statistics'), '<a href="http://wordpress.org/extend/plugins/wp-sms/" target="_blank">' . __('WordPress SMS', 'wp_statistics') . '</a>'); ?></p>
				<?php } ?>
			</td>
		</tr>
		
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row"  style="vertical-align: top;">
				<label for="content-report"><?php _e('Report body', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<?php wp_editor( $WP_Statistics->get_option('content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5) ); ?>
				<p class="description"><?php _e('Enter the contents of the report.', 'wp_statistics'); ?></p>
				<p class="description data">
					<?php _e('Any shortcode supported by your installation of WordPress, include all shortcodes for WP Statistics (see the admin manual for a list of codes available) are supported in the body of the message.  Here are some examples:', 'wp_statistics'); ?><br><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('User Online', 'wp_statistics'); ?>: <code>[wpstatistics stat=usersonline]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Today Visitor', 'wp_statistics'); ?>: <code>[wpstatistics stat=visitors time=today]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Today Visit', 'wp_statistics'); ?>: <code>[wpstatistics stat=visits time=today]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Yesterday Visitor', 'wp_statistics'); ?>: <code>[wpstatistics stat=visitors time=yesterday]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Yesterday Visit', 'wp_statistics'); ?>: <code>[wpstatistics stat=visits time=yesterday]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Total Visitor', 'wp_statistics'); ?>: <code>[wpstatistics stat=visitors time=total]</code><br>
					&nbsp;&nbsp;&nbsp;&nbsp;<?php _e('Total Visit', 'wp_statistics'); ?>: <code>[wpstatistics stat=visits time=total]</code><br>
				</p>
			</td>
		</tr>
	</tbody>
</table>