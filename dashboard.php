<?php
	function wp_statistics_dashboard_widget_load() {
		GLOBAL $WP_Statistics;

		$WP_Statistics->load_user_options();
		
		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) { return; }
		
		if (!$WP_Statistics->get_option('disable_dashboard') && !$WP_Statistics->get_user_option('disable_user_dashboard')) {
			wp_add_dashboard_widget( 'wp-statistics-dashboard-widget', 'Statistics', 'wp_statistics_dashboard_widget', $control_callback = null );
		}
	}

	function wp_statistics_dashboard_widget() {
		GLOBAL $WP_Statistics;

		$hidden_widgets = get_user_option('metaboxhidden_dashboard');
		if( !is_array( $hidden_widgets ) ) { $hidden_widgets = array(); }
		
		if( in_array( 'wp-statistics-dashboard-widget', $hidden_widgets ) ) {
			_e('Please reload the dashboard to display the content of this widget.', 'wp_statistics');
			return;
		}
		
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
		
		// Include the summary widget, we're just going to use the content for the the users online and visit/visitor totals
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/summary.php");

		wp_statistics_generate_summary_postbox_content(null, false, false)
?>		
		<br>
		<hr width="80%"/>
		<br>
<?php
		
		// Include the hits chart widget, we're going to display the last 10 days only as the WordPress columns are kind of small to do much else.
		include_once( dirname( __FILE__ ) . "/includes/log/widgets/hits.php");

		wp_statistics_generate_hits_postbox_contents("300px", 10);
		
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

	add_action( 'wp_dashboard_setup', 'wp_statistics_dashboard_widget_load' );
?>