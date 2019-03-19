<?php

/**
 * Class WP_Statistics_Install
 */
class WP_Statistics_Install {

	/**
	 * List Of wp-statistics Mysql Table
	 * @var array
	 */
	public static $db_table = array( 'useronline', 'visit', 'visitor', 'exclusions', 'pages', 'search', 'historical', 'visitor_relationships' );

	/**
	 * WP_Statistics_Install constructor.
	 *
	 * @internal param $WP_Statistics
	 */
	function __construct() {
		global $WP_Statistics, $wpdb;
		if ( is_admin() ) {

			// The follow variables are used to define the table structure for new and upgrade installations.
			$create_useronline_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_useronline (
						ID int(11) NOT NULL AUTO_INCREMENT,
	  					ip varchar(60) NOT NULL,
						created int(11),
						timestamp int(10) NOT NULL,
						date datetime NOT NULL,
						referred text CHARACTER SET utf8 NOT NULL,
						agent varchar(255) NOT NULL,
						platform varchar(255),
						version varchar(255),
						location varchar(10),
						PRIMARY KEY  (ID)
					) CHARSET=utf8" );

			$create_visit_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_visit (
						ID int(11) NOT NULL AUTO_INCREMENT,
						last_visit datetime NOT NULL,
						last_counter date NOT NULL,
						visit int(10) NOT NULL,
						PRIMARY KEY  (ID),
						UNIQUE KEY unique_date (last_counter)
					) CHARSET=utf8" );

			$create_visitor_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_visitor (
						ID int(11) NOT NULL AUTO_INCREMENT,
						last_counter date NOT NULL,
						referred text NOT NULL,
						agent varchar(255) NOT NULL,
						platform varchar(255),
						version varchar(255),
						UAString varchar(255),
						ip varchar(60) NOT NULL,
						location varchar(10),
						hits int(11),
						honeypot int(11),
						PRIMARY KEY  (ID),
						UNIQUE KEY date_ip_agent (last_counter,ip,agent(75),platform(75),version(75)),
						KEY agent (agent),
						KEY platform (platform),
						KEY version (version),
						KEY location (location)
					) CHARSET=utf8" );

			$create_visitor_table_old = ( "
					CREATE TABLE {$wpdb->prefix}statistics_visitor (
						ID int(11) NOT NULL AUTO_INCREMENT,
						last_counter date NOT NULL,
						referred text NOT NULL,
						agent varchar(255) NOT NULL,
						platform varchar(255),
						version varchar(255),
						UAString varchar(255),
						ip varchar(60) NOT NULL,
						location varchar(10),
						hits int(11),
						honeypot int(11),
						PRIMARY KEY  (ID),
						UNIQUE KEY date_ip_agent (last_counter,ip,agent (75),platform (75),version (75)),
						KEY agent (agent),
						KEY platform (platform),
						KEY version (version),
						KEY location (location)
					) CHARSET=utf8" );

			$create_exclusion_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_exclusions (
						ID int(11) NOT NULL AUTO_INCREMENT,
						date date NOT NULL,
						reason varchar(255) DEFAULT NULL,
						count bigint(20) NOT NULL,
						PRIMARY KEY  (ID),
						KEY date (date),
						KEY reason (reason)
					) CHARSET=utf8" );

			$create_pages_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_pages (
						uri varchar(255) NOT NULL,
						type varchar(255) NOT NULL,
						date date NOT NULL,
						count int(11) NOT NULL,
						id int(11) NOT NULL,
						UNIQUE KEY date_2 (date,uri),
						KEY url (uri),
						KEY date (date),
						KEY id (id),
						KEY `uri` (`uri`,`count`,`id`)
					) CHARSET=utf8" );

			$create_historical_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_historical (
						ID bigint(20) NOT NULL AUTO_INCREMENT,
						category varchar(25) NOT NULL,
						page_id bigint(20) NOT NULL,
						uri varchar(255) NOT NULL,
						value bigint(20) NOT NULL,
						PRIMARY KEY  (ID),
						KEY category (category),
						UNIQUE KEY page_id (page_id),
						UNIQUE KEY uri (uri)
					) CHARSET=utf8" );

			$create_search_table = ( "
					CREATE TABLE {$wpdb->prefix}statistics_search (
						ID bigint(20) NOT NULL AUTO_INCREMENT,
						last_counter date NOT NULL,
						engine varchar(64) NOT NULL,
						host varchar(255),
						words varchar(255),
						visitor bigint(20),
						PRIMARY KEY  (ID),
						KEY last_counter (last_counter),
						KEY engine (engine),
						KEY host (host)
					) CHARSET=utf8" );

			// Check to see if the historical table exists yet, aka if this is a upgrade instead of a first install.
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_historical'" );

			if ( $result == 1 ) {
				// Before we update the historical table, check to see if it exists with the old keys
				$result = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}statistics_historical LIKE 'key'" );

				if ( $result > 0 ) {
					$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_historical` CHANGE `id` `page_id` bigint(20)" );
					$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_historical` CHANGE `key` `ID` bigint(20)" );
					$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_historical` CHANGE `type` `category` varchar(25)" );
				}
			}

			// This includes the dbDelta function from WordPress.
			if ( ! function_exists( 'dbDelta' ) ) {
				require( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			// Create/update the plugin tables.
			dbDelta( $create_useronline_table );
			dbDelta( $create_visit_table );
			dbDelta( $create_visitor_table );
			dbDelta( $create_exclusion_table );
			dbDelta( $create_pages_table );
			dbDelta( $create_historical_table );
			dbDelta( $create_search_table );

			// Some old versions (in the 5.0.x line) of MySQL have issue with the compound index on the visitor table
			// so let's make sure it was created, if not, use the older format to create the table manually instead of
			// using the dbDelta() call.
			$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$wpdb->dbname}` = '{$wpdb->prefix}statistics_visitor'" );

			if ( $result != 1 ) {
				$wpdb->query( $create_visitor_table_old );
			}

			// Check to see if the date_ip index still exists, if so get rid of it.
			$result = $wpdb->query( "SHOW INDEX FROM {$wpdb->prefix}statistics_visitor WHERE Key_name = 'date_ip'" );

			// Note, the result will be the number of fields contained in the index.
			if ( $result > 1 ) {
				$wpdb->query( "DROP INDEX `date_ip` ON {$wpdb->prefix}statistics_visitor" );
			}

			// One final database change, drop the 'AString' column from visitors if it exists as it's a typo from an old version.
			$result = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}statistics_visitor LIKE 'AString'" );

			if ( $result > 0 ) {
				$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_visitor` DROP `AString`" );
			}

			//Added page_id column in statistics_pages if not exist
			$result = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}statistics_pages LIKE 'page_id'" );
			if ( $result == 0 ) {
				$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_pages` ADD `page_id` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`page_id`);" );
			}

			//Added User_id and Page_id in user online table
			$result = $wpdb->query( "SHOW COLUMNS FROM {$wpdb->prefix}statistics_useronline LIKE 'user_id'" );
			if ( $result == 0 ) {
				$wpdb->query( "ALTER TABLE `{$wpdb->prefix}statistics_useronline` ADD `user_id` BIGINT(48) NOT NULL AFTER `location`, ADD `page_id` BIGINT(48) NOT NULL AFTER `user_id`, ADD `type` VARCHAR(100) NOT NULL AFTER `page_id`;" );
				$wpdb->query( "DELETE FROM `{$wpdb->prefix}usermeta` WHERE `meta_key` = 'meta-box-order_toplevel_page_wps_overview_page';" );
			}

			// Store the new version information.
			update_option( 'wp_statistics_plugin_version', WP_Statistics::$reg['version'] );
			update_option( 'wp_statistics_db_version', WP_Statistics::$reg['version'] );

			// Now check to see what database updates may be required and record them for a user notice later.
			$dbupdates = array( 'date_ip_agent' => false, 'unique_date' => false );

			// Check the number of index's on the visitors table, if it's only 5 we need to check for duplicate entries and remove them
			$result = $wpdb->query( "SHOW INDEX FROM {$wpdb->prefix}statistics_visitor WHERE Key_name = 'date_ip_agent'" );

			// Note, the result will be the number of fields contained in the index, so in our case 5.
			if ( $result != 5 ) {
				$dbupdates['date_ip_agent'] = true;
			}

			// Check the number of index's on the visits table, if it's only 5 we need to check for duplicate entries and remove them
			$result = $wpdb->query( "SHOW INDEX FROM {$wpdb->prefix}statistics_visit WHERE Key_name = 'unique_date'" );

			// Note, the result will be the number of fields contained in the index, so in our case 1.
			if ( $result != 1 ) {
				$dbupdates['unique_date'] = true;
			}

			$WP_Statistics->update_option( 'pending_db_updates', $dbupdates );

			$default_options = $WP_Statistics->Default_Options();

			if ( WP_Statistics::$installed_version == false ) {

				// If this is a first time install, we just need to setup the primary values in the tables.
				$WP_Statistics->Primary_Values();

				// By default, on new installs, use the new search table.
				$WP_Statistics->update_option( 'search_converted', 1 );

			} else {

				// If this is an upgrade, we need to check to see if we need to convert anything from old to new formats.
				// Check to see if the "new" settings code is in place or not, if not, upgrade the old settings to the new system.
				if ( get_option( 'wp_statistics' ) === false ) {
					$core_options   = array(
						'wps_disable_map',
						'wps_map_location',
						'wps_google_coordinates',
						'wps_schedule_dbmaint',
						'wps_schedule_dbmaint_days',
						'wps_geoip',
						'wps_update_geoip',
						'wps_schedule_geoip',
						'wps_last_geoip_dl',
						'wps_auto_pop',
						'wps_useronline',
						'wps_check_online',
						'wps_visits',
						'wps_visitors',
						'wps_visitors_log',
						'wps_store_ua',
						'wps_coefficient',
						'wps_pages',
						'wps_track_all_pages',
						'wps_use_cache_plugin',
						'wps_geoip_city',
						'wps_disable_column',
						'wps_hit_post_metabox',
						'wps_menu_bar',
						'wps_hide_notices',
						'wps_chart_totals',
						'wps_stats_report',
						'wps_time_report',
						'wps_send_report',
						'wps_content_report',
						'wps_read_capability',
						'wps_manage_capability',
						'wps_record_exclusions',
						'wps_robotlist',
						'wps_exclude_ip',
						'wps_exclude_loginpage',
						'wps_exclude_adminpage',
					);
					$var_options    = array( 'wps_disable_se_%', 'wps_exclude_%' );
					$widget_options = array(
						'name_widget',
						'useronline_widget',
						'tvisit_widget',
						'tvisitor_widget',
						'yvisit_widget',
						'yvisitor_widget',
						'wvisit_widget',
						'mvisit_widget',
						'ysvisit_widget',
						'ttvisit_widget',
						'ttvisitor_widget',
						'tpviews_widget',
						'ser_widget',
						'select_se',
						'tp_widget',
						'tpg_widget',
						'tc_widget',
						'ts_widget',
						'tu_widget',
						'ap_widget',
						'ac_widget',
						'au_widget',
						'lpd_widget',
						'select_lps',
					);

					// Handle the core options, we're going to strip off the 'wps_' header as we store them in the new settings array.
					foreach ( $core_options as $option ) {
						$new_name = substr( $option, 4 );
						$WP_Statistics->store_option( $new_name, get_option( $option ) );
						delete_option( $option );
					}

					$widget = array();

					// Handle the widget options, we're going to store them in a sub-array.
					foreach ( $widget_options as $option ) {
						$widget[ $option ] = get_option( $option );

						delete_option( $option );
					}

					$WP_Statistics->store_option( 'widget', $widget );

					foreach ( $var_options as $option ) {
						// Handle the special variables options.
						$result = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}options WHERE option_name LIKE '{$option}'" );

						foreach ( $result as $opt ) {
							$new_name = substr( $opt->option_name, 4 );
							$WP_Statistics->store_option( $new_name, $opt->option_value );
							delete_option( $opt->option_name );
						}
					}

					$WP_Statistics->save_options();
				}

				// If the robot list is empty, fill in the defaults.
				$wps_temp_robotslist = $WP_Statistics->get_option( 'robotlist' );

				if ( trim( $wps_temp_robotslist ) == "" || $WP_Statistics->get_option( 'force_robot_update' ) == true ) {
					$WP_Statistics->update_option( 'robotlist', $default_options['robotlist'] );
				}

				// WP Statistics V4.2 and below automatically exclude the administrator for statistics collection
				// newer versions allow the option to be set for any role in WordPress, however we should mimic
				// 4.2 behaviour when we upgrade, so see if the option exists in the database and if not, set it.
				// This will not work correctly on a WordPress install that has removed the administrator role.
				// However that seems VERY unlikely.
				$exclude_admins = $WP_Statistics->get_option( 'exclude_administrator', '2' );
				if ( $exclude_admins == '2' ) {
					$WP_Statistics->update_option( 'exclude_administrator', '1' );
				}

				// WordPress 4.3 broke the diplay of the sidebar widget because it no longer accepted a null value
				// to be returned from the widget update function, let's look to see if we need to update any
				// occurances in the options table.
				$widget_options = get_option( 'widget_wpstatistics_widget' );
				if ( is_array( $widget_options ) ) {
					foreach ( $widget_options as $key => $value ) {
						// We want to update all null array keys that are integers.
						if ( $value === null && is_int( $key ) ) {
							$widget_options[ $key ] = array();
						}
					}

					// Store the widget options back to the database.
					update_option( 'widget_wpstatistics_widget', $widget_options );
				}
			}

			// We've already handled some of the default or need to do more logic checks on them so create a list to exclude from the next loop.
			$excluded_defaults = array( 'force_robot_update', 'robot_list' );

			// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
			foreach ( $default_options as $key => $value ) {
				if ( ! in_array( $key, $excluded_defaults ) && false === $WP_Statistics->get_option( $key ) ) {
					$WP_Statistics->store_option( $key, $value );
				}
			}

			if ( WP_Statistics::$installed_version == false ) {
				// We now need to set the robot list to update during the next release.  This is only done for new installs to ensure we don't overwrite existing custom robot lists.
				$WP_Statistics->store_option( 'force_robot_update', true );
			}

			// Save the settings now that we've set them.
			$WP_Statistics->save_options();

			if ( $WP_Statistics->get_option( 'upgrade_report' ) == true ) {
				$WP_Statistics->update_option( 'send_upgrade_email', true );
			}

			// Handle multi site implementations
			if ( is_multisite() ) {
				$current_blog = get_current_blog_id();

				// Loop through each of the sites.
				$sites = $WP_Statistics->get_wp_sites_list();
				foreach ( $sites as $blog_id ) {

					// Since we've just upgraded/installed the current blog, don't execute a remote call for us.
					if ( $blog_id != $current_blog ) {

						// Get the admin url for the current site.
						$url = get_admin_url( $blog_id );

						// Go and visit the admin url of the site, this will rerun the install script for each site.
						// We turn blocking off because we don't really care about the response so why wait for it.
						wp_remote_request( $url, array( 'blocking' => false ) );
					}
				}
			}
		}
	}

	/**
	 * Setup Visitor RelationShip Table
	 */
	public static function setup_visitor_relationship_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . 'statistics_visitor_relationships';
		if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {

			// This includes the dbDelta function from WordPress.
			if ( ! function_exists( 'dbDelta' ) ) {
				require( ABSPATH . 'wp-admin/includes/upgrade.php' );
			}

			$create_visitor_relationships_table =
				"CREATE TABLE IF NOT EXISTS $table_name (
				`ID` bigint(20) NOT NULL AUTO_INCREMENT,
				`visitor_id` bigint(20) NOT NULL,
				`page_id` bigint(20) NOT NULL,
				`date` datetime NOT NULL,
				PRIMARY KEY  (ID),
				KEY visitor_id (visitor_id),
				KEY page_id (page_id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8";

			dbDelta( $create_visitor_relationships_table );
		}
	}

	/**
	 * Update WordPress Page Type for older wp-statistics Version
	 *
	 * @since 12.6
	 *
	 * -- List Methods ---
	 * _init_page_type_updater        -> define WordPress Hook
	 * _get_require_number_update     -> Get number of rows that require update page type
	 * _is_require_update_page        -> Check Wp-statistics require update page table
	 * _get_page_type_by_obj          -> Get Page Type by information
	 */
	public static function _init_page_type_updater() {

		# Check Require Admin Process
		if ( self::_is_require_update_page() === true ) {

			# Add Admin Notice
			add_action( 'admin_notices', function () {
				echo '<div class="notice notice-info is-dismissible" id="wp-statistics-update-page-area" style="display: none;">';
				echo '<p style="margin-top: 17px; float:' . ( is_rtl() ? 'right' : 'left' ) . '">';
				echo __( 'WP-Statistics database requires upgrade.', 'wp-statistics' );
				echo '</p>';
				echo '<div style="float:' . ( is_rtl() ? 'left' : 'right' ) . '">';
				echo '<button type="button" id="wps-upgrade-db" class="button button-primary" style="padding: 20px;line-height: 0px;box-shadow: none !important;border: 0px !important;margin: 10px 0;"/>' . __( 'Upgrade Database', 'wp-statistics' ) . '</button>';
				echo '</div>';
				echo '<div style="clear:both;"></div>';
				echo '</div>';
			} );

			# Add Script
			add_action( 'admin_footer', function () {
				?>
                <script>
                    jQuery(document).ready(function () {

                        // Check Page is complete Loaded
                        jQuery(window).load(function () {
                            jQuery("#wp-statistics-update-page-area").fadeIn(2000);
                            jQuery("#wp-statistics-update-page-area button.notice-dismiss").hide();
                        });

                        // Update Page type function
                        function wp_statistics_update_page_type() {

                            //Complete Progress
                            let wps_end_progress = `<div id="wps_end_process" style="display:none;">`;
                            wps_end_progress += `<p>`;
                            wps_end_progress += `<?php _e( 'Database upgrade operation completed!', 'wp-statistics' ); ?>`;
                            wps_end_progress += `</p>`;
                            wps_end_progress += `</div>`;
                            wps_end_progress += `<button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>`;

                            //new Ajax Request
                            jQuery.ajax({
                                url: ajaxurl,
                                type: 'get',
                                dataType: "json",
                                cache: false,
                                data: {
                                    'action': 'wp_statistics_update_post_type_db',
                                    'number_all': <?php echo self::_get_require_number_update(); ?>
                                },
                                success: function (data) {
                                    if (data.process_status === "complete") {

                                        // Get Process Area
                                        let wps_notice_area = jQuery("#wp-statistics-update-page-area");
                                        //Add Html Content
                                        wps_notice_area.html(wps_end_progress);
                                        //Fade in content
                                        jQuery("#wps_end_process").fadeIn(2000);
                                        //enable demiss button
                                        wps_notice_area.removeClass('notice-info').addClass('notice-success');
                                    } else {

                                        //Get number Process
                                        jQuery("span#wps_num_page_process").html(data.number_process);
                                        //Get process Percentage
                                        jQuery("progress#wps_upgrade_html_progress").attr("value", data.percentage);
                                        jQuery("span#wps_num_percentage").html(data.percentage);
                                        //again request
                                        wp_statistics_update_page_type();
                                    }
                                },
                                error: function () {
                                    jQuery("#wp-statistics-update-page-area").html('<p><?php _e( 'Error occurred during operation. Please refresh the page.', 'wp-statistics' ); ?></p>');
                                }
                            });
                        }

                        //Click Start Progress
                        jQuery(document).on('click', 'button#wps-upgrade-db', function (e) {
                            e.preventDefault();

                            // Added Progress Html
                            let wps_progress = `<div id="wps_process_upgrade" style="display:none;"><p>`;
                            wps_progress += `<?php _e( 'Please don\'t close the browser window until the database operation was completed.', 'wp-statistic' ); ?>`;
                            wps_progress += `</p><p><b>`;
                            wps_progress += `<?php echo __( 'Item processed', 'wp-statistics' ); ?>`;
                            wps_progress += ` : <span id="wps_num_page_process">0</span> / <?php echo number_format( self::_get_require_number_update() ); ?> &nbsp;<span class="wps-text-warning">(<span id="wps_num_percentage">0</span>%)</span></b></p>`;
                            wps_progress += '<p><progress id="wps_upgrade_html_progress" value="0" max="100" style="height: 20px;width: 100%;"></progress></p></div>';

                            // set new Content
                            jQuery("#wp-statistics-update-page-area").html(wps_progress);
                            jQuery("#wps_process_upgrade").fadeIn(2000);

                            // Run WordPress Ajax Updator
                            wp_statistics_update_page_type();
                        });

                        //Remove Notice event
                        jQuery(document).on('click', '#wp-statistics-update-page-area button.notice-dismiss', function (e) {
                            e.preventDefault();
                            jQuery("#wp-statistics-update-page-area").fadeOut('normal');
                        });
                    });
                </script>
				<?php
			} );

		}

		# Add Admin Ajax Process
		add_action( 'wp_ajax_wp_statistics_update_post_type_db', function () {
			global $wpdb;

			# Create Default Obj
			$return = array( 'process_status' => 'complete', 'number_process' => 0, 'percentage' => 0 );

			# Check is Ajax WordPress
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

				# Check Status Of Process
				if ( self::_is_require_update_page() === true ) {

					# Number Process Per Query
					$number_per_query = 80;

					# Check Number Process
					$number_process = self::_get_require_number_update();
					$i              = 0;
					if ( $number_process > 0 ) {

						# Start Query
						$query = $wpdb->get_results( "SELECT * FROM `" . wp_statistics_db_table( 'pages' ) . "` WHERE `type` = '' ORDER BY `page_id` DESC LIMIT 0,{$number_per_query}", ARRAY_A );
						foreach ( $query as $row ) {

							# Get Page Type
							$page_type = self::_get_page_type_by_obj( $row['id'], $row['uri'] );

							# Update Table
							$wpdb->update(
								wp_statistics_db_table( 'pages' ),
								array(
									'type' => $page_type
								),
								array( 'page_id' => $row['page_id'] )
							);

							$i ++;
						}

						if ( $_GET['number_all'] > $number_per_query ) {
							# calculate number process
							$return['number_process'] = $_GET['number_all'] - ( $number_process - $i );

							# Calculate Per
							$return['percentage'] = round( ( $return['number_process'] / $_GET['number_all'] ) * 100 );

							# Set Process
							$return['process_status'] = 'incomplete';

						} else {

							$return['number_process'] = $_GET['number_all'];
							$return['percentage']     = 100;
							update_option( 'wp_statistics_update_page_type', 'yes' );
						}
					}
				} else {

					# Closed Process
					update_option( 'wp_statistics_update_page_type', 'yes' );
				}

				# Export Data
				wp_send_json( $return );
				exit;
			}
		} );


	}

	public static function _get_require_number_update() {
		global $wpdb;
		return $wpdb->get_var( "SELECT COUNT(*) FROM `" . wp_statistics_db_table( 'pages' ) . "` WHERE `type` = ''" );
	}

	public static function _is_require_update_page() {

		# require update option name
		$opt_name = 'wp_statistics_update_page_type';

		# Check exist option
		$get_opt = get_option( $opt_name );
		if ( ! empty( $get_opt ) ) {
			return false;
		}

		# Check number require row
		if ( self::_get_require_number_update() > 0 ) {
			return true;
		}

		return false;
	}

	public static function _get_page_type_by_obj( $obj_ID, $page_url ) {

		//Default page type
		$page_type = 'unknown';

		//check if Home Page
		if ( $page_url == "/" ) {
			return 'home';

		} else {

			// Page url
			$page_url = ltrim( $page_url, "/" );
			$page_url = trim( get_bloginfo( 'url' ), "/" ) . "/" . $page_url;

			// Check Page Path is exist
			$exist_page = url_to_postid( $page_url );

			//Check Post Exist
			if ( $exist_page > 0 ) {

				# Get Post Type
				$p_type = get_post_type( $exist_page );

				# Check Post Type
				if ( $p_type == "product" ) {
					$page_type = 'product';
				} elseif ( $p_type == "page" ) {
					$page_type = 'page';
				} elseif ( $p_type == "attachment" ) {
					$page_type = 'attachment';
				} else {
					$page_type = 'post';
				}

			} else {

				# Check is Term
				$term = get_term( $obj_ID );
				if ( is_wp_error( get_term_link( $term ) ) === true ) {
					//Don't Stuff
				} else {
					//Which Taxonomy
					$taxonomy = $term->taxonomy;

					//Check Url is contain
					$term_link = get_term_link( $term );
					$term_link = ltrim( str_ireplace( get_bloginfo( 'url' ), "", $term_link ), "/" );
					if ( stristr( $page_url, $term_link ) === false ) {
						//Return Unknown
					} else {
						//Check Type of taxonomy
						if ( $taxonomy == "category" ) {
							$page_type = 'category';
						} elseif ( $taxonomy == "post_tag" ) {
							$page_type = 'post_tag';
						} else {
							$page_type = 'tax';
						}
					}

				}
			}
		}

		return $page_type;
	}

}
