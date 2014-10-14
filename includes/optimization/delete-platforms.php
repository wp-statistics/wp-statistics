<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$platform = $_POST['platform_name'];
	
	if($platform) {
		
		$result = $wpdb->query("DELETE FROM {$table_prefix}statistics_visitor WHERE platform = '$platform'");
		
		if($result) {
			echo sprintf(__('%s platform data deleted successfully.', 'wp_statistics'), '<code>' . $platform . '</code>');
		}
		else {
			_e('No platform data found to remove!', 'wp_statistics');
		}
		
	} else {
		_e('Please select the desired items.', 'wp_statistics');
	}
?>