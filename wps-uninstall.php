<?php
	if( is_admin() ) {
		GLOBAL $wpdb;

		// Delete the options from the WordPress options table.
		delete_option('wp_statistics');
		delete_option('wp_statistics_db_version');
		delete_option('wp_statistics_plugin_version');
		
		// Delete the user options.
		$wpdb->query("DELETE FROM {$wp_prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'");
		
		// Drop the tables
		$wpdb->query("DROP TABLE IF EXISTS {$wp_prefix}statistics_useronline, {$wp_prefix}statistics_visit, {$wp_prefix}statistics_visitor, {$wp_prefix}statistics_exclusions, {$wp_prefix}statistics_pages, {$wp_prefix}statistics_historical");

		// Make sure we don't try and remove the data more than once.
		update_option( 'wp_statistics_removal', 'done');
	}
?>