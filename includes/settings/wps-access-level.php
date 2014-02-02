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
				<?php settings_fields('wps_settings'); ?>
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
					$option_list = '';
					
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
					<th scope="row" colspan="2">
						<p class="description"><?php echo sprintf(__('See the  %sWordPress Roles and Capabilities page%s for details on capability levels.', 'wp_statistics'), '<a target=_blank href="http://codex.wordpress.org/Roles_and_Capabilities">', '</a>'); ?></p>
						<p class="description"><?php echo __('Hint: manage_network = Super Admin, manage_options = Administrator, edit_others_posts = Editor, publish_posts = Author, edit_posts = Contributor, read = Everyone.', 'wp_statistics'); ?></p>
						<p class="description"><?php echo __('Each of the above casscades the rights upwards in the default WordPress configuration.  So for example selecting publish_posts grants the right to Authors, Editors, Admins and Super Admins.', 'wp_statistics'); ?></p>
						<p class="description"><?php echo sprintf(__('If you need a more robust solution to delegate access you might want to look at %s in the WordPress plugin directory.', 'wp_statistics'), '<a href="http://wordpress.org/plugins/capability-manager-enhanced/" target=_blank>Capability Manager Enhanced</a>'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('Exclude User Roles', 'wp_statistics'); ?></h3></th>
				</tr>
				<?php
					global $wp_roles;
					
					$role_list = $wp_roles->get_names();
					
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
					<th scope="row" colspan="2"><h3><?php _e('IP/Robot Exclusions', 'wp_statistics'); ?></h3></th>
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
			</tbody>
		</table>	
		<?php submit_button(); ?>
	</form>
</div>