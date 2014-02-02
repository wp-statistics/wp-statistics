<?php
/*
Plugin Name: Wordpress Statistics
Plugin URI: http://iran98.org/category/wordpress/wp-statistics/
Description: Complete statistics for your blog.
Version: 4.7
Author: Mostafa Soufi
Author URI: http://iran98.org/
Text Domain: wp_statistics
Domain Path: /languages/
License: GPL2
*/

	if( get_option('timezone_string') ) {
		date_default_timezone_set( get_option('timezone_string') );
	}
	
	define('WP_STATISTICS_VERSION', '4.7');
	define('WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION', '5.3.0');
	define('WPS_EXPORT_FILE_NAME', 'wp-statistics');
	
	load_plugin_textdomain('wp_statistics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	__('Wordpress Statistics', 'wp_statistics');
	__('Complete statistics for your blog.', 'wp_statistics');

	// Check to see if we're installed and are the current version.
	$WPS_Installed = get_option('wp_statistics_plugin_version');
	if( $WPS_Installed != WP_STATISTICS_VERSION ) {	
	
		if( $WPS_Installed == false ) {
			// If this is a new installed (aka wp_statistics_plugin_version doesn't exists, register the activation hook
			// We don't need to execute this on every activation as the user may have deactivated us at some point and is
			// just re-activating us.
			include_once dirname( __FILE__ ) . '/install.php';
		
			register_activation_hook(__FILE__, 'wp_statistics_install');
		}
		else {
			// If it's an upgrade (aka wp_statistics_plugin_version exists and is some number other than what we're running.
			include_once dirname( __FILE__ ) . '/upgrade.php';
		}
	}
	
	include_once dirname( __FILE__ ) . '/includes/functions/parse-user-agent.php';
	
	include_once dirname( __FILE__ ) . '/includes/classes/statistics.class.php';
	include_once dirname( __FILE__ ) . '/includes/classes/useronline.class.php';

	if( get_option('wps_geoip') && version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') ) {
		include_once dirname( __FILE__ ) . '/includes/classes/hits.geoip.class.php';
	} else {
		include_once dirname( __FILE__ ) . '/includes/classes/hits.class.php';
	}
	
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

	// We can wait until the very end of the page to process the statistics, that way the page loads and displays
	// quickly.
	add_action('shutdown', 'wp_statistics_shutdown_action');
	
	function wp_statistics_shutdown_action() {
		$o = new Useronline();
		$h = new Hits();
	
		if( get_option('wps_useronline') )
			$o->Check_online();

		if( get_option('wps_visits') )
			$h->Visits();

		if( get_option('wps_visitors') )
			$h->Visitors();

		if( get_option('wps_check_online') )
			$o->second = get_option('wps_check_online');
		
		// Check to see if the GeoIP database needs to be downloaded and do so if required.
		if( get_option('wps_update_geoip') )
			wp_statistics_download_geoip();
	}
	
	function wp_statistics_validate_capability( $capability ) {
	
		global $wp_roles;

		$role_list = $wp_roles->get_names();
		$all_caps = array();

		foreach( $wp_roles->roles as $role ) {
		
			$cap_list = $role['capabilities'];
			
			foreach( $cap_list as $key => $cap ) {
				if( substr($key,0,6) != 'level_' ) {
					if( array_key_exists( $capability, $all_caps ) ) { return $capability; }
				}
			}
		}

		if( $all_caps[$capability] == 1 ) { return $capability; }
		
		return 'manage_options';
	
	}
	
	function wp_statistics_menu() {
	
		$read_cap = wp_statistics_validate_capability( get_option('wps_read_capability', 'manage_options') );
		$manage_cap = wp_statistics_validate_capability( get_option('wps_manage_capability', 'manage_options') );
		
		add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), $read_cap, __FILE__, 'wp_statistics_log_overview');
		
		add_submenu_page(__FILE__, __('Overview', 'wp_statistics'), __('Overview', 'wp_statistics'), $read_cap, __FILE__, 'wp_statistics_log_overview');
		add_submenu_page(__FILE__, __('Browsers', 'wp_statistics'), __('Browsers', 'wp_statistics'), $read_cap, 'wps_browsers_menu', 'wp_statistics_log_browsers');
		if( get_option('wps_geoip') ) {
			add_submenu_page(__FILE__, __('Countries', 'wp_statistics'), __('Countries', 'wp_statistics'), $read_cap, 'wps_countries_menu', 'wp_statistics_log_countries');
		}
		add_submenu_page(__FILE__, __('Hits', 'wp_statistics'), __('Hits', 'wp_statistics'), $read_cap, 'wps_hits_menu', 'wp_statistics_log_hits');
		add_submenu_page(__FILE__, __('Referers', 'wp_statistics'), __('Referers', 'wp_statistics'), $read_cap, 'wps_referers_menu', 'wp_statistics_log_referers');
		add_submenu_page(__FILE__, __('Searches', 'wp_statistics'), __('Searches', 'wp_statistics'), $read_cap, 'wps_searches_menu', 'wp_statistics_log_searches');
		add_submenu_page(__FILE__, __('Search Words', 'wp_statistics'), __('Search Words', 'wp_statistics'), $read_cap, 'wps_words_menu', 'wp_statistics_log_words');
		add_submenu_page(__FILE__, __('Visitors', 'wp_statistics'), __('Visitors', 'wp_statistics'), $read_cap, 'wps_visitors_menu', 'wp_statistics_log_visitors');
		add_submenu_page(__FILE__, '', '', $manage_cap, 'wps_break_menu', 'wp_statistics_log_overview');
		add_submenu_page(__FILE__, __('Optimization', 'wp_statistics'), __('Optimization', 'wp_statistics'), $manage_cap, 'wp-statistics/optimization', 'wp_statistics_optimization');
		add_submenu_page(__FILE__, __('Settings', 'wp_statistics'), __('Settings', 'wp_statistics'), $manage_cap, 'wp-statistics/settings', 'wp_statistics_settings');
	}
	add_action('admin_menu', 'wp_statistics_menu');
	
	function wp_statistics_icon() {
	
		global $wp_version;
		
		if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_enqueue_style('wpstatistics-admin-css', plugin_dir_url(__FILE__) . 'styles/admin.css', true, '1.0');
		} else {
			wp_enqueue_style('wpstatistics-admin-css', plugin_dir_url(__FILE__) . 'styles/admin-old.css', true, '1.0');
		}
	}
	add_action('admin_head', 'wp_statistics_icon');
	
	function wp_statistics_menubar() {
	
		global $wp_admin_bar, $wp_version;
		
		if ( is_super_admin() || is_admin_bar_showing() ) {
		
			if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
				$wp_admin_bar->add_menu( array(
					'id'		=>	'wp-statistic-menu',
					'title'		=>	'<span class="ab-icon"></span>',
					'href'		=>	get_bloginfo('url') . '/wp-admin/admin.php?page=wp-statistics/wp-statistics.php'
				));
			} else {
				$wp_admin_bar->add_menu( array(
					'id'		=>	'wp-statistic-menu',
					'title'		=>	'<img src="'.plugin_dir_url(__FILE__).'/images/icon.png"/>',
					'href'		=>	get_bloginfo('url') . '/wp-admin/admin.php?page=wp-statistics/wp-statistics.php'
				));
			}
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('User Online', 'wp_statistics') . ": " . wp_statistics_useronline()
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('Today visitor', 'wp_statistics') . ": " . wp_statistics_visitor('today')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('Today visit', 'wp_statistics') . ": " . wp_statistics_visit('today')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('Yesterday visitor', 'wp_statistics') . ": " . wp_statistics_visitor('yesterday')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('Yesterday visit', 'wp_statistics') . ": " . wp_statistics_visit('yesterday')
			));
			
			$wp_admin_bar->add_menu( array(
				'parent'	=>	'wp-statistic-menu',
				'title'		=>	__('View Stats', 'wp_statistics'),
				'href'		=>	get_bloginfo('url') . '/wp-admin/admin.php?page=wp-statistics/wp-statistics.php'
			));
		}
	}
	
	if( get_option('wps_menu_bar') ) {
		add_action('admin_bar_menu', 'wp_statistics_menubar', 20);
	}
	
	function wp_statistics_register() {
	
		global $wp_roles;
		
		//register_setting('wps_settings', 'wps_useronline');
		//register_setting('wps_settings', 'wps_visits');
		//register_setting('wps_settings', 'wps_visitors');
		//register_setting('wps_settings', 'wps_check_online');
		//register_setting('wps_settings', 'wps_menu_bar');
		//register_setting('wps_settings', 'wps_coefficient');
		//register_setting('wps_settings', 'wps_chart_type');
		//register_setting('wps_settings', 'wps_stats_report');
		//register_setting('wps_settings', 'wps_time_report');
		//register_setting('wps_settings', 'wps_send_report');
		//register_setting('wps_settings', 'wps_content_report');
		//register_setting('wps_settings', 'wps_geoip');
		//register_setting('wps_settings', 'wps_update_geoip');
		//register_setting('wps_settings', 'wps_store_ua');
		register_setting('wps_settings', 'wps_robotlist');
		register_setting('wps_settings', 'wps_exclude_ip');
		register_setting('wps_settings', 'wps_read_capability');
		register_setting('wps_settings', 'wps_manage_capability');
		//register_setting('wps_settings', 'wps_schedule_geoip');
		//register_setting('wps_settings', 'wps_auto_pop');
		//register_setting('wps_settings', 'wps_schedule_dbmaint');
		//register_setting('wps_settings', 'wps_schedule_dbmaint_days');
		
		$role_list = $wp_roles->get_names();
		
		foreach( $role_list as $role ) {
			$option_name = 'wps_exclude_' . str_replace(" ", "_", strtolower($role) );

			register_setting('wps_settings', $option_name );
		}

	}
	add_action('admin_init', 'wp_statistics_register');
	
	function wp_statistics_log_overview() {
	
		wp_statistics_log();
	}
	
	function wp_statistics_log_browsers() {
	
		wp_statistics_log('all-browsers');
	}
	
	function wp_statistics_log_hits() {
	
		wp_statistics_log('hit-statistics');
	}
	
	function wp_statistics_log_searches() {
	
		wp_statistics_log('search-statistics');
	}
	
	function wp_statistics_log_visitors() {
	
		wp_statistics_log('last-all-visitor');
	}
	
	function wp_statistics_log_countries() {
	
		wp_statistics_log('top-countries');
	}
	
	function wp_statistics_log_referers() {
	
		wp_statistics_log('top-referring-site');
	}
	
	function wp_statistics_log_words() {
	
		wp_statistics_log('last-all-search');
	}
	
	function wp_statistics_log( $log_type = "" ) {
	
		if( $log_type == "" && array_key_exists('type', $_GET)) 
			$log_type = $_GET['type'];
			
		if (!current_user_can(wp_statistics_validate_capability(get_option('wps_read_capability', 'manage_option')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		$result['useronline'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_useronline`");
		$result['visit'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_visit`");
		$result['visitor'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_visitor`");
		
		if( ($result['useronline']) && ($result['visit']) && ($result['visitor']) != '1' )
			wp_die('<div class="error"><p>'.__('Table plugin does not exist! Please disable and re-enable the plugin.', 'wp_statistics').'</p></div>');
		
		wp_enqueue_script('postbox');
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/log.css', true, '1.1');
		wp_enqueue_style('pagination-css', plugin_dir_url(__FILE__) . 'styles/pagination.css', true, '1.0');
		
		if( is_rtl() )
			wp_enqueue_style('rtl-css', plugin_dir_url(__FILE__) . 'styles/rtl.css', true, '1.1');

		wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'js/highcharts.js', true, '2.3.5');
			
		include_once dirname( __FILE__ ) . '/includes/classes/pagination.class.php';

		if( $log_type == 'last-all-search' ) {
		
			include_once dirname( __FILE__ ) . '/includes/log/last-search.php';
			
		} else if( $log_type == 'last-all-visitor' ) {
		
			include_once dirname( __FILE__ ) . '/includes/log/last-visitor.php';
			
		} else if( $log_type == 'top-referring-site' ) {
		
			include_once dirname( __FILE__ ) . '/includes/log/top-referring.php';
			
		} else if( $log_type == 'all-browsers' ) {

			include_once dirname( __FILE__ ) . '/includes/log/all-browsers.php';
			
		} else if( $log_type == 'top-countries' ) {

			include_once dirname( __FILE__ ) . '/includes/log/top-countries.php';
			
		} else if( $log_type == 'hit-statistics' ) {

			include_once dirname( __FILE__ ) . '/includes/log/hit-statistics.php';
			
		} else if( $log_type == 'search-statistics' ) {

			include_once dirname( __FILE__ ) . '/includes/log/search-statistics.php';
			
		} else {
		
			include_once dirname( __FILE__ ) . '/includes/log/log.php';
		}
	}
	
	function wp_statistics_optimization() {
	
		if (!current_user_can(wp_statistics_validate_capability(get_option('wps_manage_capability', 'manage_options')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		$result['useronline'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_useronline`");
		$result['visit'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visit`");
		$result['visitor'] = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor`");
		
		switch($_GET['tab']) {
			case 'export':
				include_once dirname( __FILE__ ) . "/includes/optimization/templates/wps-optimization-export.php";
			break;
			
			case 'purging':
				include_once dirname( __FILE__ ) . "/includes/optimization/templates/wps-optimization-purging.php";
			break;
			
			case 'updates':
				include_once dirname( __FILE__ ) . "/includes/optimization/templates/wps-optimization-updates.php";
			break;
			
			default:
				include_once dirname( __FILE__ ) . "/includes/optimization/templates/wps-optimization.php";
			break;
		}
	}

	function wp_statistics_download_geoip() {

		$download_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';

		$DBFile = plugin_dir_path( __FILE__ ) . 'GeoIP2-db/GeoLite2-Country.mmdb';

		// Download
		$TempFile = download_url( $download_url );
		if (is_wp_error( $TempFile ) ) {
			$result = "<div class='updated settings-error'><p><strong>" . sprintf(__('Error downloading GeoIP database from: %s', 'wp_statistics'), $download_url) . "</strong></p></div>";
		}
		else {
			// Ungzip File
			$ZipHandle = gzopen( $TempFile, 'rb' );
			$DBfh = fopen( $DBFile, 'wb' );

			if( ! $ZipHandle ) {
				$result = "<div class='updated settings-error'><p><strong>" . sprintf(__('Error could not open downloaded GeoIP database for reading: %s', 'wp_statistics'), $TempFile) . "</strong></p></div>";
				
				unlink( $TempFile );
			}
			else {
				if( !$DBfh ) {
					$result = "<div class='updated settings-error'><p><strong>" . sprintf(__('Error could not open destination GeoIP database for writing %s', 'wp_statistics'), $DBFile) . "</strong></p></div>";
					unlink( $TempFile );
				}
				else {
					while( ( $data = gzread( $ZipHandle, 4096 ) ) != false ) {
						fwrite( $DBfh, $data );
					}

					gzclose( $ZipHandle );
					fclose( $DBfh );

					unlink( $TempFile );
					
					$result = "<div class='updated settings-error'><p><strong>" . __('GeoIP Database updated successfully!', 'wp_statistics') . "</strong></p></div>";
					
					update_option('wps_last_geoip_dl', time());
					update_option('wps_update_geoip', false);

					if( get_option('wps_geoip') && version_compare(phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>') && get_option('wps_auto_pop')) {
						include_once dirname( __FILE__ ) . '/includes/functions/geoip-populate.php';
						$result .= wp_statistics_populate_geoip_info();
					}
				}
			}
		}
		
		return $result;
	}
	
	function wp_statistics_settings() {

		if (!current_user_can(wp_statistics_validate_capability(get_option('wps_manage_capability', 'manage_options')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'styles/style.css', true, '1.0');
		
		// We could let the download happen at the end of the page, but this way we get to give some
		// feedback to the users about the result.
		if( get_option('wps_update_geoip') == true ) {
			echo wp_statistics_download_geoip();
		}
		
		switch($_GET['tab']) {
			case 'access-level':
				include_once dirname( __FILE__ ) . "/includes/settings/wps-access-level.php";
			break;
			
			case 'geoip':
				include_once dirname( __FILE__ ) . "/includes/settings/wps-geoip.php";
			break;
			
			case 'maintenance':
				include_once dirname( __FILE__ ) . "/includes/settings/wps-maintenance.php";
			break;
			
			default:
				include_once dirname( __FILE__ ) . "/includes/settings/wps-settings.php";
			break;
		}
	}