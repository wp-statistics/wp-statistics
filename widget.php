<?php
	function wp_statistics_widget() {
	
		wp_register_sidebar_widget('wp-statistics', __('Statistics', 'wp_statistics'), 'wp_statistics_show_widget', array(
			'description'	=>	__('Show site stats in sidebar', 'wp_statistics')));
		wp_register_widget_control('wp-statistics', __('Statistics', 'wp_statistics'), 'wp_statistics_control_widget');
	}
	add_action("plugins_loaded", "wp_statistics_widget");

	function wp_statistics_show_widget($args) {
	
		extract($args);
		echo $before_widget;
		echo $before_title . get_option('name_widget') . $after_title;
		
			echo "<ul>";
			if(get_option('useronline_widget')) {
				echo "<li>";
					echo __('User Online', 'wp_statistics'). ": ";
					echo wp_statistics_useronline();
				echo "</li>";
			}
			
			if(get_option('tvisit_widget')) {
				echo "<li>";
					echo __('Today Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('today');
				echo "</li>";
			}
			
			if(get_option('tvisitor_widget')) {
				echo "<li>";
					echo __('Today Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('today');
				echo "</li>";
			}

			if(get_option('yvisit_widget')) {
				echo "<li>";
					echo __('Yesterday Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('yesterday');
				echo "</li>";
			}
			
			if(get_option('yvisitor_widget')) {
				echo "<li>";
					echo __('Yesterday Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('yesterday');
				echo "</li>";
			}

			if(get_option('wvisit_widget')) {
				echo "<li>";
					echo __('Week Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('week');
				echo "</li>";
			}

			if(get_option('mvisit_widget')) {
				echo "<li>";
					echo __('Month Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('month');
				echo "</li>";
			}

			if(get_option('ysvisit_widget')) {
				echo "<li>";
					echo __('Years Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('year');
				echo "</li>";
			}

			if(get_option('ttvisit_widget')) {
				echo "<li>";
					echo __('Total Visit', 'wp_statistics'). ": ";
					echo wp_statistics_visit('total');
				echo "</li>";
			}
			
			if(get_option('ttvisitor_widget')) {
				echo "<li>";
					echo __('Total Visitor', 'wp_statistics'). ": ";
					echo wp_statistics_visitor('total');
				echo "</li>";
			}

			if(get_option('ser_widget')) {
			
				echo "<li>";
					echo __('Search Engine reffered', 'wp_statistics'). ": ";
					if(get_option('select_se') == "google"){
						echo wp_statistics_searchengine("google");
					} else if(get_option('select_se') == "yahoo"){
						echo wp_statistics_searchengine("yahoo");
					} else if(get_option('select_se') == "bing"){
						echo wp_statistics_searchengine("bing");
					} else {
						echo wp_statistics_searchengine('all');
					}
				echo "</li>";
			}
			
			if(get_option('tp_widget')) {
				echo "<li>";
					echo __('Total Posts', 'wp_statistics'). ": ";
					echo wp_statistics_countposts();
				echo "</li>";
			}

			if(get_option('tpg_widget')) {
				echo "<li>";
					echo __('Total Pages', 'wp_statistics'). ": ";
					echo wp_statistics_countpages();
				echo "</li>";
			}

			if(get_option('tc_widget')) {
				echo "<li>";
					echo __('Total Comments', 'wp_statistics'). ": ";
					echo wp_statistics_countcomment();
				echo "</li>";
			}

			if(get_option('ts_widget')) {
				echo "<li>";
					echo __('Total Spams', 'wp_statistics'). ": ";
					echo wp_statistics_countspam();
				echo "</li>";
			}

			if(get_option('tu_widget')) {
				echo "<li>";
					echo __('Total Users', 'wp_statistics'). ": ";
					echo wp_statistics_countusers();
				echo "</li>";
			}

			if(get_option('ap_widget')) {
				echo "<li>";
					echo __('Average Posts', 'wp_statistics'). ": ";
					echo wp_statistics_average_post();
				echo "</li>";
			}

			if(get_option('ac_widget')) {
				echo "<li>";
					echo __('Average Comments', 'wp_statistics'). ": ";
					echo wp_statistics_average_comment();
				echo "</li>";
			}

			if(get_option('au_widget')) {
				echo "<li>";
					echo __('Average Users', 'wp_statistics'). ": ";
					echo wp_statistics_average_registeruser();
				echo "</li>";
			}

			if(get_option('lpd_widget')) {
				echo "<li>";
					echo __('Last Post Date', 'wp_statistics'). ": ";
					if(get_option('select_lps') == "farsi") {
						echo wp_statistics_lastpostdate("farsi");
					} else {
						echo wp_statistics_lastpostdate();
					}
				echo "</li>";
			}
			echo "</ul>";
		echo $after_widget;
	}

		function wp_statistics_control_widget() {
			if ($_POST['wp_statistics_control_widget_submit']) {
				update_option('name_widget', $_POST['name_widget']);
				update_option('useronline_widget', $_POST['useronline_widget']);
				update_option('tvisit_widget', $_POST['tvisit_widget']);
				update_option('tvisitor_widget', $_POST['tvisitor_widget']);
				update_option('yvisit_widget', $_POST['yvisit_widget']);
				update_option('yvisitor_widget', $_POST['yvisitor_widget']);
				update_option('wvisit_widget', $_POST['wvisit_widget']);
				update_option('mvisit_widget', $_POST['mvisit_widget']);
				update_option('ysvisit_widget', $_POST['ysvisit_widget']);
				update_option('ttvisit_widget', $_POST['ttvisit_widget']);
				update_option('ttvisitor_widget', $_POST['ttvisitor_widget']);
				update_option('ser_widget', $_POST['ser_widget']);
				update_option('select_se', $_POST['select_se']);
				update_option('tp_widget', $_POST['tp_widget']);
				update_option('tpg_widget', $_POST['tpg_widget']);
				update_option('tc_widget', $_POST['tc_widget']);
				update_option('ts_widget', $_POST['ts_widget']);
				update_option('tu_widget', $_POST['tu_widget']);
				update_option('ap_widget', $_POST['ap_widget']);
				update_option('ac_widget', $_POST['ac_widget']);
				update_option('au_widget', $_POST['au_widget']);
				update_option('lpd_widget', $_POST['lpd_widget']);
				update_option('select_lps', $_POST['select_lps']);
			}
			
			include_once dirname( __FILE__ ) . '/includes/setting/widget.php';
		}
?>