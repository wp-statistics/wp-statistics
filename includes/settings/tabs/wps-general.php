<?php 
$selist = wp_statistics_searchengine_list( true );

if( $wps_nonce_valid ) {
	foreach( $selist as $se ) {
		$se_post = 'wps_disable_se_' . $se['tag'];
		
		if( array_key_exists( $se_post, $_POST ) ) { $value = $_POST[$se_post]; } else { $value = ''; }
		$new_option = str_replace( "wps_", "", $se_post );
		$WP_Statistics->store_option($new_option, $value);
	}

	$wps_option_list = array("wps_useronline","wps_visits","wps_visitors","wps_pages","wps_track_all_pages","wps_disable_column","wps_check_online","wps_menu_bar","wps_coefficient","wps_stats_report","wps_time_report","wps_send_report","wps_content_report","wps_chart_totals","wps_store_ua","wps_hide_notices","wps_email_list","wps_delete_manual","wps_hash_ips" );
	
	// If the IP hash's are enabled, disable storing the complete user agent.
	if( array_key_exists( 'wps_hash_ips', $_POST ) ) { $_POST['wps_store_ua'] = ''; }
	
	foreach( $wps_option_list as $option ) {
		if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }
		$new_option = str_replace( "wps_", "", $option );
		$WP_Statistics->store_option($new_option, $value);
	}

	if( $WP_Statistics->get_option('delete_manual') == true ) {
		$filepath = realpath( plugin_dir_path(__FILE__) . "../../../" ) . "/";

		if( file_exists( $filepath . WP_STATISTICS_MANUAL . 'html' ) ) { unlink( $filepath . WP_STATISTICS_MANUAL . 'html' ); }
		if( file_exists( $filepath . WP_STATISTICS_MANUAL . 'odt' ) ) { unlink( $filepath . WP_STATISTICS_MANUAL . 'odt' ); }
	}
	
}

?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#delete_manual").click(function(){
			if(!this.checked)
				return;
				
			var agree = confirm('<?php _e('This will delete the manual when you save the settings, are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				jQuery("#delete_manual").attr("checked", false);
		
		});
	});
	
	function ToggleStatOptions() {
		jQuery('[id^="wps_stats_report_option"]').fadeToggle();	
	}
</script>

<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('IP Addresses', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="useronline"><?php _e('Hash IP Addresses', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="hash_ips" type="checkbox" value="1" name="wps_hash_ips" <?php echo $WP_Statistics->get_option('hash_ips')==true? "checked='checked'":'';?>>
				<label for="hash_ips"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('This feature will not store IP addresses in the database but instead used a unique hash.  The "Store entire user agent string" setting will be disabled if this is selected.  You will not be able to recover the IP addresses in the future to recover location information if this is enabled.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Users Online', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="useronline"><?php _e('User online', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo $WP_Statistics->get_option('useronline')==true? "checked='checked'":'';?>>
				<label for="useronline"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="check_online"><?php _e('Check for online users every', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input type="text" class="small-text code" id="check_online" name="wps_check_online" value="<?php echo $WP_Statistics->get_option('check_online'); ?>"/>
				<?php _e('Second', 'wp_statistics'); ?>
				<p class="description"><?php echo sprintf(__('Time for the check accurate online user in the site. Now: %s Second', 'wp_statistics'), $WP_Statistics->get_option('check_online')); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Visits', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="visits"><?php _e('Visits', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="visits" type="checkbox" value="1" name="wps_visits" <?php echo $WP_Statistics->get_option('visits')==true? "checked='checked'":'';?>>
				<label for="visits"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Visitors', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="visitors"><?php _e('Visitors', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="visitors" type="checkbox" value="1" name="wps_visitors" <?php echo $WP_Statistics->get_option('visitors')==true? "checked='checked'":'';?>>
				<label for="visitors"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="visitors"><?php _e('Store entire user agent string', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo $WP_Statistics->get_option('store_ua')==true? "checked='checked'":'';?>>
				<label for="store_ua"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Only enabled for debugging', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="coefficient"><?php _e('Coefficient per visitor', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input type="text" class="small-text code" id="coefficient" name="wps_coefficient" value="<?php echo $WP_Statistics->get_option('coefficient'); ?>"/>
				<p class="description"><?php echo sprintf(__('For each visit to account for several hits. Currently %s.', 'wp_statistics'), $WP_Statistics->get_option('coefficient')); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Pages', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="pages"><?php _e('Pages', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="pages" type="checkbox" value="1" name="wps_pages" <?php echo $WP_Statistics->get_option('pages')==true? "checked='checked'":'';?>>
				<label for="pages"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="pages"><?php _e('Track all pages', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="all_pages" type="checkbox" value="1" name="wps_track_all_pages" <?php echo $WP_Statistics->get_option('track_all_pages')==true? "checked='checked'":'';?>>
				<label for="all_pages"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="pages"><?php _e('Disable hits column in post/pages list', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="disable_column" type="checkbox" value="1" name="wps_disable_column" <?php echo $WP_Statistics->get_option('disable_column')==true? "checked='checked'":'';?>>
				<label for="disable_column"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Miscellaneous', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="menu-bar"><?php _e('Show stats in menu bar', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<select name="wps_menu_bar" id="menu-bar">
					<option value="0" <?php selected($WP_Statistics->get_option('menu_bar'), '0'); ?>><?php _e('No', 'wp_statistics'); ?></option>
					<option value="1" <?php selected($WP_Statistics->get_option('menu_bar'), '1'); ?>><?php _e('Yes', 'wp_statistics'); ?></option>
				</select>
				<p class="description"><?php _e('Show stats in admin menu bar', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row">
				<label for="hide_notices"><?php _e('Hide admin notices about non active features', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="hide_notices" type="checkbox" value="1" name="wps_hide_notices" <?php echo $WP_Statistics->get_option('hide_notices')==true? "checked='checked'":'';?>>
				<label for="store_ua"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('By default WP Statistics displays an alert if any of the core features are disabled on every admin page, this option will disable these notices.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="hide_notices"><?php _e('Delete the manual', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="delete_manual" type="checkbox" value="1" name="wps_delete_manual" <?php echo $WP_Statistics->get_option('delete_manual')==true? "checked='checked'":'';?>>
				<label for="delete_manual"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('By default WP Statistics stores the admin manual in the plugin directory (~5 meg), if this option is enabled it will be deleted now and during upgrades in the future.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Search Enginges', 'wp_statistics'); ?></h3></th>
		</tr>
		
		<tr valign="top">
			<th scope="row" colspan="2">
				<p class="description"><?php _e('Disabling all search engines is not allowed, doing so will result in all search engines being active.', 'wp_statistics');?></p>
			</th>
		</tr>
		<?php
			$se_option_list = '';
		
			foreach( $selist as $se ) {
				$option_name = 'wps_disable_se_' . $se['tag'];
				$store_name = 'disable_se_' . $se['tag'];
				$se_option_list .= $option_name . ',';
		?>
		
		<tr valign="top">
			<th scope="row"><label for="<?php echo $option_name;?>"><?php _e($se['name'], 'wp_statistics'); ?>:</label></th>
			<td>
				<input id="<?php echo $option_name;?>" type="checkbox" value="1" name="<?php echo $option_name;?>" <?php echo $WP_Statistics->get_option($store_name)==true? "checked='checked'":'';?>><label for="<?php echo $option_name;?>"><?php _e('disable', 'wp_statistics'); ?></label>
				<p class="description"><?php echo sprintf(__('Disable %s from data collection and reporting.', 'wp_statistics'), $se['name']); ?></p>
			</td>
		</tr>
		<?php } ?>

		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Charts', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="chart-totals"><?php _e('Include totals', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="chart-totals" type="checkbox" value="1" name="wps_chart_totals" <?php echo $WP_Statistics->get_option('chart_totals')==true? "checked='checked'":'';?>>
				<label for="chart-totals"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('Add a total line to charts with multiple values, like the search engine referrals', 'wp_statistics'); ?></p>
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
				<p class="description"><?php _e('Select when receiving statistics report.', 'wp_statistics'); ?></p>
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
					<option value="sms" <?php selected($WP_Statistics->get_option('send_report'), 'sms'); ?>><?php _e('SMS', 'wp_statistics'); ?></option>
				</select>
				<p class="description"><?php _e('Type Select Get Status Report.', 'wp_statistics'); ?></p>
				
				<?php if( !is_plugin_active('wp-sms/wp-sms.php') ) { ?>
					<p class="description note"><?php echo sprintf(__('Note: To send SMS text messages please install the %s plugin.', 'wp_statistics'), '<a href="http://wordpress.org/extend/plugins/wp-sms/" target="_blank">' . __('WordPress SMS', 'wp_statistics') . '</a>'); ?></p>
				<?php } ?>
			</td>
		</tr>
		
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row" style="vertical-align: top;">
				<label for="email-report"><?php _e('E-mail addresses', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<input type="text" id="email_list" name="wps_email_list" size="30" value="<?php if( $WP_Statistics->get_option('email_list') == '' ) { $WP_Statistics->store_option('email_list', get_bloginfo('admin_email')); } echo $WP_Statistics->get_option('email_list'); ?>"/>
				<p class="description"><?php _e('A comma separated list of e-mail addresses to send the reports to if e-mail is selected above.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row"  style="vertical-align: top;">
				<label for="content-report"><?php _e('Report body', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<?php wp_editor( $WP_Statistics->get_option('content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5) ); ?>
				<p class="description"><?php _e('Enter the contents of the reports received.', 'wp_statistics'); ?></p>
				<p class="description data">
					<?php _e('Any shortcode supported by your installation of WordPress, include all shortcodes for WP Statistics (see the admin manual for a list of codes available) are supported in the body of the message.', 'wp_statistics'); ?>
				</p>
			</td>
		</tr>
	</tbody>
</table>