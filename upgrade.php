<?php
	if( is_admin() ) {
	
		global $wp_statistics_db_version, $table_prefix, $wpdb;
		
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
		
		// Check to see if the "new" settings code is in place or not, if not, upgrade the old settings to the new system.
		if( get_option('wp_statistics') === FALSE ) {
			$core_options = array('wps_disable_map', 'wps_map_location', 'wps_google_coordinates', 'wps_schedule_dbmaint', 'wps_schedule_dbmaint_days', 'wps_geoip', 'wps_update_geoip', 'wps_schedule_geoip', 'wps_last_geoip_dl', 'wps_auto_pop', 'wps_useronline', 'wps_check_online', 'wps_visits', 'wps_visitors', 'wps_store_ua', 'wps_coefficient', 'wps_pages', 'wps_track_all_pages', 'wps_disable_column', 'wps_menu_bar', 'wps_hide_notices', 'wps_chart_type', 'wps_chart_totals', 'wps_stats_report', 'wps_time_report', 'wps_send_report', 'wps_content_report', 'wps_read_capability', 'wps_manage_capability', 'wps_record_exclusions', 'wps_robotlist', 'wps_exclude_ip', 'wps_exclude_loginpage', 'wps_exclude_adminpage');
			$var_options = array('wps_disable_se_%', 'wps_exclude_%');
			$widget_options = array( 'name_widget', 'useronline_widget', 'tvisit_widget', 'tvisitor_widget', 'yvisit_widget', 'yvisitor_widget', 'wvisit_widget', 'mvisit_widget', 'ysvisit_widget', 'ttvisit_widget', 'ttvisitor_widget', 'tpviews_widget', 'ser_widget', 'select_se', 'tp_widget', 'tpg_widget', 'tc_widget', 'ts_widget', 'tu_widget', 'ap_widget', 'ac_widget', 'au_widget', 'lpd_widget', 'select_lps');
			
			// Handle the core options, we're going to strip off the 'wps_' header as we store them in the new settings array.
			foreach( $core_options as $option ) {
				$new_name = substr( $option, 4 );
				
				$WP_Statistics->store_option($new_name, get_option( $option ));
				
				delete_option($option);
			}
			
			$wiget = array();
			
			// Handle the widget options, we're goin to store them in a subarray.
			foreach( $widget_options as $option ) {
				$widget[$option] = get_option($option);
				
				delete_option($option);
			}

			$WP_Statistics->store_option('widget', $widget);
			
			foreach( $var_options as $option ) {
				// Handle the special variables options.
				$result = $wpdb->get_results("SELECT * FROM {$table_prefix}options WHERE option_name LIKE '{$option}'");

				foreach( $result as $opt ) {
					$new_name = substr( $opt->option_name, 4 );
				
					$WP_Statistics->store_option($new_name, $opt->option_value);

					delete_option($opt->option_name);
				}
			}

			$WP_Statistics->save_options();
		}
		
		$wps_robotslist = $WP_Statistics->get_option('robotlist'); 

		if(trim($wps_robotlist) == "") {
			include_once('robotslist.php');
		}

		$WP_Statistics->update_option('robotlist', $wps_robotslist);

		// WP Statistics V4.2 and below automatically exclude the administrator for statistics collection
		// newer versions allow the option to be set for any role in WordPress, however we should mimic
		// 4.2 behaviour when we upgrade, so see if the option exists in the database and if not, set it.
		// This will not work correctly on a WordPress install that has removed the administrator role.
		// However that seems VERY unlikely.
		$exclude_admins = $WP_Statistics->get_option('exclude_administrator', '2');
		if( $exclude_admins == '2' ) { $WP_Statistics->update_option('exclude_administrator', '1'); }
	}
?>