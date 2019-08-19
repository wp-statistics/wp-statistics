<?php

/**
 * Class WP_Statistics
 *
 * This is the primary class for WP Statistics recording hits on the WordPress site.
 * It is extended by the Hits class and the GEO_IP_Hits class.
 * This class handles; visits, visitors and pages.
 */
class WP_Statistics {

	/**
	 * IP address of visitor
	 *
	 * @var bool|string
	 */
	public $ip = false;
	/**
	 * Hash of visitors IP address
	 *
	 * @var bool|string
	 */
	public $ip_hash = false;
	/**
	 * Agent of visitor browser
	 *
	 * @var string
	 */
	public $agent;
	/**
	 * a coefficient to record number of visits
	 *
	 * @var int
	 */
	public $coefficient = 1;
	/**
	 * Visitor User ID
	 *
	 * @var int
	 */
	public $user_id = 0;
	/**
	 * Plugin options (Recorded in database)
	 *
	 * @var array
	 */
	public $options = array();
	/**
	 * User Options
	 *
	 * @var array
	 */
	public $user_options = array();
	/**
	 * Menu Slugs
	 *
	 * @var array
	 */
	public $menu_slugs = array();
	/**
	 * is current request
	 *
	 * @var bool
	 */
	public $is_ajax_logger_request = false;

	/**
	 * Result of queries
	 *
	 * @var
	 */
	private $result;
	/**
	 * Historical data
	 *
	 * @var array
	 */
	private $historical = array();
	/**
	 * is user options loaded?
	 *
	 * @var bool
	 */
	private $user_options_loaded = false;
	/**
	 * Timezone offset
	 *
	 * @var int|mixed|void
	 */
	private $tz_offset = 0;
	/**
	 * Country Codes
	 *
	 * @var bool|string
	 */
	private $country_codes = false;
	/**
	 * Referrer
	 *
	 * @var bool
	 */
	private $referrer = false;

	/**
	 * Installed Version
	 *
	 * @var string
	 */
	public static $installed_version;
	/**
	 * Registry for plugin settings
	 *
	 * @var array
	 */
	public static $reg = array();
	/**
	 * Pages slugs
	 *
	 * @var array
	 */
	public static $page = array();
	/**
	 * Rest Api init
	 *
	 * @var array
	 */
	public $restapi;
	/**
	 * Check Plugin Cache is enabled
	 *
	 * @var bool|string
	 */
	public $use_cache = false;

	/**
	 * __construct
	 * WP_Statistics constructor.
	 */
	public function __construct() {
		if ( ! isset( WP_Statistics::$reg['plugin-url'] ) ) {

			//Get Plugin Data
			if ( ! function_exists( 'get_plugin_data' ) ) {
				require( ABSPATH . 'wp-admin/includes/plugin.php' );
			}
			$plugin_data = get_plugin_data( WP_STATISTICS_MAIN_FILE );

			//Prepare Plugin config
			WP_Statistics::$reg = array(
				'plugin-data'          => $plugin_data,
				'plugin-url'           => plugin_dir_url( WP_STATISTICS_MAIN_FILE ),
				'plugin-dir'           => plugin_dir_path( WP_STATISTICS_MAIN_FILE ),
				'main-file'            => WP_STATISTICS_MAIN_FILE,
				'version'              => $plugin_data['Version'],
				'required-php-version' => '5.4.0',
			);
		}
	}

	/**
	 * List of $_SERVER
	 *
	 * @return array
	 */
	public static function list_of_server_ip_variable() {
		return array( 'REMOTE_ADDR', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'HTTP_X_REAL_IP', 'HTTP_X_CLUSTER_CLIENT_IP' );
	}

	/**
	 * Get Basis For Get User IP
	 */
	public static function getIPMethod() {

		// Set Default Method
		$method = 'REMOTE_ADDR';

		// Get Option
		$wp_statistics = get_option( 'wp_statistics' );
		if ( isset( $wp_statistics ) and is_array( $wp_statistics ) and isset( $wp_statistics['ip_method'] ) and trim( $wp_statistics['ip_method'] ) != "" ) {
			$method = $wp_statistics['ip_method'];
		}

		return $method;
	}

	/**
	 * Run when plugin loads
	 */
	public function run() {
		global $WP_Statistics;

		// Check the PHP version,
		// if we don't meet the minimum version to run WP Statistics return so we don't cause a critical error.
		if ( ! version_compare( phpversion(), WP_Statistics::$reg['required-php-version'], ">=" ) ) {
			add_action( 'admin_notices', 'WP_Statistics::unsupported_version_admin_notice', 10, 2 );

			return;
		}

		// Autoload composer
		require( WP_Statistics::$reg['plugin-dir'] . 'includes/vendor/autoload.php' );

		// Define an autoload method to automatically load classes in /includes/classes
		spl_autoload_register( array( $this, 'autoload' ) );

		//Set TimeZone
		$this->set_timezone();

		//Set Options
		$this->load_options();

		//Set coefficient
		$this->set_coefficient();

		// Check the cache option is enabled.
		if ( $this->get_option( 'use_cache_plugin' ) == true ) {
			$this->use_cache = 1;
		}

		//Load Rest Api
		$this->init_rest_api();

		//Get user Ip
		$this->get_IP();

		// Check if the has IP is enabled.
		if ( $this->get_option( 'hash_ips' ) == true ) {
			$this->ip_hash = $this->get_hash_string();
		}

		//Set Pages
		$this->set_pages();

		// Load Plugin Text Domain
		add_action( 'init', array( $this, 'load_textdomain' ) );

		// Load the rest of the required files for our global functions,
		// online user tracking and hit tracking.
		if ( ! function_exists( 'wp_statistics_useronline' ) ) {
			include WP_Statistics::$reg['plugin-dir'] . 'includes/functions/functions.php';
		}

		//Reset User Online Count
		add_action( 'wp_loaded', array( $this, 'reset_user_online' ) );

		//Get Current User Agent
		$this->agent   = $this->get_UserAgent();
		$WP_Statistics = $this;

		//Load WP_Statistics_Schedule
		new WP_Statistics_Schedule;

		if ( is_admin() ) {
			// JUST ADMIN AREA
			new WP_Statistics_Admin;
		} else {
			// JUST FRONTEND AREA
			new WP_Statistics_Frontend;
		}

		//Show Wordpress Admin Bar
		if ( $WP_Statistics->get_option( 'menu_bar' ) ) {
			add_action( 'admin_bar_menu', array( $this, 'menubar' ), 20 );
		}

		//Add Wp-statistics Widget
		add_action( 'widgets_init', array( $this, 'widget' ) );

		//Add Short Code `wpstatistics`
		add_shortcode( 'wpstatistics', 'WP_Statistics_Shortcode::shortcodes' );
	}

	/**
	 * Autoload classes of the plugin
	 *
	 * @param string $class Class name
	 */
	public function autoload( $class ) {
		// This check is for performance of loading plugin classes
		if ( ! class_exists( $class ) && substr( $class, 0, 14 ) === 'WP_Statistics_' ) {
			$lower_class_name = str_replace( '_', '-', strtolower( $class ) );
			$class_full_path  = WP_Statistics::$reg['plugin-dir'] . 'includes/classes/class-' . $lower_class_name . '.php';
			if ( file_exists( $class_full_path ) ) {
				require $class_full_path;
			}
		}
	}

	/**
	 * Loads the load_plugin_textdomain code.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-statistics', false, WP_Statistics::$reg['plugin-dir'] . 'languages' );
	}

	/**
	 * Check the REST API
	 */
	public function init_rest_api() {
		$this->restapi = new WP_Statistics_Rest();
	}

	/**
	 * Set Time Zone
	 */
	public function set_timezone() {
		if ( get_option( 'timezone_string' ) ) {
			$this->tz_offset = timezone_offset_get(
				timezone_open( get_option( 'timezone_string' ) ),
				new DateTime()
			);
		} elseif ( get_option( 'gmt_offset' ) ) {
			$this->tz_offset = get_option( 'gmt_offset' ) * 60 * 60;
		}
	}

	/**
	 * Set pages slugs
	 */
	public function set_pages() {
		if ( ! isset( WP_Statistics::$page['overview'] ) ) {

			/**
			 * List Of Admin Page Slug WP-statistics
			 *
			 * -- Array Arg ---
			 * key   : page key for using another methods
			 * value : Admin Page Slug
			 */
			$list = array(
				'overview'     => 'overview',
				'browser'      => 'browsers',
				'countries'    => 'countries',
				'exclusions'   => 'exclusions',
				'hits'         => 'hits',
				'online'       => 'online',
				'pages'        => 'pages',
				'categories'   => 'categories',
				'authors'      => 'authors',
				'tags'         => 'tags',
				'referrers'    => 'referrers',
				'searches'     => 'searches',
				'words'        => 'words',
				'top-visitors' => 'top_visitors',
				'visitors'     => 'visitors',
				'optimization' => 'optimization',
				'settings'     => 'settings',
				'plugins'      => 'plugins',
				'donate'       => 'donate',
			);
			foreach ( $list as $page_key => $page_slug ) {
				WP_Statistics::$page[ $page_key ] = 'wps_' . $page_slug . '_page';
			}
		}
	}

	/**
	 * Set Coefficient
	 */
	public function set_coefficient() {
		// Set the default co-efficient.
		$this->coefficient = $this->get_option( 'coefficient', 1 );
		// Double check the co-efficient setting to make sure it's not been set to 0.
		if ( $this->coefficient <= 0 ) {
			$this->coefficient = 1;
		}
	}

	/**
	 * Generate hash string
	 */
	public function get_hash_string() {
		// Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			return '#hash#' . $this->restapi->params( 'hash_ip' );
		}

		// Check the user agent has exist.
		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$key = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$key = 'Unknown';
		}

		return '#hash#' . sha1( $this->ip . $key );
	}

	/**
	 * Loads the options from WordPress
	 */
	public function load_options() {
		$this->options = get_option( 'wp_statistics' );

		if ( ! is_array( $this->options ) ) {
			$this->user_options = array();
		}
	}

	/**
	 * Registers Widget
	 */
	public function widget() {
		register_widget( 'WP_Statistics_Widget' );
	}

	/**
	 * geo ip Loader
	 *
	 * @param $pack
	 * @return bool|\GeoIp2\Database\Reader
	 */
	static function geoip_loader( $pack ) {

		$upload_dir = wp_upload_dir();
		$geoip      = $upload_dir['basedir'] . '/wp-statistics/' . WP_Statistics_Updates::$geoip[ $pack ]['file'] . '.mmdb';
		if ( file_exists( $geoip ) ) {
			try {
				$reader = new GeoIp2\Database\Reader( $geoip );
			} catch ( \MaxMind\Db\Reader\InvalidDatabaseException $e ) {
				return false;
			}
		} else {
			return false;
		}

		return $reader;
	}

	/**
	 * Loads the user options from WordPress.
	 * It is NOT called during the class constructor.
	 *
	 * @param bool|false $force
	 */
	public function load_user_options( $force = false ) {
		if ( $this->user_options_loaded == true && $force != true ) {
			return;
		}

		if ( $this->user_id == 0 ) {
			$this->user_id = get_current_user_id();
		}

		// Not sure why, but get_user_meta() is returning an array or array's unless $single is set to true.
		$this->user_options = get_user_meta( $this->user_id, 'wp_statistics', true );

		if ( ! is_array( $this->user_options ) ) {
			$this->user_options = array();
		}

		$this->user_options_loaded = true;
	}

	/**
	 * mimics WordPress's get_option() function but uses the array instead of individual options.
	 *
	 * @param      $option
	 * @param null $default
	 *
	 * @return bool|null
	 */
	public function get_option( $option, $default = null ) {
		// If no options array exists, return FALSE.
		if ( ! is_array( $this->options ) ) {
			return false;
		}

		// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
		if ( ! array_key_exists( $option, $this->options ) ) {
			if ( isset( $default ) ) {
				return $default;
			} else {
				return false;
			}
		}

		// Return the option.
		return $this->options[ $option ];
	}

	/**
	 * mimics WordPress's get_user_meta() function
	 * But uses the array instead of individual options.
	 *
	 * @param      $option
	 * @param null $default
	 *
	 * @return bool|null
	 */
	public function get_user_option( $option, $default = null ) {
		// If the user id has not been set or no options array exists, return FALSE.
		if ( $this->user_id == 0 ) {
			return false;
		}
		if ( ! is_array( $this->user_options ) ) {
			return false;
		}

		// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
		if ( ! array_key_exists( $option, $this->user_options ) ) {
			if ( isset( $default ) ) {
				return $default;
			} else {
				return false;
			}
		}

		// Return the option.
		return $this->user_options[ $option ];
	}

	/**
	 * Mimics WordPress's update_option() function
	 * But uses the array instead of individual options.
	 *
	 * @param $option
	 * @param $value
	 */
	public function update_option( $option, $value ) {
		// Store the value in the array.
		$this->options[ $option ] = $value;

		// Write the array to the database.
		update_option( 'wp_statistics', $this->options );
	}

	/**
	 * Mimics WordPress's update_user_meta() function
	 * But uses the array instead of individual options.
	 *
	 * @param $option
	 * @param $value
	 *
	 * @return bool
	 */
	public function update_user_option( $option, $value ) {
		// If the user id has not been set return FALSE.
		if ( $this->user_id == 0 ) {
			return false;
		}

		// Store the value in the array.
		$this->user_options[ $option ] = $value;

		// Write the array to the database.
		update_user_meta( $this->user_id, 'wp_statistics', $this->user_options );
	}

	/**
	 * This function is similar to update_option,
	 * but it only stores the option in the array.
	 * This save some writing to the database if you have multiple values to update.
	 *
	 * @param $option
	 * @param $value
	 */
	public function store_option( $option, $value ) {
		$this->options[ $option ] = $value;
	}

	/**
	 * This function is similar to update_user_option,
	 * but it only stores the option in the array.
	 * This save some writing to the database if you have multiple values to update.
	 *
	 * @param $option
	 * @param $value
	 *
	 * @return bool
	 */
	public function store_user_option( $option, $value ) {
		// If the user id has not been set return FALSE.
		if ( $this->user_id == 0 ) {
			return false;
		}

		$this->user_options[ $option ] = $value;
	}

	/**
	 * Saves the current options array to the database.
	 */
	public function save_options() {
		update_option( 'wp_statistics', $this->options );
	}

	/**
	 * Saves the current user options array to the database.
	 *
	 * @return bool
	 */
	public function save_user_options() {
		if ( $this->user_id == 0 ) {
			return false;
		}

		update_user_meta( $this->user_id, 'wp_statistics', $this->user_options );
	}

	/**
	 * Check to see if an option is currently set or not.
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	public function isset_option( $option ) {
		if ( ! is_array( $this->options ) ) {
			return false;
		}

		return array_key_exists( $option, $this->options );
	}

	/**
	 * check to see if a user option is currently set or not.
	 *
	 * @param $option
	 *
	 * @return bool
	 */
	public function isset_user_option( $option ) {
		if ( $this->user_id == 0 ) {
			return false;
		}
		if ( ! is_array( $this->user_options ) ) {
			return false;
		}

		return array_key_exists( $option, $this->user_options );
	}

	/**
	 * During installation of WP Statistics some initial data needs to be loaded
	 * in to the database so errors are not displayed.
	 * This function will add some initial data if the tables are empty.
	 */
	public function Primary_Values() {
		global $wpdb;

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_useronline" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_useronline",
				array(
					'ip'        => $this->store_ip_to_db(),
					'timestamp' => $this->Current_Date( 'U' ),
					'date'      => $this->Current_Date(),
					'referred'  => $this->get_Referred(),
					'agent'     => $this->agent['browser'],
					'platform'  => $this->agent['platform'],
					'version'   => $this->agent['version'],
				)
			);
		}

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_visit" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_visit",
				array(
					'last_visit'   => $this->Current_Date(),
					'last_counter' => $this->Current_date( 'Y-m-d' ),
					'visit'        => 1,
				)
			);
		}

		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_visitor" );

		if ( ! $this->result ) {

			$wpdb->insert(
				$wpdb->prefix . "statistics_visitor",
				array(
					'last_counter' => $this->Current_date( 'Y-m-d' ),
					'referred'     => $this->get_Referred(),
					'agent'        => $this->agent['browser'],
					'platform'     => $this->agent['platform'],
					'version'      => $this->agent['version'],
					'ip'           => $this->store_ip_to_db(),
					'location'     => '000',
				)
			);
		}
	}

	/**
	 * During installation of WP Statistics some initial options need to be set.
	 * This function will save a set of default options for the plugin.
	 *
	 * @param null $option_name
	 *
	 * @return array
	 */
	public function Default_Options( $option_name = null ) {
		$options = array();

		if ( ! isset( $wps_robotarray ) ) {
			// Get the robots list, we'll use this for both upgrades and new installs.
			include( WP_Statistics::$reg['plugin-dir'] . 'includes/robotslist.php' );
		}

		$options['robotlist'] = trim( $wps_robotslist );

		// By default, on new installs, use the new search table.
		$options['search_converted'] = 1;

		// If this is a first time install or an upgrade and we've added options, set some intelligent defaults.
		$options['anonymize_ips']         = false;
		$options['geoip']                 = false;
		$options['useronline']            = true;
		$options['visits']                = true;
		$options['visitors']              = true;
		$options['pages']                 = true;
		$options['check_online']          = '120';
		$options['menu_bar']              = false;
		$options['coefficient']           = '1';
		$options['stats_report']          = false;
		$options['time_report']           = 'daily';
		$options['send_report']           = 'mail';
		$options['content_report']        = '';
		$options['update_geoip']          = true;
		$options['store_ua']              = false;
		$options['robotlist']             = $wps_robotslist;
		$options['exclude_administrator'] = true;
		$options['disable_se_clearch']    = true;
		$options['disable_se_qwant']      = true;
		$options['disable_se_baidu']      = true;
		$options['disable_se_ask']        = true;
		$options['map_type']              = 'jqvmap';
		$options['ip_method']             = 'REMOTE_ADDR';

		$options['force_robot_update'] = true;

		if ( $option_name and isset( $options[ $option_name ] ) ) {
			return $options[ $option_name ];
		}

		return $options;
	}

	/**
	 * Processes a string that represents an IP address and returns
	 * either FALSE if it's invalid or a valid IP4 address.
	 *
	 * @param $ip
	 *
	 * @return bool|string
	 */
	private function get_ip_value( $ip ) {
		// Reject anything that's not a string.
		if ( ! is_string( $ip ) ) {
			return false;
		}

		// Trim off any spaces.
		$ip = trim( $ip );

		// Process IPv4 and v6 addresses separately.
		if ( $this->isValidIPv6( $ip ) ) {
			// Reject any IPv6 addresses if IPv6 is not compiled in to this version of PHP.
			if ( ! defined( 'AF_INET6' ) ) {
				return false;
			}
		} else {
			// Trim off any port values that exist.
			if ( strstr( $ip, ':' ) !== false ) {
				$temp = explode( ':', $ip );
				$ip   = $temp[0];
			}

			// Check to make sure the http header is actually an IP address and not some kind of SQL injection attack.
			$long = ip2long( $ip );

			// ip2long returns either -1 or FALSE if it is not a valid IP address depending on the PHP version, so check for both.
			if ( $long == - 1 || $long === false ) {
				return false;
			}
		}

		// If the ip address is blank, reject it.
		if ( $ip == '' ) {
			return false;
		}

		// We're got a real IP address, return it.
		return $ip;
	}

	/**
	 * Returns the current IP address of the remote client.
	 *
	 * @return bool|string
	 */
	public function get_IP() {

		//Check If Rest Api Request
		if ( $this->restapi->is_rest() ) {
			$this->ip = sanitize_text_field( $this->restapi->params( 'ip' ) );
			if ( filter_var( $this->ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
				return $this->ip;
			}
		}

		// Check to see if we've already retrieved the IP address and if so return the last result.
		if ( $this->ip !== false ) {
			return $this->ip;
		}

		// Get User Set $_SERVER HEADER
		$ip_method = self::getIPMethod();

		// Get User IP
		if ( isset( $_SERVER[ $ip_method ] ) ) {
			$this->ip = esc_html( $_SERVER[ $ip_method ] );
		}

		/**
		 * This Filter Used For Custom $_SERVER String
		 */
		$user_ip = apply_filters( 'wp_statistics_sanitize_user_ip', $this->ip );

		// Check If X_FORWARDED_FOR
		foreach ( explode( ',', $user_ip ) as $ip ) {
			$ip = trim( $ip );
			if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) !== false ) {
				$user_ip = $ip;
			}
		}

		// If no valid ip address has been found, use 127.0.0.1 (aka localhost).
		if ( false === $user_ip ) {
			$this->ip = '127.0.0.1';
		} else {
			$this->ip = $user_ip;
		}

		return $this->ip;
	}

	/**
	 * Store User IP To Database
	 */
	public function store_ip_to_db() {

		//Get User ip
		$user_ip = $this->ip;

		// use 127.0.0.1 If no valid ip address has been found.
		if ( false === $user_ip ) {
			return '127.0.0.1';
		}

		// If the anonymize IP enabled for GDPR.
		if ( $this->get_option( 'anonymize_ips' ) == true ) {
			$user_ip = substr( $user_ip, 0, strrpos( $user_ip, '.' ) ) . '.0';
		}

		return $user_ip;
	}

	/**
	 * Check IP contain Special Character
	 *
	 * @param $ip
	 * @return bool
	 */
	public function check_sanitize_ip( $ip ) {
		$preg = preg_replace( '/[^0-9- .:]/', '', $ip );
		return $preg == $ip;
	}

	/**
	 * Validate an IPv6 IP address
	 *
	 * @param  string $ip
	 *
	 * @return boolean - true/false
	 */
	private function isValidIPv6( $ip ) {
		if ( false === filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Calls the user agent parsing code.
	 *
	 * @return array|\string[]
	 */
	public function get_UserAgent() {
		//Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			return array(
				'browser'  => $this->restapi->params( 'browser' ),
				'platform' => $this->restapi->params( 'platform' ),
				'version'  => $this->restapi->params( 'version' )
			);
		}

		// Check function exist.
		if ( function_exists( 'getallheaders' ) ) {
			$user_agent = getallheaders();
		} elseif ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			$user_agent = $_SERVER['HTTP_USER_AGENT'];
		} else {
			$user_agent = '';
		}

		$result = new WhichBrowser\Parser( $user_agent );
		$agent  = array(
			'browser'  => ( isset( $result->browser->name ) ) ? $result->browser->name : _x( 'Unknown', 'Browser', 'wp-statistics' ),
			'platform' => ( isset( $result->os->name ) ) ? $result->os->name : _x( 'Unknown', 'Platform', 'wp-statistics' ),
			'version'  => ( isset( $result->os->version->value ) ) ? $result->os->version->value : _x( 'Unknown', 'Version', 'wp-statistics' ),
		);

		return $agent;
	}

	/**
	 * return the referrer link for the current user.
	 *
	 * @param bool|false $default_referrer
	 *
	 * @return array|bool|string|void
	 */
	public function get_Referred( $default_referrer = false ) {

		//Check If Rest Request
		if ( $this->restapi->is_rest() ) {
			$this->referrer = $this->restapi->params( 'referred' );

			return $this->referrer;
		}

		if ( $this->referrer !== false ) {
			return $this->referrer;
		}

		$this->referrer = '';

		if ( isset( $_SERVER['HTTP_REFERER'] ) ) {
			$this->referrer = $_SERVER['HTTP_REFERER'];
		}
		if ( $default_referrer ) {
			$this->referrer = $default_referrer;
		}

		$this->referrer = esc_sql( strip_tags( $this->referrer ) );

		if ( ! $this->referrer ) {
			$this->referrer = get_bloginfo( 'url' );
		}

		if ( $this->get_option( 'addsearchwords', false ) ) {
			// Check to see if this is a search engine referrer
			$SEInfo = $this->Search_Engine_Info( $this->referrer );

			if ( is_array( $SEInfo ) ) {
				// If we're a known SE, check the query string
				if ( $SEInfo['tag'] != '' ) {
					$result = $this->Search_Engine_QueryString( $this->referrer );

					// If there were no search words, let's add the page title
					if ( $result == '' || $result == 'No search query found!' ) {
						$result = wp_title( '', false );
						if ( $result != '' ) {
							$this->referrer = esc_url(
								add_query_arg(
									$SEInfo['querykey'],
									urlencode( '~"' . $result . '"' ),
									$this->referrer
								)
							);
						}
					}
				}
			}
		}

		return $this->referrer;
	}

	/**
	 * Returns a date string in the desired format with a passed in timestamp.
	 *
	 * @param $format
	 * @param $timestamp
	 *
	 * @return bool|string
	 */
	public function Local_Date( $format, $timestamp ) {
		return date( $format, $timestamp + $this->tz_offset );
	}

	// Returns a date string in the desired format.

	/**
	 * @param string $format
	 * @param null $strtotime
	 * @param null $relative
	 *
	 * @return bool|string
	 */
	public function Current_Date( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {

		if ( $strtotime ) {
			if ( $relative ) {
				return date( $format, strtotime( "{$strtotime} day", $relative ) + $this->tz_offset );
			} else {
				return date( $format, strtotime( "{$strtotime} day" ) + $this->tz_offset );
			}
		} else {
			return date( $format, time() + $this->tz_offset );
		}
	}

	/**
	 * Returns a date string in the desired format.
	 *
	 * @param string $format
	 * @param null $strtotime
	 * @param null $relative
	 *
	 * @return bool|string
	 */
	public function Real_Current_Date( $format = 'Y-m-d H:i:s', $strtotime = null, $relative = null ) {

		if ( $strtotime ) {
			if ( $relative ) {
				return date( $format, strtotime( "{$strtotime} day", $relative ) );
			} else {
				return date( $format, strtotime( "{$strtotime} day" ) );
			}
		} else {
			return date( $format, time() );
		}
	}

	/**
	 * Returns an internationalized date string in the desired format.
	 *
	 * @param string $format
	 * @param null $strtotime
	 * @param string $day
	 *
	 * @return string
	 */
	public function Current_Date_i18n( $format = 'Y-m-d H:i:s', $strtotime = null, $day = ' day' ) {

		if ( $strtotime ) {
			return date_i18n( $format, strtotime( "{$strtotime}{$day}" ) + $this->tz_offset );
		} else {
			return date_i18n( $format, time() + $this->tz_offset );
		}
	}

	/**
	 * Adds the timezone offset to the given time string
	 *
	 * @param $timestring
	 *
	 * @return int
	 */
	public function strtotimetz( $timestring ) {
		return strtotime( $timestring ) + $this->tz_offset;
	}

	/**
	 * Adds current time to timezone offset
	 *
	 * @return int
	 */
	public function timetz() {
		return time() + $this->tz_offset;
	}

	/**
	 * Checks to see if a search engine exists in the current list of search engines.
	 *
	 * @param      $search_engine_name
	 * @param null $search_engine
	 *
	 * @return int
	 */
	public function Check_Search_Engines( $search_engine_name, $search_engine = null ) {

		if ( strstr( $search_engine, $search_engine_name ) ) {
			return 1;
		}
	}

	/**
	 * Returns an array of information about a given search engine based on the url passed in.
	 * It is used in several places to get the SE icon or the sql query
	 * To select an individual SE from the database.
	 *
	 * @param bool|false $url
	 *
	 * @return array|bool
	 */
	public function Search_Engine_Info( $url = false ) {

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = isset( $_SERVER['HTTP_REFERER'] ) ? $this->get_Referred() : false;
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = parse_url( $url );

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = wp_statistics_searchengine_regex( $key );

			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

			if ( isset( $matches[1] ) ) {
				// Return the first matched SE.
				return $value;
			}
		}

		// If no SE matched, return some defaults.
		return array(
			'name'         => _x( 'Unknown', 'Search Engine', 'wp-statistics' ),
			'tag'          => '',
			'sqlpattern'   => '',
			'regexpattern' => '',
			'querykey'     => 'q',
			'image'        => 'unknown.png',
		);
	}

	/**
	 * Returns an array of information about a given search engine based on the url passed in.
	 * It is used in several places to get the SE icon or the sql query
	 * to select an individual SE from the database.
	 *
	 * @param bool|false $engine
	 *
	 * @return array|bool
	 */
	public function Search_Engine_Info_By_Engine( $engine = false ) {

		// If there is no URL and no referrer, always return false.
		if ( $engine == false ) {
			return false;
		}

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		if ( array_key_exists( $engine, $search_engines ) ) {
			return $search_engines[ $engine ];
		}

		// If no SE matched, return some defaults.
		return array(
			'name'         => _x( 'Unknown', 'Search Engine', 'wp-statistics' ),
			'tag'          => '',
			'sqlpattern'   => '',
			'regexpattern' => '',
			'querykey'     => 'q',
			'image'        => 'unknown.png',
		);
	}

	/**
	 * Parses a URL from a referrer and return the search query words used.
	 *
	 * @param bool|false $url
	 *
	 * @return bool|string
	 */
	public function Search_Engine_QueryString( $url = false ) {

		// If no URL was passed in, get the current referrer for the session.
		if ( ! $url ) {
			$url = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : false;
		}

		// If there is no URL and no referrer, always return false.
		if ( $url == false ) {
			return false;
		}

		// Parse the URL in to it's component parts.
		$parts = parse_url( $url );

		// Check to see if there is a query component in the URL (everything after the ?).  If there isn't one
		// set an empty array so we don't get errors later.
		if ( array_key_exists( 'query', $parts ) ) {
			parse_str( $parts['query'], $query );
		} else {
			$query = array();
		}

		// Get the list of search engines we currently support.
		$search_engines = wp_statistics_searchengine_list();

		// Loop through the SE list until we find which search engine matches.
		foreach ( $search_engines as $key => $value ) {
			$search_regex = wp_statistics_searchengine_regex( $key );

			preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

			if ( isset( $matches[1] ) ) {
				// Check to see if the query key the SE uses exists in the query part of the URL.
				if ( array_key_exists( $search_engines[ $key ]['querykey'], $query ) ) {
					$words = strip_tags( $query[ $search_engines[ $key ]['querykey'] ] );
				} else {
					$words = '';
				}

				// If no words were found, return a pleasant default.
				if ( $words == '' ) {
					$words = 'No search query found!';
				}

				return $words;
			}
		}

		// We should never actually get to this point, but let's make sure we return something
		// just in case something goes terribly wrong.
		return 'No search query found!';
	}

	/**
	 * Get historical data
	 *
	 * @param        $type
	 * @param string $id
	 *
	 * @return int|null|string
	 */
	public function Get_Historical_Data( $type, $id = '' ) {
		global $wpdb;

		$count = 0;
		switch ( $type ) {
			case 'visitors':
				if ( array_key_exists( 'visitors', $this->historical ) ) {
					return $this->historical['visitors'];
				} else {
					$result = $wpdb->get_var( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'visitors'" );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical['visitors'] = $count;
				}

				break;
			case 'visits':
				if ( array_key_exists( 'visits', $this->historical ) ) {
					return $this->historical['visits'];
				} else {
					$result = $wpdb->get_var( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'visits'" );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical['visits'] = $count;
				}

				break;
			case 'uri':
				if ( array_key_exists( $id, $this->historical ) ) {
					return $this->historical[ $id ];
				} else {
					$result = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'uri' AND uri = %s", $id ) );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical[ $id ] = $count;
				}

				break;
			case 'page':
				if ( array_key_exists( $id, $this->historical ) ) {
					return $this->historical[ $id ];
				} else {
					$result = $wpdb->get_var( $wpdb->prepare( "SELECT value FROM {$wpdb->prefix}statistics_historical WHERE category = 'uri' AND page_id = %d", $id ) );
					if ( $result > $count ) {
						$count = $result;
					}
					$this->historical[ $id ] = $count;
				}

				break;
		}

		return $count;
	}

	/**
	 * Get country codes
	 *
	 * @return array|bool|string
	 */
	public function get_country_codes() {
		if ( $this->country_codes == false ) {
			$ISOCountryCode = array();
			include( WP_Statistics::$reg['plugin-dir'] . "includes/functions/country-codes.php" );
			$this->country_codes = $ISOCountryCode;
		}

		return $this->country_codes;
	}

	/**
	 * Returns an array of site id's
	 *
	 * @return array
	 */
	public function get_wp_sites_list() {
		global $wp_version;
		$site_list = array();

		// wp_get_sites() is deprecated in 4.6 or above and replaced with get_sites().
		if ( version_compare( $wp_version, '4.6', '>=' ) ) {
			$sites = get_sites();
			foreach ( $sites as $site ) {
				$site_list[] = $site->blog_id;
			}
		} else {
			$sites = wp_get_sites();
			foreach ( $sites as $site ) {
				$site_list[] = $site['blog_id'];
			}
		}

		return $site_list;
	}

	/**
	 * Sanitizes the referrer
	 *
	 * @param     $referrer
	 * @param int $length
	 *
	 * @return string
	 */
	public function html_sanitize_referrer( $referrer, $length = - 1 ) {
		$referrer = trim( $referrer );

		if ( 'data:' == strtolower( substr( $referrer, 0, 5 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( 'javascript:' == strtolower( substr( $referrer, 0, 11 ) ) ) {
			$referrer = 'http://127.0.0.1';
		}

		if ( $length > 0 ) {
			$referrer = substr( $referrer, 0, $length );
		}

		return htmlentities( $referrer, ENT_QUOTES );
	}

	/**
	 * Get referrer link
	 *
	 * @param  string $referrer
	 * @param string $title
	 * @param bool $is_blank
	 * @return string
	 */
	public function get_referrer_link( $referrer, $title = '', $is_blank = false ) {
		$html_referrer = $this->html_sanitize_referrer( $referrer );

		if ( substr( $html_referrer, 0, 7 ) !== 'http://' and substr( $html_referrer, 0, 8 ) !== 'https://' ) {
			// relative address, use '//' to adapt both http and https
			$html_nr_referrer = '//' . $html_referrer;
		} else {
			$html_nr_referrer = $html_referrer;
		}

		$base_url = parse_url( $html_nr_referrer );
		$title    = ( trim( $title ) == "" ? $html_nr_referrer : $title );
		return "<a href='{$html_nr_referrer}' title='{$title}'" . ( $is_blank === true ? ' target="_blank"' : '' ) . ">{$base_url['host']}</a>";
	}


	/**
	 * Unsupported Version Admin Notice
	 */
	static function unsupported_version_admin_notice() {

		$screen = get_current_screen();
		if ( 'plugins' !== $screen->id ) {
			return;
		}
		?>
        <div class="error">
            <p style="max-width:800px;">
                <b><?php _e( 'WP Statistics Disabled', 'wp-statistics' ); ?></b> <?php _e( '&#151; You are running an unsupported version of PHP.', 'wp-statistics' ); ?>
            </p>

            <p style="max-width:800px;"><?php
				echo sprintf( __( 'WP Statistics has detected PHP version %s which is unsupported, WP Statistics requires PHP Version %s or higher!', 'wp-statistics' ), phpversion(), WP_Statistics::$reg['required-php-version'] );
				echo '</p><p>';
				echo __( 'Please contact your hosting provider to upgrade to a supported version or disable WP Statistics to remove this message.', 'wp-statistics' );
				?></p>
        </div>

		<?php
	}

	/**
	 * Adds the admin bar menu if the user has selected it.
	 */
	public function menubar() {
		global $wp_admin_bar;

		if ( is_admin_bar_showing() && ( wp_statistics_check_access_user() ) ) {

			/**
			 * List Of Admin Bar Wordpress
			 *
			 * --- Array Arg ---
			 * Key : ID of Admin bar
			 */
			$admin_bar_list = array(
				'wp-statistic-menu'                   => array(
					'title' => '<span class="ab-icon"></span>',
					'href'  => WP_Statistics_Admin_Pages::admin_url( 'overview' )
				),
				'wp-statistics-menu-useronline'       => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Online User', 'wp-statistics' ) . ": " . wp_statistics_useronline(),
					'href'   => WP_Statistics_Admin_Pages::admin_url( 'online' )
				),
				'wp-statistics-menu-todayvisitor'     => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Today\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'today' ),
				),
				'wp-statistics-menu-todayvisit'       => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Today\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'today' )
				),
				'wp-statistics-menu-yesterdayvisitor' => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Yesterday\'s Visitors', 'wp-statistics' ) . ": " . wp_statistics_visitor( 'yesterday' ),
				),
				'wp-statistics-menu-yesterdayvisit'   => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'Yesterday\'s Visits', 'wp-statistics' ) . ": " . wp_statistics_visit( 'yesterday' )
				),
				'wp-statistics-menu-viewstats'        => array(
					'parent' => 'wp-statistic-menu',
					'title'  => __( 'View Stats', 'wp-statistics' ),
					'href'   => WP_Statistics_Admin_Pages::admin_url( 'overview' )
				)
			);
			foreach ( $admin_bar_list as $id => $v_admin_bar ) {
				$wp_admin_bar->add_menu( array_merge( array( 'id' => $id ), $v_admin_bar ) );
			}
		}
	}

	/**
	 * Reset Online User Process By Option time
	 *
	 * @return string
	 */
	public function reset_user_online() {
		global $WP_Statistics, $wpdb;

		//Check User Online is Active in this Wordpress
		if ( $WP_Statistics->get_option( 'useronline' ) ) {

			//Get Not timestamp
			$now = $WP_Statistics->current_date( 'U' );

			// Set the default seconds a user needs to visit the site before they are considered offline.
			$reset_time = 120;

			// Get the user set value for seconds to check for users online.
			if ( $WP_Statistics->get_option( 'check_online' ) ) {
				$reset_time = $WP_Statistics->get_option( 'check_online' );
			}

			// We want to delete users that are over the number of seconds set by the admin.
			$time_diff = $now - $reset_time;

			//Last check Time
			$wps_run = get_option( "wp_statistics_check_useronline" );
			if ( isset( $wps_run ) and is_numeric( $wps_run ) ) {
				if ( ( $wps_run + $reset_time ) > $now ) {
					return;
				}
			}

			// Call the deletion query.
			$wpdb->query( "DELETE FROM `" . wp_statistics_db_table( 'useronline' ) . "` WHERE timestamp < {$time_diff}" );

			//Update Last run this Action
			update_option( "wp_statistics_check_useronline", $now );
		}
	}

	/**
	 * Get Number Days From install this plugin
	 * this method used for `ALL` Option in Time Range Pages
	 */
	public static function get_number_days_install_plugin() {
		global $wpdb, $WP_Statistics;

		//Create Empty default Option
		$first_day = '';

		//First Check Visitor Table , if not exist Web check Pages Table
		$list_tbl = array(
			'visitor' => array( 'order_by' => 'ID', 'column' => 'last_counter' ),
			'pages'   => array( 'order_by' => 'page_id', 'column' => 'date' ),
		);
		foreach ( $list_tbl as $tbl => $val ) {
			$first_day = $wpdb->get_var( "SELECT `" . $val['column'] . "` FROM `" . wp_statistics_db_table( $tbl ) . "` ORDER BY `" . $val['order_by'] . "` ASC LIMIT 1" );
			if ( ! empty( $first_day ) ) {
				break;
			}
		}

		//Calculate hit day if range is exist
		if ( empty( $first_day ) ) {
			$result = array(
				'days' => 1,
				'date' => current_time( 'timestamp' )
			);
		} else {
			$earlier = new \DateTime( $first_day );
			$later   = new \DateTime( $WP_Statistics->Current_date( 'Y-m-d' ) );
			$result  = array(
				'days'      => $later->diff( $earlier )->format( "%a" ),
				'timestamp' => strtotime( $first_day ),
				'first_day' => $first_day,
			);
		}

		return $result;
	}

}
