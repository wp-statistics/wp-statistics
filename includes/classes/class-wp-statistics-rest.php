<?php

/**
 * Class WP_Statistics_Rest
 */
class WP_Statistics_Rest {

	//Set Default namespace
	const route = 'wpstatistics/v1';

	//Set Default Statistic Save method
	const func = 'hit';

	//Set Route Test Rest Api is Active
	const test_rest_api = 'wp_rest_api_active';

	//Set Default POST Name
	const _POST = 'wp_statistics_hit';


	/**
	 * Setup an Wordpress REst Api action.
	 */
	static function init() {

		/*
		 * add Router Rest Api
		 */
		if ( WP_Statistics_Frontend::is_cache_active() ) {
			add_action( 'rest_api_init', array( self::class, 'route' ) );
		}

	}


	/*
	 * Add Endpoint Route
	 */
	static function route() {

		//Get Hit
		register_rest_route( self::route, '/' . self::func, array(
			'methods'  => 'POST',
			'callback' => array( self::class, 'hit' ),
		) );

		//Test REST Api is Active
		register_rest_route( self::route, '/' . self::test_rest_api, array(
			'methods'  => 'GET',
			'callback' => array( self::class, 'wp_rest_api_active' ),
		) );
	}


	/*
	 * Wp Statistic Hit Save
	 */
	static public function hit() {
		global $WP_Statistics;


		/*
		 * Check Security Referer Only This Domain Access
		 */
		$header = getallheaders();


		//Check Auth Key Request
		if ( ! isset( $header['X-Ajax-WP-Statistics'] ) ) {
			return new WP_Error( 'error', 'You have no right to access', array( 'status' => 403 ) );
		}

		// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
		// This seems to happen sometimes with WP Cron calls.
		if ( ! is_object( $WP_Statistics ) ) {
			return;
		}

		$h = new WP_Statistics_GEO_IP_Hits;

		// Call the online users tracking code.
		if ( $WP_Statistics->get_option( 'useronline' ) ) {
			$h->Check_online();
		}

		// Call the visitor tracking code.
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$h->Visitors();
		}

		// Call the visit tracking code.
		if ( $WP_Statistics->get_option( 'visits' ) ) {
			$h->Visits();
		}

		// Call the page tracking code.
		if ( $WP_Statistics->get_option( 'pages' ) ) {
			$h->Pages();
		}
	}

	/*
	 * Test Rest Api is Active
	 */
	public static function wp_rest_api_active() {

		return array( "is_activate_wp_rest_api" => "OK" );
	}

	/*
	 * Check is Rest Request
	 */
	static public function is_rest() {
		$header = getallheaders();
		if ( isset( $header['X-Ajax-WP-Statistics'] ) and isset( $_POST[ self::_POST ] ) ) {
			return true;
		}

		return false;
	}

	/*
	 * Get Params Request
	 */
	static public function params( $params ) {
		return $_POST[ self::_POST ][ $params ];
	}

}
