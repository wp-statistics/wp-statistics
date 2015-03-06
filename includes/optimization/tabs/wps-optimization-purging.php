<script type="text/javascript">
	jQuery(document).ready(function() {
		jQuery("#purge-data-submit").click(function(){
		
			var action = jQuery('#purge-data').val();
			
			if(action == 0)
				return false;
				
			var agree = confirm('<?php _e('Are you sure?', 'wp_statistics'); ?>');

			if(!agree)
				return false;
				
			var data = new Array();
			data['purge-days'] = action;
			
			jQuery("#purge-data-submit").attr("disabled", "disabled");
			jQuery("#purge-data-status").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/assets/images/loading.gif'/>");
			
			jQuery.post("<?php echo parse_url(plugins_url('../purge-data.php', __FILE__), PHP_URL_PATH ); ?>", {purge_days:data['purge-days']})
				.always(function(result){
				jQuery("#purge-data-status").html("");
				jQuery("#purge-data-result").html(result.responseText);
				jQuery("#purge-data-submit").removeAttr("disabled");
				jQuery("#wps_historical_purge").show();
			});
		});

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
			jQuery("#empty-status").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/assets/images/loading.gif'/>");
			
			jQuery.post("<?php echo parse_url(plugins_url('../empty.php', __FILE__), PHP_URL_PATH ); ?>", {table_name:data['table-name']})
				.always(function(result){
				jQuery("#empty-status").html("");
				jQuery("#empty-result").html(result.responseText);
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
			jQuery("#delete-agents-status").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/assets/images/loading.gif'/>");
	
			jQuery.post("<?php echo parse_url(plugins_url('../delete-agents.php', __FILE__), PHP_URL_PATH ); ?>", {agent_name:data['agent-name']})
				.always(function(result){
					jQuery("#delete-agents-status").html("");
					jQuery("#delete-agents-result").html(result.responseText);
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
			jQuery("#delete-platforms-status").html("<img src='<?php echo plugins_url('wp-statistics'); ?>/assets/images/loading.gif'/>");
	
			jQuery.post("<?php echo parse_url(plugins_url('../delete-platforms.php', __FILE__), PHP_URL_PATH ); ?>", {platform_name:data['platform-name']})
				.always(function(result){
				jQuery("#delete-platforms-status").html("");
				jQuery("#delete-platforms-result").html(result.responseText);
				jQuery("#delete-platforms-submit").removeAttr("disabled");
				pid = data['platform-name'].replace(/[^a-zA-Z]/g, "");
				jQuery("#platform-" + pid + "-id").remove();
			});
		});		

	});
</script>

<div class="wrap">
	<table class="form-table">
		<tbody>
			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Data', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="empty-table"><?php _e('Empty Table', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="empty-table" name="empty-table">
						<option value="0"><?php _e('Please select', 'wp_statistics'); ?></option>
						<option value="useronline"><?php echo $wpdb->prefix . 'statistics_useronline'; ?></option>
						<option value="visit"><?php echo $wpdb->prefix . 'statistics_visit'; ?></option>
						<option value="visitor"><?php echo $wpdb->prefix . 'statistics_visitor'; ?></option>
						<option value="exclusions"><?php echo $wpdb->prefix . 'statistics_exclusions'; ?></option>
						<option value="pages"><?php echo $wpdb->prefix . 'statistics_pages'; ?></option>
						<option value="all"><?php echo __('All','wp_statistics'); ?></option>
					</select>
					<p class="description"><?php _e('All data table will be lost.', 'wp_statistics'); ?></p>
					<input id="empty-table-submit" class="button button-primary" type="submit" value="<?php _e('Clear now!', 'wp_statistics'); ?>" name="empty-table-submit" Onclick="return false;"/>
					<span id="empty-status"></span>
					<div id="empty-result"></div>
				</td>
			</tr>
			
			<tr>
				<th scope="row">
					<label for="purge-data"><?php _e('Purge records older than', 'wp_statistics'); ?>:</label>
				</th>

				<td>
					<input type="text" class="small-text code" id="purge-data" name="wps_purge_data" value="365"/>
					<label for="purge-data"><?php _e('days', 'wp_statistics'); ?></label>
					<p class="description"><?php _e('Deleted user statistics data older than the selected number of days.  Minimum value is 30 days.', 'wp_statistics'); ?></p>
					<input id="purge-data-submit" class="button button-primary" type="submit" value="<?php _e('Purge now!', 'wp_statistics'); ?>" name="purge-data-submit" Onclick="return false;"/>
					<span id="purge-data-status"></span>
					<div id="purge-data-result"></div>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row" colspan="2"><h3><?php _e('Delete User Agent Types', 'wp_statistics'); ?></h3></th>
			</tr>
			
			<tr valign="top">
				<th scope="row">
					<label for="delete-agent"><?php _e('Delete Agents', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="delete-agent" name="delete-agent">
						<option value="0"><?php _e('Please select', 'wp_statistics'); ?></option>
						<?php
							$agents = wp_statistics_ua_list();
							
							foreach($agents as $agent) {
								$aid = preg_replace( "/[^a-zA-Z]/", "", $agent );
								echo "<option value='$agent' id='agent-" . $aid . "-id'>" . $agent . "</option>";
							}
						?>
					</select>
					<p class="description"><?php _e('All visitor data will be lost for this agent type.', 'wp_statistics'); ?></p>
					<input id="delete-agents-submit" class="button button-primary" type="submit" value="<?php _e('Delete now!', 'wp_statistics'); ?>" name="delete-agents-submit" Onclick="return false;">
					<span id="delete-agents-status"></span>
					<div id="delete-agents-result"></div>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row">
					<label for="delete-platform"><?php _e('Delete Platforms', 'wp_statistics'); ?>:</label>
				</th>
				
				<td>
					<select id="delete-platform" name="delete-platform">
						<option value="0"><?php _e('Please select', 'wp_statistics'); ?></option>
						<?php
							$platforms = wp_statistics_platform_list();
							
							foreach($platforms as $platform) {
								$pid = preg_replace( "/[^a-zA-Z]/", "", $platform );
								echo "<option value='$platform' id='platform-" . $pid . "-id'>" . $platform . "</option>";
							}
						?>
					</select>
					<p class="description"><?php _e('All visitor data will be lost for this platform type.', 'wp_statistics'); ?></p>
					<input id="delete-platforms-submit" class="button button-primary" type="submit" value="<?php _e('Delete now!', 'wp_statistics'); ?>" name="delete-platforms-submit" Onclick="return false;">
					<span id="delete-platforms-status"></span>
					<div id="delete-platforms-result"></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>