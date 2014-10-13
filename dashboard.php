<?php
	function wp_statistics_dashboard_widget_load() {
		wp_add_dashboard_widget( 'wp-statistics-dashboard-widget', 'Statistics', 'wp_statistics_dashboard_widget', $control_callback = null );
	}

	function wp_statistics_dashboard_widget() {
		GLOBAL $WP_Statistics;

		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) {
			return;
		}

		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'assets/css/log.css', true, '1.1');
		
		$widget_options = $WP_Statistics->get_option('widget');
?>		
		<table width="100%" class="widefat table-stats" id="summary-stats">
			<tbody>
				<tr>
					<th><?php _e('User(s) Online', 'wp_statistics'); ?>:</th>
					<th colspan="2" id="th-colspan"><span><?php echo wp_statistics_useronline(); ?></span></th>
				</tr>
				
				<tr>
					<th width="60%"></th>
					<th class="th-center"><?php _e('Visitor', 'wp_statistics'); ?></th>
					<th class="th-center"><?php _e('Visit', 'wp_statistics'); ?></th>
				</tr>
				
				<tr>
					<th><?php _e('Today', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('today',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('today')); ?></span></th>
				</tr>
				
				<tr>
					<th><?php _e('Yesterday', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('yesterday',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('yesterday')); ?></span></th>
				</tr>
				
				<tr>
					<th><?php _e('Week', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('week',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('week')); ?></span></th>
				</tr>
				
				<tr>
					<th><?php _e('Month', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('month',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('month')); ?></span></th>
				</tr>
				
				<tr>
					<th><?php _e('Year', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('year',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('year')); ?></span></th>
				</tr>
				
				<tr>
					<th><?php _e('Total', 'wp_statistics'); ?>:</th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('total',null,true)); ?></span></th>
					<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('total')); ?></span></th>
				</tr>
				
			</tbody>
		</table>
<?php
	}

	add_action( 'wp_dashboard_setup', 'wp_statistics_dashboard_widget_load' );
?>