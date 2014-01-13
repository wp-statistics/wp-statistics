<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$table_name = $_POST['table_name'];
	
	if($table_name) {
		
		$result = $wpdb->query("DELETE FROM {$table_prefix}statistics_{$table_name}");
		
		if($result) {
		
			echo sprintf(__('<code>%s</code> table data deleted successfully.', 'wp_statistics'), "{$table_prefix}statistics_{$table_name}");
			
			$s = new WP_Statistics();
			
			$s->Primary_Values();
		}
		
	} else {
		_e('Please select the desired items.', 'wp_statistics');
	}
?>