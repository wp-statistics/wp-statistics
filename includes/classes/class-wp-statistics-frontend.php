<?php

/**
 * Class WP_Statistics_Frontend
 */
final class WP_Statistics_Frontend {

	public function __construct(){

		add_action('widgets_init', array( $this, 'widget' ));
		add_filter('widget_text', 'do_shortcode');

		new \WP_Statistics_Schedule;

	}

	/**
	 * Registers Widget
	 */
	public function widget() {
		register_widget('WP_Statistics_Widget');
	}

}