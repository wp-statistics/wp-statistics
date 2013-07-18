<div class="wrap">
    <?php screen_icon('options-general'); ?>
    <h2><?php echo get_admin_page_title(); ?></h2>
	<form method="post" action="options.php">
		<table class="form-table">
			<tbody>
				<?php settings_fields('wps_settings'); ?>
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('General Settings', 'wp_statistics'); ?></h3></th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="useronline"><?php _e('User Online', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input id="useronline" type="checkbox" value="1" name="wps_useronline" <?php echo get_option('wps_useronline')==true? "checked='checked'":'';?>>
						<label for="useronline"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="visits"><?php _e('Visits', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input id="visits" type="checkbox" value="1" name="wps_visits" <?php echo get_option('wps_visits')==true? "checked='checked'":'';?>>
						<label for="visits"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="visitors"><?php _e('Visitors', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input id="visitors" type="checkbox" value="1" name="wps_visitors" <?php echo get_option('wps_visitors')==true? "checked='checked'":'';?>>
						<label for="visitors"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="check_online"><?php _e('Check for online users every', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input type="text" class="small-text code" id="check_online" name="wps_check_online" value="<?php echo get_option('wps_check_online'); ?>"/>
						<?php _e('Secound', 'wp_statistics'); ?>
						<p class="description"><?php echo sprintf(__('Time for the check accurate online user in the site. Now: %s Second', 'wp_statistics'), $o->second); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="menu-bar"><?php _e('Show stats in menu bar', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<select name="wps_menu_bar" id="menu-bar">
							<option value="0" <?php selected(get_option('wps_menu_bar'), '0'); ?>><?php _e('No', 'wp_statistics'); ?></option>
							<option value="1" <?php selected(get_option('wps_menu_bar'), '1'); ?>><?php _e('Yes', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Show stats in admin menu bar', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="coefficient"><?php _e('Coefficient per visitor', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input type="text" class="small-text code" id="coefficient" name="wps_coefficient" value="<?php echo get_option('wps_coefficient'); ?>"/>
						<p class="description"><?php echo sprintf(__('For each visit to account for several hits. Currently %s.', 'wp_statistics'), $h->coefficient); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('Chart Settings', 'wp_statistics'); ?></h3></th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="chart-type"><?php _e('Chart type', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
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
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row" colspan="2"><h3><?php _e('Statistical reporting settings', 'wp_statistics'); ?></h3></th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="stats-report"><?php _e('Statistical reporting', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<input id="stats-report" type="checkbox" value="1" name="wps_stats_report" <?php echo get_option('wps_stats_report')==true? "checked='checked'":'';?>>
						<label for="stats-report"><?php _e('Active', 'wp_statistics'); ?></label>
						<p class="description"><?php _e('Enable or disable this feature', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<?php if( get_option('wps_stats_report') ) { ?>
				<tr valign="top">
					<th scope="row">
						<label for="time-report"><?php _e('Time send', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<select name="wps_time_report" id="time-report">
							<option value="0" <?php selected(get_option('wps_time_report'), '0'); ?>><?php _e('Please select.', 'wp_statistics'); ?></option>
							<option value="hourly" <?php selected(get_option('wps_time_report'), 'hourly'); ?>><?php _e('Hourly', 'wp_statistics'); ?></option>
							<option value="twicedaily" <?php selected(get_option('wps_time_report'), 'twicedaily'); ?>><?php _e('Twice daily', 'wp_statistics'); ?></option>
							<option value="daily" <?php selected(get_option('wps_time_report'), 'daily'); ?>><?php _e('daily', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Select when receiving statistics report.', 'wp_statistics'); ?></p>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="send-report"><?php _e('Send Statistical reporting to', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
						<select name="wps_send_report" id="send-report">
							<option value="0" <?php selected(get_option('wps_send_report'), '0'); ?>><?php _e('Please select.', 'wp_statistics'); ?></option>
							<option value="mail" <?php selected(get_option('wps_send_report'), 'mail'); ?>><?php _e('Email', 'wp_statistics'); ?></option>
							<option value="sms" <?php selected(get_option('wps_send_report'), 'sms'); ?>><?php _e('SMS', 'wp_statistics'); ?></option>
						</select>
						<p class="description"><?php _e('Type Select Get Status Report.', 'wp_statistics'); ?></p>
						
						<?php if( get_option('wps_send_report') == 'sms' && !class_exists(get_option('wp_webservice')) ) { ?>
							<p class="description note"><?php echo sprintf(__('Note: To send SMS text messages please install the <a href="%s" target="_blank">Wordpress SMS</a> plugin.', 'wp_statistics'), 'http://wordpress.org/extend/plugins/wp-sms/'); ?></p>
						<?php } ?>
					</th>
				</tr>
				
				<tr valign="top">
					<th scope="row">
						<label for="content-report"><?php _e('Send Content Report', 'wp_statistics'); ?>:</label>
					</th>
					
					<th>
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
					</th>
				</tr>
				<?php } ?>
			</tbody>
		</table>	
		<?php submit_button(); ?>
	</form>
</div>