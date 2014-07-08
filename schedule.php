<?php

	// Add the report schedule if it doesn't exist and is enabled.
	if( !wp_next_scheduled('report_hook') && $WP_Statistics->get_option('stats_report') ) {
	
		wp_schedule_event(time(), $WP_Statistics->get_option('time_report'), 'report_hook');
	}

	// Remove the report schedule if it does exist and is disabled.
	if( wp_next_scheduled('report_hook') && !$WP_Statistics->get_option('stats_report') ) {
	
		wp_unschedule_event(wp_next_scheduled('report_hook'), 'report_hook');
	}

	// Add the GeoIP update schedule if it doesn't exist and it should be.
	if( !wp_next_scheduled('wp_statistics_geoip_hook') && $WP_Statistics->get_option('schedule_geoip') && $WP_Statistics->get_option('geoip') ) {
	
		wp_schedule_event(time(), 'daily', 'wp_statistics_geoip_hook'); 
	}

	// Remove the GeoIP update schedule if it does exist and it should shouldn't.
	if( wp_next_scheduled('wp_statistics_geoip_hook') && (!$WP_Statistics->get_option('schedule_geoip') || !$WP_Statistics->get_option('geoip') ) ) {
	
		wp_unschedule_event(wp_next_scheduled('wp_statistics_geoip_hook'), 'wp_statistics_geoip_hook'); 
	}

	// Add the GeoIP update schedule if it doesn't exist and it should be.
	if( !wp_next_scheduled('wp_statistics_dbmaint_hook') && $WP_Statistics->get_option('schedule_dbmaint') ) {
	
		wp_schedule_event(time(), 'daily', 'wp_statistics_dbmaint_hook'); 
	}

	// Remove the GeoIP update schedule if it does exist and it should shouldn't.
	if( wp_next_scheduled('wp_statistics_dbmaint_hook') && (!$WP_Statistics->get_option('schedule_dbmaint') ) ) {
	
		wp_unschedule_event(wp_next_scheduled('wp_statistics_dbmaint_hook'), 'wp_statistics_dbmaint_hook'); 
	}

	function wp_statistics_geoip_event() {
	
		// Maxmind updates the geoip database on the first Tuesday of the month, to make sure we don't update before they post
		// the update, download it two days later.
		$thisupdate = strtotime('First Tuesday of this month') + (86400 * 2);

		$lastupdate = $WP_Statistics->get_option('last_geoip_dl');
		
		$upload_dir = wp_upload_dir();
		 
		// We're also going to look to see if our filesize is to small, this means the plugin stub still exists and should
		// be replaced with a proper file.
		$dbsize = filesize($upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb');
		
		if( $lastupdate < $thisupdate || $dbsize < 1024 ) {
		
			// We can't fire the download function directly here as we rely on some functions that haven't been loaded yet
			// in WordPress, so instead just set the flag in the options table and the shutdown hook will take care of the
			// actual download at the end of the page.
			update_option('wps_update_geoip',TRUE);
		}
	}
	add_action('wp_statistics_geoip_hook', 'wp_statistics_geoip_event');


	function wp_statistics_dbmaint_event() {

		global $wpdb;
		
		$purge_days = intval( $WP_Statistics->get_option('schedule_dbmaint_days', FALSE) );
		
		if(  $purge_days > 30 ) {
		
			$table_name = $wpdb->prefix . 'statistics_visit';
			$date_string = date( 'Y-m-d', strtotime( '-' . $purge_days . ' days')); 
	 
			$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `last_counter` < \'' . $date_string . '\'');
			
			$table_name = $wpdb->prefix . 'statistics_visitor';

			$result = $wpdb->query('DELETE FROM ' . $table_name . ' WHERE `last_counter` < \'' . $date_string . '\'');
		}
	}
	add_action('wp_statistics_dbmaint_hook', 'wp_statistics_dbmaint_event');

	
	function wp_statistics_send_report() {
	
		$string = $WP_Statistics->get_option('content_report');
		
		$template_vars = array(
			'user_online'		=>	wp_statistics_useronline(),
			'today_visitor'		=>	wp_statistics_visitor('today'),
			'today_visit'		=>	wp_statistics_visit('today'),
			'yesterday_visitor'	=>	wp_statistics_visitor('yesterday'),
			'yesterday_visit'	=>	wp_statistics_visit('yesterday'),
			'total_visitor'		=>	wp_statistics_visitor('total'),
			'total_visit'		=>	wp_statistics_visit('total')
		);

		$final_text_report = preg_replace('/%(.*?)%/ime', "\$template_vars['$1']", $string);
		
		if( $WP_Statistics->get_option('send_report') == 'mail' ) {
		
			$blogname = get_bloginfo('name');
			$blogemail = get_bloginfo('admin_email');
			
			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";
			
			wp_mail( get_bloginfo('admin_email'), __('Statistical reporting', 'wp_statistics'), $final_text_report, $headers );
			
		} else if( $WP_Statistics->get_option('send_report') == 'sms' ) {
		
			global $obj;

			if( class_exists(get_option('wp_webservice')) ) {
			
				$obj->to = array(get_option('wp_admin_mobile'));
				$obj->msg = $final_text_report;
				$obj->send_sms();
			}
			
		}
	}
	add_action('report_hook', 'wp_statistics_send_report');
?>