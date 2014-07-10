<?php
	if( is_admin() ) {

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
			UNIQUE KEY `date_ip` (`last_counter`,`ip`),
			KEY `agent` (`agent`),
			KEY `platform` (`platform`),
			KEY `version` (`version`),
			KEY `location` (`location`)
		) CHARSET=utf8");
		
		$create_exclusion_table = ("CREATE TABLE {$table_prefix}statistics_exclusions (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`date` date NOT NULL,
			`reason` varchar(255) DEFAULT NULL,
			`count` bigint(20) NOT NULL,
			PRIMARY KEY (`ID`),
			KEY `date` (`date`),
			KEY `reason` (`reason`)
		) CHARSET=utf8");

		$create_pages_table = ("CREATE TABLE {$table_prefix}statistics_pages (
			`uri` varchar(255) NOT NULL,
			`date` date NOT NULL,
			`count` int(11) NOT NULL,
			`id` int(11) NOT NULL,
			UNIQUE KEY `date_2` (`date`,`uri`),
			KEY `url` (`uri`),
			KEY `date` (`date`),
			KEY `id` (`id`)
		) CHARSET=utf8");
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		dbDelta($create_useronline_table);
		dbDelta($create_visit_table);
		dbDelta($create_visitor_table);
		dbDelta($create_exclusion_table);
		dbDelta($create_pages_table);
		
		update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
		update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);

		include_once('robotslist.php');
		
		// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
		if( $WP_Statistics->get_option('geoip') === FALSE ) { $WP_Statistics->store_option('geoip',FALSE); }
		if( $WP_Statistics->get_option('useronline') === FALSE ) { $WP_Statistics->store_option('useronline',TRUE); }
		if( $WP_Statistics->get_option('visits') === FALSE ) { $WP_Statistics->store_option('visits',TRUE); }
		if( $WP_Statistics->get_option('visitors') === FALSE ) { $WP_Statistics->store_option('visitors',TRUE); }
		if( $WP_Statistics->get_option('pages') === FALSE ) { $WP_Statistics->store_option('visitors',TRUE); }
		if( $WP_Statistics->get_option('check_online') === FALSE ) { $WP_Statistics->store_option('check_online','30'); }
		if( $WP_Statistics->get_option('menu_bar') === FALSE ) { $WP_Statistics->store_option('menu_bar',FALSE); }
		if( $WP_Statistics->get_option('coefficient') === FALSE ) { $WP_Statistics->store_option('coefficient','1'); }
		if( $WP_Statistics->get_option('chart_type') === FALSE ) { $WP_Statistics->store_option('chart_type','line'); }
		if( $WP_Statistics->get_option('stats_report') === FALSE ) { $WP_Statistics->store_option('stats_report',FALSE); }
		if( $WP_Statistics->get_option('time_report') === FALSE ) { $WP_Statistics->store_option('time_report','daily'); }
		if( $WP_Statistics->get_option('send_report') === FALSE ) { $WP_Statistics->store_option('send_report','mail'); }
		if( $WP_Statistics->get_option('content_report') === FALSE ) { $WP_Statistics->store_option('content_report',''); }
		if( $WP_Statistics->get_option('update_geoip') === FALSE ) { $WP_Statistics->store_option('update_geoip',TRUE); }
		if( $WP_Statistics->get_option('store_ua') === FALSE ) { $WP_Statistics->store_option('store_ua',FALSE); }
		if( $WP_Statistics->get_option('robotlist') === FALSE ) { $WP_Statistics->store_option('robotlist',$wps_robotslist); }
		if( $WP_Statistics->get_option('exclude_administrator') === FALSE ) { $WP_Statistics->store_option('exclude_administrator',TRUE); }

		$WP_Statistics->save_options();
		
		$WP_Statistics->Primary_Values();
	}
?>