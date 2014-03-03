<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$platform = $_POST['platform_name'];
	
	if($platform) {
		
		$result = $wpdb->query("DELETE FROM {$table_prefix}statistics_visitor WHERE platform = '$platform'");
		
		if($result) {
			echo sprintf(__('<code>%s</code> platform data deleted successfully.', 'wp_statistics'), $platform);
		}
		
	} else {
		_e('Please select the desired items.', 'wp_statistics');
	}
?>