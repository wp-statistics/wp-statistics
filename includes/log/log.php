<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php 
	
	include_once( dirname( __FILE__ ) . "/../functions/country-codes.php");
	
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
	include_once( dirname( __FILE__ ) . "/widgets/words.php");

	$search_engines = wp_statistics_searchengine_list();
	
	$search_result['All'] = wp_statistics_searchengine('all','total');
	
	foreach( $search_engines as $key => $se ) {
		$search_result[$key] = wp_statistics_searchengine($key,'total');
	}

?>
<div class="wrap">
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
			
			</div>
		</div>
	</div>
</div>
<?php
	function wp_statistics_display_column_a($slot, $ISOCountryCode, $search_engines) {
		GLOBAL $WP_Statistics;
			
		$display = $WP_Statistics->get_user_option('overview_display');
			
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
			default:
			
		}
	}
?>