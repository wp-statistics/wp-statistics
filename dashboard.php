<?php
	add_action( 'wp_dashboard_setup', 'wp_statistics_dashboard_widget_load' );
	add_action( 'admin_footer', 'wp_statistics_dashboard_inline_javascript' ); 
	
	function wp_statistics_dashboard_widget_load() {
		GLOBAL $WP_Statistics;

		$WP_Statistics->load_user_options();
		
		// We need to fudge the display settings for first time users so not all of the widgets are displayed, we only want to do this on
		// the first time they visit the dashboard though so check to see if we've been here before.
		if( !$WP_Statistics->get_user_option('dashboard_set') ) {
			$WP_Statistics->update_user_option('dashboard_set', WP_STATISTICS_VERSION);
			
			$hidden_widgets = get_user_meta($WP_Statistics->user_id, 'metaboxhidden_dashboard', true);
			if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
			
			$default_hidden = array('wp-statistics-browsers-widget','wp-statistics-countries-widget','wp-statistics-hitsmap-widget',
									'wp-statistics-hits-widget','wp-statistics-pages-widget','wp-statistics-recent-widget','wp-statistics-referring-widget',
									'wp-statistics-search-widget','wp-statistics-summary-widget','wp-statistics-words-widget', 'wp-statistics-top-visitors-widget' );
			
			foreach( $default_hidden as $widget ) {
				if( !in_array( $widget, $hidden_widgets ) ) {
					$hidden_widgets[] = $widget;
				}
			}
			
			update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', $hidden_widgets );
		}
		else if( $WP_Statistics->get_user_option('dashboard_set') != WP_STATISTICS_VERSION ) {
			// We also have to fudge things when we add new widgets to the code base.
			if( version_compare( $WP_Statistics->get_user_option('dashboard_set'), '8.7', '<' ) ) {
			
				$WP_Statistics->update_user_option('dashboard_set', WP_STATISTICS_VERSION);
				
				$hidden_widgets = get_user_meta($WP_Statistics->user_id, 'metaboxhidden_dashboard', true);
				if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
				
				$default_hidden = array('wp-statistics-top-visitors-widget' );
				
				foreach( $default_hidden as $widget ) {
					if( !in_array( $widget, $hidden_widgets ) ) {
						$hidden_widgets[] = $widget;
					}
				}
				
				update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', $hidden_widgets );
			}
		}
		
		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) { return; }
		
		// If the admin has disabled the widgets, don't display them.
		if (!$WP_Statistics->get_option('disable_dashboard')) {
			wp_add_dashboard_widget( 'wp-statistics-quickstats-widget', __('Quick Stats', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'quickstats' ) );
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-browsers-widget', __('Top 10 Browsers', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'browsers' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-countries-widget', __('Top 10 Countries', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'visitors' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-hitsmap-widget', __('Today\'s Visitor Map', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'visitors' ) ); }
			if( $WP_Statistics->get_option('visits') ) { wp_add_dashboard_widget( 'wp-statistics-hits-widget', __('Hit Statistics', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'hits' ) ); }
			if( $WP_Statistics->get_option('pages') ) { wp_add_dashboard_widget( 'wp-statistics-pages-widget', __('Top 10 Pages', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'pages' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-recent-widget', __('Recent Visitors', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'recent' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-referring-widget', __('Top Referring Sites', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'referring' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-search-widget', __('Search Engine Referrals', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'search' ) ); }
			wp_add_dashboard_widget( 'wp-statistics-summary-widget', __('Summary', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'summary' ) );
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-words-widget', __('Latest Search Words', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'words' ) ); }
			if( $WP_Statistics->get_option('visitors') ) { wp_add_dashboard_widget( 'wp-statistics-top-visitors-widget', __('Top 10 Visitors Today', 'wp_statistics'), 'wp_statistics_generate_dashboard_postbox_contents', $control_callback = null, array( 'widget' => 'top_visitors' ) ); }
		}
	}

	function wp_statistics_load_widget_css_and_scripts() {
		// Load the css we use for the statistics pages.
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'assets/css/log.css', true, '1.1');
		wp_enqueue_style('jqplot-css', plugin_dir_url(__FILE__) . 'assets/css/jquery.jqplot.min.css', true, '1.0.8');
		
		// Don't forget the right to left support.
		if( is_rtl() )
			wp_enqueue_style('rtl-css', plugin_dir_url(__FILE__) . 'assets/css/rtl.css', true, '1.1');

		// Load the charts code.
		wp_enqueue_script('jqplot', plugin_dir_url(__FILE__) . 'assets/js/jquery.jqplot.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-daterenderer', plugin_dir_url(__FILE__) . 'assets/js/jqplot.dateAxisRenderer.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-tickrenderer', plugin_dir_url(__FILE__) . 'assets/js/jqplot.canvasAxisTickRenderer.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-axisrenderer', plugin_dir_url(__FILE__) . 'assets/js/jqplot.canvasAxisLabelRenderer.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-textrenderer', plugin_dir_url(__FILE__) . 'assets/js/jqplot.canvasTextRenderer.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-tooltip', plugin_dir_url(__FILE__) . 'assets/js/jqplot.highlighter.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-pierenderer', plugin_dir_url(__FILE__) . 'assets/js/jqplot.pieRenderer.min.js', true, '0.8.3');
		wp_enqueue_script('jqplot-enhancedlengend', plugin_dir_url(__FILE__) . 'assets/js/jqplot.enhancedLegendRenderer.min.js', true, '0.8.3');

		// Load our custom widgets handling javascript.
		wp_enqueue_script('wp_statistics_dashboard', plugin_dir_url(__FILE__) . 'assets/js/dashboard.js');
	}
	
	function wp_statistics_generate_dashboard_postbox_contents( $post, $args ) {
		$loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' .  plugins_url('wp-statistics/assets/images/')  . 'ajax-loading.gif" alt="' .  __( 'Loading...', 'wp_statistics' ) . '"></div>';
		$widget = $args['args']['widget'];
		$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';
		
		echo '<div id="' . $container_id . '">' . $loading_img .'</div>';
		wp_statistics_generate_widget_load_javascript( $widget, $container_id );
	}

	
	function wp_statistics_dashboard_inline_javascript() {
		wp_statistics_load_widget_css_and_scripts();
		
		$screen = get_current_screen();

		if( 'dashboard' != $screen->id ) {
			return;
		}
		
		$loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' .  plugins_url('wp-statistics/assets/images/')  . 'ajax-loading.gif" alt="' .  __( 'Reloading...', 'wp_statistics' ) . '"></div>';
		
		$new_buttons = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button><button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-migrate' ) . '</button>';
		$new_button = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '</button>';
		
		$admin_url = get_admin_url() . "/admin.php?page=";

		$page_urls = array();
		
		$page_urls['wp-statistics-browsers-widget_more_button'] 		= $admin_url . WP_STATISTICS_BROWSERS_PAGE;
		$page_urls['wp-statistics-countries-widget_more_button'] 		= $admin_url . WP_STATISTICS_COUNTRIES_PAGE;
		$page_urls['wp-statistics-exclusions-widget_more_button'] 		= $admin_url . WP_STATISTICS_EXCLUSIONS_PAGE; 
		$page_urls['wp-statistics-hits-widget_more_button'] 			= $admin_url . WP_STATISTICS_HITS_PAGE; 
		$page_urls['wp-statistics-online-widget_more_button'] 			= $admin_url . WP_STATISTICS_ONLINE_PAGE; 
		$page_urls['wp-statistics-pages-widget_more_button'] 			= $admin_url . WP_STATISTICS_PAGES_PAGE; 
		$page_urls['wp-statistics-referring-widget_more_button'] 		= $admin_url . WP_STATISTICS_REFERRERS_PAGE; 
		$page_urls['wp-statistics-search-widget_more_button'] 			= $admin_url . WP_STATISTICS_SEARCHES_PAGE; 
		$page_urls['wp-statistics-words-widget_more_button'] 			= $admin_url . WP_STATISTICS_WORDS_PAGE; 
		$page_urls['wp-statistics-top_visitors-widget_more_button'] 	= $admin_url . WP_STATISTICS_TOP_VISITORS_PAGE; 
		$page_urls['wp-statistics-visitors-widget_more_button'] 		= $admin_url . WP_STATISTICS_VISITORS_PAGE; 

?>
<script type="text/javascript">
	var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>; 
	var wp_statistics_loading_image = '<?php echo $loading_img; ?>'

	function wp_statistics_wait_for_postboxes() {
		
		if( ! jQuery('#show-settings-link').is( ':visible') ) {
			setTimeout( wp_statistics_wait_for_postboxes, 500 );
		}
		
		jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget );
		jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more );

		jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget );
	}
	
	jQuery(document).ready(function(){

		// Add the "more" and "refresh" buttons.
		jQuery('.postbox').each( function () {
			var temp = jQuery( this );
			var temp_id = temp.attr( 'id' );
			
			if( temp_id.substr( 0, 14 ) != 'wp-statistics-' ) {
				return;
			}

			var temp_html = temp.html();
			
			if( temp_id == 'wp-statistics-summary-widget' || temp_id == 'wp-statistics-quickstats-widget' ) {
				new_text = '<?php echo $new_button;?>';
				new_text = new_text.replace( '{{refreshid}}', temp_id + '_refresh_button' );
				
				temp_html = temp_html.replace( '</button>', new_text );
			} else {
				new_text = '<?php echo $new_buttons;?>';
				new_text = new_text.replace( '{{refreshid}}', temp_id + '_refresh_button' );
				new_text = new_text.replace( '{{moreid}}', temp_id + '_more_button' );
				
				temp_html = temp_html.replace( '</button>', new_text );
			}
			
			temp.html( temp_html );
		});

		// We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
		// Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
		setTimeout( wp_statistics_wait_for_postboxes, 100 );
	});
</script>
<?php
	}
	
	function wp_statistics_is_wp_widget_visible( $widget, $type = 'dashboard' ) {
		GLOBAL $WP_Statistics;
		
		$hidden_widgets = get_user_meta($WP_Statistics->user_id,'metaboxhidden_' . $type, true);
		if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
		
		if( in_array( $widget, $hidden_widgets ) ) {
			return __('Please reload the dashboard to display the content of this widget.', 'wp_statistics');
		}
		
		return true;
	}
	

	function wp_statistics_browsers_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-browsers-widget', 'dashboard' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the summary widget, we're just going to use the content for the the users online and visit/visitor totals.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/browsers.php");

		wp_statistics_generate_browsers_postbox_content();
	}

	function wp_statistics_countries_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-countries-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		$ISOCountryCode = $WP_Statistics->get_country_codes();

		// Include the country widget.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/countries.php");

		wp_statistics_generate_countries_postbox_content($ISOCountryCode);
	}
	
	function wp_statistics_hitsmap_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-hitsmap-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		$ISOCountryCode = $WP_Statistics->get_country_codes();

		// Include the map widget.
		if( $WP_Statistics->get_option( 'map_type' ) == 'jqvmap' ) {
			wp_enqueue_style('jqvmap-css', plugin_dir_url(__FILE__) . 'assets/jqvmap/jqvmap.css', true, '1.1');
			wp_enqueue_script('jquery-vmap', plugin_dir_url(__FILE__) . 'assets/jqvmap/jquery.vmap.min.js', true, '1.1');
			wp_enqueue_script('jquery-vmap-world', plugin_dir_url(__FILE__) . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.1');
			
			include_once( dirname( __FILE__ ) . "/includes/log/widgets/jqv.map.php");
		}
		else {
			include_once( dirname( __FILE__ ) . "/includes/log/widgets/google.map.php");
		}

		wp_statistics_generate_map_postbox_content($ISOCountryCode);
	}

	function wp_statistics_hits_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-hits-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/hits.php");

		wp_statistics_generate_hits_postbox_content("300px", 10);
	}

	function wp_statistics_pages_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-pages-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/pages.php");

		list( $total, $uris ) = wp_statistics_get_top_pages();

		wp_statistics_generate_pages_postbox_content($total, $uris);
	}
	
	function wp_statistics_recent_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-recent-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/recent.php");

		$ISOCountryCode = $WP_Statistics->get_country_codes();

		wp_statistics_generate_recent_postbox_content($ISOCountryCode);
	}
	
	function wp_statistics_referring_widget() {
		GLOBAL $wpdb, $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-referring-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/referring.php");

		wp_statistics_generate_referring_postbox_content();
	}
	
	function wp_statistics_search_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-search-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/search.php");

		wp_statistics_generate_search_postbox_content(wp_statistics_searchengine_list(), '300px', 10);
	}
	
	function wp_statistics_summary_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-summary-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/summary.php");

		wp_statistics_generate_summary_postbox_content(wp_statistics_searchengine_list());
	}
	
	function wp_statistics_words_widget() {
		GLOBAL $wpdb, $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-words-widget', 'dashboard'  ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		$ISOCountryCode = $WP_Statistics->get_country_codes();

		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/words.php");

		wp_statistics_generate_words_postbox_content($ISOCountryCode);
	}
	
	function wp_statistics_top_visitors_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_wp_widget_visible( 'wp-statistics-top-visitors-widget', 'dashboard' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the summary widget, we're just going to use the content for the the users online and visit/visitor totals.
		$ISOCountryCode = $WP_Statistics->get_country_codes();
		
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/top.visitors.php");

		wp_statistics_generate_top_visitors_postbox_content($ISOCountryCode, 'today', 10, true);
	}
	
?>