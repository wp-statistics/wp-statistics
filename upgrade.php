<?php
	if( is_admin() ) {
	
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

		$create_exclusion_table = ("CREATE TABLE {$table_prefix}statistics_exclusions (
			`ID` int(11) NOT NULL AUTO_INCREMENT,
			`date` date NOT NULL,
			`reason` varchar(255) DEFAULT NULL,
			`count` bigint(20) NOT NULL,
			PRIMARY KEY (`ID`),
			KEY `date` (`date`),
			KEY `reason` (`reason`)
		) CHARSET=utf8");

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		dbDelta($create_useronline_table);
		dbDelta($create_visit_table);
		dbDelta($create_visitor_table);
		dbDelta($create_exclusion_table);
		
		update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
		update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);
		
		$wps_robotslist = get_option('wps_robotlist'); 

		if(trim($wps_robotlist) == "") {
			include_once('robotslist.php');
		}

		update_option('wps_robotlist', $wps_robotslist);

		// WP Statistics V4.2 and below automatically exclude the administrator for statistics collection
		// newer versions allow the option to be set for any role in WordPress, however we should mimic
		// 4.2 behaviour when we upgrade, so see if the option exists in the database and if not, set it.
		// This will not work correctly on a WordPress install that has removed the administrator role.
		// However that seems VERY unlikely.
		$exclude_admins = get_option('wps_exclude_administrator', '2');
		if( $exclude_admins == '2' ) { update_option('wps_exclude_administrator', '1'); }
	}
?>