<?php
/*
Plugin Name: Wordpress Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 3.2
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/

	if( get_option('timezone_string') ) {
		date_default_timezone_set( get_option('timezone_string') );
	}
	
	define('WP_STATISTICS_VERSION', '3.1.5');
	define('WPS_EXPORT_FILE_NAME', 'wp-statistics');
	
	update_option('wp_statistics_plugin_version', WP_STATISTICS_VERSION);
	
	load_plugin_textdomain('wp_statistics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	
	include_once dirname( __FILE__ ) . '/upgrade.php';
	include_once dirname( __FILE__ ) . '/install.php';
	
	register_activation_hook(__FILE__, 'wp_statistics_install');
	
	include_once dirname( __FILE__ ) . '/includes/class/statistics.class.php';
	include_once dirname( __FILE__ ) . '/includes/class/useronline.class.php';
	include_once dirname( __FILE__ ) . '/includes/class/hits.class.php';
	
	$s = new WP_Statistics();
	$o = new Useronline();
	$h = new Hits();
	
	include_once dirname( __FILE__ ) . '/includes/functions/functions.php';
	include_once dirname( __FILE__ ) . '/widget.php';
	include_once dirname( __FILE__ ) . '/schedule.php';
	
	function wp_statistics_not_enable() {
		$get_bloginfo_url = get_admin_url() . "admin.php?page=wp-statistics/settings";
		echo '<div class="error"><p>'.sprintf(__('Facilities Wordpress Statistics not enabled! Please go to <a href="%s">setting page</a> and enable statistics', 'wp_statistics'), $get_bloginfo_url).'</p></div>';
	}

	if( !get_option('wps_useronline') || !get_option('wps_visits') || !get_option('wps_visitors') ) {
		add_action('admin_notices', 'wp_statistics_not_enable');
	}
	
	if( get_option('wps_coefficient') ) {
		$h->coefficient = get_option('wps_coefficient');
	}
	
	if( get_option('wps_useronline') && !is_admin() )
		$o->Check_online();

	if( get_option('wps_visits') && !is_admin() )
		$h->Visits();

	if( get_option('wps_visitors') && !is_admin() )
		$h->Visitors();

	if( get_option('wps_check_online') ) {
		$o->second = get_option('wps_check_online');
	}
	
	function wp_statistics_menu() {
	
		if (function_exists('add_options_page')) {
		
			add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), 'manage_options', __FILE__, 'wp_statistics_log', plugin_dir_url( __FILE__ ).'/images/icon.png');
			
			add_submenu_page(__FILE__, __('View Stats', 'wp_statistics'), __('View Stats', 'wp_statistics'), 'manage_options', __FILE__, 'wp_statistics_log');
			add_submenu_page(__FILE__, __('Optimization', 'wp_statistics'), __('Optimization', 'wp_statistics'), 'manage_options', 'wp-statistics/optimization', 'wp_statistics_optimization');
			add_submenu_page(__FILE__, __('Settings', 'wp_statistics'), __('Settings', 'wp_statistics'), 'manage_options', 'wp-statistics/settings', 'wp_statistics_settings');
		}
	}
	add_action('admin_menu', 'wp_statistics_menu');
	
	function wp_statistics_menubar() {
	
		global $wp_admin_bar;
		
		if ( is_super_admin() || is_admin_bar_showing() ) {
		
			$wp_admin_bar->add_menu( array(
				'id'		=>	'wp-statistic_menu',
				'title'		=>	'<img src="'.plugin_dir_url(__FILE__).'/images/icon.png"/>',
				'href'		=>	get_bloginfo('url') . '/wp-admin/admin.php?page=wp-statistics/wp-statistics.php'
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('User Online', 'wp_statistics') . ": " . wp_statistics_useronline()
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('Today visitor', 'wp_statistics') . ": " . wp_statistics_visitor('today')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('Today visit', 'wp_statistics') . ": " . wp_statistics_visit('today')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('Yesterday visitor', 'wp_statistics') . ": " . wp_statistics_visitor('yesterday')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('Yesterday visit', 'wp_statistics') . ": " . wp_statistics_visit('yesterday')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic_menu',
				'title'		=>	__('View Stats', 'wp_statistics'),
				'href'		=>	get_bloginfo('url') . '/wp-admin/admin.php?page=wp-statistics/wp-statistics.php'
			));
		}
	}
	
	if( get_option('wps_menu_bar') ) {
		add_action('admin_bar_menu', 'wp_statistics_menubar', 20);
	}
	
	function wp_statistics_register() {
	
		register_setting('wps_settings', 'wps_useronline');
		register_setting('wps_settings', 'wps_visits');
		register_setting('wps_settings', 'wps_visitors');
		register_setting('wps_settings', 'wps_check_online');
		register_setting('wps_settings', 'wps_menu_bar');
		register_setting('wps_settings', 'wps_coefficient');
		register_setting('wps_settings', 'wps_ip_information');
		register_setting('wps_settings', 'wps_chart_type');
		register_setting('wps_settings', 'wps_stats_report');
		register_setting('wps_settings', 'wps_time_report');
		register_setting('wps_settings', 'wps_send_report');
		register_setting('wps_settings', 'wps_content_report');
	}
	add_action('admin_init', 'wp_statistics_register');
	
	function wp_statistics_log() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		$result[] = $wpdb->query("SELECT * FROM {$table_prefix}statistics_useronline");
		$result[] = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visit");
		$result[] = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor");
		
		if( !$result[0] || !$result[1] || !$result[2] ) {
			wp_die(__('Table plugin does not exist! Please disable and re-enable the plugin.', 'wp_statistics'));
		}
		
		wp_enqueue_script('dashboard');
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/log.css', true, '1.1');
		wp_enqueue_style('pagination-css', plugin_dir_url(__FILE__) . 'styles/pagination.css', true, '1.0');
		
		if( is_rtl() )
			wp_enqueue_style('rtl-css', plugin_dir_url(__FILE__) . 'styles/rtl.css', true, '1.1');
			
		include_once dirname( __FILE__ ) . '/includes/class/pagination.class.php';
		
		$result['google'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%google.com%'");
		$result['yahoo'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%yahoo.com%'");
		$result['bing'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%bing.com%'");
		
		if( $_GET['type'] == 'last-all-search' ) {
		
			$result['all'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%google.com%' OR `referred` LIKE '%yahoo.com%' OR `referred` LIKE '%bing.com%'");
		
			$referred = $_GET['referred'];
			if( $referred ) {
				$total = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%{$referred}%'");
			} else {
				$total = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%google.com%' OR `referred` LIKE '%yahoo.com%' OR `referred` LIKE '%bing.com%'");
			}
		
			include_once dirname( __FILE__ ) . '/includes/log/last-search.php';
			
		} else if( $_GET['type'] == 'last-all-visitor' ) {
		
			$agent = $_GET['agent'];
			if( $agent ) {
				$total = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `agent` LIKE '%{$agent}%'");
			} else {
				$total = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor`");
			}
			
			include_once dirname( __FILE__ ) . '/includes/log/last-visitor.php';
			
		} else if( $_GET['type'] == 'top-referring-site' ) {
		
			$referr = $_GET['referr'];
			if( $referr ) {
				$total = $wpdb->query("SELECT `referred` FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%{$referr}%'");
			} else {
				$total = $wpdb->query("SELECT `referred` FROM `{$table_prefix}statistics_visitor` WHERE referred <> ''");
			}
			
			include_once dirname( __FILE__ ) . '/includes/log/top-referring.php';
			
		} else {
		
			wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'js/highcharts.js', true, '2.3.5');
			
			include_once dirname( __FILE__ ) . '/includes/log/log.php';
		}
		
	}
	
	function wp_statistics_optimization() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		$result['useronline'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_useronline`");
		$result['visit'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visit`");
		$result['visitor'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor`");
		
		include_once dirname( __FILE__ ) . '/includes/optimization/optimization.php';
		
	}
	
	function wp_statistics_settings() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $o, $h;
		
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/style.css', true, '1.0');
		
		include_once dirname( __FILE__ ) . '/includes/setting/settings.php';
		
	}