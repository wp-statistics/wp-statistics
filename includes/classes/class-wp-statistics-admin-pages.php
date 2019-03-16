<?php

/**
 * Class WP_Statistics_Admin_Pages
 */
class WP_Statistics_Admin_Pages {

	//Transient For Show Notice Setting
	public static $setting_notice = '_show_notice_wp_statistics';

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

		//Left Show User online table
		if ( $WP_Statistics->get_option( 'useronline' ) ) {
			add_meta_box( 'wps_users_online_postbox', __( 'Online Users', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'users_online' ) );
		}
	}


	/**
	 * Check in admin page
	 *
	 * @param $page | For Get List @see \WP_STATISTICS\WP_Statistics::$page
	 * @return bool
	 */
	public static function in_page( $page ) {
		global $pagenow;

		//Check is custom page
		if ( $pagenow == "admin.php" and isset( $_REQUEST['page'] ) and $_REQUEST['page'] == WP_Statistics::$page[ $page ] ) {
			return true;
		}

		return false;
	}

	/**
	 * Show Page title
	 * @param string $title
	 */
	public static function show_page_title( $title = '' ) {

		//Check if $title not Set
		if ( empty( $title ) and function_exists( 'get_admin_page_title' ) ) {
			$title = get_admin_page_title();
		}

		//show Page title
		echo '<img src="' . plugins_url( 'wp-statistics/assets/images/' ) . '/title-logo.png" class="wps_page_title"><h2 class="wps_title">' . $title . '</h2>';

		//do_action after wp_statistics
		do_action( 'wp_statistics_after_title' );
	}

	/**
	 * Get Admin Url
	 *
	 * @param null $page
	 * @param array $arg
	 * @area is_admin
	 * @return string
	 */
	public static function admin_url( $page = null, $arg = array() ) {

		//Check If Pages is in Wp-statistics
		if ( array_key_exists( $page, WP_Statistics::$page ) ) {
			$page = WP_Statistics::$page[ $page ];
		}

		return add_query_arg( array_merge( array( 'page' => $page ), $arg ), admin_url( 'admin.php' ) );
	}

	/**
	 * Show MetaBox button Refresh/Direct Button Link in Top of Meta Box
	 *
	 * @param string $export
	 * @return string
	 */
	public static function meta_box_button( $export = 'all' ) {

		//Prepare button
		$refresh = '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' . wp_statistics_icons( 'dashicons-update' ) . '<span class="screen-reader-text">' . __( 'Reload', 'wp-statistics' ) . '</span></button>';
		$more    = '<button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' . wp_statistics_icons( 'dashicons-external' ) . '<span class="screen-reader-text">' . __( 'More Details', 'wp-statistics' ) . '</span></button>';

		//Export
		if ( $export == 'all' ) {
			return $refresh . $more;
		} else {
			return $$export;
		}
	}

	/**
	 * Show Loading Meta Box
	 */
	public static function loading_meta_box() {
		$loading = '<div class="wps_loading_box"><img src=" ' . plugins_url( 'wp-statistics/assets/images/' ) . 'loading.svg" alt="' . __( 'Reloading...', 'wp-statistics' ) . '"></div>';
		return $loading;
	}

	/**
	 * Sanitize Email Subject
	 *
	 * @param $subject
	 * @return string|string[]|null
	 */
	public static function sanitize_mail_subject( $subject ) {

		# Remove Special character
		$str = preg_replace( '/[\'^£$%&*()}{@#~?><>,|=+¬]/', '', $subject );

		# Replace sequences of spaces with hyphen
		$str = preg_replace( '/  */', '-', $str );

		# You may also want to try this alternative:
		$str = preg_replace( '/\\s+/', ' ', $str );

		return $str;
	}

	/**
	 * Plugins
	 */
	static function plugins() {
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
		global $wpdb, $WP_Statistics;

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
		// during the creation of the class.  Instead load them now that the user exists.
		$WP_Statistics->load_user_options();

		// Get the row count for each of the tables, we'll use this later on in the wps_optimization.php file.
		$list_table = wp_statistics_db_table( 'all' );
		$result     = array();
		foreach ( $list_table as $tbl_key => $tbl_name ) {
			$result[ $tbl_name ] = $wpdb->get_var( "SELECT COUNT(*) FROM `$tbl_name`" );
		}

		include WP_Statistics::$reg['plugin-dir'] . "includes/optimization/wps-optimization.php";
	}

	/**
	 * This function displays the HTML for the settings page.
	 */
	static function settings() {
		global $WP_Statistics;

		// Check the current user has the rights to be here.
		if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) ) ) ) {
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
		}

		// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
		// during the creation of the class.  Instead load them now that the user exists.
		$WP_Statistics->load_user_options();

		// Check admin notices.
		if ( $WP_Statistics->get_option( 'admin_notices' ) == true ) {
			$WP_Statistics->update_option( 'disable_donation_nag', false );
			$WP_Statistics->update_option( 'disable_suggestion_nag', false );
		}

		include WP_Statistics::$reg['plugin-dir'] . "includes/settings/wps-settings.php";

		// We could let the download happen at the end of the page, but this way we get to give some
		// feedback to the users about the result.
		if ( $WP_Statistics->get_option( 'geoip' ) and isset( $_POST['update_geoip'] ) and isset( $_POST['geoip_name'] ) ) {

			//Check Geo ip Exist in Database
			if ( isset( WP_Statistics_Updates::$geoip[ $_POST['geoip_name'] ] ) ) {
				$result = WP_Statistics_Updates::download_geoip( $_POST['geoip_name'], "update" );

				if ( isset( $result['status'] ) and $result['status'] === false ) {
					add_filter( "wp_statistics_redirect_setting", function ( $redirect ) {
						$redirect = true;
						return $redirect;
					} );
				} else {
					echo $result['notice'];
				}
			}

		}

		//Enabled Geo ip Country Or City And download
		foreach ( array( "geoip" => "country", "geoip_city" => "city" ) as $geo_opt => $geo_name ) {
			if ( ! isset( $_POST['update_geoip'] ) and isset( $_POST[ 'wps_' . $geo_opt ] ) ) {

				//Check File Not Exist
				$upload_dir = wp_upload_dir();
				$file       = $upload_dir['basedir'] . '/wp-statistics/' . WP_Statistics_Updates::$geoip[ $geo_name ]['file'] . '.mmdb';
				if ( ! file_exists( $file ) ) {
					$result = WP_Statistics_Updates::download_geoip( $geo_name );
					if ( isset( $result['status'] ) and $result['status'] === false ) {
						add_filter( "wp_statistics_redirect_setting", function ( $redirect ) {
							$redirect = true;
							return $redirect;
						} );
					} else {
						echo $result['notice'];
					}
				}
			}
		}

		//Redirect Set Setting
		self::wp_statistics_redirect_setting();
	}

	/**
	 * Set Transient Notice
	 *
	 * @param $text
	 * @param string $type
	 */
	public static function set_admin_notice( $text, $type = 'error' ) {
		$get = get_transient( WP_Statistics_Admin_Pages::$setting_notice );
		if ( $get != false ) {
			$results = $get;
		}
		delete_transient( WP_Statistics_Admin_Pages::$setting_notice );
		$results[] = array( "text" => $text, "type" => $type );
		set_transient( WP_Statistics_Admin_Pages::$setting_notice, $results, 1 * HOUR_IN_SECONDS );
	}

	/**
	 * Notification Setting
	 */
	public static function wp_statistics_notice_setting() {
		global $pagenow, $WP_Statistics;

		//Show Notice By Plugin
		$get = get_transient( WP_Statistics_Admin_Pages::$setting_notice );
		if ( $get != false ) {
			foreach ( $get as $item ) {
				wp_statistics_admin_notice_result( $item['type'], $item['text'] );
			}
			delete_transient( WP_Statistics_Admin_Pages::$setting_notice );
		}

		//Check referring Spam Update
		if ( $pagenow == "admin.php" and isset( $_GET['page'] ) and $_GET['page'] == WP_Statistics::$page['settings'] and isset( $_GET['update-referrerspam'] ) ) {

			// Update referrer spam
			$update_spam = WP_Statistics_Updates::download_referrerspam();
			if ( $update_spam === true ) {
				wp_statistics_admin_notice_result( 'success', __( 'Updated Matomo Referrer Spam.', 'wp-statistics' ) );
			} else {
				wp_statistics_admin_notice_result( 'error', __( 'error in get referrer spam list. please try again.', 'wp-statistics' ) );
			}
		}
	}

	/**
	 * Redirect Jquery
	 * @param bool $redirect
	 */
	public static function wp_statistics_redirect_setting( $redirect = false ) {
		$redirect = apply_filters( 'wp_statistics_redirect_setting', $redirect );
		if ( $redirect === true ) {
			echo '<script>window.location.replace("' . ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]" . '");</script>';
		}
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

			$get_bloginfo_url = WP_Statistics_Admin_Pages::admin_url( 'optimization', array( 'tab' => 'database' ) );
			$missing_tables   = array();

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
						'The following plugin table(s) do not exist in the database, please re-run the %s install routine %s:',
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
		wp_enqueue_style( 'wpstatistics-log-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/log.css', true, WP_Statistics::$reg['version'] );
		wp_enqueue_style( 'wpstatistics-pagination-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/pagination.css', true, WP_Statistics::$reg['version'] );

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
			case 'top-pages':
				// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
				if ( array_key_exists( 'page-id', $_GET ) || array_key_exists( 'page-uri', $_GET ) || array_key_exists( 'prepage', $_GET ) ) {
					include WP_Statistics::$reg['plugin-dir'] . 'includes/log/page-statistics.php';
				} else {
					include WP_Statistics::$reg['plugin-dir'] . 'includes/log/top-pages.php';
				}

				break;
			default:
				if ( get_current_screen()->parent_base == WP_Statistics::$page['overview'] ) {

					wp_enqueue_style( 'wpstatistics-jqvmap-css', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jqvmap.css', true, '1.5.1' );
					wp_enqueue_script( 'wpstatistics-jquery-vmap', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jquery.vmap.js', true, '1.5.1' );
					wp_enqueue_script( 'wpstatistics-jquery-vmap-world', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.5.1' );

					// Load our custom widgets handling javascript.
					wp_enqueue_script( 'wp_statistics_log', WP_Statistics::$reg['plugin-url'] . 'assets/js/log.js' );

					include WP_Statistics::$reg['plugin-dir'] . 'includes/log/log.php';
				}

				break;
		}
	}

}