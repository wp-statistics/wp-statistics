<?php
/*
	This is the primary class for WP Statistics recording hits on the WordPress site.  It is extended by the Hits class and the GeoIPHits class.
	
	This class handles; visits, visitors and pages.
*/

	class WP_Statistics {
		
		// Setup our protected, private and public variables.		
		protected $db;
		protected $tb_prefix;
		protected $ip;
		protected $ip_hash = false;
		protected $agent;
		
		private $result;
		private $historical;
		private $user_options_loaded = false;
		
		public $coefficient = 1;
		public $plugin_dir = '';
		public $user_id = 0;
		public $options = array();
		public $user_options = array();

		// Construction function.
		public function __construct() {
		
			global $wpdb, $table_prefix;
			
			$this->db = $wpdb;
			$this->tb_prefix = $table_prefix;
			$this->agent = $this->get_UserAgent();
			$this->historical = array();

			// Load the options from the database
			$this->options = get_option( 'wp_statistics' ); 

			// Set the default co-efficient.
			$this->coefficient = $this->get_option('coefficient', 1);

			// Double check the co-efficient setting to make sure it's not been set to 0.
			if( $this->coefficient <= 0 ) { $this->coefficient = 1; }
			
			// This is a bit of a hack, we strip off the "includes/classes" at the end of the current class file's path.
			$this->plugin_dir = substr( dirname( __FILE__ ), 0, -17 );
			
			$this->get_IP();
			
			if( $this->get_option('hash_ips') == true ) { $this->ip_hash = '#hash#' . sha1( $this->ip + $_SERVER['HTTP_USER_AGENT'] ); }

		}

		// This function sets the current WordPress user id for the class.
		public function set_user_id() {
			if( $this->user_id == 0 ) {
				$this->user_id = get_current_user_id();
			}
		}
		
		// This function loads the options from WordPress, it is included here for completeness as the options are loaded automatically in the class constructor.
		public function load_options() {
			$this->options = get_option( 'wp_statistics' ); 
		}
		
		// This function loads the user options from WordPress.  It is NOT called during the class constructor.
		public function load_user_options( $force = false) {
			if( $this->user_options_loaded == true && $force != true ) { return; }
			
			$this->set_user_id();

			// Not sure why, but get_user_meta() is returning an array or array's unless $single is set to true.
			$this->user_options = get_user_meta( $this->user_id, 'wp_statistics', true );
			
			$this->user_options_loaded = true;
		}
		
		// The function mimics WordPress's get_option() function but uses the array instead of individual options.
		public function get_option($option, $default = null) {
			// If no options array exists, return FALSE.
			if( !is_array($this->options) ) { return FALSE; }
		
			// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
			if( !array_key_exists($option, $this->options) ) {
				if( isset( $default ) ) {
					return $default;
				} else {
					return FALSE;
				}
			}
			
			// Return the option.
			return $this->options[$option];
		}
		
		// This function mimics WordPress's get_user_meta() function but uses the array instead of individual options.
		public function get_user_option($option, $default = null) {
			// If the user id has not been set or no options array exists, return FALSE.
			if( $this->user_id == 0 ) {return FALSE; }
			if( !is_array($this->user_options) ) { return FALSE; }
			
			// if the option isn't set yet, return the $default if it exists, otherwise FALSE.
			if( !array_key_exists($option, $this->user_options) ) {
				if( isset( $default ) ) {
					return $default;
				} else {
					return FALSE;
				}
			}
			
			// Return the option.
			return $this->user_options[$option];
		}

		// The function mimics WordPress's update_option() function but uses the array instead of individual options.
		public function update_option($option, $value) {
			// Store the value in the array.
			$this->options[$option] = $value;
			
			// Write the array to the database.
			update_option('wp_statistics', $this->options);
		}
		
		// The function mimics WordPress's update_user_meta() function but uses the array instead of individual options.
		public function update_user_option($option, $value) {
			// If the user id has not been set return FALSE.
			if( $this->user_id == 0 ) { return FALSE; }

			// Store the value in the array.
			$this->user_options[$option] = $value;
			
			// Write the array to the database.
			update_user_meta( $this->user_id, 'wp_statistics', $this->user_options );
		}

		// This function is similar to update_option, but it only stores the option in the array.  This save some writing to the database if you have multiple values to update.
		public function store_option($option, $value) {
			$this->options[$option] = $value;
		}
		
		// This function is similar to update_user_option, but it only stores the option in the array.  This save some writing to the database if you have multiple values to update.
		public function store_user_option($option, $value) {
			// If the user id has not been set return FALSE.
			if( $this->user_id == 0 ) { return FALSE; }

			$this->user_options[$option] = $value;
		}

		// This function saves the current options array to the database.
		public function save_options() {
			update_option('wp_statistics', $this->options);
		}
		
		// This function saves the current user options array to the database.
		public function save_user_options() {
			if( $this->user_id == 0 ) { return FALSE; }

			update_user_meta( $this->user_id, 'wp_statistics', $this->user_options );
		}
		
		// This function check to see if an option is currently set or not.
		public function isset_option($option) {
			if( !is_array($this->options) ) { return FALSE; }
			
			return array_key_exists( $option, $this->options );
		}
		
		// This function check to see if a user option is currently set or not.
		public function isset_user_option($option) {
			if( $this->user_id == 0 ) { return FALSE; }
			if( !is_array($this->user_options) ) { return FALSE; }

			return array_key_exists( $option, $this->user_options );
		}

		// During installation of WP Statistics some inital data needs to be loaded in to the database so errors are not displayed.
		// This function will add some inital data if the tables are empty.
		public function Primary_Values() {
		
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline");
			
			if( !$this->result ) {
			
				$this->db->insert(
					$this->tb_prefix . "statistics_useronline",
					array(
						'ip'		=>	$this->get_IP(),
						'timestamp'	=>	date('U'),
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$this->agent['browser'],
						'platform'	=>	$this->agent['platform'],
						'version'	=> 	$this->agent['version']
					)
				);
			}
			
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_visit");
			
			if( !$this->result ) {
			
				$this->db->insert(
					$this->tb_prefix . "statistics_visit",
					array(
						'last_visit'	=>	$this->Current_Date(),
						'last_counter'	=>	$this->Current_date('Y-m-d'),
						'visit'			=>	1
					)
				);
			}
			
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_visitor");
			
			if( !$this->result ) {
			
				$this->db->insert(
					$this->tb_prefix . "statistics_visitor",
					array(
						'last_counter'	=>	$this->Current_date('Y-m-d'),
						'referred'		=>	$this->get_Referred(),
						'agent'			=>	$this->agent['browser'],
						'platform'		=>	$this->agent['platform'],
						'version'		=> 	$this->agent['version'],
						'ip'			=>	$this->get_IP(),
						'location'		=>	'000'
					)
				);
			}
		}
		
		// This function returns the current IP address of the remote client.
		public function get_IP() {
		
			// By default we use the remote address the server has.
			$temp_ip = $_SERVER['REMOTE_ADDR'];
		
			// Check to see if any of the HTTP headers are set to identify the remote user.
			// These often give better results as they can identify the remote user even through firewalls etc, 
			// but are sometimes used in SQL injection attacks.
			if (getenv('HTTP_CLIENT_IP')) {
				$temp_ip = getenv('HTTP_CLIENT_IP');
			} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
				$temp_ip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_X_FORWARDED')) {
				$temp_ip = getenv('HTTP_X_FORWARDED');
			} elseif (getenv('HTTP_FORWARDED_FOR')) {
				$temp_ip = getenv('HTTP_FORWARDED_FOR');
			} elseif (getenv('HTTP_FORWARDED')) {
				$temp_ip = getenv('HTTP_FORWARDED');
			} 

			// Trim off any port values that exist.
			if( strstr( $temp_ip, ':' ) !== FALSE ) {
				$temp_a = explode(':', $temp_ip);
				$temp_ip = $temp_a[0];
			}
			
			// Check to make sure the http header is actually an IP address and not some kind of SQL injection attack.
			$long = ip2long($temp_ip);
		
			// ip2long returns either -1 or FALSE if it is not a valid IP address depending on the PHP version, so check for both.
			if($long == -1 || $long === FALSE) {
				// If the headers are invalid, use the server variable which should be good always.
				$temp_ip = $_SERVER['REMOTE_ADDR'];
			}
			
			$this->ip = $temp_ip;
			
			return $this->ip;
		}
		
		// This function calls the user agent parsing code.
		public function get_UserAgent() {
		
			// Parse the agent stirng.
			try 
				{
				$agent = parse_user_agent();
				}
			catch( Exception $e )
				{
				$agent = array( 'browser' => 'Unknown', 'platform' => 'Unknown', 'version' => 'Unknown' );
				}
			
			// null isn't a very good default, so set it to Unknown instead.
			if( $agent['browser'] == null ) { $agent['browser'] = "Unknown"; }
			if( $agent['platform'] == null ) { $agent['platform'] = "Unknown"; }
			if( $agent['version'] == null ) { $agent['version'] = "Unknown"; }
			
			return $agent;
		}
		
		// This function will return the referrer link for the current user.
		public function get_Referred($default_referr = false) {
		
			$referr = '';
			
			if( isset($_SERVER['HTTP_REFERER']) ) { $referr = $_SERVER['HTTP_REFERER']; }
			if( $default_referr ) { $referr = $default_referr; }
			
			$referr = esc_sql(strip_tags($referr) );
			
			if( !$referr ) { $referr = get_bloginfo('url'); }
			
			return $referr;
		}
		
		// This function returns a date string in the desired format.
		public function Current_Date($format = 'Y-m-d H:i:s', $strtotime = null) {
		
			if( $strtotime ) {
				return date($format, strtotime("{$strtotime} day") ) ;
			} else {
				return date($format) ;
			}
		}
		
		// This function returns an internationalized date string in the desired format.
		public function Current_Date_i18n($format = 'Y-m-d H:i:s', $strtotime = null, $day=' day') {
		
			if( $strtotime ) {
				return date_i18n($format, strtotime("{$strtotime}{$day}") ) ;
			} else {
				return date_i18n($format) ;
			}
		}

		// This function checks to see if a search engine exists in the current list of search engines.
		public function Check_Search_Engines ($search_engine_name, $search_engine = null) {
		
			if( strstr($search_engine, $search_engine_name) ) {
				return 1;
			}
		}
		
		// This function returns an array of information about a given search engine based on the url passed in.
		// It is used in several places to get the SE icon or the sql query to select an individual SE from the database.
		public function Search_Engine_Info($url = false) {
		
			// If no URL was passed in, get the current referrer for the session.
			if(!$url) {
				$url = isset($_SERVER['HTTP_REFERER']) ? $this->get_Referred() : false;
			}
			
			// If there is no URL and no referrer, always return false.
			if($url == false) {
				return false;
			}
			
			// Parse the URL in to it's component parts.
			$parts = parse_url($url);
			
			// Get the list of search engines we currently support.
			$search_engines = wp_statistics_searchengine_list();
			
			// Loop through the SE list until we find which search engine matches.
			foreach( $search_engines as $key => $value ) {
				$search_regex = wp_statistics_searchengine_regex($key);
				
				preg_match( '/' . $search_regex . '/', $parts['host'], $matches);
				
				if( isset($matches[1]) )
					{
					// Return the first matched SE.
					return $value;
					}
			}
			
			// If no SE matched, return some defaults.
			return array('name' => 'Unknown', 'tag' => '', 'sqlpattern' => '', 'regexpattern' => '', 'querykey' => 'q', 'image' => 'unknown.png' );
		}
		
		// This function will parse a URL from a referrer and return the search query words used.
		public function Search_Engine_QueryString($url = false) {
		
			// If no URL was passed in, get the current referrer for the session.
			if(!$url) {
				$url = isset($_SERVER['HTTP_REFERER']) ? $this->get_Referred() : false;
			}
			
			// If there is no URL and no referrer, always return false.
			if($url == false) {
				return false;
			}
			
			// Parse the URL in to it's component parts.
			$parts = parse_url($url);

			// Check to see if there is a query component in the URL (everything after the ?).  If there isn't one
			// set an empty array so we don't get errors later.
			if( array_key_exists('query',$parts) ) { parse_str($parts['query'], $query); } else { $query = array(); }
			
			// Get the list of search engines we currently support.
			$search_engines = wp_statistics_searchengine_list();
			
			// Loop through the SE list until we find which search engine matches.
			foreach( $search_engines as $key => $value ) {
				$search_regex = wp_statistics_searchengine_regex($key);
				
				preg_match( '/' . $search_regex . '/', $parts['host'], $matches);
				
				if( isset($matches[1]) )
					{
					// Check to see if the query key the SE uses exists in the query part of the URL.
					if( array_key_exists($search_engines[$key]['querykey'], $query) ) {
						$words = strip_tags($query[$search_engines[$key]['querykey']]);
					}
					else {
						$words = '';
					}
				
					// If no words were found, return a pleasent default.
					if( $words == '' ) { $words = 'No search query found!'; }
					return $words;
					}
			}
			
			// We should never actually get to this point, but let's make sure we return something
			// just in case something goes terribly wrong.
			return 'No search query found!';
		}
		
		public function Get_Historical_Data($type, $id = '') {
		
			$count = 0;
		
			switch( $type ) {
				case 'visitors':
					if( array_key_exists( 'visitors', $this->historical ) ) {
						return $this->historical['visitors'];
					}
					else {
						$result = $this->db->get_var("SELECT value FROM {$this->tb_prefix}statistics_historical WHERE category = 'visitors'");
						if( $result > $count ) { $count = $result; }
						$this->historical['visitors'] = $count;
					}
				
					break;
				case 'visits':
					if( array_key_exists( 'visits', $this->historical ) ) {
						return $this->historical['visits'];
					}
					else {
						$result = $this->db->get_var("SELECT value FROM {$this->tb_prefix}statistics_historical WHERE category = 'visits'");
						if( $result > $count ) { $count = $result; }
						$this->historical['visits'] = $count;
					}
				
					break;
				case 'uri':
					if( array_key_exists( $id, $this->historical ) ) {
						return $this->historical[$id];
					}
					else {
						$result = $this->db->get_var($this->db->prepare("SELECT value FROM {$this->tb_prefix}statistics_historical WHERE category = 'uri' AND uri = %s", $id));
						if( $result > $count ) { $count = $result; }
						$this->historical[$id] = $count;
					}
					
					break;
				}
		
			return $count;
		}
	}