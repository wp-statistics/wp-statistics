<?php

/**
 * Class WP_Statistics_Admin_Pages
 */
class WP_Statistics_Admin_Pages {

	/**
	 * Load Overview Page
	 */
	static function overview() {
		global $WP_Statistics;

		// Right side "wide" widgets
		if ( $WP_Statistics->get_option( 'visits' ) ) {
			add_meta_box(
				'wps_hits_postbox',
				__( 'Hit Statistics', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'hits' )
			);
		}

		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			add_meta_box(
				'wps_top_visitors_postbox',
				__( 'Top Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'top.visitors' )
			);
			add_meta_box(
				'wps_search_postbox',
				__( 'Search Engine Referrals', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'search' )
			);
			add_meta_box(
				'wps_words_postbox',
				__( 'Top Searched Phrases (30 Days)', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'searched.phrases' )
			);
			add_meta_box(
				'wps_words_postbox',
				__( 'Latest Search Words', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'words' )
			);
			add_meta_box(
				'wps_recent_postbox',
				__( 'Recent Visitors', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'recent' )
			);

			if ( $WP_Statistics->get_option( 'geoip' ) ) {
				add_meta_box(
					'wps_map_postbox',
					__( 'Today\'s Visitors Map', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					$WP_Statistics->menu_slugs['overview'],
					'normal',
					null,
					array( 'widget' => 'map' )
				);
			}
		}

		if ( $WP_Statistics->get_option( 'pages' ) ) {
			add_meta_box(
				'wps_pages_postbox',
				__( 'Top 10 Pages', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'normal',
				null,
				array( 'widget' => 'pages' )
			);
		}

		// Left side "thin" widgets.
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			add_meta_box(
				'wps_summary_postbox',
				__( 'Summary', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'side',
				null,
				array( 'widget' => 'summary' )
			);
			add_meta_box(
				'wps_browsers_postbox',
				__( 'Browsers', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'side',
				null,
				array( 'widget' => 'browsers' )
			);
			add_meta_box(
				'wps_referring_postbox',
				__( 'Top Referring Sites', 'wp-statistics' ),
				'wp_statistics_generate_overview_postbox_contents',
				$WP_Statistics->menu_slugs['overview'],
				'side',
				null,
				array( 'widget' => 'referring' )
			);

			if ( $WP_Statistics->get_option( 'geoip' ) ) {
				add_meta_box(
					'wps_countries_postbox',
					__( 'Top 10 Countries', 'wp-statistics' ),
					'wp_statistics_generate_overview_postbox_contents',
					$WP_Statistics->menu_slugs['overview'],
					'side',
					null,
					array( 'widget' => 'countries' )
				);
			}
		}
	}

	/**
	 * Plugins
	 */
	static function plugins() {
		// Load our CSS to be used.
		wp_enqueue_style(
			'wpstatistics-admin-css',
			WP_Statistics::$reg['plugin-url'] . 'assets/css/admin.css',
			true,
			WP_Statistics::$reg['version']
		);

		// Activate or deactivate the selected plugin
		if ( isset( $_GET['action'] ) ) {
			if ( $_GET['action'] == 'activate' ) {
				$result = activate_plugin( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					wp_statistics_admin_notice_result( 'error', $result->get_error_message() );
				} else {
					wp_statistics_admin_notice_result( 'success', __( 'Add-On activated.', 'wp-statistics' ) );
				}
			}
			if ( $_GET['action'] == 'deactivate' ) {
				$result = deactivate_plugins( $_GET['plugin'] . '/' . $_GET['plugin'] . '.php' );
				if ( is_wp_error( $result ) ) {
					wp_statistics_admin_notice_result( 'error', $result->get_error_message() );
				} else {
					wp_statistics_admin_notice_result( 'success', __( 'Add-On deactivated.', 'wp-statistics' ) );
				}
			}
		}
		$response      = wp_remote_get( 'https://wp-statistics.com/wp-json/plugin/addons' );
		$response_code = wp_remote_retrieve_response_code( $response );
		$error         = null;
		$plugins       = array();
		// Check response
		if ( is_wp_error( $response ) ) {
			$error = $response->get_error_message();
		} else {
			if ( $response_code == '200' ) {
				$plugins = json_decode( $response['body'] );
			} else {
				$error = $response['body'];
			}
		}
		include WP_Statistics::$reg['plugin-dir'] . 'includes/templates/plugins.php';
	}

	/**
	 * Donate
	 */
	static function donate() {
		echo "<script>window.location.href='http://wp-statistics.com/donate';</script>";
	}

	/**
	 * Loads the optimization page code.
	 */
	static function optimization() {
		GLOBAL $wpdb, $WP_Statistics;

		// Check the current user has the rights to be here.
		if ( ! current_user_can(
			wp_statistics_validate_capability(
				$WP_Statistics->get_option(
					'manage_capability',
					'manage_options'
				)
			)
		)
		) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
		// during the creation of the class.  Instead load them now that the user exists.
		$WP_Statistics->load_user_options();

		// Load our JS to be used.
		wp_enqueue_script(
			'wp-statistics-admin-js',
			WP_Statistics::$reg['plugin-url'] . 'assets/js/admin.js',
			array( 'jquery' ),
			'1.0'
		);

		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css', true, WP_Statistics::$reg['version'] );
		}

		// Get the row count for each of the tables, we'll use this later on in the wps_optimization.php file.
		$result['useronline'] = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_useronline`" );
		$result['visit']      = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_visit`" );
		$result['visitor']    = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_visitor`" );
		$result['exclusions'] = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_exclusions`" );
		$result['pages']      = $wpdb->get_var( "SELECT COUNT(uri) FROM `{$wpdb->prefix}statistics_pages`" );
		$result['historical'] = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_historical`" );
		$result['search']     = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_search`" );

		include WP_Statistics::$reg['plugin-dir'] . "includes/optimization/wps-optimization.php";
	}

	/**
	 * This function displays the HTML for the settings page.
	 */
	static function settings() {
		global $WP_Statistics;

		// Check the current user has the rights to be here.
		if ( ! current_user_can(
			wp_statistics_validate_capability(
				$WP_Statistics->get_option(
					'read_capability',
					'manage_options'
				)
			)
		)
		) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
		// during the creation of the class.  Instead load them now that the user exists.
		$WP_Statistics->load_user_options();

		// Load our JS to be used.
		wp_enqueue_script(
			'wp-statistics-admin-js',
			WP_Statistics::$reg['plugin-url'] . 'assets/js/admin.js',
			array( 'jquery' ),
			'1.0'
		);

		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css', true, '1.1' );
		}

		// We could let the download happen at the end of the page, but this way we get to give some
		// feedback to the users about the result.
		if ( $WP_Statistics->get_option( 'update_geoip' ) == true ) {
			echo WP_Statistics_Updates::download_geoip();
		}

		include WP_Statistics::$reg['plugin-dir'] . "includes/settings/wps-settings.php";
	}

	/**
	 * @param string $log_type Log Type
	 */
	static function log( $log_type = "" ) {
		global $wpdb, $WP_Statistics, $plugin_page;

		switch ( $plugin_page ) {
			case WP_Statistics::$page['browser']:
				$log_type = 'all-browsers';

				break;
			case WP_Statistics::$page['countries']:
				$log_type = 'top-countries';

				break;
			case WP_Statistics::$page['exclusions']:
				$log_type = 'exclusions';

				break;
			case WP_Statistics::$page['hits']:
				$log_type = 'hit-statistics';

				break;
			case WP_Statistics::$page['online']:
				$log_type = 'online';

				break;
			case WP_Statistics::$page['pages']:
				$log_type = 'top-pages';

				break;
			case WP_Statistics::$page['categories']:
				$log_type = 'categories';

				break;
			case WP_Statistics::$page['tags']:
				$log_type = 'tags';

				break;
			case WP_Statistics::$page['authors']:
				$log_type = 'authors';

				break;
			case WP_Statistics::$page['referrers']:
				$log_type = 'top-referring-site';

				break;
			case WP_Statistics::$page['searched-phrases']:
				$log_type = 'searched-phrases';

				break;
			case WP_Statistics::$page['searches']:
				$log_type = 'search-statistics';

				break;
			case WP_Statistics::$page['words']:
				$log_type = 'last-all-search';

				break;
			case WP_Statistics::$page['top-visitors']:
				$log_type = 'top-visitors';

				break;
			case WP_Statistics::$page['visitors']:
				$log_type = 'last-all-visitor';

				break;
			default:
				$log_type = "";
		}

		// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
		// during the creation of the class.  Instead load them now that the user exists.
		$WP_Statistics->load_user_options();

		// We allow for a get style variable to be passed to define which function to use.
		if ( $log_type == "" && array_key_exists( 'type', $_GET ) ) {
			$log_type = $_GET['type'];
		}

		// Verify the user has the rights to see the statistics.
		if ( ! current_user_can(
			wp_statistics_validate_capability(
				$WP_Statistics->get_option(
					'read_capability',
					'manage_option'
				)
			)
		)
		) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// We want to make sure the tables actually exist before we blindly start access them.
		$result = $wpdb->query(
			"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline' OR `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'"
		);

		if ( $result != 7 ) {
			$get_bloginfo_url = get_admin_url() .
			                    "admin.php?page=" .
			                    WP_Statistics::$page['optimization'] .
			                    "&tab=database";

			$missing_tables = array();

			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visitor';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visit'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_visit';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_exclusions'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_exclusions';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_historical';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_useronline'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_useronline';
			}
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_pages'" );
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_pages';
			}
			$result = $wpdb->query(
				"SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_search'"
			);
			if ( $result != 1 ) {
				$missing_tables[] = $wpdb->prefix . 'statistics_search';
			}

			wp_die(
				'<div class="error"><p>' . sprintf(
					__(
						'The following plugin table(s) do not exist in the database, please re-run the %s install routine %s: ',
						'wp-statistics'
					),
					'<a href="' . $get_bloginfo_url . '">',
					'</a>'
				) . implode( ', ', $missing_tables ) . '</p></div>'
			);
		}

		// Load the postbox script that provides the widget style boxes.
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'wp-lists' );
		wp_enqueue_script( 'postbox' );

		// Load the css we use for the statistics pages.
		wp_enqueue_style(
			'wpstatistics-log-css',
			WP_Statistics::$reg['plugin-url'] . 'assets/css/log.css',
			true,
			WP_Statistics::$reg['version']
		);
		wp_enqueue_style(
			'wpstatistics-pagination-css',
			WP_Statistics::$reg['plugin-url'] . 'assets/css/pagination.css',
			true,
			WP_Statistics::$reg['version']
		);

		// Don't forget the right to left support.
		if ( is_rtl() ) {
			wp_enqueue_style(
				'wpstatistics-rtl-css',
				WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css',
				true,
				WP_Statistics::$reg['version']
			);
		}

		// The different pages have different files to load.
		switch ( $log_type ) {
			case 'all-browsers':
			case 'top-countries':
			case 'hit-statistics':
			case 'search-statistics':
			case 'exclusions':
			case 'online':
			case 'top-visitors':
			case 'categories':
			case 'tags':
			case 'authors':
				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/' . $log_type . '.php';
				break;
			case 'last-all-search':
				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/last-search.php';

				break;
			case 'last-all-visitor':
				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/last-visitor.php';

				break;
			case 'top-referring-site':
				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/top-referring.php';

				break;
			case 'searched-phrases':
				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/searched-phrases.php';

				break;
			case 'top-pages':
				// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
				if ( array_key_exists( 'page-id', $_GET ) || array_key_exists( 'page-uri', $_GET ) || array_key_exists( 'prepage', $_GET ) ) {
					include WP_Statistics::$reg['plugin-dir'] . 'includes/log/page-statistics.php';
				} else {
					include WP_Statistics::$reg['plugin-dir'] . 'includes/log/top-pages.php';
				}

				break;
			default:
				wp_enqueue_style(
					'wpstatistics-jqvmap-css',
					WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jqvmap.css',
					true,
					'1.5.1'
				);
				wp_enqueue_script(
					'wpstatistics-jquery-vmap',
					WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jquery.vmap.js',
					true,
					'1.5.1'
				);
				wp_enqueue_script(
					'wpstatistics-jquery-vmap-world',
					WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/maps/jquery.vmap.world.js',
					true,
					'1.5.1'
				);

				// Load our custom widgets handling javascript.
				wp_enqueue_script( 'wp_statistics_log', WP_Statistics::$reg['plugin-url'] . 'assets/js/log.js' );

				include WP_Statistics::$reg['plugin-dir'] . 'includes/log/log.php';

				break;
		}
	}

}