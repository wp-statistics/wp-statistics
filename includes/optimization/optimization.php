<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#empty-table-submit").click(function(){
		
			var action = jQuery('#empty-table').val();
			
			if(action == 0)
				return false;
				
			var agree = confirm('<?php _e('Are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				return false;
				
			var data = new Array();
			data['table-name'] = jQuery("#empty-table").val();
			
			
			jQuery("#empty-table-submit").attr("disabled", "disabled");
			jQuery("#empty-result").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/images/loading.gif'/>");
			
			jQuery.post("<?php echo parse_url(plugins_url('empty.php', __FILE__), PHP_URL_PATH ); ?>", {table_name:data['table-name']})
				.done(function(result){
				jQuery("#empty-result").html(result);
				jQuery("#empty-table-submit").removeAttr("disabled");
			});
		});

		jQuery("#delete-agents-submit").click(function(){
		
			var action = jQuery('#delete-agent').val();
			
			if(action == 0)
				return false;
				
			var agree = confirm('<?php _e('Are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				return false;
				
			var data = new Array();
			data['agent-name'] = jQuery("#delete-agent").val();
			
			
			jQuery("#delete-agents-submit").attr("disabled", "disabled");
			jQuery("#delete-agents-result").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/images/loading.gif'/>");
	
			jQuery.post("<?php echo parse_url(plugins_url('delete-agents.php', __FILE__), PHP_URL_PATH ); ?>", {agent_name:data['agent-name']})
				.done(function(result){
					jQuery("#delete-agents-result").html(result);
					jQuery("#delete-agents-submit").removeAttr("disabled");
					aid = data['agent-name'].replace(/[^a-zA-Z]/g, "");
					jQuery("#agent-" + aid + "-id").remove();
			});
		});		

		jQuery("#delete-platforms-submit").click(function(){
		
			var action = jQuery('#delete-platform').val();
			
			if(action == 0)
				return false;
				
			var agree = confirm('<?php _e('Are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				return false;
				
			var data = new Array();
			data['platform-name'] = jQuery("#delete-platform").val();
			
			
			jQuery("#delete-platforms-submit").attr("disabled", "disabled");
			jQuery("#delete-platforms-result").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/images/loading.gif'/>");
	
			jQuery.post("<?php echo parse_url(plugins_url('delete-platforms.php', __FILE__), PHP_URL_PATH ); ?>", {platform_name:data['platform-name']})
				.done(function(result){
				jQuery("#delete-platforms-result").html(result);
				jQuery("#delete-platforms-submit").removeAttr("disabled");
				pid = data['platform-name'].replace(/[^a-zA-Z]/g, "");
				jQuery("#platform-" + pid + "-id").remove();
			});
		});		

	});
</script>
<a name="top"></a>
<div class="wrap">
    <?php screen_icon('options-general'); ?>
    <h2><?php echo get_admin_page_title(); ?></h2>
	<br>
	<a href="#resources"><?php _e('Resources', 'wp_statistics'); ?></a> | <a href="#versioninfo"><?php _e('Version Info', 'wp_statistics'); ?></a> | <a href="#clientinfo"><?php _e('Client Info', 'wp_statistics'); ?></a> | <a href="#export"><?php _e('Export', 'wp_statistics'); ?></a> | <a href="#empty"><?php _e('Empty', 'wp_statistics'); ?></a> | <a href="#deleteuseragenttypes"><?php _e('Delete User Agent Types', 'wp_statistics'); ?></a>
	
	<form method="post" action="<?php echo plugins_url('export.php', __FILE__); ?>">
	<table class="form-table">
		<tbody>
			<?php settings_fields('wps_settings'); ?>
			<tr valign="top">
				<th scope="row" colspan="2"><a name="resources" href="#top" style='text-decoration: none;'><h3><?php _e('Resources', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Memory usage in PHP', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo number_format(memory_get_usage()); ?></strong> <?php _e('Byte', 'wp_statistics'); ?>
					<p class="description"><?php _e('Memory usage in PHP', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_useronline</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<td>
					<strong><?php echo $result['useronline']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_visit</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<td>
					<strong><?php echo $result['visit']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php echo sprintf(__('Number of rows in the <code>%sstatistics_visitor</code> table', 'wp_statistics'), $table_prefix); ?>:
				</th>
				
				<td>
					<strong><?php echo $result['visitor']; ?></strong> <?php _e('Row', 'wp_statistics'); ?>
					<p class="description"><?php _e('Number of rows', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><a name="versioninfo" href="#top" style='text-decoration: none;'><h3><?php _e('Version Info', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('WP Statistics Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo WP_STATISTICS_VERSION; ?></strong>
					<p class="description"><?php _e('The WP Statistics version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('PHP Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo phpversion(); ?></strong>
					<p class="description"><?php _e('The PHP version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('jQuery Version', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><script type="text/javascript">document.write(jQuery().jquery);</script></strong>
					<p class="description"><?php _e('The jQuery version you are running.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><a name="clientinfo" href="#top" style='text-decoration: none;'><h3><?php _e('Client Info', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<?php _e('Client IP', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php $wpstats = new WP_Statistics(); echo $wpstats->get_IP(); ?></strong>
					<p class="description"><?php _e('The client IP address.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<?php _e('User Agent', 'wp_statistics'); ?>:
				</th>
				
				<td>
					<strong><?php echo $_SERVER['HTTP_USER_AGENT']; ?></strong>
					<p class="description"><?php _e('The client user agent string.', 'wp_statistics'); ?></p>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><a name="export" href="#top" style='text-decoration: none;'><h3><?php _e('Export', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="table-to-export"><?php _e('Export from', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="table-to-export" name="table-to-export">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $table_prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $table_prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $table_prefix . 'statistics_visitor'; ?></option>
					</select>
					<p class="description"><?php _e('Select the table for the output file.', 'wp_statistics'); ?></p>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="export-file-type"><?php _e('Export To', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="export-file-type" name="export-file-type">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="excel">Excel</option>
						<option value="xml">XML</option>
						<option value="csv">CSV</option>
						<option value="tsv">TSV</option>
					</select>
					<p class="description"><?php _e('Select the output file type.', 'wp_statistics'); ?></p>
					<?php submit_button(__('Start Now!', 'wp_statistics'), 'primary', 'export-file-submit'); ?>
				</td>
			</tr>
			
			<tr valign="top">
				<th scope="row" colspan="2"><a name="empty" href="#top" style='text-decoration: none;'><h3><?php _e('Empty', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="empty-table"><?php _e('Empty Table', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="empty-table" name="empty-table">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $table_prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $table_prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $table_prefix . 'statistics_visitor'; ?></option>
					</select>
					<p class="description"><?php _e('All data table will be lost.', 'wp_statistics'); ?></p>
					<input id="empty-table-submit" class="button button-primary" type="submit" value="<?php _e('Clear now!', 'wp_statistics'); ?>" name="empty-table-submit" Onclick="return false;"/>
					
					<span id="empty-result"></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><a name="deleteuseragenttypes" href="#top" style='text-decoration: none;'><h3><?php _e('Delete User Agent Types', 'wp_statistics'); ?></h3></a></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="delete-agent"><?php _e('Delete Agents', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="delete-agent" name="delete-agent">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<?php
							$agents = wp_statistics_ua_list();
							
							foreach($agents as $agent) {
								$aid = preg_replace( "/[^a-zA-Z]/", "", $agent );
								echo "<option value='$agent' id='agent-" . $aid . "-id'>" . __($agent, 'wp_statistics') . "</option>";
							}
						?>
					</select>
					<p class="description"><?php _e('All visitor data will be lost for this agent type.', 'wp_statistics'); ?></p>
					<input id="delete-agents-submit" class="button button-primary" type="submit" value="<?php _e('Delete now!', 'wp_statistics'); ?>" name="delete-agents-submit" Onclick="return false;">
					
					<span id="delete-agents-result"></span>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="delete-platform"><?php _e('Delete Platforms', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="delete-platform" name="delete-platform">
						<option value="0"><?php _e('Please select.', 'wp_statistics'); ?></option>
						<?php
							$platforms = wp_statistics_platform_list();
							
							foreach($platforms as $platform) {
								$pid = preg_replace( "/[^a-zA-Z]/", "", $platform );
								echo "<option value='$platform' id='platform-" . $pid . "-id'>" . __($platform, 'wp_statistics') . "</option>";
							}
						?>
					</select>
					<p class="description"><?php _e('All visitor data will be lost for this platform type.', 'wp_statistics'); ?></p>
					<input id="delete-platforms-submit" class="button button-primary" type="submit" value="<?php _e('Delete now!', 'wp_statistics'); ?>" name="delete-platforms-submit" Onclick="return false;">
					
					<span id="delete-platforms-result"></span>
				</td>
			</tr>
		</tbody>
	</table>
	</form>
</div>