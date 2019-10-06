<?php
$nag_html = '';

if ( ! $WP_Statistics->get_option( 'geoip' ) ) {
	$nag_html .= '<div class="notice notice-warning"><p>' . sprintf( __( 'GeoIP collection is not enabled. Please go to <a href="%s">setting page</a> to enable GeoIP for getting more information and location (country) from the visitor.', 'wp-statistics' ), WP_Statistics_Admin_Pages::admin_url( 'settings', array( 'tab' => 'externals-settings' ) ) ) . '</p></div>';
}

if ( ! $WP_Statistics->get_option( 'disable_donation_nag', false ) ) {
	$nag_html .= '<div class="notice notice-success is-dismissible wps-donate-notice"><p>' . __( 'Have you thought about donating to WP Statistics?', 'wp-statistics' ) . ' <a href="http://wp-statistics.com/donate/" target="_blank">' . __( 'Donate Now!', 'wp-statistics' ) . '</a></p></div>';
}

// WP Statistics 10.0 had a bug which could corrupt  the metabox display if the user re-ordered the widgets.  Check to see if the meta data is corrupt and if so delete it.
$widget_order = get_user_meta( $WP_Statistics->user_id, 'meta-box-order_toplevel_page_wps_overview_page', true );

if ( is_array( $widget_order ) && count( $widget_order ) > 2 ) {
	delete_user_meta( $WP_Statistics->user_id, 'meta-box-order_toplevel_page_wps_overview_page' );
}

// Add the about box here as metaboxes added on the actual page load cannot be closed.
add_meta_box( 'wps_about_postbox', sprintf( __( 'WP Statistics - Version %s', 'wp-statistics' ), WP_Statistics::$reg['version'] ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'about' ) );

function wp_statistics_generate_overview_postbox_contents( $post, $args ) {
	$widget       = $args['args']['widget'];
	$container_id = str_replace( '.', '_', $widget . '_postbox' );

	echo '<div id="' . $container_id . '">' . WP_Statistics_Admin_Pages::loading_meta_box() . '</div>';
	wp_statistics_generate_widget_load_javascript( $widget, $container_id );
}

?>
<div class="wrap wps-wrap">
	<?php echo $nag_html; ?>
	<?php WP_Statistics_Admin_Pages::show_page_title(); ?>
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

//Prepare List Of Page Url
$page_urls   = array();
$widget_list = array( 'browsers', 'countries', 'hits', 'pages', 'referring', 'search', 'words', 'top-visitors', 'recent' );
$all_widget  = WP_Statistics_Dashboard::widget_list();
foreach ( $widget_list as $widget ) {
	if ( array_key_exists( $widget, $all_widget ) ) {
		$page_urls[ 'wps_' . str_replace( "-", "_", $widget ) . '_more_button' ] = WP_Statistics_Admin_Pages::admin_url( $all_widget[ $widget ]['page_url'] );
	}
}

//Add Extra Pages For Overview Page
foreach ( array( 'exclusions' => 'exclusions', 'users_online' => 'online' ) as $p_key => $p_link ) {
	$page_urls[ 'wps_' . $p_key . '_more_button' ] = WP_Statistics_Admin_Pages::admin_url( $p_link );
}
?>
<script type="text/javascript">
    var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
    var wp_statistics_loading_image = '<?php echo WP_Statistics_Admin_Pages::loading_meta_box(); ?>'

    jQuery(document).ready(function () {

        // Add the "more" and "refresh" buttons.
        jQuery('.postbox').each(function () {
            var temp = jQuery(this);
            var temp_id = temp.attr('id');
            var temp_html = temp.html();
            if (temp_id == 'wps_summary_postbox' || temp_id == 'wps_map_postbox' || temp_id == 'wps_about_postbox') {
                if (temp_id != 'wps_about_postbox') {
                    new_text = '<?php echo WP_Statistics_Admin_Pages::meta_box_button( 'refresh' );?>';
                    new_text = new_text.replace('{{refreshid}}', temp_id.replace('_postbox', '_refresh_button'));

                    temp_html = temp_html.replace('</button>', new_text);
                }
            } else {
                new_text = '<?php echo WP_Statistics_Admin_Pages::meta_box_button();?>';
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
        jQuery('.wps-donate-notice').on('click', '.notice-dismiss', function () {
            var data = {
                'action': 'wp_statistics_close_notice',
                'notice': 'donate',
            };

            jQuery.ajax({
                url: ajaxurl,
                type: 'get',
                data: data,
                datatype: 'json',
            });
        });

        // Added New Ads
		<?php
		$overview_ads = get_option( 'wp_statistics_overview_page_ads', false );
		if($overview_ads != false and is_array( $overview_ads ) and $overview_ads['ads']['ID'] != $overview_ads['view'] and $overview_ads['ads']['status'] == "yes" and apply_filters( 'wp_statistics_ads_overview_page_show', true ) === true) {
		?>
        jQuery(`<div id="wps_overview_ads_postbox" class="postbox"><div class="inside"><div class="close-overview-ads"><span class="dashicons dashicons-dismiss"></span></div><a href="<?php echo( isset( $overview_ads['ads']['link'] ) ? $overview_ads['ads']['link'] : '' ); ?>" title="<?php echo( isset( $overview_ads['ads']['title'] ) ? $overview_ads['ads']['title'] : '' ); ?>" <?php echo( $overview_ads['ads']['_target'] == "yes" ? ' target="_blank"' : '' ); ?>><img style="display: block;" src="<?php echo( isset( $overview_ads['ads']['image'] ) ? $overview_ads['ads']['image'] : '' ); ?>" alt="<?php echo( isset( $overview_ads['ads']['title'] ) ? $overview_ads['ads']['title'] : '' ); ?>"></a></div></div>`).insertAfter("#wps-postbox-container-2 #normal-sortables div.postbox:first");
        jQuery(document).on('click', '.close-overview-ads', function () {
            jQuery("#wps_overview_ads_postbox").fadeOut("normal");
            jQuery.ajax({
                url: ajaxurl,
                type: 'get',
                data: {
                    'action': 'wp_statistics_close_overview_ads',
                    'ads_id': '<?php echo $overview_ads['ads']['ID']; ?>',
                    'wps_nonce': '<?php echo wp_create_nonce( 'overview_ads_nonce' ); ?>'
                },
                datatype: 'json'
            });
        });
		<?php
		}
		?>

    });
</script>