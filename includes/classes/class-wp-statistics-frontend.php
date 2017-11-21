<?php

/**
 * Class WP_Statistics_Frontend
 */
class WP_Statistics_Frontend {

	public function __construct() {
		global $WP_Statistics;

		add_filter('widget_text', 'do_shortcode');

		new \WP_Statistics_Schedule;

		// Add the honey trap code in the footer.
		add_action('wp_footer', 'WP_Statistics_Frontend::footer_action');

		// If we've been told to exclude the feeds from the statistics
		// add a detection hook when WordPress generates the RSS feed.
		if ( $WP_Statistics->get_option('exclude_feeds') ) {
			add_filter('the_title_rss', 'WP_Statistics_Frontend::check_feed_title');
		}
	}



	/**
	 * Footer Action
	 */
	static function footer_action() {
		global $WP_Statistics;
		if ( $WP_Statistics->get_option('use_honeypot') && $WP_Statistics->get_option('honeypot_postid') > 0 ) {
			$post_url = get_permalink($WP_Statistics->get_option('honeypot_postid'));
			echo '<a href="' . $post_url . '" style="display: none;">&nbsp;</a>';
		}
	}

	/**
	 * Check Feed Title
	 *
	 * @param string $title Title
	 *
	 * @return string Title
	 */
	static function check_feed_title( $title ) {
		global $WP_Statistics;
		$WP_Statistics->is_feed = true;
		return $title;
	}

}