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
		
		// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
		$result = $wpdb->query('SHOW INDEX FROM wp_statistics_visitor');
		
		if( $result < 6 ) {
			// We have to loop through all the rows in the visitors table to check for duplicates that may have been created in error.
			$result = $wpdb->get_results( "SELECT ID, last_counter, ip FROM {$table_prefix}statistics_visitor ORDER BY last_counter, ip" );
			
			// Setup the inital values.
			$lastrow = array( 'last_counter' => '', 'ip' => '' );
			$deleterows = array();
			
			// Ok, now iterate over the results.
			foreach( $result as $row ) {
				// if the last_counter (the date) and IP is the same as the last row, add the row to be deleted.
				if( $row->last_counter == $lastrow['last_counter'] && $row->ip == $lastrow['ip'] ) { $deleterows[] .=  $row->ID;}
				
				// Update the lastrow data.
				$lastrow['last_counter'] = $row->last_counter;
				$lastrow['ip'] = $row->ip;
			}
			
			// Now do the acutal deletions.
			foreach( $deleterows as $row ) {
				$wpdb->delete( $table_prefix . 'statistics_visitor', array( 'ID' => $row ) );
			}
			
		}

		// This includes the dbDelta function from WordPress.
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// Create/update the plugin tables.
		dbDelta($create_useronline_table);
		dbDelta($create_visit_table);
		dbDelta($create_visitor_table);
		dbDelta($create_exclusion_table);
		dbDelta($create_pages_table);
		
		// Store the new version information.
		update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
		update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);

		// Get the robots list, we'll use this for both upgrades and new installs.
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

		// Save the settings now that we've set them.
		$WP_Statistics->save_options();
			
		if( $WPS_Installed == false ) {
		
			// If this is a first time install, we just need to setup the primary values in the tables.
		
			$WP_Statistics->Primary_Values();
			
		} else {

			// If this is an upgrade, we need to check to see if we need to convert anything from old to new formats.
		
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
			
			// If the robot list is empty, fill in the defaults.
			$wps_temp_robotslist = $WP_Statistics->get_option('robotlist'); 

			if(trim($wps_temp_robotlist) == "") {
				$WP_Statistics->update_option('robotlist', $wps_robotslist);
			}

			// WP Statistics V4.2 and below automatically exclude the administrator for statistics collection
			// newer versions allow the option to be set for any role in WordPress, however we should mimic
			// 4.2 behaviour when we upgrade, so see if the option exists in the database and if not, set it.
			// This will not work correctly on a WordPress install that has removed the administrator role.
			// However that seems VERY unlikely.
			$exclude_admins = $WP_Statistics->get_option('exclude_administrator', '2');
			if( $exclude_admins == '2' ) { $WP_Statistics->update_option('exclude_administrator', '1'); }
		}
	}
?>