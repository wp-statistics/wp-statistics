<?php

/**
 * Class WP_Statistics_Welcome
 */
class WP_Statistics_Welcome {
	/**
	 * Register page
	 */
	public static function menu() {
		add_submenu_page( null, 'WP-Statistics Welcome', 'WP-Statistics Welcome', 'administrator', 'wps_welcome', 'WP_Statistics_Welcome::page_callback' );
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

		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/welcome.php" );
	}

	/**
	 * @param $upgrader_object
	 * @param $options
	 */
	public function redirect_to_welcome( $upgrader_object, $options ) {
		$current_plugin_path_name = plugin_basename( __FILE__ );

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					wp_redirect( admin_url( 'index.php?page=custom-dashboard' ) );
				}
			}
		}
	}

	public static function get_change_log() {
		$response = wp_remote_get( 'https://api.github.com/repos/wp-statistics/wp-statistics/releases/latest' );

		// Check response
		if ( is_wp_error( $response ) ) {
			return;
		}

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( $response_code == '200' ) {
			$data = json_decode( $response['body'] );

			return nl2br($data->body);
		}
	}
}