<?php
	require('../../../../../wp-blog-header.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));
		
	$agent = $_POST['agent_name'];
	
	if($agent) {
		
		$result = $wpdb->query("DELETE FROM {$table_prefix}statistics_visitor WHERE agent = '$agent'");
		
		if($result) {
			echo sprintf(__('%s agent data deleted successfully.', 'wp_statistics'), '<code>' . $agent . '</code>');
		}
		else {
			_e('No agent data found to remove!', 'wp_statistics');
		}
		
	} else {
		_e('Please select the desired items.', 'wp_statistics');
	}
?>