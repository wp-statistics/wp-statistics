<script type="text/javascript">
	function ToggleStatOptions() {
		jQuery('[id^="wps_stats_report_option"]').fadeToggle();	
	}
</script>
<a name="top"></a>
<div class="wrap">
    <?php screen_icon('options-general'); ?>
    <h2><?php echo get_admin_page_title(); ?></h2>
	<br>
	<a href="#generalsettings"><?php _e('General Settings', 'wp_statistics'); ?></a> | <a href="#adminlevels"><?php _e('Admin Levels', 'wp_statistics'); ?><a/> | <a href="#excludeuserroles"><?php _e('Exclude User Roles', 'wp_statistics'); ?><a/> | <a href="#iprobotexclusions"><?php _e('IP/Robot Exclusions', 'wp_statistics'); ?><a/> | <a href="#charts"><?php _e('Charts', 'wp_statistics'); ?><a/> | <a href="#statisticalreportingsettings"><?php _e('Statistical reporting settings', 'wp_statistics'); ?><a/> | <a href="#geoip"><?php _e('GeoIP', 'wp_statistics'); ?></a>
	
	<form method="post" action="options.php">
		<table class="form-table">
			<tbody>
				<?php settings_fields('wps_settings'); ?>
				<tr valign="top">
					<th scope="row" colspan="2"><a href="#top" name="generalsettings" style='text-decoration: none;'><h3><?php _e('General Settings', 'wp_statistics'); ?></h3></a></th>
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
						<p class="description"><?php echo sprintf(__('Time for the check accurate online user in the site. Now: %s Second', 'wp_statistics'), $o->second); ?></p>
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
						<p class="description"><?php echo sprintf(__('Exclude %s role from data collection.', 'wp_statistics'), $role); ?></p>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2">
						<a name="adminlevels" href="#top" style='text-decoration: none;'><h3><?php _e('Admin Levels', 'wp_statistics'); ?></h3></a>
						<p class="description"><?php echo sprintf(__('See the  %sWordPress Roles and Capabilities page%s for details on capability levels.', 'wp_statistics'), '<a target=_blank href="http://codex.wordpress.org/Roles_and_Capabilities">', '</a>'); ?></p>
						<p class="description"><?php echo __('Hint: manage_network = Super Admin, manage_options = Administrator, edit_others_posts = Editor, publish_posts = Author, edit_posts = Contributor, read = Everyone.', 'wp_statistics'); ?></p>
						<p class="description"><?php echo __('Each of the above casscades the rights upwards in the default WordPress configuration.  So for example selecting publish_posts grants the right to Authors, Editors, Admins and Super Admins.', 'wp_statistics'); ?></p>
						<p class="description"><?php echo sprintf(__('If you need a more robust solution to delegate access you might want to look at %s in the WordPress plugin directory.', 'wp_statistics'), '<a href="http://wordpress.org/plugins/capability-manager-enhanced/" target=_blank>Capability Manager Enhanced</a>'); ?></p>
					</th>
				</tr>

				<?php
					global $wp_roles;

					$role_list = $wp_roles->get_names();
	
					foreach( $wp_roles->roles as $role ) {
					
						$cap_list = $role['capabilities'];
						
						foreach( $cap_list as $key => $cap ) {
							if( substr($key,0,6) != 'level_' ) {
								$all_caps[$key] = 1;
							}
						}
					}
					
					ksort( $all_caps );
					
					$read_cap = get_option('wps_read_capability','manage_options');
					
					foreach( $all_caps as $key => $cap ) {
						if( $key == $read_cap ) { $selected = " SELECTED"; } else { $selected = ""; }
						$option_list .= "<option value='{$key}'{$selected}>{$key}</option>";
					}
				?>
				<tr valign="top">
					<th scope="row"><label for="wps_read_capability"><?php _e('Required user level to view WP Statistics', 'wp_statistics')?>:</label></th>
					<td>
						<select id="wps_read_capability" name="wps_read_capability"><?php echo $option_list;?></select>
					</td>
				</tr>

				<?php
					$manage_cap = get_option('wps_manage_capability','manage_options');
					
					foreach( $all_caps as $key => $cap ) {
						if( $key == $manage_cap ) { $selected = " SELECTED"; } else { $selected = ""; }
						$option_list .= "<option value='{$key}'{$selected}>{$key}</option>";
					}
				?>
				<tr valign="top">
					<th scope="row"><label for="wps_manage_capability"><?php _e('Required user level to manage WP Statistics', 'wp_statistics')?>:</label></th>
					<td>
						<select id="wps_manage_capability" name="wps_manage_capability"><?php echo $option_list;?></select>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row" colspan="2"><a name="excludeuserroles" href="#top" style='text-decoration: none;'><h3><?php _e('Exclude User Roles', 'wp_statistics'); ?></h3></a></th>
				</tr>
				<?php
					foreach( $role_list as $role ) {
						$option_name = 'wps_exclude_' . str_replace(" ", "_", strtolower($role) );
				?>
				
				<tr valign="top">
					<th scope="row"><label for="<?php echo $option_name;?>"><?php _e($role, 'wp_statistics'); ?>:</label></th>
					<td>
						<input id="<?php echo $option_name;?>" type="checkbox" value="1" name="<?php echo $option_name;?>" <?php echo get_option($option_name)==true? "checked='checked'":'';?>><label for="<?php echo $option_name;?>"><?php _e('Exclude', 'wp_statistics'); ?></label>
						<p class="description"><?php echo sprintf(__('Exclude %s role from data collection.', 'wp_statistics'), $role); ?></p>
					</td>
				</tr>
				<?php } ?>
				
				<tr valign="top">
					<th scope="row" colspan="2"><a name="iprobotexclusions" href="#top" style='text-decoration: none;'><h3><?php _e('IP/Robot Exclusions', 'wp_statistics'); ?></h3></a></th>
				</tr>

				<tr valign="top">
					<th scope="row"><?php _e('Robot List', 'wp_statistics'); ?>:</th>
					<td>
						<textarea name="wps_robotlist" class="code" dir="ltr" rows="10" cols="60" id="wps_robotlist"><?php 
							$robotlist = get_option('wps_robotlist'); 

							include_once dirname( __FILE__ ) . '/../../robotslist.php';						
							if( $robotlist == "" ) { 
								$robotlist = $wps_robotlist; 
								update_option( 'wps_robotlist', $robotlist );
							}

							echo $robotlist;?></textarea>
						<p class="description"><?php echo __('A list of words (one per line) to match against to detect robots.  Entries must be at least 4 characters long or they will be ignored.', 'wp_statistics'); ?></p>
						<a onclick="var wps_robotlist = getElementById('wps_robotlist'); wps_robotlist.value = '<?php echo implode('\n', $wps_robotarray);?>';" class="button"><?php _e('Reset to Default', 'wp_statistics');?></a>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e('Excluded IP Address List', 'wp_statistics'); ?>:</th>
					<td>
						<textarea id="wps_exclude_ip" name="wps_exclude_ip" rows="5" cols="60" class="code" dir="ltr"><?php echo get_option('wps_exclude_ip');?></textarea>
						<p class="description"><?php echo __('A list of IP addresses and (optional) subnet masks (one per line) to exclude from statistics collection (both 192.168.0.0/24 and 192.168.0.0/255.255.255.0 formats are accepted).  To specify an IP address only, do not add any subnet value.', 'wp_statistics'); ?></p>
						<a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n10.0.0.0/8' ); }" class="button"><?php _e('Add 10.0.0.0', 'wp_statistics');?></a>
						<a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n172.16.0.0/12' ); }" class="button"><?php _e('Add 172.16.0.0', 'wp_statistics');?></a>
						<a onclick="var wps_exclude_ip = getElementById('wps_exclude_ip'); if( wps_exclude_ip != null ) { wps_exclude_ip.value = jQuery.trim( wps_exclude_ip.value + '\n192.168.0.0/16' ); }" class="button"><?php _e('Add 192.168.0.0', 'wp_statistics');?></a>
					</td>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2"><a name="charts" href="#top" style='text-decoration: none;'><h3><?php _e('Charts', 'wp_statistics'); ?></h3></a></th>
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
					<th scope="row" colspan="2"><a name="statisticalreportingsettings" href="#top" style='text-decoration: none;'><h3><?php _e('Statistical reporting settings', 'wp_statistics'); ?></h3></a></th>
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
					<th scope="row">
						<label for="time-report"><?php _e('Time send', 'wp_statistics'); ?>:</label>
					</th>
					
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
					<th scope="row">
						<label for="send-report"><?php _e('Send Statistical reporting to', 'wp_statistics'); ?>:</label>
					</th>
					
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
					<th scope="row">
						<label for="content-report"><?php _e('Send Content Report', 'wp_statistics'); ?>:</label>
					</th>
					
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

				<tr valign="top">
					<th scope="row" colspan="2"><a name="geoip" href="#top" style='text-decoration: none;'><h3><?php _e('GeoIP settings', 'wp_statistics'); ?></h3></a></th>
				</tr>

				<tr valign="top">
					<th scope="row" colspan="2">IP location services provided by GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.
					</th>
				</tr>

<?php 		if( version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') ) {?>
				<tr valign="top">
					<th scope="row">
						<label for="geoip-enable"><?php _e('GeoIP collection', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="geoip-enable" type="checkbox" name="wps_geoip" <?php echo get_option('wps_geoip')==true? "checked='checked'":'';?>>
						<label for="geoip-enable"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('For get more information and location (country) from visitor, enable this feature.', 'wp_statistics'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="geoip-update"><?php _e('Update GeoIP Info', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="geoip-update" type="checkbox" name="wps_update_geoip" <?php echo get_option('wps_update_geoip')==true? "checked='checked'":'';?>>
						<label for="geoip-update"><?php _e('Download GeoIP Database', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Save changes on this page to download the update.', 'wp_statistics'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="geoip-schedule"><?php _e('Schedule monthly update of GeoIP DB', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="geoip-schedule" type="checkbox" name="wps_schedule_geoip" <?php echo get_option('wps_schedule_geoip')==true? "checked='checked'":'';?>>
						<label for="geoip-schedule"><?php _e('Active', 'wp_statistics'); ?></label>
<?php 
	if( get_option('wps_schedule_geoip') ) {
		echo '						<p class="description">' . __('Next update will be') .': <code>';
		$last_update = get_option('wps_last_geoip_dl');
		$this_month = strtotime('First Tuesday of this month');

		if( $last_update > $this_month ) { $next_update = strtotime('First Tuesday of next month') + (86400 * 2);}
		else { $next_update = $this_month + (86400 * 2); }

		$next_schedule = wp_next_scheduled('wp_statistics_geoip_hook');

		if( $next_schedule ) {
			echo date( get_option('date_format'), $next_update ) . ' @ ' . date( get_option('time_format'), $next_schedule );
		}
		else {
			echo date( get_option('date_format'), $next_update ) . ' @ ' . date( get_option('time_format'), time() );
		}

		echo '</code></p>';
	}
?>
						<p class="description"><?php _e('Download of the GeoIP database will be scheduled for 2 days after the first Tuesday of the month.', 'wp_statistics'); ?></p>
						<p class="description"><?php _e('This option will also download the database if the local filesize is less than 1k (which usually means the stub that comes with the plugin is still in place).', 'wp_statistics'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="geoip-schedule"><?php _e('Populate missing GeoIP after update of GeoIP DB', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="geoip-auto-pop" type="checkbox" name="wps_auto_pop" <?php echo get_option('wps_auto_pop')==true? "checked='checked'":'';?>>
						<label for="geoip-auto-pop"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Update any missing GeoIP data after downloading a new database.', 'wp_statistics'); ?></p>
					</td>
				</tr>
<?php 	}
		else {
?>
				<tr valign="top">
					<th scope="row" colspan="2">
						<?php printf( __('GeoIP collection requires PHP %s or above, it is currently disabled due to the installed PHP version being  ', 'wp_statistics'), '<code>' . WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION . '</code>' ); echo '<code>' . phpversion() . '</code>.'; ?>
					</th>
				</tr>
<?php	} ?>
		
			</tbody>
		</table>	
		<?php submit_button(); ?>
	</form>
</div>