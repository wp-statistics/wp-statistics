<?php
if ( is_admin() ) {
	GLOBAL $wpdb, $WP_Statistics;

	$wp_prefix = $wpdb->prefix;

	// The follow variables are used to define the table structure for new and upgrade installations.
	$create_useronline_table = ( "CREATE TABLE {$wp_prefix}statistics_useronline (
			ID int(11) NOT NULL AUTO_INCREMENT,
			ip varchar(60) NOT NULL,
			created int(11),
			timestamp int(10) NOT NULL,
			date datetime NOT NULL,
			referred text CHARACTER SET utf8 NOT NULL,
			agent varchar(255) NOT NULL,
			platform varchar(255),
			version varchar(255),
			location varchar(10),
			PRIMARY KEY  (ID)
		) CHARSET=utf8" );

	$create_visit_table = ( "CREATE TABLE {$wp_prefix}statistics_visit (
			ID int(11) NOT NULL AUTO_INCREMENT,
			last_visit datetime NOT NULL,
			last_counter date NOT NULL,
			visit int(10) NOT NULL,
			PRIMARY KEY  (ID),
			UNIQUE KEY unique_date (last_counter)
		) CHARSET=utf8" );

	$create_visitor_table = ( "CREATE TABLE {$wp_prefix}statistics_visitor (
			ID int(11) NOT NULL AUTO_INCREMENT,
			last_counter date NOT NULL,
			referred text NOT NULL,
			agent varchar(255) NOT NULL,
			platform varchar(255),
			version varchar(255),
			UAString varchar(255),
			ip varchar(60) NOT NULL,
			location varchar(10),
			hits int(11),
			honeypot int(11),
			PRIMARY KEY  (ID),
			UNIQUE KEY date_ip_agent (last_counter,ip,agent(75),platform(75),version(75)),
			KEY agent (agent),
			KEY platform (platform),
			KEY version (version),
			KEY location (location)
		) CHARSET=utf8" );

	$create_visitor_table_old = ( "CREATE TABLE {$wp_prefix}statistics_visitor (
			ID int(11) NOT NULL AUTO_INCREMENT,
			last_counter date NOT NULL,
			referred text NOT NULL,
			agent varchar(255) NOT NULL,
			platform varchar(255),
			version varchar(255),
			UAString varchar(255),
			ip varchar(60) NOT NULL,
			location varchar(10),
			hits int(11),
			honeypot int(11),
			PRIMARY KEY  (ID),
			UNIQUE KEY date_ip_agent (last_counter,ip,agent (75),platform (75),version (75)),
			KEY agent (agent),
			KEY platform (platform),
			KEY version (version),
			KEY location (location)
		) CHARSET=utf8" );

	$create_exclusion_table = ( "CREATE TABLE {$wp_prefix}statistics_exclusions (
			ID int(11) NOT NULL AUTO_INCREMENT,
			date date NOT NULL,
			reason varchar(255) DEFAULT NULL,
			count bigint(20) NOT NULL,
			PRIMARY KEY  (ID),
			KEY date (date),
			KEY reason (reason)
		) CHARSET=utf8" );

	$create_pages_table = ( "CREATE TABLE {$wp_prefix}statistics_pages (
			uri varchar(255) NOT NULL,
			date date NOT NULL,
			count int(11) NOT NULL,
			id int(11) NOT NULL,
			UNIQUE KEY date_2 (date,uri),
			KEY url (uri),
			KEY date (date),
			KEY id (id)
		) CHARSET=utf8" );

	$create_historical_table = ( "CREATE TABLE {$wp_prefix}statistics_historical (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			category varchar(25) NOT NULL,
			page_id bigint(20) NOT NULL,
			uri varchar(255) NOT NULL,
			value bigint(20) NOT NULL,
			PRIMARY KEY  (ID),
			KEY category (category),
			UNIQUE KEY page_id (page_id),
			UNIQUE KEY uri (uri)
		) CHARSET=utf8" );

	$create_search_table = ( "CREATE TABLE {$wp_prefix}statistics_search (
			ID bigint(20) NOT NULL AUTO_INCREMENT,
			last_counter date NOT NULL,
			engine varchar(64) NOT NULL,
			host varchar(255),
			words varchar(255),
			visitor bigint(20),
			PRIMARY KEY  (ID),
			KEY last_counter (last_counter),
			KEY engine (engine),
			KEY host (host)
		) CHARSET=utf8" );

	// Grab the database name we're using from the global.
	$dbname = DB_NAME;

	// Check to see if the historical table exists yet, aka if this is a upgrade instead of a first install.
	$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_historical'" );

	if ( $result == 1 ) {
		// Before we update the historical table, check to see if it exists with the old keys
		$result = $wpdb->query( "SHOW COLUMNS FROM {$wp_prefix}statistics_historical LIKE 'key'" );

		if ( $result > 0 ) {
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `id` `page_id` bigint(20)" );
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `key` `ID` bigint(20)" );
			$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_historical` CHANGE `type` `category` varchar(25)" );
		}
	}

	// This includes the dbDelta function from WordPress.
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	// Create/update the plugin tables.
	dbDelta( $create_useronline_table );
	dbDelta( $create_visit_table );
	dbDelta( $create_visitor_table );
	dbDelta( $create_exclusion_table );
	dbDelta( $create_pages_table );
	dbDelta( $create_historical_table );
	dbDelta( $create_search_table );

	// Some old versions (in the 5.0.x line) of MySQL have issue with the compound index on the visitor table
	// so let's make sure it was created, if not, use the older format to create the table manually instead of
	// using the dbDelta() call.
	$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_visitor'" );

	if ( $result != 1 ) {
		$wpdb->query( $create_visitor_table_old );
	}

	// Check to see if the date_ip index still exists, if so get rid of it.
	$result = $wpdb->query( "SHOW INDEX FROM {$wp_prefix}statistics_visitor WHERE Key_name = 'date_ip'" );

	// Note, the result will be the number of fields contained in the index.
	if ( $result > 1 ) {
		$wpdb->query( "DROP INDEX `date_ip` ON {$wp_prefix}statistics_visitor" );
	}

	// One final database change, drop the 'AString' column from visitors if it exists as it's a typo from an old version.
	$result = $wpdb->query( "SHOW COLUMNS FROM {$wp_prefix}statistics_visitor LIKE 'AString'" );

	if ( $result > 0 ) {
		$wpdb->query( "ALTER TABLE `{$wp_prefix}statistics_visitor` DROP `AString`" );
	}

	// Store the new version information.
	update_option( 'wp_statistics_plugin_version', WP_STATISTICS_VERSION );
	update_option( 'wp_statistics_db_version', WP_STATISTICS_VERSION );

	// Now check to see what database updates may be required and record them for a user notice later.
	$dbupdates = array( 'date_ip_agent' => false, 'unique_date' => false );

	// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
	$result = $wpdb->query( "SHOW INDEX FROM {$wp_prefix}statistics_visitor WHERE Key_name = 'date_ip_agent'" );

	// Note, the result will be the number of fields contained in the index, so in our case 5.
	if ( $result != 5 ) {
		$dbupdates['date_ip_agent'] = true;
	}

	// Check the number of index's on the visits table, if it's only 5 we need to check for duplicate entries and remove them
	$result = $wpdb->query( "SHOW INDEX FROM {$wp_prefix}statistics_visit WHERE Key_name = 'unique_date'" );

	// Note, the result will be the number of fields contained in the index, so in our case 1.
	if ( $result != 1 ) {
		$dbupdates['unique_date'] = true;
	}

	$WP_Statistics->update_option( 'pending_db_updates', $dbupdates );

	$default_options = $WP_Statistics->Default_Options();

	if ( $WPS_Installed == false ) {

		// If this is a first time install, we just need to setup the primary values in the tables.

		$WP_Statistics->Primary_Values();

		// By default, on new installs, use the new search table.
		$WP_Statistics->update_option( 'search_converted', 1 );

	} else {

		// If this is an upgrade, we need to check to see if we need to convert anything from old to new formats.

		// Check to see if the "new" settings code is in place or not, if not, upgrade the old settings to the new system.
		if ( get_option( 'wp_statistics' ) === false ) {
			$core_options   = array(
				'wps_disable_map',
				'wps_map_location',
				'wps_google_coordinates',
				'wps_schedule_dbmaint',
				'wps_schedule_dbmaint_days',
				'wps_geoip',
				'wps_update_geoip',
				'wps_schedule_geoip',
				'wps_last_geoip_dl',
				'wps_auto_pop',
				'wps_useronline',
				'wps_check_online',
				'wps_visits',
				'wps_visitors',
				'wps_store_ua',
				'wps_coefficient',
				'wps_pages',
				'wps_track_all_pages',
				'wps_disable_column',
				'wps_menu_bar',
				'wps_hide_notices',
				'wps_chart_totals',
				'wps_stats_report',
				'wps_time_report',
				'wps_send_report',
				'wps_content_report',
				'wps_read_capability',
				'wps_manage_capability',
				'wps_record_exclusions',
				'wps_robotlist',
				'wps_exclude_ip',
				'wps_exclude_loginpage',
				'wps_exclude_adminpage'
			);
			$var_options    = array( 'wps_disable_se_%', 'wps_exclude_%' );
			$widget_options = array(
				'name_widget',
				'useronline_widget',
				'tvisit_widget',
				'tvisitor_widget',
				'yvisit_widget',
				'yvisitor_widget',
				'wvisit_widget',
				'mvisit_widget',
				'ysvisit_widget',
				'ttvisit_widget',
				'ttvisitor_widget',
				'tpviews_widget',
				'ser_widget',
				'select_se',
				'tp_widget',
				'tpg_widget',
				'tc_widget',
				'ts_widget',
				'tu_widget',
				'ap_widget',
				'ac_widget',
				'au_widget',
				'lpd_widget',
				'select_lps'
			);

			// Handle the core options, we're going to strip off the 'wps_' header as we store them in the new settings array.
			foreach ( $core_options as $option ) {
				$new_name = substr( $option, 4 );

				$WP_Statistics->store_option( $new_name, get_option( $option ) );

				delete_option( $option );
			}

			$wiget = array();

			// Handle the widget options, we're going to store them in a sub-array.
			foreach ( $widget_options as $option ) {
				$widget[ $option ] = get_option( $option );

				delete_option( $option );
			}

			$WP_Statistics->store_option( 'widget', $widget );

			foreach ( $var_options as $option ) {
				// Handle the special variables options.
				$result = $wpdb->get_results( "SELECT * FROM {$wp_prefix}options WHERE option_name LIKE '{$option}'" );

				foreach ( $result as $opt ) {
					$new_name = substr( $opt->option_name, 4 );

					$WP_Statistics->store_option( $new_name, $opt->option_value );

					delete_option( $opt->option_name );
				}
			}

			$WP_Statistics->save_options();
		}

		// If the robot list is empty, fill in the defaults.
		$wps_temp_robotslist = $WP_Statistics->get_option( 'robotlist' );

		if ( trim( $wps_temp_robotslist ) == "" || $WP_Statistics->get_option( 'force_robot_update' ) == true ) {
			$WP_Statistics->update_option( 'robotlist', $default_options['robotlist'] );
		}

		// WP Statistics V4.2 and below automatically exclude the administrator for statistics collection
		// newer versions allow the option to be set for any role in WordPress, however we should mimic
		// 4.2 behaviour when we upgrade, so see if the option exists in the database and if not, set it.
		// This will not work correctly on a WordPress install that has removed the administrator role.
		// However that seems VERY unlikely.
		$exclude_admins = $WP_Statistics->get_option( 'exclude_administrator', '2' );
		if ( $exclude_admins == '2' ) {
			$WP_Statistics->update_option( 'exclude_administrator', '1' );
		}

		// WordPress 4.3 broke the diplay of the sidebar widget because it no longer accepted a null value
		// to be returned from the widget update function, let's look to see if we need to update any
		// occurances in the options table.
		$widget_options = get_option( 'widget_wpstatistics_widget' );
		if ( is_array( $widget_options ) ) {
			foreach ( $widget_options as $key => $value ) {
				// We want to update all null array keys that are integers.
				if ( $value === null && is_int( $key ) ) {
					$widget_options[ $key ] = array();
				}
			}

			// Store the widget options back to the database.
			update_option( 'widget_wpstatistics_widget', $widget_options );
		}
	}

	// We've already handled some of the default or need to do more logic checks on them so create a list to exclude from the next loop.
	$excluded_defaults = array( 'force_robot_update', 'robot_list' );

	// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
	foreach ( $default_options as $key => $value ) {
		if ( ! in_array( $key, $excluded_defaults ) && false === $WP_Statistics->get_option( $key ) ) {
			$WP_Statistics->store_option( $key, $value );
		}
	}

	if ( $WPS_Installed == false ) {
		// We now need to set the robot list to update during the next release.  This is only done for new installs to ensure we don't overwrite existing custom robot lists.
		$WP_Statistics->store_option( 'force_robot_update', true );
	}

	// For version 8.0, we're removing the old %option% types from the reports, so let's upgrade anyone who still has them to short codes.
	$report_content = $WP_Statistics->get_option( 'content_report' );

	// Check to make sure we have a report to process.
	if ( trim( $report_content ) == '' ) {
		// These are the variables we can replace in the template and the short codes we're going to replace them with.
		$template_vars = array(
			'user_online'       => '[wpstatistics stat=usersonline]',
			'today_visitor'     => '[wpstatistics stat=visitors time=today]',
			'today_visit'       => '[wpstatistics stat=visits time=today]',
			'yesterday_visitor' => '[wpstatistics stat=visitors time=yesterday]',
			'yesterday_visit'   => '[wpstatistics stat=visits time=yesterday]',
			'total_visitor'     => '[wpstatistics stat=visitors time=total]',
			'total_visit'       => '[wpstatistics stat=visits time=total]',
		);

		// Replace the items in the template.
		$final_report = preg_replace_callback( '/%(.*?)%/im', function ( $m ) {
			return $template_vars[ $m[1] ];
		}, $report_content );

		// Store the updated report content.
		$WP_Statistics->store_option( 'content_report', $final_report );
	}

	// Save the settings now that we've set them.
	$WP_Statistics->save_options();

	if ( $WP_Statistics->get_option( 'upgrade_report' ) == true ) {
		$WP_Statistics->update_option( 'send_upgrade_email', true );
	}

	// Handle multi site implementations
	if ( is_multisite() ) {
		$current_blog = get_current_blog_id();

		// Loop through each of the sites.
		$sites = $WP_Statistics->get_wp_sites_list();
		foreach ( $sites as $blog_id ) {

			// Since we've just upgraded/installed the current blog, don't execute a remote call for us.
			if ( $blog_id != $current_blog ) {

				// Get the admin url for the current site.
				$url = get_admin_url( $blog_id );

				// Go and visit the admin url of the site, this will rerun the install script for each site.
				// We turn blocking off because we don't really care about the response so why wait for it.
				wp_remote_request( $url, array( 'blocking' => false ) );
			}
		}
	}
}
?>