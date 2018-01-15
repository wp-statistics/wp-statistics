<?php

/**
 * Class WP_Statistics_Ajax
 */
class WP_Statistics_Ajax {

	/**
	 * WP_Statistics_Ajax constructor.
	 */
	function __construct() {
		add_action(
			'wp_ajax_wp_statistics_close_donation_nag',
			'WP_Statistics_Ajax::close_donation_nag_action_callback'
		);
		add_action( 'wp_ajax_wp_statistics_delete_agents', 'WP_Statistics_Ajax::delete_agents_action_callback' );
		add_action(
			'wp_ajax_wp_statistics_delete_platforms',
			'WP_Statistics_Ajax::delete_platforms_action_callback'
		);
		add_action( 'wp_ajax_wp_statistics_empty_table', 'WP_Statistics_Ajax::empty_table_action_callback' );
		add_action( 'wp_ajax_wp_statistics_purge_data', 'WP_Statistics_Ajax::purge_data_action_callback' );
		add_action(
			'wp_ajax_wp_statistics_purge_visitor_hits',
			'WP_Statistics_Ajax::purge_visitor_hits_action_callback'
		);
		add_action( 'wp_ajax_wp_statistics_get_widget_contents', 'WP_Statistics_Ajax::get_widget_contents_callback' );
	}

	/**
	 * Setup an AJAX action to close the donation nag banner on the overview page.
	 */
	static function close_donation_nag_action_callback() {
		GLOBAL $WP_Statistics; // this is how you get access to the database

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$WP_Statistics->update_option( 'disable_donation_nag', true );
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to delete an agent in the optimization page.
	 */
	static function delete_agents_action_callback() {
		GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

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

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to delete a platform in the optimization page.
	 */
	static function delete_platforms_action_callback() {
		GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

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

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to empty a table in the optimization page.
	 */
	static function empty_table_action_callback() {
		GLOBAL $WP_Statistics, $wpdb; // this is how you get access to the database

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

		if ( current_user_can( $manage_cap ) ) {
			$table_name = $_POST['table-name'];

			if ( $table_name ) {

				switch ( $table_name ) {
					case 'useronline':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_useronline' );
						break;
					case 'visit':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_visit' );
						break;
					case 'visitors':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_visitor' );
						break;
					case 'exclusions':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_exclusions' );
						break;
					case 'pages':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_pages' );
						break;
					case 'search':
						echo wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_search' );
						break;
					case 'all':
						$result_string = wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_useronline' );
						$result_string .= '<br>' . wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_visit' );
						$result_string .= '<br>' . wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_visitor' );
						$result_string .= '<br>' . wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_exclusions' );
						$result_string .= '<br>' . wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_pages' );
						$result_string .= '<br>' . wp_statitiscs_empty_table( $wpdb->prefix . 'statistics_search' );

						echo $result_string;

						break;
					default:
						_e( 'Please select the desired items.', 'wp-statistics' );
				}

				$WP_Statistics->Primary_Values();

			} else {
				_e( 'Please select the desired items.', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to purge old data in the optimization page.
	 */
	static function purge_data_action_callback() {
		GLOBAL $WP_Statistics; // this is how you get access to the database

		require( WP_Statistics::$reg['plugin-dir'] . 'includes/functions/purge.php' );

		$manage_cap = wp_statistics_validate_capability(
			$WP_Statistics->get_option( 'manage_capability', 'manage_options' )
		);

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

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to purge visitors with more than a defined number of hits.
	 */
	static function purge_visitor_hits_action_callback() {
		GLOBAL $WP_Statistics; // this is how you get access to the database

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

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Setup an AJAX action to purge visitors with more than a defined number of hits.
	 */
	static function get_widget_contents_callback() {
		GLOBAL $WP_Statistics; // this is how you get access to the database

		$widgets = array(
			'about',
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
			'words',
			'searched.phrases',
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
				case 'searched.phrases':
					wp_statistics_generate_searched_phrases_postbox_content();

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
					_e( 'This feature temporarily disabled.', 'wp-statistics' );

					if ( array_key_exists( 'page-id', $_POST ) ) {
						$pageid = (int) $_POST['page-id'];
						echo '&nbsp;';
						echo sprintf(
							__( '<a href="%s">Click here</a> to see page stats.', 'wp-statistics' ),
							'admin.php?page=wps_pages_page&page-id=' . $pageid
						);

						// This feature temporarily disabled because there is conflicts.
						//wp_statistics_generate_page_postbox_content( null, $pageid );
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
				case 'about':
					wp_statistics_generate_about_postbox_content( $ISOCountryCode );

					break;
				default:
					_e( 'ERROR: Widget not found!', 'wp-statistics' );
			}
		} else {
			_e( 'Access denied!', 'wp-statistics' );
		}

		wp_die(); // this is required to terminate immediately and return a proper response
	}
}
