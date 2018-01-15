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

		add_action( 'admin_init', 'WP_Statistics_Admin::export_data', 9 );

		add_action( 'wp_dashboard_setup', 'WP_Statistics_Dashboard::widget_load' );
		add_action( 'admin_footer', 'WP_Statistics_Dashboard::inline_javascript' );
		add_action( 'add_meta_boxes', 'WP_Statistics_Editor::add_meta_box' );
		new WP_Statistics_Ajax;

		// Display the admin notices if we should.
		if ( isset( $pagenow ) && array_key_exists( 'page', $_GET ) ) {
			if ( $pagenow == "admin.php" && substr( $_GET['page'], 0, 14 ) == 'wp-statistics/' ) {
				add_action( 'admin_notices', 'WP_Statistics_Admin::not_enable' );
			}
		}

		add_filter(
			'plugin_action_links_' . plugin_basename( WP_Statistics::$reg['main-file'] ),
			'WP_Statistics_Admin::settings_links',
			10,
			2
		);

		add_filter( 'plugin_row_meta', 'WP_Statistics_Admin::add_meta_links', 10, 2 );

		add_action( 'load-edit.php', 'WP_Statistics_Admin::load_edit_init' );

		if ( $WP_Statistics->get_option( 'pages' ) && ! $WP_Statistics->get_option( 'disable_column' ) ) {
			add_action( 'post_submitbox_misc_actions', 'WP_Statistics_Admin::post_init' );
		}

		add_action( 'admin_menu', 'WP_Statistics_Admin::menu' );

		if ( is_multisite() ) {
			add_action( 'network_admin_menu', 'WP_Statistics_Network_Admin::menu' );
		}

		add_action( 'admin_enqueue_scripts', 'WP_Statistics_Admin::enqueue_scripts' );
		add_action( 'admin_init', 'WP_Statistics_Shortcode::shortcake' );

		// WP-Statistics welcome page hooks
		add_action( 'admin_menu', 'WP_Statistics_Welcome::menu' );
		add_action( 'upgrader_process_complete', 'WP_Statistics_Welcome::do_welcome', 10, 2 );
		add_action( 'admin_init', 'WP_Statistics_Welcome::init' );

        // Runs some scripts at the end of the admin panel inside the body tag.
        add_action('admin_footer', array($this, 'admin_footer_scripts'));
	}

	/**
	 * Set the headers to download the export file and then stop running WordPress.
	 */
	static function export_data() {

		if ( array_key_exists( 'wps_export', $_POST ) ) {
			if ( ! function_exists( 'wp_statistics_export_data' ) ) {
				include WP_Statistics::$reg['plugin-dir'] . 'includes/functions/export.php';
			}
			wp_statistics_export_data();
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

				echo '<p>';
				echo __( 'WP Statistics has been removed, please disable and delete it.', 'wp-statistics' );
				echo '</p>';
				?></p>
        </div>
		<?php
	}

	/**
	 * This function outputs error messages in the admin interface
	 * if the primary components of WP Statistics are enabled.
	 */
	static function not_enable() {
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

			$get_bloginfo_url = get_admin_url() . "admin.php?page=" . WP_Statistics::$page['settings'];

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
				echo '<div class="update-nag">' . sprintf(
						__(
							'The following features are disabled, please go to %ssettings page%s and enable them: %s',
							'wp-statistics'
						),
						'<a href="' . $get_bloginfo_url . '">',
						'</a>',
						implode( __( ',', 'wp-statistics' ), $itemstoenable )
					) . '</div>';
			}

			$get_bloginfo_url = get_admin_url() .
			                    "admin.php?page=" .
			                    WP_Statistics::$page['optimization'] .
			                    "&tab=database";

			$dbupdatestodo = array();

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
					echo '<div class="update-nag">' . sprintf(
							__(
								'Database updates are required, please go to %soptimization page%s and update the following: %s',
								'wp-statistics'
							),
							'<a href="' . $get_bloginfo_url . '">',
							'</a>',
							implode( __( ',', 'wp-statistics' ), $dbupdatestodo )
						) . '</div>';
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
	static function settings_links( $links, $file ) {
		global $WP_Statistics;

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			array_unshift(
				$links,
				'<a href="' . admin_url( 'admin.php?page=' . WP_Statistics::$page['settings'] ) . '">' . __(
					'Settings',
					'wp-statistics'
				) . '</a>'
			);
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
	static function add_meta_links( $links, $file ) {
		if ( $file == plugin_basename( WP_Statistics::$reg['main-file'] ) ) {
			$plugin_url = 'http://wordpress.org/plugins/wp-statistics/';

			$links[] = '<a href="' . $plugin_url . '" target="_blank" title="' . __(
					'Click here to visit the plugin on WordPress.org',
					'wp-statistics'
				) . '">' . __( 'Visit WordPress.org page', 'wp-statistics' ) . '</a>';

			$rate_url = 'https://wordpress.org/support/plugin/wp-statistics/reviews/?rate=5#new-post';
			$links[]  = '<a href="' . $rate_url . '" target="_blank" title="' . __(
					'Click here to rate and review this plugin on WordPress.org',
					'wp-statistics'
				) . '">' . __( 'Rate this plugin', 'wp-statistics' ) . '</a>';
		}

		return $links;
	}

	/**
	 * Call the add/render functions at the appropriate times.
	 */
	static function load_edit_init() {
		GLOBAL $WP_Statistics;

		$read_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'read_capability', 'manage_options' )
		);

		if ( current_user_can( $read_cap ) && $WP_Statistics->get_option( 'pages' ) && ! $WP_Statistics->get_option(
				'disable_column'
			)
		) {
			$post_types = (array) get_post_types( array( 'show_ui' => true ), 'object' );

			foreach ( $post_types as $type ) {
				add_action( 'manage_' . $type->name . '_posts_columns', 'WP_Statistics_Admin::add_column', 10, 2 );
				add_action(
					'manage_' . $type->name . '_posts_custom_column',
					'WP_Statistics_Admin::render_column',
					10,
					2
				);
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
			echo "<a href='" .
			     get_admin_url() .
			     "admin.php?page=" .
			     WP_Statistics::$page['pages'] .
			     "&page-id={$post_id}'>" .
			     wp_statistics_pages( 'total', "", $post_id ) .
			     "</a>";
		}
	}

	/**
	 * Add the hit count to the publish widget in the post/pages editor.
	 */
	static function post_init() {
		global $post;

		$id = $post->ID;

		echo "<div class='misc-pub-section'>" .
		     __( 'WP Statistics - Hits', 'wp-statistics' ) .
		     ": <b><a href='" .
		     get_admin_url() .
		     "admin.php?page=" .
		     WP_Statistics::$page['pages'] .
		     "&page-id={$id}'>" .
		     wp_statistics_pages( 'total', "", $id ) .
		     "</a></b></div>";
	}

	/**
	 * This function adds the primary menu to WordPress.
	 */
	static function menu() {
		GLOBAL $WP_Statistics;

		// Get the read/write capabilities required to view/manage the plugin as set by the user.
		$read_cap   = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'read_capability', 'manage_options' )
		);
		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		// Add the top level menu.
		$WP_Statistics->menu_slugs['top'] = add_menu_page(
			__( 'Statistics', 'wp-statistics' ),
			__( 'Statistics', 'wp-statistics' ),
			$read_cap,
			WP_Statistics::$page['overview'],
			'WP_Statistics_Admin_Pages::log',
			'dashicons-chart-pie'
		);

		// Add the sub items.
		$WP_Statistics->menu_slugs['overview'] = add_submenu_page(
			WP_Statistics::$page['overview'],
			__( 'Overview', 'wp-statistics' ),
			__( 'Overview', 'wp-statistics' ),
			$read_cap,
			WP_Statistics::$page['overview'],
			'WP_Statistics_Admin_Pages::log'
		);
		if ( $WP_Statistics->get_option( 'visits' ) ) {
			$WP_Statistics->menu_slugs['hits'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Hits', 'wp-statistics' ),
				__( 'Hits', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['hits'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'useronline' ) ) {
			$WP_Statistics->menu_slugs['online'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Online', 'wp-statistics' ),
				__( 'Online', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['online'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['referrers'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Referrers', 'wp-statistics' ),
				__( 'Referrers', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['referrers'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['words'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Search Words', 'wp-statistics' ),
				__( 'Search Words', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['words'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['searched.phrases'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Top Search Words', 'wp-statistics' ),
				__( 'Top Search Words', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['searched-phrases'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['searches'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Search Engines', 'wp-statistics' ),
				__( 'Search Engines', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['searches'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'pages' ) ) {
			$WP_Statistics->menu_slugs['pages'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Pages', 'wp-statistics' ),
				__( 'Pages', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['pages'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['visitors'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Visitors', 'wp-statistics' ),
				__( 'Visitors', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['visitors'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'geoip' ) && $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['countries'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Countries', 'wp-statistics' ),
				__( 'Countries', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['countries'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'pages' ) ) {
			$WP_Statistics->menu_slugs['categories'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Categories', 'wp-statistics' ),
				__( 'Categories', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['categories'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'pages' ) ) {
			$WP_Statistics->menu_slugs['tags'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Tags', 'wp-statistics' ),
				__( 'Tags', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['tags'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'pages' ) ) {
			$WP_Statistics->menu_slugs['authors'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Authors', 'wp-statistics' ),
				__( 'Authors', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['authors'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['browsers'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Browsers', 'wp-statistics' ),
				__( 'Browsers', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['browser'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'visitors' ) ) {
			$WP_Statistics->menu_slugs['top.visotors'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Top Visitors Today', 'wp-statistics' ),
				__( 'Top Visitors Today', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['top-visitors'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		if ( $WP_Statistics->get_option( 'record_exclusions' ) ) {
			$WP_Statistics->menu_slugs['exclusions'] = add_submenu_page(
				WP_Statistics::$page['overview'],
				__( 'Exclusions', 'wp-statistics' ),
				__( 'Exclusions', 'wp-statistics' ),
				$read_cap,
				WP_Statistics::$page['exclusions'],
				'WP_Statistics_Admin_Pages::log'
			);
		}
		$WP_Statistics->menu_slugs['break']    = add_submenu_page(
			WP_Statistics::$page['overview'],
			'',
			'',
			$read_cap,
			'wps_break_menu',
			'WP_Statistics_Admin_Pages::log'
		);
		$WP_Statistics->menu_slugs['optimize'] = add_submenu_page(
			WP_Statistics::$page['overview'],
			__( 'Optimization', 'wp-statistics' ),
			__( 'Optimization', 'wp-statistics' ),
			$manage_cap,
			WP_Statistics::$page['optimization'],
			'WP_Statistics_Admin_Pages::optimization'
		);
		$WP_Statistics->menu_slugs['settings'] = add_submenu_page(
			WP_Statistics::$page['overview'],
			__( 'Settings', 'wp-statistics' ),
			__( 'Settings', 'wp-statistics' ),
			$read_cap,
			WP_Statistics::$page['settings'],
			'WP_Statistics_Admin_Pages::settings'
		);
		$WP_Statistics->menu_slugs['plugins']  = add_submenu_page(
			WP_Statistics::$page['overview'],
			__( 'Add-Ons', 'wp-statistics' ),
			'<span style="color:#dc6b26">' . __( 'Add-Ons', 'wp-statistics' ) . '</span>',
			$read_cap,
			WP_Statistics::$page['plugins'],
			'WP_Statistics_Admin_Pages::plugins'
		);
		$WP_Statistics->menu_slugs['donate']   = add_submenu_page(
			WP_Statistics::$page['overview'],
			__( 'Donate', 'wp-statistics' ),
			'<span style="color:#459605">' . __( 'Donate', 'wp-statistics' ) . '</span>',
			$read_cap,
			WP_Statistics::$page['donate'],
			'WP_Statistics_Admin_Pages::donate'
		);

		// Add action to load the meta boxes to the overview page.
		add_action( 'load-' . $WP_Statistics->menu_slugs['overview'], 'WP_Statistics_Admin_Pages::overview' );

	}

	/**
	 * Enqueue Scripts
	 *
	 * @param string $hook Not Used
	 */
	static function enqueue_scripts( $hook ) {

		// Load our CSS to be used.
		wp_enqueue_style(
			'wpstatistics-admin-css',
			WP_Statistics::$reg['plugin-url'] . 'assets/css/admin.css',
			true,
			WP_Statistics::$reg['version']
		);

		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css', true, WP_Statistics::$reg['version'] );
		}

		if ( ! isset( $_GET['page'] ) ) {
			return;
		}

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

		if ( array_search( $_GET['page'], $pages_required_chart ) !== false ) {
			$load_in_footer              = true;
			$pages_required_load_in_head = array(
				'wps_browsers_page',
				'wps_hits_page',
				'wps_pages_page',
				'wps_categories_page',
				'wps_tags_page',
				'wps_authors_page',
				'wps_searches_page',
			);

			if ( array_search( $_GET['page'], $pages_required_load_in_head ) !== false ) {
				$load_in_footer = false;
			}

			wp_enqueue_script(
				'wp-statistics-chart-js',
				WP_Statistics::$reg['plugin-url'] . 'assets/js/Chart.bundle.min.js',
				false,
				'2.7.0',
				$load_in_footer
			);
		}
	}

    /**
     * Admin footer scripts
     */
	public function admin_footer_scripts() {
        global $WP_Statistics;

        // Check to see if the browscap database needs to be downloaded and do so if required.
        if ( $WP_Statistics->get_option( 'update_browscap' ) ) {
            echo WP_Statistics_Updates::download_browscap();
        }

        // Check to see if the GeoIP database needs to be downloaded and do so if required.
        if ( $WP_Statistics->get_option( 'update_geoip' ) ) {
            echo WP_Statistics_Updates::download_geoip();
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

            wp_mail(
                $WP_Statistics->get_option( 'email_list' ),
                sprintf( __( 'WP Statistics %s installed on', 'wp-statistics' ), WP_Statistics::$reg['version'] ) .
                ' ' .
                $blogname,
                "Installation/upgrade complete!",
                $headers
            );
        }
    }
}