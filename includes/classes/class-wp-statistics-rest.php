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
			'ua',
			'url',
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

		// Test REST API WordPress is activate
		register_rest_route( self::route, '/connection', array(
			'methods'  => \WP_REST_Server::READABLE,
			'callback' => array( $this, 'connection' )
		) );
	}

	/**
	 * Check Is Test Service Request
	 * @return array|null
	 */
	public function connection() {
		if ( isset( $_REQUEST['rest-api-wp-statistics'] ) ) {
			return array( "rest-api-wp-statistics" => "OK" );
		}

		return null;
	}

	/*
	 * Wp Statistic Hit Save
	 */
	public function hit( \WP_REST_Request $request ) {
		global $WP_Statistics;

		// Check Isset global
		if ( ! is_object( $WP_Statistics ) ) {
			return;
		}

		// Get Params
		$url        = $request->get_param( 'url' );
		$user_agent = $request->get_param( 'ua' );
		if ( empty( $url ) || empty( $user_agent ) ) {
			return;
		}

		// Check User Agent
		$result = new WhichBrowser\Parser( $user_agent );
		$agent  = array(
			'browser'  => ( isset( $result->browser->name ) ) ? $result->browser->name : _x( 'Unknown', 'Browser', 'wp-statistics' ),
			'platform' => ( isset( $result->os->name ) ) ? $result->os->name : _x( 'Unknown', 'Platform', 'wp-statistics' ),
			'version'  => ( isset( $result->os->version->value ) ) ? $result->os->version->value : _x( 'Unknown', 'Version', 'wp-statistics' ),
		);
		$_REQUEST['browser']  = $agent['browser'];
		$_REQUEST['platform'] = $agent['platform'];
		$_REQUEST['version']  = $agent['version'];

		// Convert Url To WordPress ID
		$page_id                  = url_to_postid( $url );
		$_REQUEST['track_all']    = ( WP_Statistics_Hits::is_track_page() === true ? 1 : 0 );
		$_REQUEST['page_uri']     = str_ireplace( get_home_url(), '', $url );
		$_REQUEST['search_query'] = '';
		if ( $page_id != false ) {
			$_REQUEST['current_page_id'] = $page_id;
			$get_post_type               = get_post_type( $page_id );
			switch ( $get_post_type ) {
				case "product":
					$_REQUEST['current_page_type'] = 'product';
					break;
				case "page":
					$_REQUEST['current_page_type'] = 'page';
					break;
				default:
					$_REQUEST['current_page_type'] = 'post';
			}
		}

		// Check If is a search Query
		$parse = parse_url( $url );
		if ( isset( $parse['query'] ) and ! empty( $parse['query'] ) ) {
			parse_str( $parse['query'], $params_arr );
			if ( isset( $params_arr['s'] ) and ! empty( $params_arr['s'] ) ) {
				$_REQUEST['current_page_type'] = 'search';
				$_REQUEST['current_page_id']   = 0;
				$_REQUEST['search_query']      = esc_html( $params_arr['s'] );
			}
		}

		// Check If Home Page
		if ( rtrim( $url, "/" ) == get_home_url() ) {
			$_REQUEST['current_page_type'] = 'home';
			$_REQUEST['current_page_id']   = 0;
		}

		// Convert Category Url to ID
		$cat_base        = 'category';
		$category_option = get_option( 'category_base' );
		if ( ! empty( $category_option ) ) {
			$cat_base = $category_option;
		}
		$sanitize_category_url = str_ireplace( rtrim( get_home_url(), "/" ) . "/" . ltrim( $cat_base, "/" ), '', $url );
		$cat                   = get_category_by_path( $sanitize_category_url );
		if ( is_object( $cat ) and $cat != false ) {
			$_REQUEST['current_page_type'] = 'category';
			$_REQUEST['current_page_id']   = $cat->term_id;
		}

		// Convert Post Tag Url TO ID
		$tag_base   = 'tag';
		$tag_option = get_option( 'tag_base' );
		if ( ! empty( $tag_option ) ) {
			$tag_base = $tag_option;
		}
		$sanitize_tag_url = str_ireplace( rtrim( get_home_url(), "/" ) . "/" . ltrim( $tag_base, "/" ), '', $url );
		$sanitize_tag_url = rawurlencode( urldecode( $sanitize_tag_url ) );
		$sanitize_tag_url = str_replace( '%2F', '/', $sanitize_tag_url );
		$sanitize_tag_url = str_replace( '%20', ' ', $sanitize_tag_url );
		$sanitize_tag_url = trim( $sanitize_tag_url, '/' );
		$post_tag         = get_term_by( 'slug', $sanitize_tag_url, 'post_tag' );
		if ( is_object( $post_tag ) and $post_tag != false ) {
			$_REQUEST['current_page_type'] = 'post_tag';
			$_REQUEST['current_page_id']   = $post_tag->term_id;
		}

		// Fix Self referral Url
		$referral = $request->get_param( 'referred' );
		if ( empty( $referral ) ) {
			$_REQUEST['referred'] = get_home_url();
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
				if ( ! in_array( $key, array( '_wpnonce' ) ) ) {
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
