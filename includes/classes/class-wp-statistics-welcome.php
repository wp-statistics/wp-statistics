<?php

/**
 * Class WP_Statistics_Welcome
 */
class WP_Statistics_Welcome {
	/**
	 * Initial
	 */
	public static function init() {
		global $WP_Statistics;
		if ( $WP_Statistics->get_option( 'show_welcome_page', false ) and ( strpos( $_SERVER['REQUEST_URI'], '/wp-admin/index.php' ) !== false or ( isset( $_GET['page'] ) and $_GET['page'] == 'wps_overview_page' ) ) ) {
			// Disable show welcome page
			$WP_Statistics->update_option( 'first_show_welcome_page', true );
			$WP_Statistics->update_option( 'show_welcome_page', false );

			// Redirect to welcome page
			wp_redirect( admin_url( 'admin.php?page=wps_welcome' ) );
		}

		if ( ! $WP_Statistics->get_option( 'first_show_welcome_page', false ) ) {
			$WP_Statistics->update_option( 'show_welcome_page', true );
		}
	}

	/**
	 * Register menu
	 */
	public static function menu() {
		add_submenu_page( __( 'WP-Statistics Welcome', 'wp-statistics' ), __( 'WP-Statistics Welcome', 'wp-statistics' ), __( 'WP-Statistics Welcome', 'wp-statistics' ), 'administrator', 'wps_welcome', 'WP_Statistics_Welcome::page_callback' );
	}

	/**
	 * Welcome page
	 */
	public static function page_callback() {
		// Load our JS to be used.
		wp_enqueue_script(
			'wp-statistics-admin-js',
			WP_Statistics::$reg['plugin-url'] . 'assets/js/admin.js',
			array( 'jquery' ),
			'1.0'
		);

		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/welcomes/last-version.php" );
	}

	/**
	 * @param $upgrader_object
	 * @param $options
	 */
	public static function do_welcome( $upgrader_object, $options ) {
		$current_plugin_path_name = 'wp-statistics/wp-statistics.php';

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					global $WP_Statistics;

					// Enable welcome page in database
					$WP_Statistics->update_option( 'show_welcome_page', true );
				}
			}
		}
	}

	/**
	 * Show change log
	 */
	public static function show_change_log() {
		$response = wp_remote_get( 'https://api.github.com/repos/wp-statistics/wp-statistics/releases/latest' );

		// Check response
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $response_code == '200' ) {
			$data      = json_decode( $response['body'] );
			$Parsedown = new Parsedown();

			echo $Parsedown->text( nl2br( $data->body ) );
		}
	}
}