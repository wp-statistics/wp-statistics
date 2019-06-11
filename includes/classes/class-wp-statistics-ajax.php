<?php

/**
 * Class WP_Statistics_Ajax
 */
class WP_Statistics_Ajax {

	/**
	 * WP_Statistics_Ajax constructor.
	 */
	function __construct() {

		/**
		 * List Of Setup Ajax request in Wordpress
		 */
		$list = array(
			'close_notice',
			'close_overview_ads',
			'delete_agents',
			'delete_platforms',
			'delete_ip',
			'empty_table',
			'purge_data',
			'purge_visitor_hits',
			'get_widget_contents'
		);
		foreach ( $list as $method ) {
			add_action( 'wp_ajax_wp_statistics_' . $method, array( $this, $method . '_action_callback' ) );
		}
	}

	/**
	 * Setup an AJAX action to close the notice on the overview page.
	 */
	public function close_notice_action_callback() {
		global $WP_Statistics;

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) and isset( $_REQUEST['notice'] ) ) {
			switch ( $_REQUEST['notice'] ) {
				case 'donate':
					$WP_Statistics->update_option( 'disable_donation_nag', true );
					break;

				case 'suggestion':
					$WP_Statistics->update_option( 'disable_suggestion_nag', true );
					break;
			}

			$WP_Statistics->update_option( 'admin_notices', false );
		}

		wp_die();
	}

	/**
	 * Close Overview Ads
	 */
	public function close_overview_ads_action_callback() {
		if ( wp_doing_ajax() and isset( $_REQUEST['ads_id'] ) ) {

			// Check Security Nonce
			check_ajax_referer( 'overview_ads_nonce', 'wps_nonce' );

			// Update Option
			$get_opt         = get_option( 'wp_statistics_overview_page_ads' );
			$get_opt['view'] = $_REQUEST['ads_id'];
			update_option( 'wp_statistics_overview_page_ads', $get_opt, 'no' );
		}
		exit;
	}

	/**
	 * Setup an AJAX action to delete an agent in the optimization page.
	 */
	public function delete_agents_action_callback() {
		global $WP_Statistics, $wpdb;

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$agent = $_POST['agent-name'];

			if ( $agent ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `agent` = %s", $agent )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s agent data deleted successfully.', 'wp-statistics' ),
						'<code>' . $agent . '</code>'
					);
				} else {
					_e( 'No agent data found to remove!', 'wp-statistics' );
				}

			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to delete a platform in the optimization page.
	 */
	public function delete_platforms_action_callback() {
		global $WP_Statistics, $wpdb;

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$platform = $_POST['platform-name'];

			if ( $platform ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `platform` = %s", $platform )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s platform data deleted successfully.', 'wp-statistics' ),
						'<code>' . htmlentities( $platform, ENT_QUOTES ) . '</code>'
					);
				} else {
					_e( 'No platform data found to remove!', 'wp-statistics' );
				}
			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to delete a ip in the optimization page.
	 */
	public function delete_ip_action_callback() {
		global $WP_Statistics, $wpdb;

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$ip_address = sanitize_text_field( $_POST['ip-address'] );

			if ( $ip_address ) {

				$result = $wpdb->query(
					$wpdb->prepare( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `ip` = %s", $ip_address )
				);

				if ( $result ) {
					echo sprintf(
						__( '%s IP data deleted successfully.', 'wp-statistics' ),
						'<code>' . htmlentities( $ip_address, ENT_QUOTES ) . '</code>'
					);
				} else {
					_e( 'No IP address data found to remove!', 'wp-statistics' );
				}
			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to empty a table in the optimization page.
	 */
	public function empty_table_action_callback() {
		global $WP_Statistics;

		//Check isset Table-post
		if ( ! isset( $_POST['table-name'] ) ) {
			_e( 'Please select the desired items.', 'wp-statistics' );
			exit;
		}

		//Check Valid Table name
		$table_name    = sanitize_text_field( $_POST['table-name'] );
		$list_db_table = wp_statistics_db_table( 'all', 'historical' );
		if ( ! array_key_exists( $table_name, $list_db_table ) ) {
			_e( 'Access denied!', 'wp-statistics' );
			exit;
		}

		//Check User Cap
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

		if ( current_user_can( $manage_cap ) ) {

			if ( $table_name == "all" ) {
				$x_tbl = 1;
				foreach ( $list_db_table as $tbl_key => $tbl_name ) {
					echo ( $x_tbl > 1 ? '<br>' : '' ) . wp_statitiscs_empty_table( $tbl_name );
					$x_tbl ++;
				}
			} else {
				echo wp_statitiscs_empty_table( wp_statistics_db_table( $table_name ) );
			}

			$WP_Statistics->Primary_Values();
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to purge old data in the optimization page.
	 */
	public function purge_data_action_callback() {
		global $WP_Statistics;

		require( WP_Statistics::$reg['plugin-dir'] . 'includes/functions/purge.php' );

		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );
		if ( current_user_can( $manage_cap ) ) {
			$purge_days = 0;

			if ( array_key_exists( 'purge-days', $_POST ) ) {
				// Get the number of days to purge data before.
				$purge_days = intval( $_POST['purge-days'] );
			}

			echo wp_statistics_purge_data( $purge_days );
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to purge visitors with more than a defined number of hits.
	 */
	public function purge_visitor_hits_action_callback() {
		global $WP_Statistics;

		require( WP_Statistics::$reg['plugin-dir'] . 'includes/functions/purge-hits.php' );

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$purge_hits = 10;

			if ( array_key_exists( 'purge-hits', $_POST ) ) {
				// Get the number of days to purge data before.
				$purge_hits = intval( $_POST['purge-hits'] );
			}

			if ( $purge_hits < 10 ) {
				_e( 'Number of hits must be greater than or equal to 10!', 'wp-statistics' );
			} else {
				echo wp_statistics_purge_visitor_hits( $purge_hits );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}

	/**
	 * Setup an AJAX action to purge visitors with more than a defined number of hits.
	 */
	public function get_widget_contents_action_callback() {
		global $WP_Statistics;

		$widgets = array(
			'about',
			'users_online',
			'browsers',
			'map',
			'countries',
			'hits',
			'hitsmap',
			'page',
			'pages',
			'quickstats',
			'recent',
			'referring',
			'search',
			'summary',
			'top.visitors',
			'words'
		);

		if ( array_key_exists( 'format', $_POST ) and $_POST['format'] == 'dashboard' ) {
			$size = 220;
			$days = 10;
		} else {
			$size = 110;
			$days = 20;
		}

		$view_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'read_capability', 'manage_options' )
		);

		if ( current_user_can( $view_cap ) ) {
			$widget = '';

			if ( array_key_exists( 'widget', $_POST ) ) {
				// Get the widget we're going to display.

				if ( in_array( $_POST['widget'], $widgets ) ) {
					$widget = $_POST['widget'];
				}
			}

			if ( $_POST['widget'] == "top_visitors" ) {
				$widget = 'top.visitors';
			}

			if ( 'map' == $widget || 'hitsmap' == $widget ) {
				$widget = 'jqv.map';
			}

			if ( '' == $widget ) {
				_e( 'No matching widget found!', 'wp-statistics' );
				wp_die();
			}

			$ISOCountryCode = $WP_Statistics->get_country_codes();
			$search_engines = wp_statistics_searchengine_list();

			require( WP_Statistics::$reg['plugin-dir'] . 'includes/log/widgets/' . $widget . '.php' );

			switch ( $widget ) {
				case 'summary':
					wp_statistics_generate_summary_postbox_content( $search_engines );

					break;
				case 'quickstats':
					wp_statistics_generate_quickstats_postbox_content( $search_engines );

					break;

				case 'browsers':
					wp_statistics_generate_browsers_postbox_content();

					break;
				case 'referring':
					wp_statistics_generate_referring_postbox_content();

					break;
				case 'countries':
					wp_statistics_generate_countries_postbox_content( $ISOCountryCode );

					break;
				case 'jqv.map':
					wp_statistics_generate_map_postbox_content( $ISOCountryCode );

					break;
				case 'hits':
					wp_statistics_generate_hits_postbox_content( $size, $days );

					break;
				case 'search':
					wp_statistics_generate_search_postbox_content( $search_engines, $size, $days );

					break;
				case 'words':
					wp_statistics_generate_words_postbox_content( $ISOCountryCode );

					break;
				case 'page':
					if ( array_key_exists( 'page-id', $_POST ) ) {
						$pageid = (int) $_POST['page-id'];

						wp_statistics_generate_page_postbox_content( null, $pageid );
					}

					break;
				case 'pages':
					wp_statistics_generate_pages_postbox_content();

					break;
				case 'recent':
					wp_statistics_generate_recent_postbox_content( $ISOCountryCode );

					break;
				case 'top.visitors':
					$format = null;

					if ( array_key_exists( 'format', $_POST ) ) {
						$format = 'compact';
					}

					wp_statistics_generate_top_visitors_postbox_content( $ISOCountryCode, 'today', 10, $format );

					break;
				case 'users_online':
					wp_statistics_generate_users_online_postbox_content( $ISOCountryCode );

					break;
				case 'about':
					wp_statistics_generate_about_postbox_content( $ISOCountryCode );

					break;
				default:
					_e( 'ERROR: Widget not found!', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die();
	}
}
