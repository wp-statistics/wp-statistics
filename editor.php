<?php
/*
	Adds a box to the main column on the Post and Page edit screens.
 */
function wp_statistics_add_editor_meta_box() {
	GLOBAL $WP_Statistics;
		
	$WP_Statistics->load_user_options();
	
	// We need to fudge the display settings for first time users so not all of the widgets are disaplyed, we only want to do this on
	// the first time they visit the dashboard though so check to see if we've been here before.
	if( !$WP_Statistics->get_user_option('editor_set') ) {
		$WP_Statistics->update_user_option('editor_set', WP_STATISTICS_VERSION);
		
		$hidden_widgets = get_user_meta($WP_Statistics->user_id, 'metaboxhidden_post', true);
		if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
		
		if( !in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
			$hidden_widgets[] = 'wp_statistics_editor_meta_box';
		}
		
		update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_post', $hidden_widgets );

		$hidden_widgets = get_user_meta($WP_Statistics->user_id, 'metaboxhidden_page', true);
		if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
		
		if( !in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
			$hidden_widgets[] = 'wp_statistics_editor_meta_box';
		}
		
		update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_page', $hidden_widgets );
	}

	// If the user does not have at least read access to the status plugin, just return without adding the widgets.
	if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) { return; }

	// If the admin has disabled the widgets don't display them.
	if ($WP_Statistics->get_option('disable_editor')) { return; }

	$screens = array( 'post', 'page' );

	foreach ( $screens as $screen ) {

		add_meta_box(
			'wp_statistics_editor_meta_box',
			__( 'Hit Statistics', 'wp_statistics' ),
			'wp_statistics_editor_meta_box',
			$screen,
			'normal',
			'high'
		);
	}
}
add_action( 'add_meta_boxes', 'wp_statistics_add_editor_meta_box' );



function wp_statistics_editor_meta_box( $post ) {
	// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
	if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp_statistics_editor_meta_box', $post->post_type ) ) !== true ) { echo $is_visible; return; }

	// If the post isn't published yet, don't output the stats as they take too much memory and CPU to compute for no reason.
	if( $post->post_status != 'publish' && $post->post_status != 'private' ) { _e('This post is not yet published.', 'wp_statistics'); return; }
	
	include_once( dirname( __FILE__ ) . '/includes/log/widgets/page.php' );
	
	wp_statistics_load_widget_css_and_scripts();
	
	wp_statistics_generate_page_postbox_content( null, $post->ID, 20, 'Hits in the last 20 days' );
}

?>