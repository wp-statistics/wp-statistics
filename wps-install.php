<?php
	if( is_admin() ) {
		GLOBAL $wpdb;
		
		$wp_prefix = $wpdb->prefix;

		// The follow variables are used to define the table structure for new and upgrade installations.
		$create_useronline_table = ("CREATE TABLE {$wp_prefix}statistics_useronline (
			ID int(11) NOT NULL AUTO_INCREMENT,
			ip varchar(60) NOT NULL,
			timestamp int(10) NOT NULL,
			date datetime NOT NULL,
			referred text CHARACTER SET utf8 NOT NULL,
			agent varchar(255) NOT NULL,
			platform varchar(255),
			version varchar(255),
			PRIMARY KEY  (ID)
		) CHARSET=utf8");
		
		$create_visit_table = ("CREATE TABLE {$wp_prefix}statistics_visit (
			ID int(11) NOT NULL AUTO_INCREMENT,
			last_visit datetime NOT NULL,
			last_counter date NOT NULL,
			visit int(10) NOT NULL,
			PRIMARY KEY  (ID)
		) CHARSET=utf8");
		
		$create_visitor_table = ("CREATE TABLE {$wp_prefix}statistics_visitor (
			ID int(11) NOT NULL AUTO_INCREMENT,
			last_counter date NOT NULL,
			referred text NOT NULL,
			agent varchar(255) NOT NULL,
			platform varchar(255),
			version varchar(255),
			UAString varchar(255),
			ip varchar(60) NOT NULL,
			location varchar(10),
			PRIMARY KEY  (ID),
			UNIQUE KEY date_ip_agent (last_counter,ip,agent (75),platform (75),version (75)),
			KEY agent (agent),
			KEY platform (platform),
			KEY version (version),
			KEY location (location)
		) CHARSET=utf8");
		
		$create_exclusion_table = ("CREATE TABLE {$wp_prefix}statistics_exclusions (
			ID int(11) NOT NULL AUTO_INCREMENT,
			date date NOT NULL,
			reason varchar(255) DEFAULT NULL,
			count bigint(20) NOT NULL,
			PRIMARY KEY  (ID),
			KEY date (date),
			KEY reason (reason)
		) CHARSET=utf8");

		$create_pages_table = ("CREATE TABLE {$wp_prefix}statistics_pages (
			uri varchar(255) NOT NULL,
			date date NOT NULL,
			count int(11) NOT NULL,
			id int(11) NOT NULL,
			UNIQUE KEY date_2 (date,uri),
			KEY url (uri),
			KEY date (date),
			KEY id (id)
		) CHARSET=utf8");

		$create_historical_table = ("CREATE TABLE {$wp_prefix}statistics_historical (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			category varchar(25) NOT NULL,
			page_id bigint(20) NOT NULL,
			uri varchar(255) NOT NULL,
			value bigint(20) NOT NULL,
			PRIMARY KEY  (ID),
			KEY category (category),
			UNIQUE KEY page_id (page_id),
			UNIQUE KEY uri (uri)
		) CHARSET=utf8");
		
		// Before we update the historical table, check to see if it exists with the old keys
		$result = $wpdb->query( "SHOW COLUMNS FROM {$wp_prefix}statistics_historical LIKE 'key'" );
		
		if( $result > 0 ) {
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `id` `page_id` bigint(20)" );
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `key` `ID` bigint(20)" );
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `type` `category` varchar(25)" );
		}
		
		// This includes the dbDelta function from WordPress.
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		// Create/update the plugin tables.
		dbDelta($create_useronline_table);
		dbDelta($create_visit_table);
		dbDelta($create_visitor_table);
		dbDelta($create_exclusion_table);
		dbDelta($create_pages_table);
		dbDelta($create_historical_table);

		$wpdb->query( "DROP INDEX `date_ip` ON {$wp_prefix}statistics_visitor" );
		
		// Store the new version information.
		update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
		update_option('wp_statistics_db_version', WP_STATISTICS_VERSION);

		// Get the robots list, we'll use this for both upgrades and new installs.
		include_once('robotslist.php');

		if( $WPS_Installed == false ) {
		
			// If this is a first time install, we just need to setup the primary values in the tables.
		
			$WP_Statistics->Primary_Values();
			
		} else {

			// If this is an upgrade, we need to check to see if we need to convert anything from old to new formats.
		
			// Check to see if the "new" settings code is in place or not, if not, upgrade the old settings to the new system.
			if( get_option('wp_statistics') === FALSE ) {
				$core_options = array('wps_disable_map', 'wps_map_location', 'wps_google_coordinates', 'wps_schedule_dbmaint', 'wps_schedule_dbmaint_days', 'wps_geoip', 'wps_update_geoip', 'wps_schedule_geoip', 'wps_last_geoip_dl', 'wps_auto_pop', 'wps_useronline', 'wps_check_online', 'wps_visits', 'wps_visitors', 'wps_store_ua', 'wps_coefficient', 'wps_pages', 'wps_track_all_pages', 'wps_disable_column', 'wps_menu_bar', 'wps_hide_notices', 'wps_chart_totals', 'wps_stats_report', 'wps_time_report', 'wps_send_report', 'wps_content_report', 'wps_read_capability', 'wps_manage_capability', 'wps_record_exclusions', 'wps_robotlist', 'wps_exclude_ip', 'wps_exclude_loginpage', 'wps_exclude_adminpage');
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
					$result = $wpdb->get_results("SELECT * FROM {$wp_prefix}options WHERE option_name LIKE '{$option}'");

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

			if(trim($wps_temp_robotslist) == "" || $WP_Statistics->get_option('force_robot_update') == TRUE) {
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

		// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
		if( $WP_Statistics->get_option('geoip') === FALSE ) { $WP_Statistics->store_option('geoip',FALSE); }
		if( $WP_Statistics->get_option('browscap') === FALSE ) { $WP_Statistics->store_option('browscap',FALSE); }
		if( $WP_Statistics->get_option('useronline') === FALSE ) { $WP_Statistics->store_option('useronline',TRUE); }
		if( $WP_Statistics->get_option('visits') === FALSE ) { $WP_Statistics->store_option('visits',TRUE); }
		if( $WP_Statistics->get_option('visitors') === FALSE ) { $WP_Statistics->store_option('visitors',TRUE); }
		if( $WP_Statistics->get_option('pages') === FALSE ) { $WP_Statistics->store_option('pages',TRUE); }
		if( $WP_Statistics->get_option('check_online') === FALSE ) { $WP_Statistics->store_option('check_online','30'); }
		if( $WP_Statistics->get_option('menu_bar') === FALSE ) { $WP_Statistics->store_option('menu_bar',FALSE); }
		if( $WP_Statistics->get_option('coefficient') === FALSE ) { $WP_Statistics->store_option('coefficient','1'); }
		if( $WP_Statistics->get_option('stats_report') === FALSE ) { $WP_Statistics->store_option('stats_report',FALSE); }
		if( $WP_Statistics->get_option('time_report') === FALSE ) { $WP_Statistics->store_option('time_report','daily'); }
		if( $WP_Statistics->get_option('send_report') === FALSE ) { $WP_Statistics->store_option('send_report','mail'); }
		if( $WP_Statistics->get_option('content_report') === FALSE ) { $WP_Statistics->store_option('content_report',''); }
		if( $WP_Statistics->get_option('update_geoip') === FALSE ) { $WP_Statistics->store_option('update_geoip',TRUE); }
		if( $WP_Statistics->get_option('store_ua') === FALSE ) { $WP_Statistics->store_option('store_ua',FALSE); }
		if( $WP_Statistics->get_option('robotlist') === FALSE ) { $WP_Statistics->store_option('robotlist',$wps_robotslist); }
		if( $WP_Statistics->get_option('exclude_administrator') === FALSE ) { $WP_Statistics->store_option('exclude_administrator',TRUE); }
		if( $WP_Statistics->get_option('disable_se_clearch') === FALSE ) { $WP_Statistics->store_option('disable_se_clearch',TRUE); }

		if( $WPS_Installed == false ) {		
			// We now need to set the robot list to update during the next release.  This is only done for new installs to ensure we don't overwrite existing custom robot lists.
			$WP_Statistics->store_option('force_robot_update',TRUE);
		}

		// For version 8.0, we're removing the old %option% types from the reports, so let's upgrade anyone who still has them to short codes.
		$report_content = $WP_Statistics->get_option('content_report');

		// Check to make sure we have a report to process.
		if( trim( $report_content ) == '' ) {
			// These are the variables we can replace in the template and the short codes we're going to replace them with.
			$template_vars = array(
				'user_online'		=>	'[wpstatistics stat=usersonline]',
				'today_visitor'		=>	'[wpstatistics stat=visitors time=today]',
				'today_visit'		=>	'[wpstatistics stat=visits time=today]',
				'yesterday_visitor'	=>	'[wpstatistics stat=visitors time=yesterday]',
				'yesterday_visit'	=>	'[wpstatistics stat=visits time=yesterday]',
				'total_visitor'		=>	'[wpstatistics stat=visitors time=total]',
				'total_visit'		=>	'[wpstatistics stat=visits time=total]',
			);

		// Replace the items in the template.
		$final_report = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $report_content);

		// Store the updated report content.
		$WP_Statistics->store_option('content_report', $final_report);
		}
		
		// Save the settings now that we've set them.
		$WP_Statistics->save_options();
		
		// If the manual has been set to auto delete, do that now.
		if( $WP_Statistics->get_option('delete_manual') == true ) {
			$filepath = realpath( plugin_dir_path(__FILE__) ) . "/";

			if( file_exists( $filepath . WP_STATISTICS_MANUAL . 'html' ) ) { unlink( $filepath . WP_STATISTICS_MANUAL . 'html' ); }
			if( file_exists( $filepath . WP_STATISTICS_MANUAL . 'odt' ) ) { unlink( $filepath . WP_STATISTICS_MANUAL . 'odt' ); }
		}
	
		if( $WP_Statistics->get_option('upgrade_report') == true ) {
			$blogname = get_bloginfo('name');
			$blogemail = get_bloginfo('admin_email');
			
			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if( $WP_Statistics->get_option('email_list') == '' ) { $WP_Statistics->update_option( 'email_list', $blogemail ); }
			
			wp_mail( $WP_Statistics->get_option('email_list'), sprintf( __('WP Statistics %s installed on', 'wp_statistics'),  WP_STATISTICS_VERSION ) . ' ' . $blogname, "Installation/upgrade complete!", $headers );
		}
	}
?>