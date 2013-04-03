<?php
	if( is_admin() ) {
	
		function wp_statistics_install() {
		
			global $wp_statistics_db_version, $table_prefix;
			
			$create_useronline_table = ("CREATE TABLE {$table_prefix}statistics_useronline (
				`ID` int(11) NOT NULL AUTO_INCREMENT,
				`ip` varchar(20) NOT NULL,
				`timestamp` int(10) NOT NULL,
				`date` datetime NOT NULL,
				`referred` text CHARACTER SET utf8 NOT NULL,
				`agent` varchar(255) NOT NULL,
				PRIMARY KEY (`ID`)
			) CHARSET=utf8");
			
			$create_visit_table = ("CREATE TABLE {$table_prefix}statistics_visit (
				`ID` int(11) NOT NULL AUTO_INCREMENT,
				`last_visit` datetime NOT NULL,
				`last_counter` date NOT NULL,
				`visit` int(10) NOT NULL,
				PRIMARY KEY (`ID`)
			) CHARSET=utf8");
			
			$create_visitor_table = ("CREATE TABLE {$table_prefix}statistics_visitor (
				`ID` int(11) NOT NULL AUTO_INCREMENT,
				`last_counter` date NOT NULL,
				`referred` text NOT NULL,
				`agent` varchar(255) NOT NULL,
				`ip` varchar(20) NOT NULL,
				PRIMARY KEY (`ID`)
			) CHARSET=utf8");
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($create_useronline_table);
			dbDelta($create_visit_table);
			dbDelta($create_visitor_table);
			
			add_option('wp_statistics_db_version', WP_STATISTICS_VERSION);
			
			$s = new WP_Statistics();
			$s->Primary_Values();
		}
	}
?>