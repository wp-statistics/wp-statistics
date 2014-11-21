<?php
	add_action( 'wp_dashboard_setup', 'wp_statistics_dashboard_widget_load' );

	function wp_statistics_dashboard_widget_load() {
		GLOBAL $WP_Statistics;

		$WP_Statistics->load_user_options();
		
		// We need to fudge the display settings for first time users so not all of the widgets are disaplyed, we only want to do this on
		// the first time they visit the dashboard though so check to see if we've been here before.
		if( !$WP_Statistics->get_user_option('dashboard_set') ) {
			$WP_Statistics->update_user_option('dashboard_set', WP_STATISTICS_VERSION);
			
			$hidden_widgets = get_user_meta($WP_Statistics->user_id, 'metaboxhidden_dashboard', true);
			if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
			
			$default_hidden = array('wp-statistics-browsers-widget','wp-statistics-countries-widget','wp-statistics-hitsmap-widget',
									'wp-statistics-hits-widget','wp-statistics-pages-widget','wp-statistics-recent-widget','wp-statistics-referring-widget',
									'wp-statistics-search-widget','wp-statistics-summary-widget','wp-statistics-words-widget' );
			
			foreach( $default_hidden as $widget ) {
				if( !in_array( $widget, $hidden_widgets ) ) {
					$hidden_widgets[] = $widget;
				}
			}
			
			update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', $hidden_widgets );
		}
		
		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) { return; }
		
		// If the admin has disabled the widgets, don't display them.
		if (!$WP_Statistics->get_option('disable_dashboard')) {
			wp_add_dashboard_widget( 'wp-statistics-quickstats-widget', __('Quick Stats', 'wp_statistics'), 'wp_statistics_quickstats_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-browsers-widget', __('Top 10 Browsers', 'wp_statistics'), 'wp_statistics_browsers_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-countries-widget', __('Top 10 Countries', 'wp_statistics'), 'wp_statistics_countries_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-hitsmap-widget', __('Today\'s Visitor Map', 'wp_statistics'), 'wp_statistics_hitsmap_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-hits-widget', __('Hit Statistics', 'wp_statistics'), 'wp_statistics_hits_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-pages-widget', __('Top 10 Pages', 'wp_statistics'), 'wp_statistics_pages_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-recent-widget', __('Recent Visitors', 'wp_statistics'), 'wp_statistics_recent_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-referring-widget', __('Top Referring Sites', 'wp_statistics'), 'wp_statistics_referring_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-search-widget', __('Search Engine Referrals', 'wp_statistics'), 'wp_statistics_search_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-summary-widget', __('Summary', 'wp_statistics'), 'wp_statistics_summary_widget', $control_callback = null );
			wp_add_dashboard_widget( 'wp-statistics-words-widget', __('Latest Search Words', 'wp_statistics'), 'wp_statistics_words_widget', $control_callback = null );
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
	}
	
	function wp_statistics_is_dashboard_widget_visible( $widget ) {
		GLOBAL $WP_Statistics;
		
		$hidden_widgets = get_user_meta($WP_Statistics->user_id,'metaboxhidden_dashboard', true);
		if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
		
		if( in_array( $widget, $hidden_widgets ) ) {
			return __('Please reload the dashboard to display the content of this widget.', 'wp_statistics');
		}
		
		return true;
	}
	
	function wp_statistics_quickstats_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-quickstats-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the summary widget, we're just going to use the content for the the users online and visit/visitor totals
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/summary.php");

		wp_statistics_generate_summary_postbox_content(null, false, false);
		
		// We can only have one hit's chart per page, so if the hits widget is visible, don't display it here.
		if( wp_statistics_is_dashboard_widget_visible( 'wp-statistics-hits-widget' ) !== true ) {
		
?>		
		<br>
		<hr width="80%"/>
		<br>
<?php
		
			// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
			include_once( dirname( __FILE__ ) . "/includes/log/widgets/hits.php");

			wp_statistics_generate_hits_postbox_content("300px", 10);
		}
?>

		<br>
		<hr width="80%"/>
		<br>

		<div style="text-align: center;">
		<a class="button-primary" href="admin.php?page=wp-statistics/wp-statistics.php"><?php _e('WP Statistics Overview', 'wp_statistics');?></a>
		</div>
		
		<br>
<?php		
		
	}

	function wp_statistics_browsers_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-browsers-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the summary widget, we're just going to use the content for the the users online and visit/visitor totals.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/browsers.php");

		wp_statistics_generate_browsers_postbox_content();
	}

	function wp_statistics_countries_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-countries-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		include_once( dirname( __FILE__ ) . "/includes/functions/country-codes.php");

		// Include the country widget.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/countries.php");

		wp_statistics_generate_countries_postbox_content($ISOCountryCode);
	}
	
	function wp_statistics_hitsmap_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-hitsmap-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		include_once( dirname( __FILE__ ) . "/includes/functions/country-codes.php");

		// Include the map widget.
		if( $WP_Statistics->get_option( 'map_type' ) == 'jqvmap' ) {
			wp_enqueue_style('jqvmap-css', plugin_dir_url(__FILE__) . 'assets/jqvmap/jqvmap.css', true, '1.1');
			wp_enqueue_script('jquery-vmap', plugin_dir_url(__FILE__) . 'assets/jqvmap/jquery.vmap.min.js', true, '1.1');
			wp_enqueue_script('jquery-vmap-world', plugin_dir_url(__FILE__) . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.1');
			
			include_once( dirname( __FILE__ ) . "/includes/log/widgets/jqv.map.php");
		}
		else {
			include_once( dirname( __FILE__ ) . "/includes/logwidgets/google.map.php");
		}

		wp_statistics_generate_map_postbox_content($ISOCountryCode);
	}

	function wp_statistics_hits_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-hits-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/hits.php");

		wp_statistics_generate_hits_postbox_content("300px", 10);
	}

	function wp_statistics_pages_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-pages-widget' ) ) !== true ) { echo $is_visible; return; }
		
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
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-recent-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/recent.php");

		include_once( dirname( __FILE__ ) . "/includes/functions/country-codes.php");

		wp_statistics_generate_recent_postbox_content($ISOCountryCode);
	}
	
	function wp_statistics_referring_widget() {
		GLOBAL $wpdb, $table_prefix, $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-referring-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/referring.php");

		$result = $wpdb->get_results("SELECT `referred` FROM `{$table_prefix}statistics_visitor` WHERE referred <> ''");
		
		if( sizeof( $result ) > 0 ) {
			wp_statistics_generate_referring_postbox_content($result);
		}
	}
	
	function wp_statistics_search_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-search-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/search.php");

		wp_statistics_generate_search_postbox_content(wp_statistics_searchengine_list(), '300px', 10);
	}
	
	function wp_statistics_summary_widget() {
		GLOBAL $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-summary-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/summary.php");

		wp_statistics_generate_summary_postbox_content(wp_statistics_searchengine_list());
	}
	
	function wp_statistics_words_widget() {
		GLOBAL $wpdb, $table_prefix, $WP_Statistics;

		// If the widget isn't visible, don't output the stats as they take too much memory and CPU to compute for no reason.
		if( ( $is_visible = wp_statistics_is_dashboard_widget_visible( 'wp-statistics-words-widget' ) ) !== true ) { echo $is_visible; return; }
		
		// Load the css we use for the statistics pages.
		wp_statistics_load_widget_css_and_scripts();
		
		include_once( dirname( __FILE__ ) . "/includes/functions/country-codes.php");

		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/words.php");

		wp_statistics_generate_words_postbox_content($ISOCountryCode);
	}
	
	
?>