<?php

/**
 * Class WP_Statistics_Dashboard
 */
class WP_Statistics_Dashboard {

	static function widget_load() {
		GLOBAL $WP_Statistics;

		$WP_Statistics->load_user_options();

		// We need to fudge the display settings for first time users so not all of the widgets are displayed, we only want to do this on
		// the first time they visit the dashboard though so check to see if we've been here before.
		if ( ! $WP_Statistics->get_user_option( 'dashboard_set' ) ) {
			$WP_Statistics->update_user_option( 'dashboard_set', WP_Statistics::$reg['version'] );

			$hidden_widgets = get_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', true );
			if ( ! is_array( $hidden_widgets ) ) {
				$hidden_widgets = array();
			}

			$default_hidden = array(
				'wp-statistics-browsers-widget',
				'wp-statistics-countries-widget',
				'wp-statistics-hitsmap-widget',
				'wp-statistics-hits-widget',
				'wp-statistics-pages-widget',
				'wp-statistics-recent-widget',
				'wp-statistics-referring-widget',
				'wp-statistics-search-widget',
				'wp-statistics-summary-widget',
				'wp-statistics-words-widget',
				'wp-statistics-top-visitors-widget',
				'wp-statistics-searched-phrases-widget',
			);

			foreach ( $default_hidden as $widget ) {
				if ( ! in_array( $widget, $hidden_widgets ) ) {
					$hidden_widgets[] = $widget;
				}
			}

			update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', $hidden_widgets );
		} elseif ( $WP_Statistics->get_user_option( 'dashboard_set' ) != WP_Statistics::$reg['version'] ) {
			// We also have to fudge things when we add new widgets to the code base.
			if ( version_compare( $WP_Statistics->get_user_option( 'dashboard_set' ), '8.7', '<' ) ) {

				$WP_Statistics->update_user_option( 'dashboard_set', WP_Statistics::$reg['version'] );

				$hidden_widgets = get_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', true );
				if ( ! is_array( $hidden_widgets ) ) {
					$hidden_widgets = array();
				}

				$default_hidden = array( 'wp-statistics-top-visitors-widget' );

				foreach ( $default_hidden as $widget ) {
					if ( ! in_array( $widget, $hidden_widgets ) ) {
						$hidden_widgets[] = $widget;
					}
				}

				update_user_meta( $WP_Statistics->user_id, 'metaboxhidden_dashboard', $hidden_widgets );
			}
		}

		// If the user does not have at least read access to the status plugin, just return without adding the widgets.
		if ( ! current_user_can(
			wp_statistics_validate_capability(
				$WP_Statistics->get_option(
					'read_capability',
					'manage_option'
				)
			)
		)
		) {
			return;
		}

		// If the admin has disabled the widgets, don't display them.
		if ( ! $WP_Statistics->get_option( 'disable_dashboard' ) ) {
			wp_add_dashboard_widget(
				'wp-statistics-quickstats-widget',
				__( 'Quick Stats', 'wp-statistics' ),
				'WP_Statistics_Dashboard::generate_postbox_contents',
				$control_callback = null,
				array( 'widget' => 'quickstats' )
			);
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-browsers-widget',
					__( 'Top 10 Browsers', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'browsers' )
				);
			}
			if ( $WP_Statistics->get_option( 'geoip' ) && $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-countries-widget',
					__( 'Top 10 Countries', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'countries' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-hitsmap-widget',
					__( 'Today\'s Visitors Map', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'hitsmap' )
				);
			}
			if ( $WP_Statistics->get_option( 'visits' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-hits-widget',
					__( 'Hit Statistics', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'hits' )
				);
			}
			if ( $WP_Statistics->get_option( 'pages' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-pages-widget',
					__( 'Top 10 Pages', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'pages' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-recent-widget',
					__( 'Recent Visitors', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'recent' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-referring-widget',
					__( 'Top Referring Sites', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'referring' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-search-widget',
					__( 'Search Engine Referrals', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'search' )
				);
			}
			wp_add_dashboard_widget(
				'wp-statistics-summary-widget',
				__( 'Summary', 'wp-statistics' ),
				'WP_Statistics_Dashboard::generate_postbox_contents',
				$control_callback = null,
				array( 'widget' => 'summary' )
			);
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-words-widget',
					__( 'Latest Search Words', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'words' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-top-visitors-widget',
					__( 'Top 10 Visitors Today', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'top.visitors' )
				);
			}
			if ( $WP_Statistics->get_option( 'visitors' ) ) {
				wp_add_dashboard_widget(
					'wp-statistics-searched-phrases-widget',
					__( 'Top Searched Phrases (30 Days)', 'wp-statistics' ),
					'WP_Statistics_Dashboard::generate_postbox_contents',
					$control_callback = null,
					array( 'widget' => 'searched.phrases' )
				);
			}
		}
	}

	static function load_widget_css_and_scripts() {
		global $WP_Statistics;

		// Load the css we use for the statistics pages.
		wp_enqueue_style( 'wpstatistics-log-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/log.css', true, '1.2' );

		// Don't forget the right to left support.
		if ( is_rtl() ) {
			wp_enqueue_style( 'rtl-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/rtl.css', true, '1.1' );
		}

		// Load the map code.
		wp_enqueue_style( 'jqvmap-css', WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jqvmap.css', true, '1.5.1' );
		wp_enqueue_script(
			'jquery-vmap',
			WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/jquery.vmap.js',
			true,
			'1.5.1'
		);
		wp_enqueue_script(
			'jquery-vmap-world',
			WP_Statistics::$reg['plugin-url'] . 'assets/jqvmap/maps/jquery.vmap.world.js',
			true,
			'1.5.1'
		);

		// Load chart library
		if ( ! isset( $_GET['post'] ) ) {
			wp_enqueue_script(
				'wp-statistics-chart-js',
				WP_Statistics::$reg['plugin-url'] . 'assets/js/Chart.bundle.min.js',
				false,
				'2.7.0'
			);
		}

		$screen = get_current_screen();

		// Load our custom widgets handling javascript.
		if ( 'post' == $screen->id || 'page' == $screen->id ) {
			wp_enqueue_script( 'wp_statistics_editor', WP_Statistics::$reg['plugin-url'] . 'assets/js/editor.js' );
		} else {
			wp_enqueue_script( 'wp_statistics_dashboard', WP_Statistics::$reg['plugin-url'] . 'assets/js/dashboard.js' );
		}
	}

	static function inline_javascript() {
		$screen = get_current_screen();

		if ( 'dashboard' != $screen->id ) {
			return;
		}

		WP_Statistics_Dashboard::load_widget_css_and_scripts();

		$loading_img = '<div style="width: 100%; text-align: center;"><img src=" ' .
		               plugins_url( 'wp-statistics/assets/images/' ) .
		               'ajax-loading.gif" alt="' .
		               __( 'Reloading...', 'wp-statistics' ) .
		               '"></div>';

		$new_buttons
			= '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' .
			  wp_statistics_icons( 'dashicons-update' ) .
			  '<span class="screen-reader-text">' .
			  __( 'Reload', 'wp-statistics' ) .
			  '</span></button><button class="handlediv button-link wps-more" type="button" id="{{moreid}}">' .
			  wp_statistics_icons( 'dashicons-migrate' ) .
			  '<span class="screen-reader-text">' .
			  __( 'More Details', 'wp-statistics' ) .
			  '</span></button>';
		$new_button
			= '</button><button class="handlediv button-link wps-refresh" type="button" id="{{refreshid}}">' .
			  wp_statistics_icons( 'dashicons-update' ) .
			  '<span class="screen-reader-text">' .
			  __( 'Reload', 'wp-statistics' ) .
			  '</span></button>';

		$admin_url = get_admin_url() . "admin.php?page=";

		$page_urls = array();

		$page_urls['wp-statistics-browsers-widget_more_button']         = $admin_url . WP_Statistics::$page['browser'];
		$page_urls['wp-statistics-countries-widget_more_button']        = $admin_url .
		                                                                  WP_Statistics::$page['countries'];
		$page_urls['wp-statistics-exclusions-widget_more_button']       = $admin_url .
		                                                                  WP_Statistics::$page['exclusions'];
		$page_urls['wp-statistics-hits-widget_more_button']             = $admin_url . WP_Statistics::$page['hits'];
		$page_urls['wp-statistics-online-widget_more_button']           = $admin_url . WP_Statistics::$page['online'];
		$page_urls['wp-statistics-pages-widget_more_button']            = $admin_url . WP_Statistics::$page['pages'];
		$page_urls['wp-statistics-referring-widget_more_button']        = $admin_url .
		                                                                  WP_Statistics::$page['referrers'];
		$page_urls['wp-statistics-searched-phrases-widget_more_button'] = $admin_url .
		                                                                  WP_Statistics::$page['searched-phrases'];
		$page_urls['wp-statistics-search-widget_more_button']           = $admin_url . WP_Statistics::$page['searches'];
		$page_urls['wp-statistics-words-widget_more_button']            = $admin_url . WP_Statistics::$page['words'];
		$page_urls['wp-statistics-top-visitors-widget_more_button']     = $admin_url .
		                                                                  WP_Statistics::$page['top-visitors'];
		$page_urls['wp-statistics-recent-widget_more_button']           = $admin_url . WP_Statistics::$page['visitors'];
		$page_urls['wp-statistics-quickstats-widget_more_button']       = $admin_url . WP_Statistics::$page['overview'];

		?>
        <script type="text/javascript">
            var wp_statistics_destinations = <?php echo json_encode( $page_urls ); ?>;
            var wp_statistics_loading_image = '<?php echo $loading_img; ?>';

            function wp_statistics_wait_for_postboxes() {

                if (!jQuery('#show-settings-link').is(':visible')) {
                    setTimeout(wp_statistics_wait_for_postboxes, 500);
                }

                jQuery('.wps-refresh').unbind('click').on('click', wp_statistics_refresh_widget);
                jQuery('.wps-more').unbind('click').on('click', wp_statistics_goto_more);

                jQuery('.hide-postbox-tog').on('click', wp_statistics_refresh_on_toggle_widget);
            }

            jQuery(document).ready(function () {

                // Add the "more" and "refresh" buttons.
                jQuery('.postbox').each(function () {
                    var temp = jQuery(this);
                    var temp_id = temp.attr('id');

                    if (temp_id.substr(0, 14) != 'wp-statistics-') {
                        return;
                    }

                    var temp_html = temp.html();

                    if (temp_id == 'wp-statistics-summary-widget') {
                        new_text = '<?php echo $new_button;?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');

                        temp_html = temp_html.replace('</button>', new_text);
                    } else {
                        new_text = '<?php echo $new_buttons;?>';
                        new_text = new_text.replace('{{refreshid}}', temp_id + '_refresh_button');
                        new_text = new_text.replace('{{moreid}}', temp_id + '_more_button');

                        temp_html = temp_html.replace('</button>', new_text);
                    }

                    temp.html(temp_html);
                });

                // We have use a timeout here because we don't now what order this code will run in comparison to the postbox code.
                // Any timeout value should work as the timeout won't run until the rest of the javascript as run through once.
                setTimeout(wp_statistics_wait_for_postboxes, 100);
            });
        </script>
		<?php
	}

	static function generate_postbox_contents( $post, $args ) {
		$loading_img  = '<div style="width: 100%; text-align: center;"><img src=" ' .
		                plugins_url( 'wp-statistics/assets/images/' ) .
		                'ajax-loading.gif" alt="' .
		                __( 'Loading...', 'wp-statistics' ) .
		                '"></div>';
		$widget       = $args['args']['widget'];
		$container_id = 'wp-statistics-' . str_replace( '.', '-', $widget ) . '-div';

		echo '<div id="' . $container_id . '">' . $loading_img . '</div>';
		wp_statistics_generate_widget_load_javascript( $widget, $container_id );
	}

}
