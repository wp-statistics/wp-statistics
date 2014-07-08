<?php
	function wp_statistics_widget() {
	
		wp_register_sidebar_widget('wp-statistics', __('Statistics', 'wp_statistics'), 'wp_statistics_show_widget', array(
			'description'	=>	__('Show site stats in sidebar', 'wp_statistics')));
		wp_register_widget_control('wp-statistics', __('Statistics', 'wp_statistics'), 'wp_statistics_control_widget');
	}
	add_action("plugins_loaded", "wp_statistics_widget");

	function wp_statistics_show_widget($args) {
		GLOBAL $WP_Statistics;
		
		extract($args);
		
		$widget_options = $WP_Statistics->get_option('widget');
		
		echo $before_widget;
		echo $before_title . $widget_options['name_widget'] . $after_title;
		
			echo "<ul>";
			if($widget_options['useronline_widget']) {
				echo "<li>";
					echo __('User Online', 'wp_statistics'). ": ";
					echo wp_statistics_useronline();
				echo "</li>";
			}
			
			if($widget_options['tvisit_widget']) {
				echo "<li>";
					echo __('Today Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('today');
				echo "</li>";
			}
			
			if($widget_options['tvisitor_widget']) {
				echo "<li>";
					echo __('Today Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('today', null, true);
				echo "</li>";
			}

			if($widget_options['yvisit_widget']) {
				echo "<li>";
					echo __('Yesterday Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('yesterday');
				echo "</li>";
			}
			
			if($widget_options['yvisitor_widget']) {
				echo "<li>";
					echo __('Yesterday Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('yesterday', null, true);
				echo "</li>";
			}

			if($widget_options['wvisit_widget']) {
				echo "<li>";
					echo __('Week Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('week');
				echo "</li>";
			}

			if($widget_options['mvisit_widget']) {
				echo "<li>";
					echo __('Month Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('month');
				echo "</li>";
			}

			if($widget_options['ysvisit_widget']) {
				echo "<li>";
					echo __('Years Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('year');
				echo "</li>";
			}

			if($widget_options['ttvisit_widget']) {
				echo "<li>";
					echo __('Total Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('total');
				echo "</li>";
			}
			
			if($widget_options['ttvisitor_widget']) {
				echo "<li>";
					echo __('Total Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('total', null, true);
				echo "</li>";
			}

			if($widget_options['tpviews_widget']) {
				echo "<li>";
					echo __('Total Page Views', 'wp_statistics'). ": ";
					echo wp_statistics_pages('total');
				echo "</li>";
			}

			if($widget_options['ser_widget']) {
			
				echo "<li>";
					echo __('Search Engine referred', 'wp_statistics'). ": ";
					echo wp_statistics_searchengine($widget_options['select_se']);
				echo "</li>";
			}
			
			if($widget_options['tp_widget']) {
				echo "<li>";
					echo __('Total Posts', 'wp_statistics'). ": ";
					echo wp_statistics_countposts();
				echo "</li>";
			}

			if($widget_options['tpg_widget']) {
				echo "<li>";
					echo __('Total Pages', 'wp_statistics'). ": ";
					echo wp_statistics_countpages();
				echo "</li>";
			}

			if($widget_options['tc_widget']) {
				echo "<li>";
					echo __('Total Comments', 'wp_statistics'). ": ";
					echo wp_statistics_countcomment();
				echo "</li>";
			}

			if($widget_options['ts_widget']) {
				echo "<li>";
					echo __('Total Spams', 'wp_statistics'). ": ";
					echo wp_statistics_countspam();
				echo "</li>";
			}

			if($widget_options['tu_widget']) {
				echo "<li>";
					echo __('Total Users', 'wp_statistics'). ": ";
					echo wp_statistics_countusers();
				echo "</li>";
			}

			if($widget_options['ap_widget']) {
				echo "<li>";
					echo __('Average Posts', 'wp_statistics'). ": ";
					echo wp_statistics_average_post();
				echo "</li>";
			}

			if($widget_options['ac_widget']) {
				echo "<li>";
					echo __('Average Comments', 'wp_statistics'). ": ";
					echo wp_statistics_average_comment();
				echo "</li>";
			}

			if($widget_options['au_widget']) {
				echo "<li>";
					echo __('Average Users', 'wp_statistics'). ": ";
					echo wp_statistics_average_registeruser();
				echo "</li>";
			}

			if($widget_options['lpd_widget']) {
				echo "<li>";
					echo __('Last Post Date', 'wp_statistics'). ": ";
					echo wp_statistics_lastpostdate();
				echo "</li>";
			}
			echo "</ul>";
		echo $after_widget;
	}

		function wp_statistics_control_widget() {
			GLOBAL $WP_Statistics;
			
			if ($_POST['wp_statistics_control_widget_submit']) {
				$widget_options['name_widget'] = $_POST['name_widget'];
				$widget_options['useronline_widget'] = $_POST['useronline_widget'];
				$widget_options['tvisit_widget'] = $_POST['tvisit_widget'];
				$widget_options['tvisitor_widget'] = $_POST['tvisitor_widget'];
				$widget_options['yvisit_widget'] = $_POST['yvisit_widget'];
				$widget_options['yvisitor_widget'] = $_POST['yvisitor_widget'];
				$widget_options['wvisit_widget'] = $_POST['wvisit_widget'];
				$widget_options['mvisit_widget'] = $_POST['mvisit_widget'];
				$widget_options['ysvisit_widget'] = $_POST['ysvisit_widget'];
				$widget_options['ttvisit_widget'] = $_POST['ttvisit_widget'];
				$widget_options['ttvisitor_widget'] = $_POST['ttvisitor_widget'];
				$widget_options['tpviews_widget'] = $_POST['tpviews_widget'];
				$widget_options['ser_widget'] = $_POST['ser_widget'];
				$widget_options['select_se'] = $_POST['select_se'];
				$widget_options['tp_widget'] = $_POST['tp_widget'];
				$widget_options['tpg_widget'] = $_POST['tpg_widget'];
				$widget_options['tc_widget'] = $_POST['tc_widget'];
				$widget_options['ts_widget'] = $_POST['ts_widget'];
				$widget_options['tu_widget'] = $_POST['tu_widget'];
				$widget_options['ap_widget'] = $_POST['ap_widget'];
				$widget_options['ac_widget'] = $_POST['ac_widget'];
				$widget_options['au_widget'] = $_POST['au_widget'];
				$widget_options['lpd_widget'] = $_POST['lpd_widget'];
				$widget_options['select_lps'] = $_POST['select_lps'];
				
				$WP_Statistics->update_option('widget', $widget_options);
			}
			
			include dirname( __FILE__ ) . '/includes/settings/widget.php';
		}
?>