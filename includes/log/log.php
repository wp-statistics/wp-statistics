<script type="text/javascript">
	jQuery(document).ready(function(){

		// close postboxes that should be closed
		jQuery('.if-js-closed').removeClass('if-js-closed').addClass('closed');
		
		// postboxes setup
		postboxes.add_postbox_toggles('<?php echo $WP_Statistics->menu_slugs['overview']; ?>');

		jQuery('#wps_close_nag').click( function(){
			var data = {
				'action': 'wp_statistics_close_donation_nag',
				'query': '',
			};

			jQuery.ajax({ url: ajaxurl,
					 type: 'get',
					 data: data,
					 datatype: 'json',
			});
			
			jQuery('#wps_nag').hide();
		});

	});
</script>
<?php 
	$nag_html = '';
	if( ! $WP_Statistics->get_option( 'disable_donation_nag', false ) ) {
		$nag_html = '<div id="wps_nag" class="update-nag" style="width: 90%;"><div id="donate-text"><p>' . __('Have you thought about donating to WP Statistics?', 'wp_statistics') . ' <a href="http://wp-statistics.com/donate/" target="_blank">'.__('Donate Now!', 'wp_statistics').'</a></p></div><div id="donate-button"><a class="button-primary" id="wps_close_nag">' . __('Close', 'wp_statistics') . '</a></div></div>';
	}

	// Add the about box here as metaboxes added on the actual page load cannot be closed.
	add_meta_box( 'wps_about_postbox', sprintf(__('About WP Statistics Version %s', 'wp_statistics'), WP_STATISTICS_VERSION), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' =>'about' ) );
	
	function wp_statistics_generate_overview_postbox_contents( $post, $args ) {
		$widget = $args['args']['widget'];
		$container_id = str_replace( '.', '_', $widget . '_postbox' );
		
		echo '<div id="' . $container_id . '"></div>';
		wp_statistics_generate_widget_load_javascript( $widget, $container_id );
	}
?>
<div class="wrap">
	<?php echo $nag_html; ?>
	<?php screen_icon('options-general'); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<?php wp_nonce_field('closedpostboxes', 'closedpostboxesnonce', false ); ?>
	<?php wp_nonce_field('meta-box-order', 'meta-box-order-nonce', false ); ?>
	<div class="metabox-holder meta-box-sortables ui-sortable" id="right-log">

			<?php do_meta_boxes( $WP_Statistics->menu_slugs['overview'], 'side', '' ); ?>

	</div>
	
	<div class="metabox-holder meta-box-sortables ui-sortable" id="left-log">

			<?php do_meta_boxes( $WP_Statistics->menu_slugs['overview'], 'normal', '' ); ?>
			
	</div>
</div>
<?php
	$WP_Statistics->update_option( 'last_overview_memory', memory_get_peak_usage(true) );

	function wp_statistics_generate_widget_load_javascript( $widget, $container_id = null ) {
		if( null == $container_id ) {
			$container_id = str_replace( '.', '_', $widget . '_postbox' );
		}
?>
<script type="text/javascript">
	jQuery(document).ready(function() {
		var data = {
			'action': 'wp_statistics_get_widget_contents',
			'widget': '<?php echo $widget; ?>',
		};
		
		jQuery.ajax({ url: ajaxurl,
				 type: 'post',
				 data: data,
				 datatype: 'json',
		})
			.always(function(result){
				jQuery("#<?php echo $container_id;?>").html("").html(result);
		});
	});
</script>
<?php
	}
?>