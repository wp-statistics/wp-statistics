<?php

/**
 * Class WP_Statistics_Admin
 */
class WP_Statistics_Admin {

	/**
	 * WP_Statistics_Admin constructor.
	 */
	public function __construct() {
		global $WP_Statistics;

		// Check to see if we're installed and are the current version.
		WP_Statistics::$installed_version = get_option( 'wp_statistics_plugin_version' );
		if ( WP_Statistics::$installed_version != WP_Statistics::$reg['version'] ) {
			new WP_Statistics_Install;
		}

		// If we've been flagged to remove all of the data, then do so now.
		if ( get_option( 'wp_statistics_removal' ) == 'true' ) {
			new WP_Statistics_Uninstall;
		}

		// If we've been removed, return without doing anything else.
		if ( get_option( 'wp_statistics_removal' ) == 'done' ) {
			add_action( 'admin_notices', array( $this, 'removal_admin_notice' ), 10, 2 );
			return;
		}

		//Show Admin Menu
		add_action( 'admin_menu', array( $this, 'menu' ) );
		if ( is_multisite() ) {
			add_action( 'network_admin_menu', 'WP_Statistics_Network_Admin::menu' );
		}

		//Load Script in Admin Area
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		//init Export Class
		new WP_Statistics_Export;

		//init Ajax Class
		new WP_Statistics_Ajax;

		//init Dashboard Widget
		new WP_Statistics_Dashboard;

		//Add Custom MetaBox in Wp-statistics Admin Page
		add_action( 'add_meta_boxes', 'WP_Statistics_Editor::add_meta_box' );

		// Display the admin notices if we should.
		if ( isset( $pagenow ) && array_key_exists( 'page', $_GET ) ) {
			if ( $pagenow == "admin.php" && substr( $_GET['page'], 0, 14 ) == 'wp-statistics/' ) {
				add_action( 'admin_notices', array( $this, 'not_enable' ) );
			}
		}

		//Change Plugin Action link in Plugin.php admin
		add_filter( 'plugin_action_links_' . plugin_basename( WP_Statistics::$reg['main-file'] ), array( $this, 'settings_links' ), 10, 2 );
		add_filter( 'plugin_row_meta', array( $this, 'add_meta_links' ), 10, 2 );

		//Add Column in Post Type Wp_List Table
		add_action( 'load-edit.php', array( $this, 'load_edit_init' ) );
		if ( $WP_Statistics->get_option( 'pages' ) && $WP_Statistics->get_option( 'hit_post_metabox' ) ) {
			add_action( 'post_submitbox_misc_actions', array( $this, 'post_init' ) );
		}

		//init ShortCode
		add_action( 'admin_init', 'WP_Statistics_Shortcode::shortcake' );

		// WP-Statistics welcome page hooks
		add_action( 'admin_menu', 'WP_Statistics_Welcome::menu' );
		add_action( 'upgrader_process_complete', 'WP_Statistics_Welcome::do_welcome', 10, 2 );
		add_action( 'admin_init', 'WP_Statistics_Welcome::init' );

		// Runs some scripts at the end of the admin panel inside the body tag
		add_action( 'admin_footer', array( $this, 'admin_footer_scripts' ) );

		// Load TinyMce Function
		new WP_Statistics_TinyMCE;

		// Add Notice Use cache plugin
		add_action( 'admin_notices', array( $this, 'notification_use_cache_plugin' ) );

		//Admin Notice Setting
		add_action( 'admin_notices', 'WP_Statistics_Admin_Pages::wp_statistics_notice_setting' );

		//Add Visitors Log Table
		add_action( 'admin_init', array( $this, 'register_visitors_log_tbl' ) );

		// Add Overview Ads
		add_action( 'load-toplevel_page_' . WP_Statistics::$page['overview'], array( $this, 'overview_page_ads' ) );

		//Check Require update page type in database
		WP_Statistics_Install::_init_page_type_updater();
	}

	/**
	 * Create a New Table Visitors Log in mysql
	 */
	public function register_visitors_log_tbl() {

		//Add Visitor RelationShip Table
		if ( WP_Statistics_Admin_Pages::in_page( 'settings' ) and isset( $_POST['wps_visitors_log'] ) and $_POST['wps_visitors_log'] == 1 ) {
			WP_Statistics_Install::setup_visitor_relationship_table();
		}

	}

	/**
	 * This adds a row after WP Statistics in the plugin page
	 * IF we've been removed via the settings page.
	 */
	public function removal_admin_notice() {
		$screen = get_current_screen();

		if ( 'plugins' !== $screen->id ) {
			return;
		}

		?>
        <div class="error">
            <p style="max-width:800px;"><?php
				echo '<p>' . __( 'WP Statistics has been removed, please disable and delete it.', 'wp-statistics' ) . '</p>';
				?></p>
        </div>
		<?php
	}

	/**
	 * OverView Page Ads
	 */
	public function overview_page_ads() {

		// Get Overview Ads
		$get_overview_ads = get_option( 'wp_statistics_overview_page_ads', false );

		// Check Expire or not exist
		if ( $get_overview_ads === false || ( is_array( $get_overview_ads ) and ( current_time( 'timestamp' ) >= ( $get_overview_ads['timestamp'] + WEEK_IN_SECONDS ) ) ) ) {

			// Check Exist
			$overview_ads = ( $get_overview_ads === false ? array() : $get_overview_ads );

			// Get New Ads from API
			$request = wp_remote_get( 'https://wp-statistics.com/wp-json/ads/overview', array( 'timeout' => 30 ) );
			if ( is_wp_error( $request ) ) {
				return;
			}

			// Get Json Data
			$data    = json_decode( wp_remote_retrieve_body( $request ), true );

			// Set new Timestamp
			$overview_ads['timestamp'] = current_time( 'timestamp' );

			// Set Ads
			$overview_ads['ads'] = ( empty( $data ) ? array( 'status' => 'no', 'ID' => 'none' ) : $data );

			// Set Last Viewed
			$overview_ads['view'] = ( isset( $get_overview_ads['view'] ) ? $get_overview_ads['view'] : '' );

			// Set Option
			update_option( 'wp_statistics_overview_page_ads', $overview_ads, 'no' );
		}
	}

	/**
	 * This function outputs error messages in the admin interface
	 * if the primary components of WP Statistics are enabled.
	 */
	public function not_enable() {
		global $WP_Statistics;

		// If the user had told us to be quite, do so.
		if ( ! $WP_Statistics->get_option( 'hide_notices' ) ) {

			// Check to make sure the current user can manage WP Statistics,
			// if not there's no point displaying the warnings.
			$manage_cap = wp_statistics_validate_capability(
				$WP_Statistics->get_option(
					'manage_capability',
					'manage_options'
				)
			);
			if ( ! current_user_can( $manage_cap ) ) {
				return;
			}


			$get_bloginfo_url = WP_Statistics_Admin_Pages::admin_url( 'settings' );

			$itemstoenable = array();
			if ( ! $WP_Statistics->get_option( 'useronline' ) ) {
				$itemstoenable[] = __( 'online user tracking', 'wp-statistics' );
			}
			if ( ! $WP_Statistics->get_option( 'visits' ) ) {
				$itemstoenable[] = __( 'hit tracking', 'wp-statistics' );
			}
			if ( ! $WP_Statistics->get_option( 'visitors' ) ) {
				$itemstoenable[] = __( 'visitor tracking', 'wp-statistics' );
			}
			if ( ! $WP_Statistics->get_option( 'geoip' ) && wp_statistics_geoip_supported() ) {
				$itemstoenable[] = __( 'geoip collection', 'wp-statistics' );
			}

			if ( count( $itemstoenable ) > 0 ) {
				echo '<div class="update-nag">' . sprintf( __( 'The following features are disabled, please go to %ssettings page%s and enable them: %s', 'wp-statistics' ), '<a href="' . $get_bloginfo_url . '">', '</a>', implode( __( ',', 'wp-statistics' ), $itemstoenable ) ) . '</div>';
			}


			$get_bloginfo_url = WP_Statistics_Admin_Pages::admin_url( 'optimization', array( 'tab' => 'database' ) );
			$dbupdatestodo    = array();

			if ( ! $WP_Statistics->get_option( 'search_converted' ) ) {
				$dbupdatestodo[] = __( 'search table', 'wp-statistics' );
			}

			// Check to see if there are any database changes the user hasn't done yet.
			$dbupdates = $WP_Statistics->get_option( 'pending_db_updates', false );

			// The database updates are stored in an array so loop thorugh it and output some notices.
			if ( is_array( $dbupdates ) ) {
				$dbstrings = array(
					'date_ip_agent' => __( 'countries database index', 'wp-statistics' ),
					'unique_date'   => __( 'visit database index', 'wp-statistics' ),
				);

				foreach ( $dbupdates as $key => $update ) {
					if ( $update == true ) {
						$dbupdatestodo[] = $dbstrings[ $key ];
					}
				}

				if ( count( $dbupdatestodo ) > 0 ) {
					echo '<div class="update-nag">' . sprintf( __( 'Database updates are required, please go to %soptimization page%s and update the following: %s', 'wp-statistics' ), '<a href="' . $get_bloginfo_url . '">', '</a>', implode( __( ',', 'wp-statistics' ), $dbupdatestodo ) ) . '</div>';
				}
			}
		}
	}

	/*
	 * Check User Active A cache Plugin in Wordpress
	 */
	static public function user_is_use_cache_plugin() {
		$use = array( 'status' => false, 'plugin' => '' );

		/* Wordpress core */
		if ( defined( 'WP_CACHE' ) && WP_CACHE ) {
			return array( 'status' => true, 'plugin' => 'core' );
		}

		/* WP Rocket */
		if ( function_exists( 'get_rocket_cdn_url' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Rocket' );
		}

		/* WP Super Cache */
		if ( function_exists( 'wpsc_init' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Super Cache' );
		}

		/* Comet Cache */
		if ( function_exists( '___wp_php_rv_initialize' ) ) {
			return array( 'status' => true, 'plugin' => 'Comet Cache' );
		}

		/* WP Fastest Cache */
		if ( class_exists( 'WpFastestCache' ) ) {
			return array( 'status' => true, 'plugin' => 'WP Fastest Cache' );
		}

		/* Cache Enabler */
		if ( defined( 'CE_MIN_WP' ) ) {
			return array( 'status' => true, 'plugin' => 'Cache Enabler' );
		}

		/* W3 Total Cache */
		if ( defined( 'W3TC' ) ) {
			return array( 'status' => true, 'plugin' => 'W3 Total Cache' );
		}

		return $use;
	}

	/*
	 * Show Notification Cache Plugin
	 */
	static public function notification_use_cache_plugin() {
		global $WP_Statistics;

		$screen = get_current_screen();

		if ( $screen->id == "toplevel_page_" . WP_Statistics::$page['overview'] or $screen->id == "statistics_page_" . WP_Statistics::$page['settings'] ) {
			$plugin = self::user_is_use_cache_plugin();

			if ( ! $WP_Statistics->get_option( 'use_cache_plugin' ) and $plugin['status'] === true ) {
				echo '<div class="notice notice-warning is-dismissible"><p>';

				$alert = sprintf( __( 'You Are Using %s Plugin in WordPress', 'wp-statistics' ), $plugin['plugin'] );
				if ( $plugin['plugin'] == "core" ) {
					$alert = __( 'WP_CACHE is Enable in Your WordPress', 'wp-statistics' );
				}

				echo $alert . ", " . sprintf( __( 'Please enable %1$sCache Setting%2$s in WP Statistics.', 'wp-statistics' ), '<a href="' . WP_Statistics_Admin_Pages::admin_url( 'settings' ) . '">', '</a>' );
				echo '</p></div>';
			}
		}

		// Test Rest Api is Active for Cache
		if ( $WP_Statistics->use_cache and $screen->id == "statistics_page_" . WP_Statistics::$page['settings'] ) {

			if ( false === ( $check_rest_api = get_transient( '_check_rest_api_wp_statistics' ) ) ) {

				$set_transient = true;
				$alert         = '<div class="notice notice-warning is-dismissible"><p>' . sprintf( __( 'Here is an error associated with Connecting WordPress Rest API, Please Flushing rewrite rules or activate wp rest api for performance WP-Statistics Plugin Cache / Go %1$sSettings->Permalinks%2$s', 'wp-statistics' ), '<a href="' . esc_url( admin_url( 'options-permalink.php' ) ) . '">', '</a>' ) . '</div>';
				$request       = wp_remote_post( path_join( get_rest_url(), WP_Statistics_Rest::route . '/' . WP_Statistics_Rest::func ), array(
					'method' => 'POST',
					'body'   => array( 'rest-api-wp-statistics' => 'wp-statistics' )
				) );
				if ( is_wp_error( $request ) ) {
					echo $alert;
					$set_transient = false;
				}
				$body = wp_remote_retrieve_body( $request );
				$data = json_decode( $body, true );
				if ( ! isset( $data['rest-api-wp-statistics'] ) and $set_transient === true ) {
					echo $alert;
					$set_transient = false;
				}

				if ( $set_transient === true ) {
					set_transient( '_check_rest_api_wp_statistics', array( "rest-api-wp-statistics" => "OK" ), 2 * HOUR_IN_SECONDS );
				}
			}

		}
	}

	/**
	 * Add a settings link to the plugin list.
	 *
	 * @param string $links Links
	 * @param string $file Not Used!
	 *
	 * @return string Links
	 */
	public function settings_links( $links, $file ) {
		global $WP_Statistics;

		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );
		if ( current_user_can( $manage_cap ) ) {
			array_unshift( $links, '<a href="' . WP_Statistics_Admin_Pages::admin_url( 'settings' ) . '">' . __( 'Settings', 'wp-statistics' ) . '</a>' );
		}

		return $links;
	}

	/**
	 * Add a WordPress plugin page and rating links to the meta information to the plugin list.
	 *
	 * @param string $links Links
	 * @param string $file File
	 *
	 * @return array Links
	 */
	public function add_meta_links( $links, $file ) {
		if ( $file == plugin_basename( WP_Statistics::$reg['main-file'] ) ) {
			$plugin_url = 'http://wordpress.org/plugins/wp-statistics/';

			$links[]  = '<a href="' . $plugin_url . '" target="_blank" title="' . __( 'Click here to visit the plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Visit WordPress.org page', 'wp-statistics' ) . '</a>';
			$rate_url = 'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post';
			$links[]  = '<a href="' . $rate_url . '" target="_blank" title="' . __( 'Click here to rate and review this plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Rate this plugin', 'wp-statistics' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Call the add/render functions at the appropriate times.
	 */
	public function load_edit_init() {
		global $WP_Statistics;

		$read_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) );

		if ( current_user_can( $read_cap ) && $WP_Statistics->get_option( 'pages' ) && ! $WP_Statistics->get_option( 'disable_column' ) ) {
			$post_types = WP_Statistics_Editor::get_list_post_type();
			foreach ( $post_types as $type ) {
				add_action( 'manage_' . $type . '_posts_columns', 'WP_Statistics_Admin::add_column', 10, 2 );
				add_action( 'manage_' . $type . '_posts_custom_column', 'WP_Statistics_Admin::render_column', 10, 2 );
			}
		}
	}

	/**
	 * Add a custom column to post/pages for hit statistics.
	 *
	 * @param array $columns Columns
	 *
	 * @return array Columns
	 */
	static function add_column( $columns ) {
		$columns['wp-statistics'] = __( 'Hits', 'wp-statistics' );

		return $columns;
	}

	/**
	 * Render the custom column on the post/pages lists.
	 *
	 * @param string $column_name Column Name
	 * @param string $post_id Post ID
	 */
	static function render_column( $column_name, $post_id ) {
		if ( $column_name == 'wp-statistics' ) {
			echo "<a href='" . WP_Statistics_Admin_Pages::admin_url( 'pages', array( 'page-id' => $post_id ) ) . "'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
		}
	}

	/**
	 * Add the hit count to the publish widget in the post/pages editor.
	 */
	public function post_init() {
		global $post;

		$id = $post->ID;
		echo "<div class='misc-pub-section'>" . __( 'WP Statistics - Hits', 'wp-statistics' ) . ": <b><a href='" . WP_Statistics_Admin_Pages::admin_url( 'pages', array( 'page-id' => $id ) ) . "'>" . wp_statistics_pages( 'total', "", $id ) . "</a></b></div>";
	}

	/**
	 * This function adds the primary menu to WordPress.
	 */
	public function menu() {
		global $WP_Statistics;

		// Get the read/write capabilities required to view/manage the plugin as set by the user.
		$read_cap   = wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) );
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

		/**
		 * List of WP-Statistics Admin Menu
		 *
		 * --- Array Arg -----
		 * name       : Menu name
		 * title      : Page title / if not exist [title == name]
		 * cap        : min require capability @default $read_cap
		 * icon       : Wordpress DashIcon name
		 * method     : method that call in page @default log
		 * sub        : if sub menu , add main menu slug
		 * page_url   : link of Slug Url Page @see WP_Statistics::$page
		 * break      : add new line after sub menu if break key == true
		 * require    : the Condition From Wp-statistics Option if == true for show admin menu
		 *
		 */
		$list = array(
			'top'          => array(
				'title'    => __( 'Statistics', 'wp-statistics' ),
				'page_url' => 'overview',
				'method'   => 'log',
				'icon'     => 'dashicons-chart-pie',
			),
			'overview'     => array(
				'sub'      => 'overview',
				'title'    => __( 'Overview', 'wp-statistics' ),
				'page_url' => 'overview',
			),
			'hits'         => array(
				'require'  => array( 'visits' ),
				'sub'      => 'overview',
				'title'    => __( 'Hits', 'wp-statistics' ),
				'page_url' => 'hits',
			),
			'online'       => array(
				'require'  => array( 'useronline' ),
				'sub'      => 'overview',
				'title'    => __( 'Online', 'wp-statistics' ),
				'page_url' => 'online',
			),
			'referrers'    => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Referrers', 'wp-statistics' ),
				'page_url' => 'referrers',
			),
			'words'        => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Search Words', 'wp-statistics' ),
				'page_url' => 'words',
			),
			'searches'     => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Search Engines', 'wp-statistics' ),
				'page_url' => 'searches',
			),
			'pages'        => array(
				'require'  => array( 'pages' ),
				'sub'      => 'overview',
				'title'    => __( 'Pages', 'wp-statistics' ),
				'page_url' => 'pages',
			),
			'visitors'     => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Visitors', 'wp-statistics' ),
				'page_url' => 'visitors',
			),
			'countries'    => array(
				'require'  => array( 'geoip', 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Countries', 'wp-statistics' ),
				'page_url' => 'countries',
			),
			'categories'   => array(
				'require'  => array( 'pages' ),
				'sub'      => 'overview',
				'title'    => __( 'Categories', 'wp-statistics' ),
				'page_url' => 'categories',
			),
			'tags'         => array(
				'require'  => array( 'pages' ),
				'sub'      => 'overview',
				'title'    => __( 'Tags', 'wp-statistics' ),
				'page_url' => 'tags',
			),
			'authors'      => array(
				'require'  => array( 'pages' ),
				'sub'      => 'overview',
				'title'    => __( 'Authors', 'wp-statistics' ),
				'page_url' => 'authors',
			),
			'browsers'     => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Browsers', 'wp-statistics' ),
				'page_url' => 'browser',
			),
			'top.visotors' => array(
				'require'  => array( 'visitors' ),
				'sub'      => 'overview',
				'title'    => __( 'Top Visitors Today', 'wp-statistics' ),
				'page_url' => 'top-visitors',
			),
			'exclusions'   => array(
				'require'  => array( 'record_exclusions' ),
				'sub'      => 'overview',
				'title'    => __( 'Exclusions', 'wp-statistics' ),
				'page_url' => 'exclusions',
				'break'    => true,
			),
			'optimize'     => array(
				'sub'      => 'overview',
				'title'    => __( 'Optimization', 'wp-statistics' ),
				'cap'      => $manage_cap,
				'page_url' => 'optimization',
				'method'   => 'optimization'
			),
			'settings'     => array(
				'sub'      => 'overview',
				'title'    => __( 'Settings', 'wp-statistics' ),
				'cap'      => $manage_cap,
				'page_url' => 'settings',
				'method'   => 'settings'
			),
			'plugins'      => array(
				'sub'      => 'overview',
				'title'    => __( 'Add-Ons', 'wp-statistics' ),
				'name'     => '<span class="wps-text-warning">' . __( 'Add-Ons', 'wp-statistics' ) . '</span>',
				'page_url' => 'plugins',
				'method'   => 'plugins'
			),
			'donate'       => array(
				'sub'      => 'overview',
				'title'    => __( 'Donate', 'wp-statistics' ),
				'name'     => '<span class="wps-text-success">' . __( 'Donate', 'wp-statistics' ) . '</span>',
				'page_url' => 'donate',
				'method'   => 'donate'
			)
		);

		//Show Admin Menu List
		foreach ( $list as $key => $menu ) {

			//Check Default variable
			$capability = $read_cap;
			$method     = 'log';
			$name       = $menu['title'];
			if ( array_key_exists( 'cap', $menu ) ) {
				$capability = $menu['cap'];
			}
			if ( array_key_exists( 'method', $menu ) ) {
				$method = $menu['method'];
			}
			if ( array_key_exists( 'name', $menu ) ) {
				$name = $menu['name'];
			}

			//Check if SubMenu or Main Menu
			if ( array_key_exists( 'sub', $menu ) ) {

				//Check Conditions For Show Menu
				if ( wp_statistics_check_option_require( $menu ) === true ) {
					$WP_Statistics->menu_slugs[ $key ] = add_submenu_page( WP_Statistics::$page[ $menu['sub'] ], $menu['title'], $name, $capability, WP_Statistics::$page[ $menu['page_url'] ], 'WP_Statistics_Admin_Pages::' . $method );
				}

				//Check if add Break Line
				if ( array_key_exists( 'break', $menu ) ) {
					$WP_Statistics->menu_slugs[ 'break_' . $key ] = add_submenu_page( WP_Statistics::$page[ $menu['sub'] ], '', '', $capability, 'wps_break_menu', 'WP_Statistics_Admin_Pages::' . $method );
				}
			} else {
				$WP_Statistics->menu_slugs[ $key ] = add_menu_page( $menu['title'], $name, $capability, WP_Statistics::$page[ $menu['page_url'] ], "WP_Statistics_Admin_Pages::" . $method, $menu['icon'] );
			}
		}

		// Add action to load the meta boxes to the overview page.
		add_action( 'load-' . $WP_Statistics->menu_slugs['overview'], 'WP_Statistics_Admin_Pages::overview' );
	}

	/**
	 * Enqueue Scripts in Admin Area
	 */
	public function enqueue_scripts() {
		global $pagenow, $WP_Statistics;

		// Load our CSS to be used.
		wp_enqueue_style( 'wpstatistics-admin-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/admin.css', true, WP_Statistics::$reg['version'] );
		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css', true, WP_Statistics::$reg['version'] );
		}

		//Load Admin Js
		wp_enqueue_script( 'wp-statistics-admin-js', WP_Statistics::$reg['plugin-url'] . 'assets/js/admin.js', array( 'jquery' ), WP_Statistics::$reg['version'] );

		//Load Chart Js
		$load_in_footer = false;
		$load_chart     = false;

		//Load in Setting Page
		$pages_required_chart = array(
			'wps_overview_page',
			'wps_browsers_page',
			'wps_hits_page',
			'wps_pages_page',
			'wps_categories_page',
			'wps_tags_page',
			'wps_authors_page',
			'wps_searches_page',
		);
		if ( isset( $_GET['page'] ) and array_search( $_GET['page'], $pages_required_chart ) !== false ) {
			$load_chart = true;
		}

		//Load in Post Page
		if ( $pagenow == "post.php" and $WP_Statistics->get_option( 'hit_post_metabox' ) ) {
			$load_chart = true;
		}

		if ( $load_chart === true ) {
			wp_enqueue_script( 'wp-statistics-chart-js', WP_Statistics::$reg['plugin-url'] . 'assets/js/Chart.bundle.min.js', false, '2.7.3', $load_in_footer );
		}

	}

	/**
	 * Admin footer scripts
	 */
	public function admin_footer_scripts() {
		global $WP_Statistics;

		// Check to see if the GeoIP database needs to be downloaded and do so if required.
		if ( $WP_Statistics->get_option( 'update_geoip' ) ) {
			foreach ( WP_Statistics_Updates::$geoip as $geoip_name => $geoip_array ) {
				WP_Statistics_Updates::download_geoip( $geoip_name, "update" );
			}
		}

		// Check to see if the referrer spam database needs to be downloaded and do so if required.
		if ( $WP_Statistics->get_option( 'update_referrerspam' ) ) {
			WP_Statistics_Updates::download_referrerspam();
		}

		if ( $WP_Statistics->get_option( 'send_upgrade_email' ) ) {
			$WP_Statistics->update_option( 'send_upgrade_email', false );

			$blogname  = get_bloginfo( 'name' );
			$blogemail = get_bloginfo( 'admin_email' );

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( $WP_Statistics->get_option( 'email_list' ) == '' ) {
				$WP_Statistics->update_option( 'email_list', $blogemail );
			}

			wp_mail( $WP_Statistics->get_option( 'email_list' ), sprintf( __( 'WP Statistics %s installed on', 'wp-statistics' ), WP_Statistics::$reg['version'] ) . ' ' . $blogname, __( 'Installation/upgrade complete!', 'wp-statistics' ), $headers );
		}
	}

}