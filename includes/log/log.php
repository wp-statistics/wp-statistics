<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);

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
	
	$ISOCountryCode = $WP_Statistics->get_country_codes();
	
	// Load the widgets.
	include_once( dirname( __FILE__ ) . "/widgets/about.php");
	include_once( dirname( __FILE__ ) . "/widgets/browsers.php");

	if( $WP_Statistics->get_option( 'map_type' ) == 'jqvmap' ) {
		include_once( dirname( __FILE__ ) . "/widgets/jqv.map.php");
	}
	else {
		include_once( dirname( __FILE__ ) . "/widgets/google.map.php");
	}
	
	include_once( dirname( __FILE__ ) . "/widgets/countries.php");
	include_once( dirname( __FILE__ ) . "/widgets/hits.php");
	include_once( dirname( __FILE__ ) . "/widgets/pages.php");
	include_once( dirname( __FILE__ ) . "/widgets/recent.php");
	include_once( dirname( __FILE__ ) . "/widgets/referring.php");
	include_once( dirname( __FILE__ ) . "/widgets/search.php");
	include_once( dirname( __FILE__ ) . "/widgets/summary.php");
	include_once( dirname( __FILE__ ) . "/widgets/top.visitors.php" );
	include_once( dirname( __FILE__ ) . "/widgets/words.php");

	$search_engines = wp_statistics_searchengine_list();
	
	$search_result['All'] = wp_statistics_searchengine('all','total');
	
	foreach( $search_engines as $key => $se ) {
		$search_result[$key] = wp_statistics_searchengine($key,'total');
	}

	$nag_html = '';
	if( ! $WP_Statistics->get_option( 'disable_donation_nag', false ) ) {
		$nag_html = '<div id="wps_nag" class="update-nag" style="width: 90%;"><div id="donate-text"><p>' . __('Have you thought about donating to WP Statistics?', 'wp_statistics') . ' <a href="http://wp-statistics.com/donate/" target="_blank">'.__('Donate Now!', 'wp_statistics').'</a></p></div><div id="donate-button"><a class="button-primary" id="wps_close_nag">' . __('Close', 'wp_statistics') . '</a></div></div>';
	}
?>
<div class="wrap">
	<?php echo $nag_html; ?>
	<?php screen_icon('options-general'); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<div class="postbox-container" id="right-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">

			<?php $ret  = wp_statistics_display_column_a(1, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(2, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(3, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(4, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(5, $ISOCountryCode, $search_engines); ?>
			
			<?php if( $ret == 0 ) { wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines); } ?>

			</div>
		</div>
	</div>
	
	<div class="postbox-container" id="left-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">

			<?php wp_statistics_display_column_b(1, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(2, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(3, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(4, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(5, $ISOCountryCode, $search_engines); ?>
			
			<?php wp_statistics_display_column_b(6, $ISOCountryCode, $search_engines); ?>
			
			<?php wp_statistics_display_column_b(7, $ISOCountryCode, $search_engines); ?>
			
			</div>
		</div>
	</div>
</div>
<?php
	$WP_Statistics->update_option( 'last_overview_memory', memory_get_peak_usage(true) );

	function wp_statistics_display_column_a($slot, $ISOCountryCode, $search_engines) {
		GLOBAL $WP_Statistics;
			
		$display = $WP_Statistics->get_user_option('overview_display');
			
		if( !is_array( $display['A'] ) ) { $display['A'][$slot] = $slot; }
		if( !array_key_exists( $slot, $display['A'] ) ) { $display['A'][$slot] = $slot; }
		if( $display['A'][$slot] == '' ) { $display['A'][$slot] = $slot; }
		
		$ret = 0;
		
		switch( $display['A'][$slot] ) {
			case 1:
			case 'summary':
				wp_statistics_generate_summary_postbox($ISOCountryCode, $search_engines);
				
				break;
			case 2:
			case 'browsers':
				wp_statistics_generate_browsers_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 3:
			case 'referring':
				wp_statistics_generate_referring_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 4:
			case 'countries':
				wp_statistics_generate_countries_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 5:
			case 'about':
				wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines);
		
				$ret = 1;
				
				break;
			default:
		}

		return $ret;
	}

	function wp_statistics_display_column_b($slot, $ISOCountryCode, $search_engines) {
		GLOBAL $WP_Statistics;
			
		$display = $WP_Statistics->get_user_option('overview_display');
		
		if( !is_array( $display['B'] ) ) { $display['B'][$slot] = $slot; }
		if( !array_key_exists( $slot, $display['B'] ) ) { $display['B'][$slot] = $slot; }
		if( $display['B'][$slot] == '' ) { $display['B'][$slot] = $slot; }
		
		switch( $display['B'][$slot] ) {
			case 1:
			case 'map':
				wp_statistics_generate_map_postbox($ISOCountryCode, $search_engines);
				
				break;
			case 2:
			case 'hits':
				wp_statistics_generate_hits_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 3:
			case 'search':
				wp_statistics_generate_search_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 4:
			case 'words':
				wp_statistics_generate_words_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 5:
			case 'pages':
				wp_statistics_generate_pages_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 6:
			case 'recent':
				wp_statistics_generate_recent_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 7:
			case 'top.visitors':
				wp_statistics_generate_top_visitors_postbox($ISOCountryCode, $search_engines);
			
				break;
			default:
			
		}
	}
?>