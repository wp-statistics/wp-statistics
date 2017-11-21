<?php

/**
 * Class WP_Statistics_Frontend
 */
final class WP_Statistics_Frontend {

	public function __construct(){

		add_action('widgets_init', array( $this, 'widget' ));
		add_filter('widget_text', 'do_shortcode');

		new \WP_Statistics_Schedule;

		// Add the honey trap code in the footer.
		add_action('wp_footer', 'WP_Statistics_Frontend::footer_action');

	}

	/**
	 * Registers Widget
	 */
	public function widget() {
		register_widget('WP_Statistics_Widget');
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
}