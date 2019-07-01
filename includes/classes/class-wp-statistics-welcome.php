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
			wp_redirect( WP_Statistics_Admin_Pages::admin_url( 'wps_welcome' ) );
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

		// Create Default Variable
		$error   = null;
		$plugins = array();

		// Check List Plugins if in Plugins Tab
		if ( isset( $_GET['tab'] ) and $_GET['tab'] == "addons" ) {
			$response      = wp_remote_get( 'https://wp-statistics.com/wp-json/plugin/addons' );
			$response_code = wp_remote_retrieve_response_code( $response );
			if ( is_wp_error( $response ) ) {
				$error = $response->get_error_message();
			} else {
				if ( $response_code == '200' ) {
					$plugins = json_decode( $response['body'] );
				} else {
					$error = $response['body'];
				}
			}
		}

		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/welcome.php" );
	}

	/**
	 * @param $upgrader_object
	 * @param $options
	 */
	public static function do_welcome( $upgrader_object, $options ) {
		$current_plugin_path_name = 'wp-statistics/wp-statistics.php';

		if ( isset( $options['action'] ) and $options['action'] == 'update' and isset( $options['type'] ) and $options['type'] == 'plugin' and isset( $options['plugins'] ) ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					global $WP_Statistics;

					// Enable welcome page in database
					$WP_Statistics->update_option( 'show_welcome_page', true );

					// Run the upgrader
					WP_Statistics_Updates::do_upgrade();
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