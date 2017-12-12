<?php

/**
 * Class WP_Statistics_About
 */
class WP_Statistics_About {
	/**
	 * Register page
	 */
	public static function menu() {
		add_submenu_page( null, 'WP-Statistics About', 'WP-Statistics About', 'administrator', 'wps_about', 'WP_Statistics_About::page_callback' );
	}

	/**
	 * About page
	 */
	public static function page_callback() {
		// Load our JS to be used.
		wp_enqueue_script(
			'wp-statistics-admin-js',
			WP_Statistics::$reg['plugin-url'] . 'assets/js/admin.js',
			array( 'jquery' ),
			'1.0'
		);

		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/about.php" );
	}

	/**
	 * @param $upgrader_object
	 * @param $options
	 */
	public function redirect_to_about( $upgrader_object, $options ) {
		$current_plugin_path_name = plugin_basename( __FILE__ );

		if ( $options['action'] == 'update' && $options['type'] == 'plugin' ) {
			foreach ( $options['plugins'] as $each_plugin ) {
				if ( $each_plugin == $current_plugin_path_name ) {
					wp_redirect( admin_url( 'index.php?page=custom-dashboard' ) );
				}
			}
		}
	}
}
