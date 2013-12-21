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
				`platform` varchar(255),
				`version` varchar(255),
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
				`platform` varchar(255),
				`version` varchar(255),
				`UAString` varchar(255),
				`ip` varchar(20) NOT NULL,
				`location` varchar(10),
				PRIMARY KEY (`ID`),
				KEY `agent` (`agent`),
				KEY `platform` (`platform`),
				KEY `version` (`version`),
				KEY `location` (`location`)
			) CHARSET=utf8");

			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
			dbDelta($create_useronline_table);
			dbDelta($create_visit_table);
			dbDelta($create_visitor_table);
			
			update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
			update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);

			// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
			if( get_option('wps_geoip') === FALSE ) { update_option('wps_geoip',FALSE); }
			if( get_option('wps_useronline') === FALSE ) { update_option('wps_useronline',TRUE); }
			if( get_option('wps_visits') === FALSE ) { update_option('wps_visits',TRUE); }
			if( get_option('wps_visitors') === FALSE ) { update_option('wps_visitors',TRUE); }
			if( get_option('wps_check_online') === FALSE ) { update_option('wps_check_online','30'); }
			if( get_option('wps_menu_bar') === FALSE ) { update_option('wps_menu_bar',FALSE); }
			if( get_option('wps_coefficient') === FALSE ) { update_option('wps_coefficient','1'); }
			if( get_option('wps_chart_type') === FALSE ) { update_option('wps_chart_type','line'); }
			if( get_option('wps_stats_report') === FALSE ) { update_option('wps_stats_report',FALSE); }
			if( get_option('wps_time_report') === FALSE ) { update_option('wps_time_report','daily'); }
			if( get_option('wps_send_report') === FALSE ) { update_option('wps_send_report','mail'); }
			if( get_option('wps_content_report') === FALSE ) { update_option('wps_content_report',''); }
			if( get_option('wps_update_geoip') === FALSE ) { update_option('wps_update_geoip',TRUE); }
			if( get_option('wps_store_ua') === FALSE ) { update_option('wps_store_ua',FALSE); }
		
			$s = new WP_Statistics();
			$s->Primary_Values();
		}
	}
?>