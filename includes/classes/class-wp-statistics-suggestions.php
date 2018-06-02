<?php

/**
 * Class WP_Statistics_Suggestions
 */
class WP_Statistics_Suggestions {
	/**
	 * WP_Statistics_Suggestions constructor.
	 */
	public function __construct() {
		add_action( 'wp_statistics_after_title', array( $this, 'travod_callback' ) );
	}

	public function travod_callback() {
		include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/suggestions/top-summary.php" );
	}

	public function getBaseURL() {
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
}