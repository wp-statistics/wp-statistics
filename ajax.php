<?php
function wp_statistics_close_donation_nag_action_callback() {
	GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

	$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option('manage_capability', 'manage_options') );
	
	if( current_user_can( $manage_cap ) ) {
		$WP_Statistics->update_option( 'disable_donation_nag', true );
	}
	
	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_wp_statistics_close_donation_nag', 'wp_statistics_close_donation_nag_action_callback' );