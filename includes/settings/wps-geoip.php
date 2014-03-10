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
					<th scope="row" colspan="2"><h3><?php _e('GeoIP settings', 'wp_statistics'); ?></h3></th>
				</tr>

				<tr valign="top">
					<th scope="row" colspan="2">IP location services provided by GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.
					</th>
				</tr>

<?php 		if( version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') && function_exists('curl_init') && function_exists('bcadd') ) {?>
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
						<?php 
						 		if( !version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') ) {
									printf( __('GeoIP collection requires PHP %s or above, it is currently disabled due to the installed PHP version being  ', 'wp_statistics'), '<code>' . WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION . '</code>' ); echo '<code>' . phpversion() . '</code>.<br>'; 
								}

								if( !function_exists('curl_init') ) {
									echo '<br>';
									_e('GeoIP collection requires the cURL PHP extension and it is not loaded on your version of PHP!','wp_statistics'); 
								}

								if( !function_exists('bcadd') ) {
									echo '<br>';
									_e('GeoIP collection requires the BC Math PHP extension and it is not loaded on your version of PHP!','wp_statistics'); 
								}
						?>
					</th>
				</tr>
<?php	} ?>
			</tbody>
		</table>	
		
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="wps_geoip,wps_update_geoip,wps_schedule_geoip,wps_auto_pop" />
			<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp-sms'); ?>" />
		</p>
		
	</form>
</div>