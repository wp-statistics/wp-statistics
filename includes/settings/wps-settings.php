<script type="text/javascript">
	function ToggleStatOptions() {
		jQuery('[id^="wps_stats_report_option"]').fadeToggle();	
	}
	
	function DBMaintWarning() {
		var checkbox = jQuery('#wps_schedule_dbmaint');
		
		if( checkbox.attr('checked') == 'checked' )
			{
			if(!confirm('<?php _e('This will permanently delete data from the database each day, are you sure you want to enable this option?', 'wp_statistics'); ?>'))
				checkbox.attr('checked', false);
			}
		

	}
</script>
<a name="top"></a>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="?page=wp-statistics/settings" class="nav-tab<?php if($_GET['tab'] == '') { echo " nav-tab-active";} ?>"><?php _e('General Settings', 'wp_statistics'); ?></a>
		<a href="?page=wp-statistics/settings&tab=access-level" class="nav-tab<?php if($_GET['tab'] == 'access-level') { echo " nav-tab-active"; } ?>"><?php _e('Access/Exclusions', 'wp_statistics'); ?></a>
		<a href="?page=wp-statistics/settings&tab=geoip" class="nav-tab<?php if($_GET['tab'] == 'geoip') { echo " nav-tab-active"; } ?>"><?php _e('GeoIP', 'wp-sms'); ?></a>
		<a href="?page=wp-statistics/settings&tab=maintenance" class="nav-tab<?php if($_GET['tab'] == 'maintenance') { echo " nav-tab-active"; } ?>"><?php _e('Maintenance', 'wp_statistics'); ?></a>
	</h2>
	
	<form method="post" action="options.php">
		<table class="form-table">
			<tbody>
				<?php wp_nonce_field('update-options');?>
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('General', 'wp_statistics'); ?></h3></th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="useronline"><?php _e('User Online', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo get_option('wps_useronline')==true? "checked='checked'":'';?>>
						<label for="useronline"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="visits"><?php _e('Visits', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="visits" type="checkbox" value="1" name="wps_visits" <?php echo get_option('wps_visits')==true? "checked='checked'":'';?>>
						<label for="visits"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="visitors"><?php _e('Visitors', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="visitors" type="checkbox" value="1" name="wps_visitors" <?php echo get_option('wps_visitors')==true? "checked='checked'":'';?>>
						<label for="visitors"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="visitors"><?php _e('Store entire user agent string', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="store_ua" type="checkbox" value="1" name="wps_store_ua" <?php echo get_option('wps_store_ua')==true? "checked='checked'":'';?>>
						<label for="store_ua"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Only enabled for debugging', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="check_online"><?php _e('Check for online users every', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input type="text" class="small-text code" id="check_online" name="wps_check_online" value="<?php echo get_option('wps_check_online'); ?>"/>
						<?php _e('Second', 'wp_statistics'); ?>
						<p class="description"><?php echo sprintf(__('Time for the check accurate online user in the site. Now: %s Second', 'wp_statistics'), get_option('wps_check_online')); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="menu-bar"><?php _e('Show stats in menu bar', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<select name="wps_menu_bar" id="menu-bar">
							<option value="0" <?php selected(get_option('wps_menu_bar'), '0'); ?>><?php _e('No', 'wp_statistics'); ?></option>
							<option value="1" <?php selected(get_option('wps_menu_bar'), '1'); ?>><?php _e('Yes', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Show stats in admin menu bar', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="coefficient"><?php _e('Coefficient per visitor', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input type="text" class="small-text code" id="coefficient" name="wps_coefficient" value="<?php echo get_option('wps_coefficient'); ?>"/>
						<p class="description"><?php echo sprintf(__('For each visit to account for several hits. Currently %s.', 'wp_statistics'), get_option('wps_coefficient')); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="hide_notices"><?php _e('Hide admin notices about non active features', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="hide_notices" type="checkbox" value="1" name="wps_hide_notices" <?php echo get_option('wps_hide_notices')==true? "checked='checked'":'';?>>
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
					$selist = wp_statistics_searchengine_list( true );
					
					foreach( $selist as $se ) {
						$option_name = 'wps_disable_se_' . $se['tag'];
						$se_option_list .= $option_name . ',';
				?>
				
				<tr valign="top">
					<th scope="row"><label for="<?php echo $option_name;?>"><?php _e($se['name'], 'wp_statistics'); ?>:</label></th>
					<td>
						<input id="<?php echo $option_name;?>" type="checkbox" value="1" name="<?php echo $option_name;?>" <?php echo get_option($option_name)==true? "checked='checked'":'';?>><label for="<?php echo $option_name;?>"><?php _e('disable', 'wp_statistics'); ?></label>
						<p class="description"><?php echo sprintf(__('Disable %s from data collection and reporting.', 'wp_statistics'), $se['name']); ?></p>
					</td>
				</tr>
				<?php } ?>

				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('Charts', 'wp_statistics'); ?></h3></th>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="chart-type"><?php _e('Chart type', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<select name="wps_chart_type" id="chart-type">
							<option value="0" <?php selected(get_option('wps_chart_type'), '0'); ?>><?php _e('Please select.', 'wp_statistics'); ?></option>
							<option value="line" <?php selected(get_option('wps_chart_type'), 'line'); ?>><?php _e('Line', 'wp_statistics'); ?></option>
							<option value="spline" <?php selected(get_option('wps_chart_type'), 'spline'); ?>><?php _e('Spline', 'wp_statistics'); ?></option>
							<option value="area" <?php selected(get_option('wps_chart_type'), 'area'); ?>><?php _e('Area', 'wp_statistics'); ?></option>
							<option value="areaspline" <?php selected(get_option('wps_chart_type'), 'areaspline'); ?>><?php _e('Area Spline', 'wp_statistics'); ?></option>
							<option value="column" <?php selected(get_option('wps_chart_type'), 'column'); ?>><?php _e('Column', 'wp_statistics'); ?></option>
							<option value="bar" <?php selected(get_option('wps_chart_type'), 'bar'); ?>><?php _e('Bar', 'wp_statistics'); ?></option>
							<option value="scatter" <?php selected(get_option('wps_chart_type'), 'scatter'); ?>><?php _e('Scatter', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Chart type in view stats.', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="chart-totals"><?php _e('Include totals', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="chart-totals" type="checkbox" value="1" name="wps_chart_totals" <?php echo get_option('wps_chart_totals')==true? "checked='checked'":'';?>>
						<label for="chart-totals"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Add a total line to charts with multiple values, like the search engine referrals', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('Map', 'wp_statistics'); ?></h3></th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="disable-map"><?php _e('Disable map', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="disable-map" type="checkbox" value="1" name="wps_disable_map" <?php echo get_option('wps_disable_map')==true? "checked='checked'":'';?>>
						<label for="disable-map"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Disable the map display', 'wp_statistics'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="map-location"><?php _e('Alternate map location', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="map-location" type="checkbox" value="1" name="wps_map_location" <?php echo get_option('wps_map_location')==true? "checked='checked'":'';?>>
						<label for="map-location"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Place the map above the recent visitors area instead of at the top of the page.', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="google-coordinates"><?php _e('Get country location from Google', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="google-coordinates" type="checkbox" value="1" name="wps_google_coordinates" <?php echo get_option('wps_google_coordinates')==true? "checked='checked'":'';?>>
						<label for="google-coordinates"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('This feature may cause a performance degradation when viewing statistics.', 'wp_statistics'); ?></p>
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
						<input id="stats-report" type="checkbox" value="1" name="wps_stats_report" <?php echo get_option('wps_stats_report')==true? "checked='checked'":'';?> onClick='ToggleStatOptions();'>
						<label for="stats-report"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</td>
				</tr>
				
				<?php if( get_option('wps_stats_report') ) { $hidden=""; } else { $hidden=" style='display: none;'"; }?>
				<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
					<td scope="row">
						<label for="time-report"><?php _e('Time send', 'wp_statistics'); ?>:</label>
					</td>
					
					<td>
						<select name="wps_time_report" id="time-report">
							<option value="0" <?php selected(get_option('wps_time_report'), '0'); ?>><?php _e('Please select.', 'wp_statistics'); ?></option>
							<option value="hourly" <?php selected(get_option('wps_time_report'), 'hourly'); ?>><?php _e('Hourly', 'wp_statistics'); ?></option>
							<option value="twicedaily" <?php selected(get_option('wps_time_report'), 'twicedaily'); ?>><?php _e('Twice daily', 'wp_statistics'); ?></option>
							<option value="daily" <?php selected(get_option('wps_time_report'), 'daily'); ?>><?php _e('daily', 'wp_statistics'); ?></option>
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
							<option value="0" <?php selected(get_option('wps_send_report'), '0'); ?>><?php _e('Please select.', 'wp_statistics'); ?></option>
							<option value="mail" <?php selected(get_option('wps_send_report'), 'mail'); ?>><?php _e('Email', 'wp_statistics'); ?></option>
							<option value="sms" <?php selected(get_option('wps_send_report'), 'sms'); ?>><?php _e('SMS', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Type Select Get Status Report.', 'wp_statistics'); ?></p>
						
						<?php if( get_option('wps_send_report') == 'sms' && !is_plugin_active('wp-sms/wp-sms.php') ) { ?>
							<p class="description note"><?php echo sprintf(__('Note: To send SMS text messages please install the <a href="%s" target="_blank">Wordpress SMS</a> plugin.', 'wp_statistics'), 'http://wordpress.org/extend/plugins/wp-sms/'); ?></p>
						<?php } ?>
					</td>
				</tr>
				
				<tr valign="top"<?php echo $hidden;?> id='wps_stats_report_option'>
					<td scope="row">
						<label for="content-report"><?php _e('Send Content Report', 'wp_statistics'); ?>:</label>
					</td>
					
					<td>
						<?php wp_editor( get_option('wps_content_report'), 'content-report', array('media_buttons' => false, 'textarea_name' => 'wps_content_report', 'textarea_rows' => 5) ); ?>
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
		
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="<?php echo $se_option_list;?>wps_useronline,wps_visits,wps_visitors,wps_check_online,wps_menu_bar,wps_coefficient,wps_chart_type,wps_stats_report,wps_time_report,wps_send_report,wps_content_report,wps_chart_totals,wps_google_coordinates,wps_store_ua,wps_disable_map,wps_map_location,wps_hide_notices" />
			<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp-sms'); ?>" />
		</p>
	</form>
</div>