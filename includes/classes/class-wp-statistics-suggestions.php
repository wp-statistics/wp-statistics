<?php

/**
 * Class WP_Statistics_Suggestions
 */
class WP_Statistics_Suggestions {
	public function __construct() {
		add_action( 'wp_statistics_after_nag', function () {

			//include( WP_Statistics::$reg['plugin-dir'] . "includes/templates/welcomes/last-version.php" );

		} );
	}
}