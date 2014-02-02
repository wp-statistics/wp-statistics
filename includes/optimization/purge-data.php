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
			$result_string = sprintf(__('<code>%s</code> data older than <code>%s</code> days purged successfully.', 'wp_statistics'), $table_name, $purge_days);
		} else {
			$result_string = sprintf(__('No records found to purge from <code>%s</code>!', 'wp_statistics'), $table_name ); 
		}

		$table_name = $table_prefix . 'statistics_visitor';

		$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `last_counter` < \'' . $date_string . '\'');
		
		if($result) {
			$result_string .= '<br>' . sprintf(__('<code>%s</code> data older than <code>%s</code> days purged successfully.', 'wp_statistics'), $table_name, $purge_days);
		} else {
			$result_string .= '<br>' . sprintf(__('No records found to purge from <code>%s</code>!', 'wp_statistics'), $table_name ); 
		}
		
		echo $result_string;
		
	} else {
		_e('Please select a value over 30 days.', 'wp_statistics');
	}

?>