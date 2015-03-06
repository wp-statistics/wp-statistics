<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$table_name = $_POST['table_name'];

	if($table_name) {

		switch( $table_name ) {
			case 'useronline':
				echo wp_statitiscs_empty_table($wpdb->prefix . 'statistics_useronline');
				break;
			case 'visit':
				echo wp_statitiscs_empty_table($wpdb->prefix . 'statistics_visit');
				break;
			case 'visitors':
				echo wp_statitiscs_empty_table($wpdb->prefix . 'statistics_visitor');
				break;
			case 'exclusions':
				echo wp_statitiscs_empty_table($wpdb->prefix . 'statistics_exclusions');
				break;
			case 'pages':
				echo wp_statitiscs_empty_table($wpdb->prefix . 'statistics_pages');
				break;
			case 'all':
				$result_string = wp_statitiscs_empty_table($wpdb->prefix . 'statistics_useronline');
				$result_string .= '<br>' . wp_statitiscs_empty_table($wpdb->prefix . 'statistics_visit');
				$result_string .= '<br>' . wp_statitiscs_empty_table($wpdb->prefix . 'statistics_visitor');
				$result_string .= '<br>' . wp_statitiscs_empty_table($wpdb->prefix . 'statistics_exclusions');
				$result_string .= '<br>' . wp_statitiscs_empty_table($wpdb->prefix . 'statistics_pages');

				echo $result_string;
				
				break;
		}

		$WP_Statistics->Primary_Values();
		
	} else {
		_e('Please select the desired items.', 'wp_statistics');
	}

function wp_statitiscs_empty_table( $table_name = FALSE ) {

	global $wpdb;
	
	if( $table_name ) {
		$result = $wpdb->query('DELETE FROM ' . $table_name);
		
		if($result) {
			return sprintf(__('%s table data deleted successfully.', 'wp_statistics'), '<code>' . $table_name . '</code>');
		}
	}

	return sprintf(__('Error, %s not emptied!', 'wp_statistics'), $table_name ); 
}	
?>