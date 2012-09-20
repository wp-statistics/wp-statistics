<?php
	require('../../../wp-load.php');
	global $wpdb, $table_prefix;

	$get_users = $wpdb->get_var("SELECT COUNT(ip) FROM {$table_prefix}statistics_useronline");
	echo $get_users;
?>