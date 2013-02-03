<?php
/*
Plugin Name: Wordpress Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Summary statistics of blog.
Version: 3.0
Author: Mostafa Soufi
Author URI: http://iran98.org/
License: GPL2
*/

	if( get_option('timezone_string') ) {
		date_default_timezone_set( get_option('timezone_string') );
	}
	
	define('WP_STATISTICS_VERSION', '3.0');
	
	load_plugin_textdomain('wp_statistics','wp-content/plugins/wp-statistics/languages');
	
	include_once dirname( __FILE__ ) . '/upgrade.php';
	include_once dirname( __FILE__ ) . '/install.php';
	
	register_activation_hook(__FILE__, 'wp_statistics_install');
	
	include_once dirname( __FILE__ ) . '/includes/class/statistics.class.php';
	include_once dirname( __FILE__ ) . '/includes/class/useronline.class.php';
	include_once dirname( __FILE__ ) . '/includes/class/hits.class.php';
	
	$s = new Statistics();
	$o = new Useronline();
	$h = new Hits();
	
	include_once dirname( __FILE__ ) . '/includes/functions/functions.php';
	include_once dirname( __FILE__ ) . '/widget.php';
	include_once dirname( __FILE__ ) . '/schedule.php';
	
	if( get_option('coefficient') ) {
		$h->coefficient = get_option('coefficient');
	}
	
	if( get_option('useronline') && !is_admin() )
		$o->Check_online();

	if( get_option('visits') && !is_admin() )
		$h->Visits();

	if( get_option('visitors') && !is_admin() )
		$h->Visitors();

	if( get_option('check_online') ) {
		$o->second = get_option('check_online');
	}
	
	function wp_statistics_menu() {
	
		if (function_exists('add_options_page')) {
		
			add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), 'manage_options', __FILE__, 'wp_statistics_log', plugin_dir_url( __FILE__ ).'/images/icon.png');
			
			add_submenu_page(__FILE__, __('View Stats', 'wp_statistics'), __('View Stats', 'wp_statistics'), 'manage_options', __FILE__, 'wp_statistics_log');
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
	
	if( get_option('menu_bar') ) {
		add_action('admin_bar_menu', 'wp_statistics_menubar', 20);
	}
	
	function wp_statistics_register() {
	
		register_setting('wps_settings', 'useronline');
		register_setting('wps_settings', 'visits');
		register_setting('wps_settings', 'visitors');
		register_setting('wps_settings', 'check_online');
		register_setting('wps_settings', 'menu_bar');
		register_setting('wps_settings', 'coefficient');
		register_setting('wps_settings', 'ip_information');
		register_setting('wps_settings', 'stats_report');
		register_setting('wps_settings', 'time_report');
		register_setting('wps_settings', 'send_report');
		register_setting('wps_settings', 'content_report');
	}
	add_action('admin_init', 'wp_statistics_register');
	
	function wp_statistics_log() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		wp_enqueue_script('dashboard');
		wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'js/highcharts.js', true, '2.3.5');
		
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/log.css', true, '1.0');
		
		if( is_rtl() )
			wp_enqueue_style('rtl-css', plugin_dir_url(__FILE__) . 'styles/rtl.css', true, '1.0');
		
		include_once dirname( __FILE__ ) . '/includes/log/log.php';
	}
	
	function wp_statistics_settings() {
	
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $o, $h;
		
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/style.css', true, '1.0');
		
		include_once dirname( __FILE__ ) . '/includes/setting/settings.php';
	}