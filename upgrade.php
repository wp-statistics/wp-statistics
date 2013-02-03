<?php
	if( is_admin() ) {
		
		if( get_option('wp_statistics_plugin_version') < WP_STATISTICS_VERSION ) {
		
			global $wpdb, $table_prefix;
			
			$result[] = $wpdb->query("DROP TABLE {$table_prefix}statistics_visits");
			$result[] = $wpdb->query("DROP TABLE {$table_prefix}statistics_date");
			$result[] = $wpdb->query("DROP TABLE {$table_prefix}statistics_useronline");
			$result[] = $wpdb->query("DROP TABLE {$table_prefix}statistics_reffered");
			
			if( !get_option('wp_statistics_plugin_version') ) {
				add_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
			}
			
			update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
			update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);
			
			do_action('wp_statistics_install');
		}
	}
?>