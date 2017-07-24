<?php
$loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'ajax-loading.gif" alt="' . __( 'Reloading...', 'wp_statistics' ) . '"></div>';

$nag_html = '';
if ( ! $WP_Statistics->get_option( 'disable_donation_nag', false ) ) {
	$nag_html = '<div id="wps_nag" class="update-nag" style="width: 90%;"><div id="donate-text"><p>' . __( 'Have you thought about donating to WP Statistics?', 'wp_statistics' ) . ' <a href="http://wp-statistics.com/donate/" target="_blank">' . __( 'Donate Now!', 'wp_statistics' ) . '</a></p></div><div id="donate-button"><a class="button-primary" id="wps_close_nag">' . __( 'Close', 'wp_statistics' ) . '</a></div></div>';
}

// WP Statistics 10.0 had a bug which could corrupt  the metabox display if the user re-ordered the widgets.  Check to see if the meta data is corrupt and if so delete it.
$widget_order = get_user_meta( $WP_Statistics->user_id, 'meta-box-order_toplevel_page_wps_overview_page', true );

if ( is_array( $widget_order ) && count( $widget_order ) > 2 ) {
	delete_user_meta( $WP_Statistics->user_id, 'meta-box-order_toplevel_page_wps_overview_page' );
}

// Add the about box here as metaboxes added on the actual page load cannot be closed.
add_meta_box( 'wps_about_postbox', sprintf( __( 'About WP Statistics Version %s', 'wp_statistics' ), WP_STATISTICS_VERSION ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'about' ) );

function wp_statistics_generate_overview_postbox_contents( $post, $args ) {
	$loading_img  = '<div style="width: 100%; text-align: center;"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'ajax-loading.gif" alt="' . __( 'Loading...', 'wp_statistics' ) . '"></div>';
	$widget       = $args['args']['widget'];
	$container_id = str_replace( '.', '_', $widget . '_postbox' );

	echo '<div id="' . $container_id . '">' . $loading_img . '</div>';
	wp_statistics_generate_widget_load_javascript( $widget, $container_id );
}

?>
<div class="wrap">
	<?php echo $nag_html; ?>
    <h2><?php echo get_admin_page_title(); ?></h2>
	<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
	<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>

    <div class="metabox-holder" id="overview-widgets">
        <div class="postbox-container" id="wps-postbox-container-1">
			<?php do_meta_boxes( $WP_Statistics->menu_slugs['overview'], 'side', '' ); ?>
        </div>

        <div class="postbox-container" id="wps-postbox-container-2">
			<?php do_meta_boxes( $WP_Statistics->menu_slugs['overview'], 'normal', '' ); ?>
        </div>
    </div>
</div>
<?php
$new_buttons = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button><button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-migrate' ) . '</button>';
$new_button  = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button>';

$admin_url = get_admin_url() . "admin.php?page=";

$page_urls = array();

$page_urls['wps_browsers_more_button']     = $admin_url . WP_STATISTICS_BROWSERS_PAGE;
$page_urls['wps_countries_more_button']    = $admin_url . WP_STATISTICS_COUNTRIES_PAGE;
$page_urls['wps_exclusions_more_button']   = $admin_url . WP_STATISTICS_EXCLUSIONS_PAGE;
$page_urls['wps_hits_more_button']         = $admin_url . WP_STATISTICS_HITS_PAGE;
$page_urls['wps_online_more_button']       = $admin_url . WP_STATISTICS_ONLINE_PAGE;
$page_urls['wps_pages_more_button']        = $admin_url . WP_STATISTICS_PAGES_PAGE;
$page_urls['wps_referring_more_button']    = $admin_url . WP_STATISTICS_REFERRERS_PAGE;
$page_urls['wps_search_more_button']       = $admin_url . WP_STATISTICS_SEARCHES_PAGE;
$page_urls['wps_words_more_button']        = $admin_url . WP_STATISTICS_WORDS_PAGE;
$page_urls['wps_top_visitors_more_button'] = $admin_url . WP_STATISTICS_TOP_VISITORS_PAGE;
$page_urls['wps_recent_more_button']       = $admin_url . WP_STATISTICS_VISITORS_PAGE;

?>
<script type="text/javascript">
    var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
    var wp_statistics_loading_image = '<?php echo $loading_img; ?>'

    jQuery(document).ready(function () {

        // Add the "more" and "refresh" buttons.
        jQuery('.postbox').each(function () {
            var temp = jQuery(this);
            var temp_id = temp.attr('id');
            var temp_html = temp.html();
            if (temp_id == 'wps_summary_postbox' || temp_id == 'wps_map_postbox' || temp_id == 'wps_about_postbox') {
                if (temp_id != 'wps_about_postbox') {
                    new_text = '<?php echo $new_button;?>';
                    new_text = new_text.replace('{{refreshid}}', temp_id.replace('_postbox', '_refresh_button'));

                    temp_html = temp_html.replace('</button>', new_text);
                }
            } else {
                new_text = '<?php echo $new_buttons;?>';
                new_text = new_text.replace('{{refreshid}}', temp_id.replace('_postbox', '_refresh_button'));
                new_text = new_text.replace('{{moreid}}', temp_id.replace('_postbox', '_more_button'));

                temp_html = temp_html.replace('</button>', new_text);
            }

            temp.html(temp_html);
        });

        // close postboxes that should be closed
        jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');

        // postboxes setup
        postboxes.add_postbox_toggles('<?php echo $WP_Statistics->menu_slugs['overview']; ?>');

        jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
        jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more);

        jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);

        jQuery('#wps_close_nag').click(function () {
            var data = {
                'action': 'wp_statistics_close_donation_nag',
                'query': '',
            };

            jQuery.ajax({
                url: ajaxurl,
                type: 'get',
                data: data,
                datatype: 'json',
            });

            jQuery('#wps_nag').hide();
        });

    });
</script>
