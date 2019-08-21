<?php

/**
 * Class WP_Statistics_Rest
 */
class WP_Statistics_Rest {

	// Set Default namespace
	const route = 'wpstatistics/v1';

	// Set Default Statistic Save method
	const func = 'hit';

	// Set Default POST Name
	const _Argument = 'wp_statistics_hit';

	/**
	 * Setup an Wordpress REst Api action.
	 */
	public function __construct() {
		global $WP_Statistics;

		/*
		 * add Router Rest Api
		 */
		if ( isset( $WP_Statistics ) and $WP_Statistics->use_cache ) {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	/*
	 * Add Endpoint Route
	 */
	public function register_routes() {
		// Get Hit
		register_rest_route( self::route, '/' . self::func, array(
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => function () {
				global $WP_Statistics;
				return ( $WP_Statistics->get_option( 'use_cache_plugin' ) == 1 ? true : false );
			},
			'callback'            => array( $this, 'hit' ),
			'args'                => array(
				'_wpnonce'      => array(
					'required'          => true,
					'validate_callback' => function ( $value ) {
						return wp_verify_nonce( $value, 'wp_rest' );
					}
				),
				self::_Argument => array(
					'required'          => true,
					'validate_callback' => function ( $value, $request, $key ) {
						return ( json_decode( wp_unslash( $value ) ) !== null );
					}
				)
			)
		) );
	}

	/*
	 * Wp Statistic Hit Save
	 */
	public function hit() {
		global $WP_Statistics;

		/*
		 * Check Is Test Service Request
		 */
		if ( isset( $_REQUEST['rest-api-wp-statistics'] ) ) {
			return array( "rest-api-wp-statistics" => "OK" );
		}


		//Check Auth Key Request
		if ( ! isset( $_REQUEST[ self::_Argument ] ) ) {
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
	 * Check is Rest Request
	 */
	static public function is_rest() {
		global $WP_Statistics;

		if ( isset( $WP_Statistics ) and $WP_Statistics->use_cache ) {
			if ( isset( $_REQUEST[ self::_Argument ] ) ) {
				return true;
			}
		}

		return false;
	}

	/*
	 * Get Params Request
	 */
	static public function params( $params ) {
		if ( isset( $_REQUEST[ self::_Argument ] ) ) {
			$data = wp_unslash( $_REQUEST[ self::_Argument ] );

			if ( ! empty( $data ) && is_string( $data ) && is_array( json_decode( $data, true ) ) && json_last_error() == 0 ) {
				$data = json_decode( $data, true );
			}

			if ( isset( $data[ $params ] ) ) {
				return $data[ $params ];
			}
		}

		return false;
	}
}
