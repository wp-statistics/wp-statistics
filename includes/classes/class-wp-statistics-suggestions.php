<?php

/**
 * Class WP_Statistics_Suggestions
 */
class WP_Statistics_Suggestions {
	/**
	 * WP_Statistics_Suggestions constructor.
	 */
	public function __construct() {
		global $WP_Statistics;

		// Check the suggestion is enabled.
		if ( ! $WP_Statistics->get_option( 'disable_suggestion_nag', false ) ) {
			add_action( 'wp_statistics_after_title', array( $this, 'travod_widget' ) );
			add_action( 'wp_statistics_after_scripts', array( $this, 'travod_script' ) );
		}
	}

	public function travod_widget() {
		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/suggestions/top-summary.php" );
	}

	public function travod_script() {
		include( WP_Statistics::$reg['plugin-dir'] . "assets/js/travod.js" );
	}

	public function get_base_url() {
		$url = get_bloginfo( 'url' );

		if ( substr( $url, 0, 8 ) == 'https://' ) {
			$url = substr( $url, 8 );
		}
		if ( substr( $url, 0, 7 ) == 'http://' ) {
			$url = substr( $url, 7 );
		}
		if ( substr( $url, 0, 4 ) == 'www.' ) {
			$url = substr( $url, 4 );
		}
		if ( strpos( $url, '/' ) !== false ) {
			$explode = explode( '/', $url );
			$url     = $explode['0'];
		}

		return ucfirst( $url );
	}

	public function get_current_username() {
		$user = wp_get_current_user();

		if ( isset( $user->data->display_name ) ) {
			return $user->data->display_name;
		}
	}
}