<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$purge_days = intval($_POST['purge_days']);

	if($purge_days > 30) {
		
		$table_name = $table_prefix . 'statistics_visit';
		$date_string = date( 'Y-m-d', strtotime( '-' . $purge_days . ' days')); 
 
		$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `last_counter` < \'' . $date_string . '\'');
		
		if($result) {
			$result_string = sprintf(__('%s data older than %s days purged successfully.', 'wp_statistics'), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>');
		} else {
			$result_string = sprintf(__('No records found to purge from %s!', 'wp_statistics'), '<code>' . $table_name . '</code>' ); 
		}

		$table_name = $table_prefix . 'statistics_visitor';

		$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `last_counter` < \'' . $date_string . '\'');
		
		if($result) {
			$result_string .= '<br>' . sprintf(__('%s data older than %s days purged successfully.', 'wp_statistics'), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>');
		} else {
			$result_string .= '<br>' . sprintf(__('No records found to purge from %s!', 'wp_statistics'), '<code>' . $table_name . '</code>' ); 
		}

		$table_name = $table_prefix . 'statistics_exclusions';

		$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `date` < \'' . $date_string . '\'');
		
		if($result) {
			$result_string .= '<br>' . sprintf(__('%s data older than %s days purged successfully.', 'wp_statistics'), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>');
		} else {
			$result_string .= '<br>' . sprintf(__('No records found to purge from %s!', 'wp_statistics'), '<code>' . $table_name . '</code>' ); 
		}

		$table_name = $table_prefix . 'statistics_pages';

		$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `date` < \'' . $date_string . '\'');
		
		if($result) {
			$result_string .= '<br>' . sprintf(__('%s data older than %s days purged successfully.', 'wp_statistics'), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>');
		} else {
			$result_string .= '<br>' . sprintf(__('No records found to purge from %s!', 'wp_statistics'), '<code>' . $table_name . '</code>' ); 
		}
		
		echo $result_string;
		
	} else {
		_e('Please select a value over 30 days.', 'wp_statistics');
	}

?>