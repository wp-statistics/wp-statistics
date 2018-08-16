<?php

/**
 * Class WP_Statistics_Schedule
 */
class WP_Statistics_Schedule {

	/**
	 * WP_Statistics_Schedule constructor.
	 *
	 * @param $WP_Statistics
	 */
	function __construct() {
		global $WP_Statistics;

		// before construct
		add_filter( 'cron_schedules', 'WP_Statistics_Schedule::addcron' );

		// Add the report schedule if it doesn't exist and is enabled.
		if ( ! wp_next_scheduled( 'report_hook' ) && $WP_Statistics->get_option( 'stats_report' ) ) {

			wp_schedule_event( time(), $WP_Statistics->get_option( 'time_report' ), 'report_hook' );
		}

		// Remove the report schedule if it does exist and is disabled.
		if ( wp_next_scheduled( 'report_hook' ) && ! $WP_Statistics->get_option( 'stats_report' ) ) {

			wp_unschedule_event( wp_next_scheduled( 'report_hook' ), 'report_hook' );
		}

		// Add the GeoIP update schedule if it doesn't exist and it should be.
		if ( ! wp_next_scheduled( 'wp_statistics_geoip_hook' ) &&
		     $WP_Statistics->get_option( 'schedule_geoip' ) &&
		     $WP_Statistics->get_option( 'geoip' )
		) {

			wp_schedule_event( time(), 'daily', 'wp_statistics_geoip_hook' );
		}

		// Remove the GeoIP update schedule if it does exist and it should shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_geoip_hook' ) &&
		     ( ! $WP_Statistics->get_option( 'schedule_geoip' ) || ! $WP_Statistics->get_option( 'geoip' ) )
		) {

			wp_unschedule_event( wp_next_scheduled( 'wp_statistics_geoip_hook' ), 'wp_statistics_geoip_hook' );
		}

		// Add the browscap update schedule if it doesn't exist and it should be.
		if ( ! wp_next_scheduled( 'wp_statistics_browscap_hook' ) && $WP_Statistics->get_option( 'schedule_browscap' ) ) {

			wp_schedule_event( time(), 'weekly', 'wp_statistics_browscap_hook' );
		}

		// Remove the browscap update schedule if it does exist and it should shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_browscap_hook' ) && ! $WP_Statistics->get_option( 'schedule_browscap' ) ) {

			wp_unschedule_event( wp_next_scheduled( 'wp_statistics_browscap_hook' ), 'wp_statistics_browscap_hook' );
		}

		// Add the referrerspam update schedule if it doesn't exist and it should be.
		if ( ! wp_next_scheduled( 'wp_statistics_referrerspam_hook' ) &&
		     $WP_Statistics->get_option( 'schedule_referrerspam' )
		) {

			wp_schedule_event( time(), 'weekly', 'wp_statistics_referrerspam_hook' );
		}

		// Remove the referrerspam update schedule if it does exist and it should shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_referrerspam_hook' ) &&
		     ! $WP_Statistics->get_option( 'schedule_referrerspam' )
		) {

			wp_unschedule_event(
				wp_next_scheduled( 'wp_statistics_referrerspam_hook' ),
				'wp_statistics_referrerspam_hook'
			);
		}

		// Add the database maintenance schedule if it doesn't exist and it should be.
		if ( ! wp_next_scheduled( 'wp_statistics_dbmaint_hook' ) && $WP_Statistics->get_option( 'schedule_dbmaint' ) ) {

			wp_schedule_event( time(), 'daily', 'wp_statistics_dbmaint_hook' );
		}

		// Remove the database maintenance schedule if it does exist and it shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_dbmaint_hook' ) && ( ! $WP_Statistics->get_option( 'schedule_dbmaint' ) ) ) {

			wp_unschedule_event( wp_next_scheduled( 'wp_statistics_dbmaint_hook' ), 'wp_statistics_dbmaint_hook' );
		}

		// Add the visitor database maintenance schedule if it doesn't exist and it should be.
		if ( ! wp_next_scheduled( 'wp_statistics_dbmaint_visitor_hook' ) &&
		     $WP_Statistics->get_option( 'schedule_dbmaint_visitor' )
		) {

			wp_schedule_event( time(), 'daily', 'wp_statistics_dbmaint_visitor_hook' );
		}

		// Remove the visitor database maintenance schedule if it does exist and it shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_dbmaint_visitor_hook' ) &&
		     ( ! $WP_Statistics->get_option( 'schedule_dbmaint_visitor' ) )
		) {

			wp_unschedule_event(
				wp_next_scheduled( 'wp_statistics_dbmaint_visitor_hook' ),
				'wp_statistics_dbmaint_visitor_hook'
			);
		}

		// Remove the add visit row schedule if it does exist and it shouldn't.
		if ( wp_next_scheduled( 'wp_statistics_add_visit_hook' ) && ( ! $WP_Statistics->get_option( 'visits' ) ) ) {

			wp_unschedule_event( wp_next_scheduled( 'wp_statistics_add_visit_hook' ), 'wp_statistics_add_visit_hook' );
		}

		// Add the add visit table row schedule if it does exist and it should.
		if ( ! wp_next_scheduled( 'wp_statistics_add_visit_hook' ) && $WP_Statistics->get_option( 'visits' ) ) {

			wp_schedule_event( time(), 'daily', 'wp_statistics_add_visit_hook' );
		}

		//after construct
		add_action( 'wp_statistics_add_visit_hook', 'WP_Statistics_Schedule::add_visit_event' );
		add_action( 'wp_statistics_geoip_hook', 'WP_Statistics_Schedule::geoip_event' );
		add_action( 'wp_statistics_browscap_hook', 'WP_Statistics_Schedule::browscap_event' );
		add_action( 'wp_statistics_referrerspam_hook', 'WP_Statistics_Schedule::referrerspam_event' );
		add_action( 'wp_statistics_dbmaint_hook', 'WP_Statistics_Schedule::dbmaint_event' );
		add_action( 'wp_statistics_dbmaint_visitor_hook', 'WP_Statistics_Schedule::dbmaint_visitor_event' );
		add_action( 'report_hook', 'WP_Statistics_Schedule::send_report' );

	}

	/**
	 * @param array $schedules
	 *
	 * @return mixed
	 */
	static function addcron( $schedules ) {
		// Adds once weekly to the existing schedules.
		if ( ! array_key_exists( 'weekly', $schedules ) ) {
			$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => __( 'Once Weekly' ),
			);
		}

		if ( ! array_key_exists( 'biweekly', $schedules ) ) {
			$schedules['biweekly'] = array(
				'interval' => 1209600,
				'display'  => __( 'Once Every 2 Weeks' ),
			);
		}

		if ( ! array_key_exists( '4weeks', $schedules ) ) {
			$schedules['4weeks'] = array(
				'interval' => 2419200,
				'display'  => __( 'Once Every 4 Weeks' ),
			);
		}

		return $schedules;
	}


	/**
	 * adds a record for tomorrow to the visit table to avoid a race condition.
	 *
	 */
	static function add_visit_event() {
		GLOBAL $wpdb, $WP_Statistics;

		$sqlstring = $wpdb->prepare(
			'INSERT INTO ' . $wpdb->prefix . 'statistics_visit (last_visit, last_counter, visit) VALUES ( %s, %s, %d)',
			$WP_Statistics->Current_Date( null, '+1' ),
			$WP_Statistics->Current_date( 'Y-m-d', '+1' ),
			0
		);

		$wpdb->query( $sqlstring );
	}

	/**
	 * Updates the GeoIP database from MaxMind.
	 */
	static function geoip_event() {

		GLOBAL $WP_Statistics;

		// Maxmind updates the geoip database on the first Tuesday of the month, to make sure we don't update before they post
		// the update, download it two days later.
		$thisupdate = strtotime( __( 'First Tuesday of this month', 'wp-statistics' ) ) + ( 86400 * 2 );

		$lastupdate = $WP_Statistics->get_option( 'last_geoip_dl' );

		$upload_dir = wp_upload_dir();

		// We're also going to look to see if our filesize is to small, this means the plugin stub still exists and should
		// be replaced with a proper file.
		$dbsize = filesize( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );

		if ( $lastupdate < $thisupdate || $dbsize < 1024 ) {

			// We can't fire the download function directly here as we rely on some functions that haven't been loaded yet
			// in WordPress, so instead just set the flag in the options table and the shutdown hook will take care of the
			// actual download at the end of the page.
			$WP_Statistics->update_option( 'update_geoip', true );
		}
	}

	/**
	 * Updates the browscap database.
	 */
	static function browscap_event() {

		GLOBAL $WP_Statistics;

		// Check for a new browscap once a week
		$WP_Statistics->update_option( 'update_browscap', true );
	}

	/**
	 * Updates the browscap database.
	 */
	static function referrerspam_event() {

		GLOBAL $WP_Statistics;

		// Check for a new referrerspam once a week
		$WP_Statistics->update_option( 'update_referrerspam', true );
	}

	/**
	 * Purges old records on a schedule based on age.
	 */
	static function dbmaint_event() {
		global $WP_Statistics;
		if ( ! function_exists( 'wp_statistics_purge_data' ) ) {
			require( WP_Statistics::$reg['plugin-dir'] . 'includes/functions/purge.php' );
		}
		$purge_days = intval( $WP_Statistics->get_option( 'schedule_dbmaint_days', false ) );
		wp_statistics_purge_data( $purge_days );
	}

	/**
	 * Purges visitors with more than a defined number of hits in a day.
	 */
	static function dbmaint_visitor_event() {
		global $WP_Statistics;
		if ( ! function_exists( 'wp_statistics_purge_visitor_hits' ) ) {
			require( WP_Statistics::$reg['plugin-dir'] . 'includes/functions/purge-hits.php' );
		}
		$purge_hits = intval( $WP_Statistics->get_option( 'schedule_dbmaint_visitor_hits', false ) );
		wp_statistics_purge_visitor_hits( $purge_hits );
	}

	/**
	 * Sends the statistics report to the selected users.
	 */
	static function send_report() {
		global $WP_Statistics, $sms;

		// Retrieve the template from the options.
		$final_text_report = $WP_Statistics->get_option( 'content_report' );

		// Process shortcodes in the template.  Note that V8.0 upgrade script replaced the old %option% codes with the appropriate short codes.
		$final_text_report = do_shortcode( $final_text_report );
		$final_text_report = apply_filters( 'wp_statistics_final_text_report_email', $final_text_report );

		// Send the report through the selected transport agent.
		if ( $WP_Statistics->get_option( 'send_report' ) == 'mail' ) {

			$blogname  = get_bloginfo( 'name' );
			$blogemail = get_bloginfo( 'admin_email' );

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( $WP_Statistics->get_option( 'email_list' ) == '' ) {
				$WP_Statistics->update_option( 'email_list', $blogemail );
			}

			wp_mail(
				$WP_Statistics->get_option( 'email_list' ),
				__( 'Statistical reporting', 'wp-statistics' ),
				$final_text_report,
				$headers
			);

		} else if ( $WP_Statistics->get_option( 'send_report' ) == 'sms' ) {

			if ( class_exists( get_option( 'wp_webservice' ) ) ) {

				$sms->to  = array( get_option( 'wp_admin_mobile' ) );
				$sms->msg = $final_text_report;
				$sms->SendSMS();
			}

		}
	}


}
