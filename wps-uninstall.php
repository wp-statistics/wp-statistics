<?php
	if( is_admin() ) {
		GLOBAL $wpdb;

		// Handle multi site implementations
		if( is_multisite() ) {
			
			// Loop through each of the sites.
			foreach( wp_get_sites() as $blog ) {

				switch_to_blog( $blog['blog_id'] );
				wp_statistics_site_removal( $wpdb->prefix );
			}
			
			restore_current_blog();
		}
		else {

			wp_statistics_site_removal( $wpdb->prefix );
			
		}

		// Make sure we don't try and remove the data more than once.
		update_option( 'wp_statistics_removal', 'done');
	}

	function wp_statistics_site_removal( $wp_prefix ) {
		GLOBAL $wpdb;
					
		// Delete the options from the WordPress options table.
		delete_option('wp_statistics');
		delete_option('wp_statistics_db_version');
		delete_option('wp_statistics_plugin_version');
		
		// Delete the user options.
		$wpdb->query("DELETE FROM {$wp_prefix}usermeta WHERE meta_key LIKE 'wp_statistics%'");
		
		// Drop the tables
		$wpdb->query("DROP TABLE IF EXISTS {$wp_prefix}statistics_useronline, {$wp_prefix}statistics_visit, {$wp_prefix}statistics_visitor, {$wp_prefix}statistics_exclusions, {$wp_prefix}statistics_pages, {$wp_prefix}statistics_historical");
	}
	
?>