<?php
/*
Plugin Name: WP Statistics
Plugin URI: http://wp-statistics.com
Description: Complete statistics for your blog.
Version: 6.1
Author: Mostafa Soufi & Greg Ross
Text Domain: wp_statistics
Domain Path: /languages/
License: GPL2
*/

	if( get_option('timezone_string') ) {
		date_default_timezone_set( get_option('timezone_string') );
	}
	
	define('WP_STATISTICS_VERSION', '6.1');
	define('WP_STATISTICS_MANUAL', 'manual/WP Statistics Admin Manual.');
	define('WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION', '5.3.0');
	define('WPS_EXPORT_FILE_NAME', 'wp-statistics');
	
	load_plugin_textdomain('wp_statistics', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');
	__('WP Statistics', 'wp_statistics');
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
	
	include_once dirname( __FILE__ ) . '/includes/functions/functions.php';
	include_once dirname( __FILE__ ) . '/includes/functions/parse-user-agent.php';
	
	include_once dirname( __FILE__ ) . '/includes/classes/statistics.class.php';

	$WP_Statistics = new WP_Statistics();

	include_once dirname( __FILE__ ) . '/includes/classes/useronline.class.php';
	include_once dirname( __FILE__ ) . '/includes/classes/hits.class.php';

	if( $WP_Statistics->get_option('geoip') && wp_statistics_geoip_supported() ) {
		include_once dirname( __FILE__ ) . '/includes/classes/hits.geoip.class.php';
	}
	
	include_once dirname( __FILE__ ) . '/widget.php';
	include_once dirname( __FILE__ ) . '/shortcode.php';
	include_once dirname( __FILE__ ) . '/schedule.php';
	
	function wp_statistics_not_enable() {

		if( !$WP_Statistics->get_option('hide_notices') ) {
			$get_bloginfo_url = get_admin_url() . "admin.php?page=wp-statistics/settings";
			
			if( !$WP_Statistics->get_option('useronline') || !$WP_Statistics->get_option('visits') || !$WP_Statistics->get_option('visitors') )
				echo '<div class="error"><p>'.sprintf(__('WP Statistics is not enabled! Please go to <a href="%s">setting page</a> and enable statistics', 'wp_statistics'), $get_bloginfo_url).'</p></div>';
			
			if(!$WP_Statistics->get_option('geoip') && wp_statistics_geoip_supported())
				echo '<div class="error"><p>'.sprintf(__('GeoIP collection is not active! Please go to <a href="%s">Setting page > GeoIP</a> and enable this feature (GeoIP can detect the visitors country)', 'wp_statistics'), $get_bloginfo_url . '&tab=geoip').'</p></div>';
		}
	}

	if( !$WP_Statistics->get_option('useronline') || !$WP_Statistics->get_option('visits') || !$WP_Statistics->get_option('visitors') || !$WP_Statistics->get_option('geoip') ) {
		add_action('admin_notices', 'wp_statistics_not_enable');
	}

	// We can wait until the very end of the page to process the statistics, that way the page loads and displays
	// quickly.
	add_action('shutdown', 'wp_statistics_shutdown_action');
	
	function wp_statistics_shutdown_action() {
		GLOBAL $WP_Statistics;
		
		$o = new Useronline();
		
		if( class_exists( 'GeoIPHits' ) ) { 
			$h = new GeoIPHits();
		} else {
			$h = new Hits();
		}
	
		if( $WP_Statistics->get_option('useronline') )
			$o->Check_online();

		if( $WP_Statistics->get_option('visits') )
			$h->Visits();

		if( $WP_Statistics->get_option('visitors') )
			$h->Visitors();

		if( $WP_Statistics->get_option('pages') )
			$h->Pages();

		if( $WP_Statistics->get_option('check_online') )
			$o->second = $WP_Statistics->get_option('check_online');
		
		// Check to see if the GeoIP database needs to be downloaded and do so if required.
		if( $WP_Statistics->get_option('update_geoip') )
			wp_statistics_download_geoip();
	}

	// Add a settings link to the plugin list.
	function wp_statistics_settings_links( $links, $file ) {
		GLOBAL $WP_Statistics;
		
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option('manage_capability', 'manage_options') );
		
		if( current_user_can( $manage_cap ) ) {
			array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=wp-statistics/settings' ) . '">' . __( 'Settings', 'wp_statistics' ) . '</a>' );
		}
		
		return $links;
	}
	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wp_statistics_settings_links', 10, 2 );

	// Add a WordPress plugin page and rating links to the meta information to the plugin list.
	function wp_statistics_add_meta_links($links, $file) {
		if( $file == plugin_basename(__FILE__) ) {
			$plugin_url = 'http://wordpress.org/plugins/wp-statistics/';
			
			$links[] = '<a href="'. $plugin_url .'" target="_blank" title="'. __('Click here to visit the plugin on WordPress.org', 'wp_statistics') .'">'. __('Visit WordPress.org page', 'wp_statistics') .'</a>';
			
			$rate_url = 'http://wordpress.org/support/view/plugin-reviews/wp-statistics?rate=5#postform';
			$links[] = '<a href="'. $rate_url .'" target="_blank" title="'. __('Click here to rate and review this plugin on WordPress.org', 'wp_statistics') .'">'. __('Rate this plugin', 'wp_statistics') .'</a>';
		}
		
		return $links;
	}
	add_filter('plugin_row_meta', 'wp_statistics_add_meta_links', 10, 2);
	
	// Add a custom column to post/pages for hit statistics.
	function wp_statistics_add_column( $columns ) {
		$columns['wp-statistics'] = __('Hits', 'wp_statistics');
		
		return $columns;
	}

	// Render the custom column on the post/pages lists.
	function wp_statistics_render_column( $column_name, $post_id ) {
		if( $column_name == 'wp-statistics' ) {
			echo "<a href='" . get_admin_url() . "admin.php?page=wps_pages_menu&page-id={$post_id}'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
		}
	}
	
	// Call the add/render functions at the appropriate times.
	function wp_statistics_load_edit_init() {
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option('manage_capability', 'manage_options') );
		
		if( current_user_can( $manage_cap ) && $WP_Statistics->get_option('pages') && !$WP_Statistics->get_option('disable_column') ) {
			$post_types = (array)get_post_types( array( 'show_ui' => true ), 'object' );
			
			foreach( $post_types as $type ) {
				add_action( 'manage_' . $type->name . '_posts_columns', 'wp_statistics_add_column', 10, 2 );
				add_action( 'manage_' . $type->name . '_posts_custom_column', 'wp_statistics_render_column', 10, 2 );
			}
		}
	}
	add_action( 'load-edit.php', 'wp_statistics_load_edit_init' );

	// Add the hit count to the publish widget in the post/pages editor.
	function wp_statistics_post_init() {
		global $post;
		
		$id = $post->ID;
	
		echo "<div class='misc-pub-section'>" . __( 'WP Statistics - Hits', 'wp_statistics') . ': <b>' . wp_statistics_pages( 'total', '', $id ) . '</b></div>';
	}
	if( $WP_Statistics->get_option('pages') && !$WP_Statistics->get_option('disable_column') ) {
		add_action( 'post_submitbox_misc_actions', 'wp_statistics_post_init' );
	}
	
	function wp_statistics_validate_capability( $capability ) {
	
		global $wp_roles;

		$role_list = $wp_roles->get_names();

		foreach( $wp_roles->roles as $role ) {
		
			$cap_list = $role['capabilities'];
			
			foreach( $cap_list as $key => $cap ) {
				if( $capability == $key ) { return $capability; }
			}
		}

		return 'manage_options';
	}
	
	function wp_statistics_menu() {
		GLOBAL $WP_Statistics;
		
		$read_cap = wp_statistics_validate_capability( $WP_Statistics->get_option('read_capability', 'manage_options') );
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option('manage_capability', 'manage_options') );
		
		add_menu_page(__('Statistics', 'wp_statistics'), __('Statistics', 'wp_statistics'), $read_cap, __FILE__, 'wp_statistics_log_overview');
		
		add_submenu_page(__FILE__, __('Overview', 'wp_statistics'), __('Overview', 'wp_statistics'), $read_cap, __FILE__, 'wp_statistics_log_overview');
		add_submenu_page(__FILE__, __('Browsers', 'wp_statistics'), __('Browsers', 'wp_statistics'), $read_cap, 'wps_browsers_menu', 'wp_statistics_log_browsers');
		if( $WP_Statistics->get_option('geoip') ) {
			add_submenu_page(__FILE__, __('Countries', 'wp_statistics'), __('Countries', 'wp_statistics'), $read_cap, 'wps_countries_menu', 'wp_statistics_log_countries');
		}
		add_submenu_page(__FILE__, __('Hits', 'wp_statistics'), __('Hits', 'wp_statistics'), $read_cap, 'wps_hits_menu', 'wp_statistics_log_hits');
		add_submenu_page(__FILE__, __('Exclusions', 'wp_statistics'), __('Exclusions', 'wp_statistics'), $read_cap, 'wps_exclusions_menu', 'wp_statistics_log_exclusions');
		add_submenu_page(__FILE__, __('Referers', 'wp_statistics'), __('Referers', 'wp_statistics'), $read_cap, 'wps_referers_menu', 'wp_statistics_log_referers');
		add_submenu_page(__FILE__, __('Searches', 'wp_statistics'), __('Searches', 'wp_statistics'), $read_cap, 'wps_searches_menu', 'wp_statistics_log_searches');
		add_submenu_page(__FILE__, __('Search Words', 'wp_statistics'), __('Search Words', 'wp_statistics'), $read_cap, 'wps_words_menu', 'wp_statistics_log_words');
		add_submenu_page(__FILE__, __('Visitors', 'wp_statistics'), __('Visitors', 'wp_statistics'), $read_cap, 'wps_visitors_menu', 'wp_statistics_log_visitors');
		add_submenu_page(__FILE__, __('Pages', 'wp_statistics'), __('Pages', 'wp_statistics'), $read_cap, 'wps_pages_menu', 'wp_statistics_log_pages');
		add_submenu_page(__FILE__, '', '', $manage_cap, 'wps_break_menu', 'wp_statistics_log_overview');
		add_submenu_page(__FILE__, __('Optimization', 'wp_statistics'), __('Optimization', 'wp_statistics'), $manage_cap, 'wp-statistics/optimization', 'wp_statistics_optimization');
		add_submenu_page(__FILE__, __('Settings', 'wp_statistics'), __('Settings', 'wp_statistics'), $manage_cap, 'wp-statistics/settings', 'wp_statistics_settings');
		add_submenu_page(__FILE__, __('Manual', 'wp_statistics'), __('Manual', 'wp_statistics'), $manage_cap, 'wps_manual_menu', 'wp_statistics_manual');
	}
	add_action('admin_menu', 'wp_statistics_menu');
	
	function wp_statistics_menu_icon() {
	
		global $wp_version;
		
		if( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
			wp_enqueue_style('wpstatistics-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin.css', true, '1.0');
		} else {
			wp_enqueue_style('wpstatistics-admin-css', plugin_dir_url(__FILE__) . 'assets/css/admin-old.css', true, '1.0');
		}
	}
	add_action('admin_head', 'wp_statistics_menu_icon');
	
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
					'title'		=>	'<img src="'.plugin_dir_url(__FILE__).'/assets/images/icon.png"/>',
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
	
	if( $WP_Statistics->get_option('menu_bar') ) {
		add_action('admin_bar_menu', 'wp_statistics_menubar', 20);
	}
	
	function wp_statistics_manual() {
		if( file_exists(plugin_dir_path(__FILE__) . WP_STATISTICS_MANUAL . 'html') ) { 
			echo '<script type="text/javascript">' . "\n";
			echo '    function AdjustiFrameHeight(id,fudge)' . "\n";
			echo '    {' . "\n";
			echo '        var frame = document.getElementById(id);' . "\n";
			echo '        frame.height = frame.contentDocument.body.offsetHeight + fudge;' . "\n";
			echo '    }' . "\n";
			echo '</script>' . "\n";

			echo '<br>';
			echo '<a href="' .  plugin_dir_url(__FILE__) . 'manual/manual.php?type=odt' . '" target="_blank"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/ODT.png' . '" height="32" width="32" alt="' . __('Download ODF file', 'wp_statistics') . '"></a>&nbsp;';
			echo '<a href="' .  plugin_dir_url(__FILE__) . 'manual/manual.php?type=html' . '" target="_blank"><img src="' . plugin_dir_url(__FILE__) . 'assets/images/HTML.png' . '" height="32" width="32" alt="' . __('Download HTML file', 'wp_statistics') . '"></a><br>';
			
			echo '<iframe src="' .  plugin_dir_url(__FILE__) . WP_STATISTICS_MANUAL . 'html' . '" width="100%" frameborder="0" scrolling="no" id="wps_inline_docs" onload="AdjustiFrameHeight(\'wps_inline_docs\', 50);"></iframe>';
		} else {
			echo __("Manual file not found.", 'wp_statistics');
		}
	}
	
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

	function wp_statistics_log_pages() {
	
		wp_statistics_log('top-pages');
	}
	
	function wp_statistics_log_page() {
		
		wp_statistics_log('page-statistics');
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
	
	function wp_statistics_log_exclusions() {
	
		wp_statistics_log('exclusions');
	}
	
	
	function wp_statistics_log( $log_type = "" ) {
		GLOBAL $WP_Statistics;
		
		$WP_Statistics->load_user_options();

		if( $log_type == "" && array_key_exists('type', $_GET)) 
			$log_type = $_GET['type'];
			
		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('read_capability', 'manage_option')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		$wpstats = new WP_Statistics();
		
		$result['useronline'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_useronline`");
		$result['visit'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_visit`");
		$result['visitor'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_visitor`");
		$result['exclusions'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_exclusions`");
		$result['pages'] = $wpdb->query("CHECK TABLE `{$table_prefix}statistics_pages`");
		
		if( ($result['useronline']) && ($result['visit']) && ($result['visitor']) != '1' && ($result['exclusions']) != '1' && ($result['pages']) != '1' )
			wp_die('<div class="error"><p>'.__('Table plugin does not exist! Please disable and re-enable the plugin.', 'wp_statistics').'</p></div>');
		
		wp_enqueue_script('postbox');
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'assets/css/log.css', true, '1.1');
		wp_enqueue_style('pagination-css', plugin_dir_url(__FILE__) . 'assets/css/pagination.css', true, '1.0');
		
		if( is_rtl() )
			wp_enqueue_style('rtl-css', plugin_dir_url(__FILE__) . 'assets/css/rtl.css', true, '1.1');

		wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . 'assets/js/highcharts.js', true, '3.0.9');
			
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
			
		} else if( $log_type == 'exclusions' ) {

			include_once dirname( __FILE__ ) . '/includes/log/exclusions.php';
			
		} else if( $log_type == 'top-pages' ) {

			// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
			if( $_GET['page-id'] || $_GET['page-uri'] ) {
				include_once dirname( __FILE__ ) . '/includes/log/page-statistics.php';
			} else {
				include_once dirname( __FILE__ ) . '/includes/log/top-pages.php';
			}
			
		} else {
		
			include_once dirname( __FILE__ ) . '/includes/log/log.php';
		}
	}
	
	function wp_statistics_optimization() {
		GLOBAL $WP_Statistics;
		
		$WP_Statistics->load_user_options();
	
		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		global $wpdb, $table_prefix;
		
		
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
				$result['useronline'] = $wpdb->get_var("SELECT COUNT(ID) FROM `{$table_prefix}statistics_useronline`");
				$result['visit'] = $wpdb->get_var("SELECT COUNT(ID) FROM `{$table_prefix}statistics_visit`");
				$result['visitor'] = $wpdb->get_var("SELECT COUNT(ID) FROM `{$table_prefix}statistics_visitor`");
				$result['exclusions'] = $wpdb->get_var("SELECT COUNT(ID) FROM `{$table_prefix}statistics_exclusions`");
				$result['pages'] = $wpdb->get_var("SELECT COUNT(uri) FROM `{$table_prefix}statistics_pages`");
				
				include_once dirname( __FILE__ ) . "/includes/optimization/templates/wps-optimization.php";
			break;
		}
	}

	function wp_statistics_download_geoip() {

		if( !function_exists( 'download_url' ) ) { return ''; }
	
		$download_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';

		$upload_dir = wp_upload_dir();
		 
		$dbsize = filesize($upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb');
		$DBFile = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb';

		if( !file_exists($upload_dir['basedir'] . '/wp-statistics') ) { mkdir($upload_dir['basedir'] . '/wp-statistics'); }
		
		// Download
		$TempFile = download_url( $download_url );
		if (is_wp_error( $TempFile ) ) {
			$result = "<div class='updated settings-error'><p><strong>" . sprintf(__('Error downloading GeoIP database from: %s - %s', 'wp_statistics'), $download_url, $TempFile->get_error_message() ) . "</strong></p></div>";
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

					if( $WP_Statistics->get_option('geoip') && wp_statistics_geoip_supported() && $WP_Statistics->get_option('auto_pop')) {
						include_once dirname( __FILE__ ) . '/includes/functions/geoip-populate.php';
						$result .= wp_statistics_populate_geoip_info();
					}
				}
			}
		}
		
		return $result;
	}
	
	function wp_statistics_settings() {
		GLOBAL $WP_Statistics;
		
		$WP_Statistics->load_user_options();

		if (!current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options')))) {
			wp_die(__('You do not have sufficient permissions to access this page.'));
		}
		
		wp_enqueue_style('log-css', plugin_dir_url(__FILE__) . 'assets/css/style.css', true, '1.0');
		wp_register_style("jquery-ui-css", plugin_dir_url(__FILE__) . "assets/css/jquery-ui-1.10.4.custom.css");
		wp_enqueue_style("jquery-ui-css");

		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-tabs');
		
		// We could let the download happen at the end of the page, but this way we get to give some
		// feedback to the users about the result.
		if( $WP_Statistics->get_option('update_geoip') == true ) {
			echo wp_statistics_download_geoip();
		}
		
		include_once dirname( __FILE__ ) . "/includes/settings/wps-settings.php";
	}