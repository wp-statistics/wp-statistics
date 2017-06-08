<?php
/*
	Adds a box to the main column on the Post and Page edit screens.
 */
function wp_statistics_add_editor_meta_box() {
	GLOBAL $WP_Statistics;

	$WP_Statistics->load_user_options();

	// We need to fudge the display settings for first time users so not all of the widgets are displayed, we only want to do this on
	// the first time they visit the dashboard though so check to see if we've been here before.
	if ( ! $WP_Statistics->get_user_option( 'editor_set' ) ) {
		$WP_Statistics->update_user_option( 'editor_set', WP_STATISTICS_VERSION );

		$hidden_widgets = get_user_meta( $WP_Statistics->user_id, 'metaboxhidden_post', true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		if ( ! in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
			$hidden_widgets[] = 'wp_statistics_editor_meta_box';
		}

		update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_post', $hidden_widgets );

		$hidden_widgets = get_user_meta( $WP_Statistics->user_id, 'metaboxhidden_page', true );
		if ( ! is_array( $hidden_widgets ) ) {
			$hidden_widgets = array();
		}

		if ( ! in_array( 'wp_statistics_editor_meta_box', $hidden_widgets ) ) {
			$hidden_widgets[] = 'wp_statistics_editor_meta_box';
		}

		update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_page', $hidden_widgets );
	}

	// If the user does not have at least read access to the status plugin, just return without adding the widgets.
	if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_option' ) ) ) ) {
		return;
	}

	// If the admin has disabled the widgets don't display them.
	if ( $WP_Statistics->get_option( 'disable_editor' ) ) {
		return;
	}

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
	// If the post isn't published yet, don't output the stats as they take too much memory and CPU to compute for no reason.
	if ( $post->post_status != 'publish' && $post->post_status != 'private' ) {
		_e( 'This post is not yet published.', 'wp_statistics' );

		return;
	}

	add_action( 'admin_footer', 'wp_statistics_editor_inline_javascript' );

	wp_statistics_generate_editor_postbox_contents( $post->ID, array( 'args' => array( 'widget' => 'page' ) ) );
}

function wp_statistics_generate_editor_postbox_contents( $post, $args ) {
	$loading_img  = '<div style="width: 100%; text-align: center;"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'ajax-loading.gif" alt="' . __( 'Loading...', 'wp_statistics' ) . '"></div>';
	$widget       = $args['args']['widget'];
	$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';

	echo '<div id="' . $container_id . '">' . $loading_img . '</div>';
	echo '<script type="text/javascript">var wp_statistics_current_id = \'' . $post . '\';</script>';
	wp_statistics_generate_widget_load_javascript( $widget, $container_id );
}

function wp_statistics_editor_inline_javascript() {
	$screen = get_current_screen();

	if ( 'post' != $screen->id && 'page' != $screen->id ) {
		return;
	}

	wp_statistics_load_widget_css_and_scripts();

	$loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'ajax-loading.gif" alt="' . __( 'Reloading...', 'wp_statistics' ) . '"></div>';

	$new_buttons = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button><button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-migrate' ) . '</button>';
	$new_button  = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button>';

	$admin_url = get_admin_url() . "/admin.php?page=";

	$page_urls = array();

	$page_urls['wp_statistics_editor_meta_box_more_button'] = $admin_url . WP_STATISTICS_PAGES_PAGE . '&page-id=';

	?>
    <script type="text/javascript">
        var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
        var wp_statistics_loading_image = '<?php echo $loading_img; ?>'

        function wp_statistics_wait_for_postboxes() {

            if (!jQuery('#show-settings-link').is(':visible')) {
                setTimeout(wp_statistics_wait_for_postboxes, 500);
            }

            jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
            jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more);

            jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);
        }

        jQuery(document).ready(function () {

            // Add the "more" and "refresh" buttons.
            jQuery('.postbox').each(function () {
                var temp = jQuery(this);
                var temp_id = temp.attr('id');

                if (temp_id == 'wp_statistics_editor_meta_box') {

                    var temp_html = temp.html();

                    new_text = '<?php echo $new_buttons;?>';
                    new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                    new_text = new_text.replace('{{moreid}}', temp_id + '_more_button');

                    temp_html = temp_html.replace('</button>', new_text);

                    temp.html(temp_html);
                }
            });

            // We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
            // Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
            setTimeout(wp_statistics_wait_for_postboxes, 100);
        });
    </script>
	<?php
}

?>