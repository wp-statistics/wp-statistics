<?php

/**
 * Class WP_Statistics_Admin
 */
final class WP_Statistics_Admin {

	public function __construct(){

		/**
		 * Required PHP Version
		 */
		WP_Statistics::$reg['required-php-version'] = '5.4.0';
		//define('WP_STATISTICS_REQUIRED_PHP_VERSION', '5.4.0');

		/**
		 * Required GEO IP PHP Version
		 */
		WP_Statistics::$reg['geoip-php-version'] = WP_Statistics::$reg['required-php-version'];
		//define('WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION', WP_Statistics::$reg['required_php_version']);

		$this->set_pages();

		// Check the PHP version,
		// if we don't meet the minimum version to run WP Statistics return so we don't cause a critical error.
		if ( ! version_compare(phpversion(), WP_Statistics::$reg['required-php-version'], ">=") ) {
			add_action('admin_notices', array( $this, 'unsupported_version_admin_notice' ), 10, 2);

			return;
		}

		add_action('admin_init', array( $this, 'admin_init' ), 9);

		// If we've been removed, return without doing anything else.
		if ( get_option('wp_statistics_removal') == 'done' ) {
			add_action('admin_notices', array( $this, 'removal_admin_notice' ), 10, 2);

			return;
		}

		// Check to see if we're installed and are the current version.
		WP_Statistics::$installed_version = get_option('wp_statistics_plugin_version');
		if ( WP_Statistics::$installed_version != WP_Statistics::$reg['version'] ) {
			new \WP_Statistics_Install;
		}

		// If we've been flagged to remove all of the data, then do so now.
		if ( get_option('wp_statistics_removal') == 'true' ) {
			new \WP_Statistics_Uninstall;
		}

		add_action('wp_dashboard_setup', 'WP_Statistics_Dashboard::widget_load');
		add_action('admin_footer', 'WP_Statistics_Dashboard::inline_javascript');
		add_action('add_meta_boxes', 'WP_Statistics_Editor::add_meta_box');
		new \WP_Statistics_Ajax;
	}

	public function set_pages() {
		if ( ! isset( WP_Statistics::$page['overview'] ) ) {
			/**
			 * Overview Page
			 */
			WP_Statistics::$page['overview'] = 'wps_overview_page';
			//define('WP_STATISTICS_OVERVIEW_PAGE', 'wps_overview_page');
			/**
			 * Browsers Page
			 */
			WP_Statistics::$page['browser'] = 'wps_browsers_page';
			//define('WP_STATISTICS_BROWSERS_PAGE', 'wps_browsers_page');
			/**
			 * Countries Page
			 */
			WP_Statistics::$page['countries'] = 'wps_countries_page';
			//define('WP_STATISTICS_COUNTRIES_PAGE', 'wps_countries_page');
			/**
			 * Exclusions Page
			 */
			WP_Statistics::$page['exclusions'] = 'wps_exclusions_page';
			//define('WP_STATISTICS_EXCLUSIONS_PAGE', 'wps_exclusions_page');
			/**
			 * Hits Page
			 */
			WP_Statistics::$page['hits'] = 'wps_hits_page';
			//define('WP_STATISTICS_HITS_PAGE', 'wps_hits_page');
			/**
			 * Online Page
			 */
			WP_Statistics::$page['online'] = 'wps_online_page';
			//define('WP_STATISTICS_ONLINE_PAGE', 'wps_online_page');
			/**
			 * Pages Page
			 */
			WP_Statistics::$page['pages'] = 'wps_pages_page';
			//define('WP_STATISTICS_PAGES_PAGE', 'wps_pages_page');
			/**
			 * Categories Page
			 */
			WP_Statistics::$page['categories'] = 'wps_categories_page';
			//define('WP_STATISTICS_CATEGORIES_PAGE', 'wps_categories_page');
			/**
			 * Authors Page
			 */
			WP_Statistics::$page['authors'] = 'wps_authors_page';
			//define('WP_STATISTICS_AUTHORS_PAGE', 'wps_authors_page');
			/**
			 * Tags Page
			 */
			WP_Statistics::$page['tags'] = 'wps_tags_page';
			//define('WP_STATISTICS_TAGS_PAGE', 'wps_tags_page');
			/**
			 * Referer Page
			 */
			WP_Statistics::$page['referrers'] = 'wps_referrers_page';
			//define('WP_STATISTICS_REFERRERS_PAGE', 'wps_referrers_page');
			/**
			 * Searched Phrases Page
			 */
			WP_Statistics::$page['searched-phrases'] = 'wps_searched_phrases_page';
			//define('WP_STATISTICS_SEARCHED_PHRASES_PAGE', 'wps_searched_phrases_page');
			/**
			 * Searches Page
			 */
			WP_Statistics::$page['searches'] = 'wps_searches_page';
			//define('WP_STATISTICS_SEARCHES_PAGE', 'wps_searches_page');
			/**
			 * Words Page
			 */
			WP_Statistics::$page['words'] = 'wps_words_page';
			//define('WP_STATISTICS_WORDS_PAGE', 'wps_words_page');
			/**
			 * Top Visitors Page
			 */
			WP_Statistics::$page['top-visitors'] = 'wps_top_visitors_page';
			//define('WP_STATISTICS_TOP_VISITORS_PAGE', 'wps_top_visitors_page');
			/**
			 * Visitors Page
			 */
			WP_Statistics::$page['visitors'] = 'wps_visitors_page';
			//define('WP_STATISTICS_VISITORS_PAGE', 'wps_visitors_page');
			/**
			 * Optimization Page
			 */
			WP_Statistics::$page['optimization'] = 'wps_optimization_page';
			//define('WP_STATISTICS_OPTIMIZATION_PAGE', 'wps_optimization_page');
			/**
			 * Settings Page
			 */
			WP_Statistics::$page['settings'] = 'wps_settings_page';
			//define('WP_STATISTICS_SETTINGS_PAGE', 'wps_settings_page');
			/**
			 * Plugins Page
			 */
			WP_Statistics::$page['plugins'] = 'wps_plugins_page';
			//define('WP_STATISTICS_PLUGINS_PAGE', 'wps_plugins_page');
			/**
			 * Donate Page
			 */
			WP_Statistics::$page['donate'] = 'wps_donate_page';
			//define('WP_STATISTICS_DONATE_PAGE', 'wps_donate_page');
		}
	}

	/**
	 * Unsupported Version Admin Notice
	 */
	public function unsupported_version_admin_notice() {

		$screen = get_current_screen();

		if ( 'plugins' !== $screen->id ) {
			return;
		}
		?>
		<div class="error">
			<p style="max-width:800px;">
				<b><?php _e(
						'WP Statistics Disabled',
						'wp-statistics'
					); ?></b> <?php _e(
					'&#151; You are running an unsupported version of PHP.',
					'wp-statistics'
				); ?>
			</p>

			<p style="max-width:800px;"><?php

				echo sprintf(
					__(
						'WP Statistics has detected PHP version %s which is unsupported, WP Statistics requires PHP Version %s or higher!',
						'wp-statistics'
					),
					phpversion(),
					WP_Statistics::$reg['required-php-version']
				);
				echo '</p><p>';
				echo __(
					'Please contact your hosting provider to upgrade to a supported version or disable WP Statistics to remove this message.',
					'wp-statistics'
				);
				?></p>
		</div>

		<?php
	}

	/**
	 * Loads the init code in admin area
	 */
	public function admin_init() {

		$this->export_data();

	}

	/**
	 * Set the headers to download the export file and then stop running WordPress.
	 */
	public function export_data(){

		if ( array_key_exists('wps_export', $_POST) ) {
			if ( ! function_exists('wp_statistics_export_data') ) {
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
				echo __('WP Statistics has been removed, please disable and delete it.', 'wp-statistics');
				echo '</p>';
				?></p>
		</div>
		<?php
	}


}