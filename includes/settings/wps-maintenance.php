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
					<th scope="row" colspan="2"><h3><?php _e('Database Maintenance', 'wp_statistics'); ?></h3></th>
				</tr>

				<tr valign="top">
					<th scope="row">
						<label for="wps_schedule_dbmaint"><?php _e('Run a daily WP Cron job to prune the databases', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input id="wps_schedule_dbmaint" type="checkbox" name="wps_schedule_dbmaint" <?php echo get_option('wps_schedule_dbmaint')==true? "checked='checked'":'';?> onclick='DBMaintWarning();'>
						<label for="wps_schedule_dbmaint"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('A WP Cron job will be run daily to prune any data older than a set number of days.', 'wp_statistics'); ?></p>
					</td>
				</tr>
		
				<tr valign="top">
					<th scope="row">
						<label for="check_online"><?php _e('Prune data older than', 'wp_statistics'); ?>:</label>
					</th>
					
					<td>
						<input type="text" class="small-text code" id="wps_schedule_dbmaint_days" name="wps_schedule_dbmaint_days" value="<?php echo get_option('wps_schedule_dbmaint_days', 365); ?>"/>
						<?php _e('Days', 'wp_statistics'); ?>
						<p class="description"><?php echo __('The number of days to keep statistics for.  Minimum value is 30 days.  Invalid values will disable the daily maintenance.', 'wp_statistics'); ?></p>
					</td>
				</tr>

			</tbody>
		</table>	
		
		<p class="submit">
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="wps_schedule_dbmaint,wps_schedule_dbmaint_days" />
			<input type="submit" class="button-primary" name="Submit" value="<?php _e('Update', 'wp-sms'); ?>" />
		</p>
		
	</form>
</div>