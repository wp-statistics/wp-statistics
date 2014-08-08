<?php 
if( $wps_nonce_valid ) {

	$wps_option_list = array("wps_schedule_dbmaint","wps_schedule_dbmaint_days");
	
	foreach( $wps_option_list as $option ) {
		$new_option = str_replace( "wps_", "", $option );
		if( array_key_exists( $option, $_POST ) ) { $value = $_POST[$option]; } else { $value = ''; }
		$WP_Statistics->store_option($new_option, $value);
	}
}

?>
<script type="text/javascript">
	function DBMaintWarning() {
		var checkbox = jQuery('#wps_schedule_dbmaint');
		
		if( checkbox.attr('checked') == 'checked' )
			{
			if(!confirm('<?php _e('This will permanently delete data from the database each day, are you sure you want to enable this option?', 'wp_statistics'); ?>'))
				checkbox.attr('checked', false);
			}
		

	}
</script>
<table class="form-table">
	<tbody>
		<tr valign="top">
			<th scope="row" colspan="2"><h3><?php _e('Database Maintenance', 'wp_statistics'); ?></h3></th>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="wps_schedule_dbmaint"><?php _e('Run a daily WP Cron job to prune the databases', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input id="wps_schedule_dbmaint" type="checkbox" name="wps_schedule_dbmaint" <?php echo $WP_Statistics->get_option('schedule_dbmaint')==true? "checked='checked'":'';?> onclick='DBMaintWarning();'>
				<label for="wps_schedule_dbmaint"><?php _e('Active', 'wp_statistics'); ?></label>
				<p class="description"><?php _e('A WP Cron job will be run daily to prune any data older than a set number of days.', 'wp_statistics'); ?></p>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row">
				<label for="check_online"><?php _e('Prune data older than', 'wp_statistics'); ?>:</label>
			</th>
			
			<td>
				<input type="text" class="small-text code" id="wps_schedule_dbmaint_days" name="wps_schedule_dbmaint_days" value="<?php echo $WP_Statistics->get_option('schedule_dbmaint_days', 365); ?>"/>
				<?php _e('Days', 'wp_statistics'); ?>
				<p class="description"><?php echo __('The number of days to keep statistics for.  Minimum value is 30 days.  Invalid values will disable the daily maintenance.', 'wp_statistics'); ?></p>
			</td>
		</tr>

	</tbody>
</table>