<?php

/**
 * Class WP_Statistics_Frontend
 */
class WP_Statistics_Frontend {

	public function __construct() {
		global $WP_Statistics;

		add_filter( 'widget_text', 'do_shortcode' );

		new WP_Statistics_Schedule;

		// Add the honey trap code in the footer.
		add_action( 'wp_footer', 'WP_Statistics_Frontend::add_honeypot' );

		// Enqueue scripts & styles
		add_action( 'wp_enqueue_scripts', 'WP_Statistics_Frontend::enqueue_scripts' );

		// We can wait until the very end of the page to process the statistics,
		// that way the page loads and displays quickly.
		add_action( 'wp', 'WP_Statistics_Frontend::init' );
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
	}

	/**
	 * Show Opt-Out message for the visitors
	 */
	static function opt_out_confirmation() {
		global $WP_Statistics;

		if ( strpos( $_SERVER['REQUEST_URI'], '?' ) !== false ) {
			$concat_char = '&';
		} else {
			$concat_char = '?';
		}

		// Generate request URL
		$action_url      = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . $concat_char . 'wp_statistics_opt_out=';
		$opt_out_message = $WP_Statistics->get_option( 'opt_out_message' );

		$template_vars = array(
			'%accept_url%' => $action_url . '1',
			'%cancel_url%' => $action_url . '0',
		);

		$message = str_replace( array_keys( $template_vars ), array_values( $template_vars ), wp_kses_post($opt_out_message) );

		echo printf( '<div class="wp-statistics-opt-out">%s</div>', $message );
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
