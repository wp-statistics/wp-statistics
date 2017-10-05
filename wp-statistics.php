<?php
/*
 * Plugin Name: WP Statistics
 * Plugin URI: http://wp-statistics.com/
 * Description: Complete statistics for your WordPress site.
 * Version: 12.1.3
 * Author: WP-Statistics Team
 * Author URI: http://wp-statistics.com/
 * Text Domain: wp-statistics
 * Domain Path: /languages/
 * License: GPL2
 */

// These defines are used later for various reasons.
define( 'WP_STATISTICS_VERSION', '12.1.3' );
define( 'WP_STATISTICS_REQUIRED_PHP_VERSION', '5.4.0' );
define( 'WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION', WP_STATISTICS_REQUIRED_PHP_VERSION );
define( 'WPS_EXPORT_FILE_NAME', 'wp-statistics' );

define( 'WP_STATISTICS_OVERVIEW_PAGE', 'wps_overview_page' );
define( 'WP_STATISTICS_BROWSERS_PAGE', 'wps_browsers_page' );
define( 'WP_STATISTICS_COUNTRIES_PAGE', 'wps_countries_page' );
define( 'WP_STATISTICS_EXCLUSIONS_PAGE', 'wps_exclusions_page' );
define( 'WP_STATISTICS_HITS_PAGE', 'wps_hits_page' );
define( 'WP_STATISTICS_ONLINE_PAGE', 'wps_online_page' );
define( 'WP_STATISTICS_PAGES_PAGE', 'wps_pages_page' );
define( 'WP_STATISTICS_CATEGORIES_PAGE', 'wps_categories_page' );
define( 'WP_STATISTICS_AUTHORS_PAGE', 'wps_authors_page' );
define( 'WP_STATISTICS_TAGS_PAGE', 'wps_tags_page' );
define( 'WP_STATISTICS_REFERRERS_PAGE', 'wps_referrers_page' );
define( 'WP_STATISTICS_SEARCHES_PAGE', 'wps_searches_page' );
define( 'WP_STATISTICS_WORDS_PAGE', 'wps_words_page' );
define( 'WP_STATISTICS_TOP_VISITORS_PAGE', 'wps_top_visitors_page' );
define( 'WP_STATISTICS_VISITORS_PAGE', 'wps_visitors_page' );
define( 'WP_STATISTICS_OPTIMIZATION_PAGE', 'wps_optimization_page' );
define( 'WP_STATISTICS_SETTINGS_PAGE', 'wps_settings_page' );
define( 'WP_STATISTICS_PLUGINS_PAGE', 'wps_plugins_page' );
define( 'WP_STATISTICS_DONATE_PAGE', 'wps_donate_page' );
define( 'WP_STATISTICS_PLUGIN_DIR', plugin_dir_url( __FILE__ ) );

// Load the translation code.
function wp_statistics_language() {
	GLOBAL $WP_Statistics;

	// Users can override loading the default language code, check to see if they have.
	$override = false;

	if ( is_object( $WP_Statistics ) ) {
		if ( $WP_Statistics->get_option( 'override_language', false ) ) {
			$override = true;
		}
	}

	// If not, go ahead and load the translations.
	if ( ! $override ) {
		load_plugin_textdomain( 'wp-statistics', false, basename( dirname( __FILE__ ) ) . '/languages' );

		__( 'WP Statistics', 'wp-statistics' );
		__( 'Complete statistics for your WordPress site.', 'wp-statistics' );
	}
}

// Add translation action.  We have to load the translation code before the init otherwise the widgets won't get translated properly.
add_action( 'plugins_loaded', 'wp_statistics_language' );

// Load the init code.
function wp_statistics_init() {
	GLOBAL $WP_Statistics;

	// Check to see if we're exporting data, if so, do so now.
	// Note this will set the headers to download the export file and then stop running WordPress.
	if ( array_key_exists( 'wps_export', $_POST ) ) {
		include_once dirname( __FILE__ ) . '/includes/functions/export.php';
		wp_statistics_export_data();
	}
}

// Add init actions.  For the main init we're going to set our priority to 9 to execute before most plugins so we can export data before and set the headers without
// worrying about bugs in other plugins that output text and don't allow us to set the headers.
add_action( 'init', 'wp_statistics_init', 9 );

function wp_statistics_unsupported_version_admin_notice() {
	global $wp_version;

	$screen = get_current_screen();

	if ( 'plugins' !== $screen->id ) {
		return;
	}
	?>
    <div class="error">
        <p style="max-width:800px;">
            <b><?php _e( 'WP Statistics Disabled', 'wp-statistics' ); ?></b> <?php _e( '&#151; You are running an unsupported version of PHP.', 'glotpress' ); ?>
        </p>

        <p style="max-width:800px;"><?php

			echo sprintf( __( 'WP Statistics has detected PHP version %s which is unsupported, WP Statistics requires PHP Version %s or higher!', 'wp-statistics' ), phpversion(), WP_STATISTICS_REQUIRED_PHP_VERSION );
			echo '</p><p>';
			echo __( 'Please contact your hosting provider to upgrade to a supported version or disable WP Statistics to remove this message.', 'wp-statistics' );
			?></p>
    </div>

	<?php
}

// Check the PHP version, if we don't meet the minimum version to run WP Statistics return so we don't cause a critical error.
if ( ! version_compare( phpversion(), WP_STATISTICS_REQUIRED_PHP_VERSION, ">=" ) ) {
	add_action( 'admin_notices', 'wp_statistics_unsupported_version_admin_notice', 10, 2 );

	return;
}

// If we've been flagged to remove all of the data, then do so now.
if ( get_option( 'wp_statistics_removal' ) == 'true' ) {
	include_once( dirname( __FILE__ ) . '/wps-uninstall.php' );
}

// This adds a row after WP Statistics in the plugin page IF we've been removed via the settings page.
function wp_statistics_removal_admin_notice() {
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

// If we've been removed, return without doing anything else.
if ( get_option( 'wp_statistics_removal' ) == 'done' ) {
	add_action( 'admin_notices', 'wp_statistics_removal_admin_notice', 10, 2 );

	return;
}

// Load the user agent parsing code first, the WP_Statistics class depends on it.  Then load the WP_Statistics class.
include_once dirname( __FILE__ ) . '/vendor/donatj/phpuseragentparser/Source/UserAgentParser.php';
include_once dirname( __FILE__ ) . '/includes/classes/statistics.class.php';

// This is our global WP_Statitsics class that is used throughout the plugin.
GLOBAL $WP_Statistics;
$WP_Statistics = new WP_Statistics();

// Check to see if we're installed and are the current version.
$WPS_Installed = get_option( 'wp_statistics_plugin_version' );
if ( $WPS_Installed != WP_STATISTICS_VERSION ) {
	include_once( dirname( __FILE__ ) . '/wps-install.php' );
}

// Load the update functions for GeoIP and browscap.ini (done in a separate file to avoid a parse error in PHP 5.2 or below)
include_once dirname( __FILE__ ) . '/wps-updates.php';

// Load the rest of the required files for our global functions, online user tracking and hit tracking.
include_once dirname( __FILE__ ) . '/includes/functions/functions.php';
include_once dirname( __FILE__ ) . '/includes/classes/hits.class.php';

// If GeoIP is enabled and supported, extend the hits class to record the GeoIP information.
if ( $WP_Statistics->get_option( 'geoip' ) && wp_statistics_geoip_supported() ) {
	include_once dirname( __FILE__ ) . '/includes/classes/hits.geoip.class.php';
}

// Finally load the widget, dashboard, shortcode and scheduled events.
include_once dirname( __FILE__ ) . '/widget.php';
include_once dirname( __FILE__ ) . '/dashboard.php';
include_once dirname( __FILE__ ) . '/editor.php';
include_once dirname( __FILE__ ) . '/shortcode.php';
include_once dirname( __FILE__ ) . '/schedule.php';
include_once dirname( __FILE__ ) . '/ajax.php';

// This function outputs error messages in the admin interface if the primary components of WP Statistics are enabled.
function wp_statistics_not_enable() {
	GLOBAL $WP_Statistics;

	// If the user had told us to be quite, do so.
	if ( ! $WP_Statistics->get_option( 'hide_notices' ) ) {

		// Check to make sure the current user can manage WP Statistics, if not there's no point displaying the warnings.
		$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );
		if ( ! current_user_can( $manage_cap ) ) {
			return;
		}

		$get_bloginfo_url = get_admin_url() . "admin.php?page=" . WP_STATISTICS_SETTINGS_PAGE;

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

		$get_bloginfo_url = get_admin_url() . "admin.php?page=" . WP_STATISTICS_OPTIMIZATION_PAGE . "&tab=database";

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
				'unique_date'   => __( 'visit database index', 'wp-statistics' )
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

// Display the admin notices if we should.
if ( isset( $pagenow ) && array_key_exists( 'page', $_GET ) ) {
	if ( $pagenow == "admin.php" && substr( $_GET['page'], 0, 14 ) == 'wp-statistics/' ) {
		add_action( 'admin_notices', 'wp_statistics_not_enable' );
	}
}

// Add the honey trap code in the footer.
add_action( 'wp_footer', 'wp_statistics_footer_action' );

function wp_statistics_footer_action() {
	GLOBAL $WP_Statistics;

	if ( $WP_Statistics->get_option( 'use_honeypot' ) && $WP_Statistics->get_option( 'honeypot_postid' ) > 0 ) {
		$post_url = get_permalink( $WP_Statistics->get_option( 'honeypot_postid' ) );
		echo '<a href="' . $post_url . '" style="display: none;">&nbsp;</a>';
	}
}

// If we've been told to exclude the feeds from the statistics add a detection hook when WordPress generates the RSS feed.
if ( $WP_Statistics->get_option( 'exclude_feeds' ) ) {
	add_filter( 'the_title_rss', 'wp_statistics_check_feed_title' );
}

function wp_statistics_check_feed_title( $title ) {
	GLOBAL $WP_Statistics;

	$WP_Statistics->feed_detected();

	return $title;
}

// We can wait until the very end of the page to process the statistics, that way the page loads and displays
// quickly.
add_action( 'shutdown', 'wp_statistics_shutdown_action' );

function wp_statistics_shutdown_action() {
	GLOBAL $WP_Statistics;

	// If something has gone horribly wrong and $WP_Statistics isn't an object, bail out.  This seems to happen sometimes with WP Cron calls.
	if ( ! is_object( $WP_Statistics ) ) {
		return;
	}

	// Create a new hit class, if we're GeoIP enabled, use GeoIPHits().
	if ( class_exists( 'GeoIPHits' ) ) {
		$h = new GeoIPHits();
	} else {
		$h = new Hits();
	}

	// Call the online users tracking code.
	if ( $WP_Statistics->get_option( 'useronline' ) ) {
		$h->Check_online();
	}

	// Call the visitor tracking code.
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$h->Visitors();
	}

	// Call the visit tracking code.
	if ( $WP_Statistics->get_option( 'visits' ) ) {
		$h->Visits();
	}

	// Call the page tracking code.
	if ( $WP_Statistics->get_option( 'pages' ) ) {
		$h->Pages();
	}

	// Check to see if the GeoIP database needs to be downloaded and do so if required.
	if ( $WP_Statistics->get_option( 'update_geoip' ) ) {
		wp_statistics_download_geoip();
	}

	// Check to see if the browscap database needs to be downloaded and do so if required.
	if ( $WP_Statistics->get_option( 'update_browscap' ) ) {
		wp_statistics_download_browscap();
	}

	// Check to see if the referrerspam database needs to be downloaded and do so if required.
	if ( $WP_Statistics->get_option( 'update_referrerspam' ) ) {
		wp_statistics_download_referrerspam();
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

		wp_mail( $WP_Statistics->get_option( 'email_list' ), sprintf( __( 'WP Statistics %s installed on', 'wp-statistics' ), WP_STATISTICS_VERSION ) . ' ' . $blogname, "Installation/upgrade complete!", $headers );
	}

}

// Add a settings link to the plugin list.
function wp_statistics_settings_links( $links, $file ) {
	GLOBAL $WP_Statistics;

	$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

	if ( current_user_can( $manage_cap ) ) {
		array_unshift( $links, '<a href="' . admin_url( 'admin.php?page=' . WP_STATISTICS_SETTINGS_PAGE ) . '">' . __( 'Settings', 'wp-statistics' ) . '</a>' );
	}

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wp_statistics_settings_links', 10, 2 );

// Add a WordPress plugin page and rating links to the meta information to the plugin list.
function wp_statistics_add_meta_links( $links, $file ) {
	if ( $file == plugin_basename( __FILE__ ) ) {
		$plugin_url = 'http://wordpress.org/plugins/wp-statistics/';

		$links[] = '<a href="' . $plugin_url . '" target="_blank" title="' . __( 'Click here to visit the plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Visit WordPress.org page', 'wp-statistics' ) . '</a>';

		$rate_url = 'http://wordpress.org/support/view/plugin-reviews/wp-statistics?rate=5#postform';
		$links[]  = '<a href="' . $rate_url . '" target="_blank" title="' . __( 'Click here to rate and review this plugin on WordPress.org', 'wp-statistics' ) . '">' . __( 'Rate this plugin', 'wp-statistics' ) . '</a>';
	}

	return $links;
}

add_filter( 'plugin_row_meta', 'wp_statistics_add_meta_links', 10, 2 );

// Add a custom column to post/pages for hit statistics.
function wp_statistics_add_column( $columns ) {
	$columns['wp-statistics'] = __( 'Hits', 'wp-statistics' );

	return $columns;
}

// Render the custom column on the post/pages lists.
function wp_statistics_render_column( $column_name, $post_id ) {
	if ( $column_name == 'wp-statistics' ) {
		echo "<a href='" . get_admin_url() . "admin.php?page=" . WP_STATISTICS_PAGES_PAGE . "&page-id={$post_id}'>" . wp_statistics_pages( 'total', "", $post_id ) . "</a>";
	}
}

// Call the add/render functions at the appropriate times.
function wp_statistics_load_edit_init() {
	GLOBAL $WP_Statistics;

	$read_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) );

	if ( current_user_can( $read_cap ) && $WP_Statistics->get_option( 'pages' ) && ! $WP_Statistics->get_option( 'disable_column' ) ) {
		$post_types = (array) get_post_types( array( 'show_ui' => true ), 'object' );

		foreach ( $post_types as $type ) {
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

	echo "<div class='misc-pub-section'>" . __( 'WP Statistics - Hits', 'wp-statistics' ) . ": <b><a href='" . get_admin_url() . "admin.php?page=" . WP_STATISTICS_PAGES_PAGE . "&page-id={$id}'>" . wp_statistics_pages( 'total', "", $id ) . "</a></b></div>";
}

if ( $WP_Statistics->get_option( 'pages' ) && ! $WP_Statistics->get_option( 'disable_column' ) ) {
	add_action( 'post_submitbox_misc_actions', 'wp_statistics_post_init' );
}

// This function will validate that a capability exists, if not it will default to returning the 'manage_options' capability.
function wp_statistics_validate_capability( $capability ) {

	global $wp_roles;

	$role_list = $wp_roles->get_names();

	if ( ! is_object( $wp_roles ) || ! is_array( $wp_roles->roles ) ) {
		return 'manage_options';
	}

	foreach ( $wp_roles->roles as $role ) {

		$cap_list = $role['capabilities'];

		foreach ( $cap_list as $key => $cap ) {
			if ( $capability == $key ) {
				return $capability;
			}
		}
	}

	return 'manage_options';
}

// This function adds the primary menu to WordPress.
function wp_statistics_menu() {
	GLOBAL $WP_Statistics;

	// Get the read/write capabilities required to view/manage the plugin as set by the user.
	$read_cap   = wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) );
	$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

	// Add the top level menu.
	$WP_Statistics->menu_slugs['top'] = add_menu_page( __( 'Statistics', 'wp-statistics' ), __( 'Statistics', 'wp-statistics' ), $read_cap, WP_STATISTICS_OVERVIEW_PAGE, 'wp_statistics_log', 'dashicons-chart-pie' );

	// Add the sub items.
	$WP_Statistics->menu_slugs['overview'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Overview', 'wp-statistics' ), __( 'Overview', 'wp-statistics' ), $read_cap, WP_STATISTICS_OVERVIEW_PAGE, 'wp_statistics_log' );

	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['browsers'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Browsers', 'wp-statistics' ), __( 'Browsers', 'wp-statistics' ), $read_cap, WP_STATISTICS_BROWSERS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'geoip' ) && $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['countries'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Countries', 'wp-statistics' ), __( 'Countries', 'wp-statistics' ), $read_cap, WP_STATISTICS_COUNTRIES_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'record_exclusions' ) ) {
		$WP_Statistics->menu_slugs['exclusions'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Exclusions', 'wp-statistics' ), __( 'Exclusions', 'wp-statistics' ), $read_cap, WP_STATISTICS_EXCLUSIONS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visits' ) ) {
		$WP_Statistics->menu_slugs['hits'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Hits', 'wp-statistics' ), __( 'Hits', 'wp-statistics' ), $read_cap, WP_STATISTICS_HITS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'useronline' ) ) {
		$WP_Statistics->menu_slugs['online'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Online', 'wp-statistics' ), __( 'Online', 'wp-statistics' ), $read_cap, WP_STATISTICS_ONLINE_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'pages' ) ) {
		$WP_Statistics->menu_slugs['pages'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Pages', 'wp-statistics' ), __( 'Pages', 'wp-statistics' ), $read_cap, WP_STATISTICS_PAGES_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'pages' ) ) {
		$WP_Statistics->menu_slugs['categories'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Categories', 'wp-statistics' ), __( 'Categories', 'wp-statistics' ), $read_cap, WP_STATISTICS_CATEGORIES_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'pages' ) ) {
		$WP_Statistics->menu_slugs['tags'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Tags', 'wp-statistics' ), __( 'Tags', 'wp-statistics' ), $read_cap, WP_STATISTICS_TAGS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'pages' ) ) {
		$WP_Statistics->menu_slugs['authors'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Authors', 'wp-statistics' ), __( 'Authors', 'wp-statistics' ), $read_cap, WP_STATISTICS_AUTHORS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['referrers'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Referrers', 'wp-statistics' ), __( 'Referrers', 'wp-statistics' ), $read_cap, WP_STATISTICS_REFERRERS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['searches'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Search Engines', 'wp-statistics' ), __( 'Search Engines', 'wp-statistics' ), $read_cap, WP_STATISTICS_SEARCHES_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['words'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Search Words', 'wp-statistics' ), __( 'Search Words', 'wp-statistics' ), $read_cap, WP_STATISTICS_WORDS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['top.visotors'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Top Visitors Today', 'wp-statistics' ), __( 'Top Visitors Today', 'wp-statistics' ), $read_cap, WP_STATISTICS_TOP_VISITORS_PAGE, 'wp_statistics_log' );
	}
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		$WP_Statistics->menu_slugs['visitors'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Visitors', 'wp-statistics' ), __( 'Visitors', 'wp-statistics' ), $read_cap, WP_STATISTICS_VISITORS_PAGE, 'wp_statistics_log' );
	}

	$WP_Statistics->menu_slugs['break'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, '', '', $read_cap, 'wps_break_menu', 'wp_statistics_log' );

	$WP_Statistics->menu_slugs['optimize'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Optimization', 'wp-statistics' ), __( 'Optimization', 'wp-statistics' ), $manage_cap, WP_STATISTICS_OPTIMIZATION_PAGE, 'wp_statistics_optimization' );
	$WP_Statistics->menu_slugs['settings'] = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Settings', 'wp-statistics' ), __( 'Settings', 'wp-statistics' ), $read_cap, WP_STATISTICS_SETTINGS_PAGE, 'wp_statistics_settings' );
	$WP_Statistics->menu_slugs['plugins']  = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Add-Ons', 'wp-statistics' ), '<span style="color:#dc6b26">' . __( 'Add-Ons', 'wp-statistics' ) . '</span>', $read_cap, WP_STATISTICS_PLUGINS_PAGE, 'wp_statistics_plugins' );
	$WP_Statistics->menu_slugs['donate']   = add_submenu_page( WP_STATISTICS_OVERVIEW_PAGE, __( 'Donate', 'wp-statistics' ), '<span style="color:#459605">' . __( 'Donate', 'wp-statistics' ) . '</span>', $read_cap, WP_STATISTICS_DONATE_PAGE, 'wp_statistics_donate' );

	// Add action to load the meta boxes to the overview page.
	add_action( 'load-' . $WP_Statistics->menu_slugs['overview'], 'wp_statistics_load_overview_page' );
}

add_action( 'admin_menu', 'wp_statistics_menu' );

function wp_statistics_load_overview_page() {
	GLOBAL $WP_Statistics;

	// Right side "wide" widgets
	if ( $WP_Statistics->get_option( 'visits' ) ) {
		add_meta_box( 'wps_hits_postbox', __( 'Hit Statistics', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'hits' ) );
	}

	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		add_meta_box( 'wps_top_visitors_postbox', __( 'Top Visitors', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'top.visitors' ) );
		add_meta_box( 'wps_search_postbox', __( 'Search Engine Referrals', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'search' ) );
		add_meta_box( 'wps_words_postbox', __( 'Latest Search Words', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'words' ) );
		add_meta_box( 'wps_recent_postbox', __( 'Recent Visitors', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'recent' ) );

		if ( $WP_Statistics->get_option( 'geoip' ) ) {
			add_meta_box( 'wps_map_postbox', __( 'Today\'s Visitors Map', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'map' ) );
		}
	}

	if ( $WP_Statistics->get_option( 'pages' ) ) {
		add_meta_box( 'wps_pages_postbox', __( 'Top 10 Pages', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'normal', null, array( 'widget' => 'pages' ) );
	}

	// Left side "thin" widgets.
	if ( $WP_Statistics->get_option( 'visitors' ) ) {
		add_meta_box( 'wps_summary_postbox', __( 'Summary', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'summary' ) );
		add_meta_box( 'wps_browsers_postbox', __( 'Browsers', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'browsers' ) );
		add_meta_box( 'wps_referring_postbox', __( 'Top Referring Sites', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'referring' ) );

		if ( $WP_Statistics->get_option( 'geoip' ) ) {
			add_meta_box( 'wps_countries_postbox', __( 'Top 10 Countries', 'wp-statistics' ), 'wp_statistics_generate_overview_postbox_contents', $WP_Statistics->menu_slugs['overview'], 'side', null, array( 'widget' => 'countries' ) );
		}
	}
}

// This function adds the primary menu to WordPress network.
function wp_statistics_networkmenu() {
	GLOBAL $WP_Statistics;

	// Get the read/write capabilities required to view/manage the plugin as set by the user.
	$read_cap   = wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) );
	$manage_cap = wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) );

	// Add the top level menu.
	add_menu_page( __( 'Statistics', 'wp-statistics' ), __( 'Statistics', 'wp-statistics' ), $read_cap, __FILE__, 'wp_statistics_network_overview', 'dashicons-chart-pie' );

	// Add the sub items.
	add_submenu_page( __FILE__, __( 'Overview', 'wp-statistics' ), __( 'Overview', 'wp-statistics' ), $read_cap, __FILE__, 'wp_statistics_network_overview' );

	$count = 0;
	$sites = $WP_Statistics->get_wp_sites_list();

	foreach ( $sites as $blog_id ) {
		$details = get_blog_details( $blog_id );
		add_submenu_page( __FILE__, $details->blogname, $details->blogname, $manage_cap, 'wp_statistics_blogid_' . $blog_id, 'wp_statistics_goto_network_blog' );

		$count ++;
		if ( $count > 15 ) {
			break;
		}
	}
}

if ( is_multisite() ) {
	add_action( 'network_admin_menu', 'wp_statistics_networkmenu' );
}

function wp_statistics_network_overview() {
	GLOBAL $WP_Statistics;

	?>
    <div id="wrap">
        <br>

        <table class="widefat wp-list-table" style="width: auto;">
            <thead>
            <tr>
                <th style='text-align: left'><?php _e( 'Site', 'wp-statistics' ); ?></th>
                <th style='text-align: left'><?php _e( 'Options', 'wp-statistics' ); ?></th>
            </tr>
            </thead>

            <tbody>
			<?php
			$i = 0;

			$options = array(
				__( 'Overview', 'wp-statistics' )           => WP_STATISTICS_OVERVIEW_PAGE,
				__( 'Browsers', 'wp-statistics' )           => WP_STATISTICS_BROWSERS_PAGE,
				__( 'Countries', 'wp-statistics' )          => WP_STATISTICS_COUNTRIES_PAGE,
				__( 'Exclusions', 'wp-statistics' )         => WP_STATISTICS_EXCLUSIONS_PAGE,
				__( 'Hits', 'wp-statistics' )               => WP_STATISTICS_HITS_PAGE,
				__( 'Online', 'wp-statistics' )             => WP_STATISTICS_ONLINE_PAGE,
				__( 'Pages', 'wp-statistics' )              => WP_STATISTICS_PAGES_PAGE,
				__( 'Referrers', 'wp-statistics' )          => WP_STATISTICS_REFERRERS_PAGE,
				__( 'Searches', 'wp-statistics' )           => WP_STATISTICS_SEARCHES_PAGE,
				__( 'Search Words', 'wp-statistics' )       => WP_STATISTICS_WORDS_PAGE,
				__( 'Top Visitors Today', 'wp-statistics' ) => WP_STATISTICS_TOP_VISITORS_PAGE,
				__( 'Visitors', 'wp-statistics' )           => WP_STATISTICS_VISITORS_PAGE,
				__( 'Optimization', 'wp-statistics' )       => WP_STATISTICS_OPTIMIZATION_PAGE,
				__( 'Settings', 'wp-statistics' )           => WP_STATISTICS_SETTINGS_PAGE
			);

			$sites = $WP_Statistics->get_wp_sites_list();

			foreach ( $sites as $blog_id ) {
				$details   = get_blog_details( $blog_id );
				$url       = get_admin_url( $blog_id, '/' ) . 'admin.php?page=';
				$alternate = '';

				if ( $i % 2 == 0 ) {
					$alternate = ' class="alternate"';
				}
				?>

                <tr<?php echo $alternate; ?>>
                    <td style='text-align: left'>
						<?php echo $details->blogname; ?>
                    </td>
                    <td style='text-align: left'>
						<?php
						$options_len = count( $options );
						$j           = 0;

						foreach ( $options as $key => $value ) {
							echo '<a href="' . $url . $value . '">' . $key . '</a>';
							$j ++;
							if ( $j < $options_len ) {
								echo ' - ';
							}
						}
						?>
                    </td>
                </tr>
				<?php
				$i ++;
			}
			?>
            </tbody>
        </table>
    </div>
	<?php
}

function wp_statistics_goto_network_blog() {
	global $plugin_page;

	$blog_id = str_replace( 'wp_statistics_blogid_', '', $plugin_page );

	$details = get_blog_details( $blog_id );

	// Get the admin url for the current site.
	$url = get_admin_url( $blog_id ) . '/admin.php?page=' . WP_STATISTICS_OVERVIEW_PAGE;

	echo "<script>window.location.href = '$url';</script>";
}

function wp_statistics_plugins() {
	// Load our CSS to be used.
	wp_enqueue_style( 'wpstatistics-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', true, '1.0' );

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
	include_once dirname( __FILE__ ) . '/includes/templates/plugins.php';
}

function wp_statistics_donate() {
	echo "<script>window.location.href='http://wp-statistics.com/donate';</script>";
}

// This function adds the admin bar menu if the user has selected it.
function wp_statistics_menubar() {
	GLOBAL $wp_admin_bar, $wp_version, $WP_Statistics;

	// Find out if the user can read or manage statistics.
	$read   = current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) ) );
	$manage = current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) ) );

	if ( is_admin_bar_showing() && ( $read || $manage ) ) {

		$AdminURL = get_admin_url();

		if ( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
			$wp_admin_bar->add_menu( array(
				'id'    => 'wp-statistic-menu',
				'title' => '<span class="ab-icon"></span>',
				'href'  => $AdminURL . 'admin.php?page=' . WP_STATISTICS_OVERVIEW_PAGE
			) );
		} else {
			$wp_admin_bar->add_menu( array(
				'id'    => 'wp-statistic-menu',
				'title' => '<img src="' . plugin_dir_url( __FILE__ ) . '/assets/images/icon.png"/>',
				'href'  => $AdminURL . 'admin.php?page=' . WP_STATISTICS_OVERVIEW_PAGE
			) );
		}

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-useronline',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'Online User', 'wp-statistics' ) . ": " . wp_statistics_useronline(),
			'href'   => $AdminURL . 'admin.php?page=' . WP_STATISTICS_ONLINE_PAGE
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-todayvisitor',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'Today\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'today' )
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-todayvisit',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'Today\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'today' )
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-yesterdayvisitor',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'Yesterday\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'yesterday' )
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-yesterdayvisit',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'Yesterday\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'yesterday' )
		) );

		$wp_admin_bar->add_menu( array(
			'id'     => 'wp-statistics-menu-viewstats',
			'parent' => 'wp-statistic-menu',
			'title'  => __( 'View Stats', 'wp-statistics' ),
			'href'   => $AdminURL . 'admin.php?page=' . WP_STATISTICS_OVERVIEW_PAGE
		) );
	}
}

if ( $WP_Statistics->get_option( 'menu_bar' ) ) {
	add_action( 'admin_bar_menu', 'wp_statistics_menubar', 20 );
}

/**
 * This is the main statistics display function.
 *
 * @param string $log_type
 */
function wp_statistics_log( $log_type = "" ) {
	GLOBAL $wpdb, $WP_Statistics, $plugin_page;

	switch ( $plugin_page ) {
		case WP_STATISTICS_BROWSERS_PAGE:
			$log_type = 'all-browsers';

			break;
		case WP_STATISTICS_COUNTRIES_PAGE:
			$log_type = 'top-countries';

			break;
		case WP_STATISTICS_EXCLUSIONS_PAGE:
			$log_type = 'exclusions';

			break;
		case WP_STATISTICS_HITS_PAGE:
			$log_type = 'hit-statistics';

			break;
		case WP_STATISTICS_ONLINE_PAGE:
			$log_type = 'online';

			break;
		case WP_STATISTICS_PAGES_PAGE:
			$log_type = 'top-pages';

			break;
		case WP_STATISTICS_CATEGORIES_PAGE:
			$log_type = 'categories';

			break;
		case WP_STATISTICS_TAGS_PAGE:
			$log_type = 'tags';

			break;
		case WP_STATISTICS_AUTHORS_PAGE:
			$log_type = 'authors';

			break;
		case WP_STATISTICS_REFERRERS_PAGE:
			$log_type = 'top-referring-site';

			break;
		case WP_STATISTICS_SEARCHES_PAGE:
			$log_type = 'search-statistics';

			break;
		case WP_STATISTICS_WORDS_PAGE:
			$log_type = 'last-all-search';

			break;
		case WP_STATISTICS_TOP_VISITORS_PAGE:
			$log_type = 'top-visitors';

			break;
		case WP_STATISTICS_VISITORS_PAGE:
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
	if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_option' ) ) ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// We want to make sure the tables actually exist before we blindly start access them.
	$dbname = DB_NAME;
	$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_visitor' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_visit' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_exclusions' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_historical' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_pages' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_useronline' OR `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_search'" );

	if ( $result != 7 ) {
		$get_bloginfo_url = get_admin_url() . "admin.php?page=" . WP_STATISTICS_OPTIMIZATION_PAGE . "&tab=database";

		$missing_tables = array();

		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_visitor'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_visitor';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_visit'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_visit';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_exclusions'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_exclusions';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_historical'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_historical';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_useronline'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_useronline';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_pages'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_pages';
		}
		$result = $wpdb->query( "SHOW TABLES WHERE `Tables_in_{$dbname}` = '{$wpdb->prefix}statistics_search'" );
		if ( $result != 1 ) {
			$missing_tables[] = $wpdb->prefix . 'statistics_search';
		}

		wp_die( '<div class="error"><p>' . sprintf( __( 'The following plugin table(s) do not exist in the database, please re-run the %s install routine %s: ', 'wp-statistics' ), '<a href="' . $get_bloginfo_url . '">', '</a>' ) . implode( ', ', $missing_tables ) . '</p></div>' );
	}

	// Load the postbox script that provides the widget style boxes.
	wp_enqueue_script( 'common' );
	wp_enqueue_script( 'wp-lists' );
	wp_enqueue_script( 'postbox' );

	// Load the css we use for the statistics pages.
	wp_enqueue_style( 'wpstatistics-log-css', plugin_dir_url( __FILE__ ) . 'assets/css/log.css', true, '1.1' );
	wp_enqueue_style( 'wpstatistics-pagination-css', plugin_dir_url( __FILE__ ) . 'assets/css/pagination.css', true, '1.0' );

	// Don't forget the right to left support.
	if ( is_rtl() ) {
		wp_enqueue_style( 'wpstatistics-rtl-css', plugin_dir_url( __FILE__ ) . 'assets/css/rtl.css', true, '1.1' );
	}

	// Load the pagination code.
	include_once dirname( __FILE__ ) . '/includes/classes/pagination.class.php';

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
			include_once dirname( __FILE__ ) . '/includes/log/' . $log_type . '.php';

			break;
		case 'last-all-search':
			include_once dirname( __FILE__ ) . '/includes/log/last-search.php';

			break;
		case 'last-all-visitor':
			include_once dirname( __FILE__ ) . '/includes/log/last-visitor.php';

			break;
		case 'top-referring-site':
			include_once dirname( __FILE__ ) . '/includes/log/top-referring.php';

			break;
		case 'top-pages':
			// If we've been given a page id or uri to get statistics for, load the page stats, otherwise load the page stats overview page.
			if ( array_key_exists( 'page-id', $_GET ) || array_key_exists( 'page-uri', $_GET ) ) {
				include_once dirname( __FILE__ ) . '/includes/log/page-statistics.php';
			} else {
				include_once dirname( __FILE__ ) . '/includes/log/top-pages.php';
			}

			break;
		default:
			wp_enqueue_style( 'wpstatistics-jqvmap-css', plugin_dir_url( __FILE__ ) . 'assets/jqvmap/jqvmap.css', true, '1.5.1' );
			wp_enqueue_script( 'wpstatistics-jquery-vmap', plugin_dir_url( __FILE__ ) . 'assets/jqvmap/jquery.vmap.js', true, '1.5.1' );
			wp_enqueue_script( 'wpstatistics-jquery-vmap-world', plugin_dir_url( __FILE__ ) . 'assets/jqvmap/maps/jquery.vmap.world.js', true, '1.5.1' );

			// Load our custom widgets handling javascript.
			wp_enqueue_script( 'wp_statistics_log', plugin_dir_url( __FILE__ ) . 'assets/js/log.js' );

			include_once dirname( __FILE__ ) . '/includes/log/log.php';

			break;
	}
}

// This function loads the optimization page code.
function wp_statistics_optimization() {
	GLOBAL $wpdb, $WP_Statistics;

	// Check the current user has the rights to be here.
	if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'manage_capability', 'manage_options' ) ) ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
	// during the creation of the class.  Instead load them now that the user exists.
	$WP_Statistics->load_user_options();

	// Load our CSS to be used.
	wp_enqueue_style( 'wpstatistics-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', true, '1.0' );

	// Load the jQuery UI code to create the tabs.
	wp_register_style( "jquery-ui-css", plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui-1.10.4.custom.min.css' );
	wp_enqueue_style( "jquery-ui-css" );

	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-tabs' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'rtl-css', plugin_dir_url( __FILE__ ) . 'assets/css/rtl.css', true, '1.1' );
	}

	// Get the row count for each of the tables, we'll use this later on in the wps_optimization.php file.
	$result['useronline'] = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_useronline`" );
	$result['visit']      = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_visit`" );
	$result['visitor']    = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_visitor`" );
	$result['exclusions'] = $wpdb->get_var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_exclusions`" );
	$result['pages']      = $wpdb->get_var( "SELECT COUNT(uri) FROM `{$wpdb->prefix}statistics_pages`" );
	$result['historical'] = $wpdb->get_Var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_historical`" );
	$result['search']     = $wpdb->get_Var( "SELECT COUNT(ID) FROM `{$wpdb->prefix}statistics_search`" );

	include_once dirname( __FILE__ ) . "/includes/optimization/wps-optimization.php";
}

// This function displays the HTML for the settings page.
function wp_statistics_settings() {
	GLOBAL $WP_Statistics;

	// Check the current user has the rights to be here.
	if ( ! current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( 'read_capability', 'manage_options' ) ) ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// When we create $WP_Statistics the user has not been authenticated yet so we cannot load the user preferences
	// during the creation of the class.  Instead load them now that the user exists.
	$WP_Statistics->load_user_options();

	// Load our CSS to be used.
	wp_enqueue_style( 'wpstatistics-admin-css', plugin_dir_url( __FILE__ ) . 'assets/css/admin.css', true, '1.0' );

	// Load the jQuery UI code to create the tabs.
	wp_register_style( "jquery-ui-css", plugin_dir_url( __FILE__ ) . 'assets/css/jquery-ui-1.10.4.custom.min.css' );
	wp_enqueue_style( "jquery-ui-css" );

	wp_enqueue_script( 'jquery-ui-core' );
	wp_enqueue_script( 'jquery-ui-tabs' );

	if ( is_rtl() ) {
		wp_enqueue_style( 'rtl-css', plugin_dir_url( __FILE__ ) . 'assets/css/rtl.css', true, '1.1' );
	}

	// We could let the download happen at the end of the page, but this way we get to give some
	// feedback to the users about the result.
	if ( $WP_Statistics->get_option( 'update_geoip' ) == true ) {
		echo wp_statistics_download_geoip();
	}

	include_once dirname( __FILE__ ) . "/includes/settings/wps-settings.php";
}

function wp_statistics_enqueue_scripts( $hook ) {
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

		wp_enqueue_script( 'wp-statistics-chart-js', WP_STATISTICS_PLUGIN_DIR . 'assets/js/Chart.bundle.min.js', false, '2.7.0', $load_in_footer );
	}
}

add_action( 'admin_enqueue_scripts', 'wp_statistics_enqueue_scripts' );