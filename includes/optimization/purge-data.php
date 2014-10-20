<?php
	require('../../../../../wp-blog-header.php');
	require('../functions/purge.php');
	
	if( !is_super_admin() )
		wp_die(__('Access denied!', 'wp_statistics'));

	$purge_days = 0;

	if( array_key_exists( 'purge_days', $_POST ) ) { 
		// Get the number of days to purge data before.
		$purge_days = intval($_POST['purge_days']);
	}

	echo wp_statistics_purge_data( $purge_days );
	
?>