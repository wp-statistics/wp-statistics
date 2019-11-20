<?php

/**
 * Class WP_Statistics_Rest
 */
class WP_Statistics_Rest {

	// Set Default namespace
	const route = 'wpstatistics/v1';

	// Set Default Statistic Save method
	const func = 'hit';

	// Set Default Name
	const _Argument = 'wp_statistics_hit_rest';

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

	/**
	 * List Of Required Params
	 *
	 * @return array
	 */
	public static function require_params_hit() {
		return array(
			'browser',
			'platform',
			'version',
		);
	}

	/*
	 * Add Endpoint Route
	 */
	public function register_routes() {

		// Create Require Params
		$params = array();
		foreach ( self::require_params_hit() as $p ) {
			$params[ $p ] = array( 'required' => true );
		}

		// Get Hit
		register_rest_route( self::route, '/' . self::func, array(
			'methods'             => \WP_REST_Server::READABLE,
			'permission_callback' => function () {
				global $WP_Statistics;
				return ( $WP_Statistics->get_option( 'use_cache_plugin' ) == 1 ? true : false );
			},
			'callback'            => array( $this, 'hit' ),
			'args'                => array_merge(
				array( '_wpnonce' => array(
					'required'          => true,
					'validate_callback' => function ( $value ) {
						return wp_verify_nonce( $value, 'wp_rest' );
					}
				) ), $params )
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

		// Check Isset global
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

		// Set Return
		return new \WP_REST_Response( array( 'status' => true, 'message' => __( 'Visitor Hit was recorded successfully.', 'wp-statistics' ) ) );
	}

	/*
	 * Check is Rest Request
	 */
	static public function is_rest() {
		global $WP_Statistics;

		if ( isset( $WP_Statistics ) and defined( 'REST_REQUEST' ) && REST_REQUEST and $WP_Statistics->use_cache ) {
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
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST and isset( $_REQUEST[ self::_Argument ] ) ) {
			$data = array();
			foreach ( $_REQUEST as $key => $value ) {
				if ( ! in_array( $key, array( '_', '_wpnonce' ) ) ) {
					$data[ $key ] = trim( $value );
				}
			}

			if ( isset( $data[ $params ] ) ) {
				return $data[ $params ];
			}
		}

		return false;
	}
}
