<?php

/**
 * Class WP_Statistics_Frontend
 */
class WP_Statistics_Frontend {

	public function __construct() {
		global $WP_Statistics;

		//init Session
		if ( WP_Statistics_Frontend::is_cache_active() ) {
			add_action( 'init', 'WP_Statistics_Frontend::init_session' );
		}

		add_filter( 'widget_text', 'do_shortcode' );

		new WP_Statistics_Schedule;

		// Add the honey trap code in the footer.
		add_action( 'wp_footer', 'WP_Statistics_Frontend::add_honeypot' );

		// Enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', 'WP_Statistics_Frontend::enqueue_scripts' );

		// We can wait until the very end of the page to process the statistics,
		// that way the page loads and displays quickly.
		add_action( 'wp', 'WP_Statistics_Frontend::init' );

		//Add inline Rest Request
		add_action( 'wp_footer', 'WP_Statistics_Frontend::add_inline_rest_js' );

		//Get params For cache enabled
		if ( self::is_cache_active() ) {
			if ( self::is_rest() ) { /* Core is not define */
			} else {
				add_action( "wp", function () {
					WP_Statistics_Frontend::set_default_params();
				}, 999 );
			}
		}

		//Add Html Comment in head
		if ( self::is_cache_active() === false ) {
			add_action( 'wp_head', 'WP_Statistics_Frontend::html_comment' );
		}
	}

	/*
	 * init Session
	 */
	static public function init_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/*
	 * Create Comment support Wappalyzer
	 */
	static public function html_comment() {
		echo '<!-- Analytics by WP-Statistics v' . WP_Statistics::$reg['version'] . ' - ' . WP_Statistics::$reg['plugin-data']['PluginURI'] . ' -->' . "\n";
	}


	/*
	 * Check Is Rest Request in Wordpress
	 */
	public static function is_rest() {
		$prefix = rest_get_url_prefix();
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST
			 || isset( $_GET['rest_route'] )
				&& strpos( trim( $_GET['rest_route'], '\\/' ), $prefix, 0 ) === 0 ) {
			return true;
		}

		$rest_url    = wp_parse_url( site_url( $prefix ) );
		$current_url = wp_parse_url( add_query_arg( array() ) );

		return strpos( $current_url['path'], $rest_url['path'], 0 ) === 0;
	}


	/**
	 * Footer Action
	 */
	static function add_honeypot() {
		global $WP_Statistics;
		if ( $WP_Statistics->get_option( 'use_honeypot' ) && $WP_Statistics->get_option( 'honeypot_postid' ) > 0 ) {
			$post_url = get_permalink( $WP_Statistics->get_option( 'honeypot_postid' ) );
			echo '<a href="' . $post_url . '" style="display: none;">&nbsp;</a>';
		}
	}

	/**
	 * Enqueue Scripts
	 *
	 * @param string $hook Not Used
	 */
	static function enqueue_scripts( $hook ) {

		// Load our CSS to be used.
		wp_enqueue_style( 'wpstatistics-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/frontend.css', true, WP_Statistics::$reg['version'] );

		if ( self::is_cache_active() ) {
			//Load Jquery
			wp_enqueue_script( 'jquery' );
		}
	}

	/*
	 * Check Is Cache Activate in wp Statistic Option
	 */
	static public function is_cache_active() {
		global $WP_Statistics;
		if ( ! $WP_Statistics->get_option( 'use_cache_plugin' ) ) {
			return false;
		}

		return true;
	}

	/*
	 * Inline Js
	 */
	static public function add_inline_rest_js() {
		if ( self::is_cache_active() ) {
			self::html_comment();
			echo '<script>jQuery(document).ready(function($){jQuery.ajax({type:\'POST\',cache:false,url:\'' . path_join( get_rest_url(), WP_Statistics_Rest::route . '/' . WP_Statistics_Rest::func ) . '\',beforeSend: function(xhr){xhr.setRequestHeader(\'X-Ajax-WP-Statistics\',\'true\');},});});</script>' . "\n";
		}
	}

	/*
	 * Set Default Params Rest Api
	 */
	static public function set_default_params() {
		global $wpdb, $wp_query, $WP_Statistics;

		/*
		 * Remove Last Session
		 */
		unset( $_SESSION[ WP_Statistics_Rest::session ] );

		/*
		 * Load Rest Api JavaScript
		 */
		$params = array();

		//Set Url
		$params['base'] = rtrim( get_rest_url(), "/" );

		//Set Browser
		$result             = $WP_Statistics->get_UserAgent();
		$params['browser']  = $result['browser'];
		$params['platform'] = $result['platform'];
		$params['version']  = $result['version'];

		//set referred
		$params['referred'] = $WP_Statistics->get_Referred();

		//set prefix Rest
		$params['api'] = rtrim( rest_get_url_prefix(), "/" );

		//Set ip
		$params['ip'] = $WP_Statistics->get_IP();

		//set hash ip
		$params['hash_ip'] = $WP_Statistics->get_hash_string();

		//exclude
		$check_exclude            = new WP_Statistics_Hits();
		$params['exclude']        = $check_exclude->exclusion_match;
		$params['exclude_reason'] = $check_exclude->exclusion_reason;

		//User Agent String
		$params['ua'] = '';
		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$params['ua'] = $_SERVER['HTTP_USER_AGENT'];
		}

		//track all page
		$params['track_all'] = 0;
		if ( WP_Statistics_Hits::is_track_all_page() === true ) {
			$params['track_all'] = 1;
		}

		//timestamp
		$params['timestamp'] = $WP_Statistics->current_date( 'U' );

		//Wp_query
		$params['is_object_wp_query'] = 'false';
		if ( is_object( $wp_query ) ) {
			$params['is_object_wp_query'] = 'true';
			$params['current_page_id']    = $wp_query->get_queried_object_id();
		}

		//page url
		$params['page_uri'] = wp_statistics_get_uri();

		//Add To Session
		$_SESSION[ WP_Statistics_Rest::session ] = $params;

		return $params['base'];
	}


	/**
	 * Shutdown Action
	 */
	static function init() {
		global $WP_Statistics;

		// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.
		// This seems to happen sometimes with WP Cron calls.
		if ( ! is_object( $WP_Statistics ) ) {
			return;
		}

		//Disable if User Active cache Plugin
		if ( self::is_cache_active() === false ) {

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

		// Check to show hits in posts/pages
		if ( $WP_Statistics->get_option( 'show_hits' ) ) {
			add_filter( 'the_content', 'WP_Statistics_Frontend::show_hits' );
		}
	}

	/**
	 * @param $content
	 *
	 * @return string
	 */
	public static function show_hits( $content ) {
		global $WP_Statistics;

		// Get post ID
		$post_id = get_the_ID();

		// Check post ID
		if ( ! $post_id ) {
			return $content;
		}

		// Get post hits
		$hits      = wp_statistics_pages( 'total', "", $post_id );
		$hits_html = '<p>' . sprintf( __( 'Hits: %s', 'wp-statistics' ), $hits ) . '</p>';

		// Check hits position
		if ( $WP_Statistics->get_option( 'display_hits_position' ) == 'before_content' ) {
			return $hits_html . $content;
		} elseif ( $WP_Statistics->get_option( 'display_hits_position' ) == 'after_content' ) {
			return $content . $hits_html;
		} else {
			return $content;
		}
	}

}
