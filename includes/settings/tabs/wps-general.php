<?php 
$selist = wp_statistics_searchengine_list( true );

if( $wps_nonce_valid ) {
	foreach( $selist as $se ) {
		$se_post = 'wps_disable_se_' . $se['tag'];
		
		$new_option = str_replace( "wps_", "", $se_post );
		$WP_Statistics->store_option($new_option, $_POST[$se_post]);
	}

	$wps_option_list = array("wps_useronline","wps_visits","wps_visitors","wps_pages","wps_track_all_pages","wps_disable_column","wps_check_online","wps_menu_bar","wps_coefficient","wps_stats_report","wps_time_report","wps_send_report","wps_content_report","wps_chart_totals","wps_store_ua","wps_hide_notices" );
	
	foreach( $wps_option_list as $option ) {
		$new_option = str_replace( "wps_", "", $option );
		$WP_Statistics->store_option($new_option, $_POST[$option]);
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
				<p class="description"><?php _e('By default WP Statistics displays an alert if any of the core features are disbaled on every admin page, this option will disable these notices.', 'wp_statistics'); ?></p>
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
			foreach( $selist as $se ) {
				$option_name = 'wps_disable_se_' . $se['tag'];
				$se_option_list .= $option_name . ',';
		?>
		
		<tr valign="top">
			<th scope="row"><label for="<?php echo $option_name;?>"><?php _e($se['name'], 'wp_statistics'); ?>:</label></th>
			<td>
				<input id="<?php echo $option_name;?>" type="checkbox" value="1" name="<?php echo $option_name;?>" <?php echo $WP_Statistics->get_option($option_name)==true? "checked='checked'":'';?>><label for="<?php echo $option_name;?>"><?php _e('disable', 'wp_statistics'); ?></label>
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
			<td scope="row">
				<label for="time-report"><?php _e('Time send', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<select name="wps_time_report" id="time-report">
					<option value="0" <?php selected($WP_Statistics->get_option('time_report'), '0'); ?>><?php _e('Please select', 'wp_statistics'); ?></option>
<?php
					$schedules = wp_get_schedules();
					
					foreach( $schedules as $key => $value ) {
						echo '					<option value="' . $key . '" ' . selected($WP_Statistics->get_option('time_report'), 'hourly') . '>' . $value['display'] . '</option>';
					}
?>					
				</select>
				<p class="description"><?php _e('Select when receiving statistics report.', 'wp_statistics'); ?></p>
			</td>
		</tr>
		
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row">
				<label for="send-report"><?php _e('Send Statistical reporting to', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<select name="wps_send_report" id="send-report">
					<option value="0" <?php selected($WP_Statistics->get_option('send_report'), '0'); ?>><?php _e('Please select', 'wp_statistics'); ?></option>
					<option value="mail" <?php selected($WP_Statistics->get_option('send_report'), 'mail'); ?>><?php _e('Email', 'wp_statistics'); ?></option>
					<option value="sms" <?php selected($WP_Statistics->get_option('send_report'), 'sms'); ?>><?php _e('SMS', 'wp_statistics'); ?></option>
				</select>
				<p class="description"><?php _e('Type Select Get Status Report.', 'wp_statistics'); ?></p>
				
				<?php if( $WP_Statistics->get_option('send_report') == 'sms' && !is_plugin_active('wp-sms/wp-sms.php') ) { ?>
					<p class="description note"><?php echo sprintf(__('Note: To send SMS text messages please install the <a href="%s" target="_blank">Wordpress SMS</a> plugin.', 'wp_statistics'), 'http://wordpress.org/extend/plugins/wp-sms/'); ?></p>
				<?php } ?>
			</td>
		</tr>
		
		<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
			<td scope="row">
				<label for="content-report"><?php _e('Send Content Report', 'wp_statistics'); ?>:</label>
			</td>
			
			<td>
				<?php wp_editor( $WP_Statistics->get_option('content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5) ); ?>
				<p class="description"><?php _e('Enter the contents of the reports received.', 'wp_statistics'); ?></p>
				<p class="description data">
					<?php _e('Input data:', 'wp_statistics'); ?>
					<?php _e('User Online', 'wp_statistics'); ?>: <code>%user_online%</code>
					<?php _e('Today Visitor', 'wp_statistics'); ?>: <code>%today_visitor%</code>
					<?php _e('Today Visit', 'wp_statistics'); ?>: <code>%today_visit%</code>
					<?php _e('Yesterday Visitor', 'wp_statistics'); ?>: <code>%yesterday_visitor%</code>
					<?php _e('Yesterday Visit', 'wp_statistics'); ?>: <code>%yesterday_visit%</code>
					<?php _e('Total Visitor', 'wp_statistics'); ?>: <code>%total_visitor%</code>
					<?php _e('Total Visit', 'wp_statistics'); ?>: <code>%total_visit%</code>
				</p>
			</td>
		</tr>
	</tbody>
</table>