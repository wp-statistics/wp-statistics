<?php
	require('../../../wp-blog-header.php');
	global $wpdb, $table_prefix;

	$count_total = $wpdb->get_var("SELECT total FROM {$table_prefix}statistics_visits");
	echo $count_total;
?>