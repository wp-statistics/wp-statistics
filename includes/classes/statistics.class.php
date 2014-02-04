<?php
	class WP_Statistics {
		
		protected $db;
		protected $tb_prefix;
		
		private $ip;
		private $result;
		private $agent;
		
		public $coefficient = 1;
		
		public function __construct() {
		
			global $wpdb, $table_prefix;
			
			$this->db = $wpdb;
			$this->tb_prefix = $table_prefix;
			$this->agent = $this->get_UserAgent();
			if( get_option('wps_coefficient') ) {
				$this->coefficient = get_option('wps_coefficient');
			}
			
		}
		
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
		
		public function get_IP() {
		
			if (getenv('HTTP_CLIENT_IP')) {
				$this->ip = getenv('HTTP_CLIENT_IP');
			} elseif (getenv('HTTP_X_FORWARDED_FOR')) {
				$this->ip = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_X_FORWARDED')) {
				$this->ip = getenv('HTTP_X_FORWARDED');
			} elseif (getenv('HTTP_FORWARDED_FOR')) {
				$this->ip = getenv('HTTP_FORWARDED_FOR');
			} elseif (getenv('HTTP_FORWARDED')) {
				$this->ip = getenv('HTTP_FORWARDED');
			} else {
				$this->ip = $_SERVER['REMOTE_ADDR'];
			}
			
			return $this->ip;
		}
		
		public function get_UserAgent() {
		
			$agent = parse_user_agent();
			
			if( $agent['browser'] == null ) { $agent['browser'] = "Unknown"; }
			if( $agent['platform'] == null ) { $agent['platform'] = "Unknown"; }
			if( $agent['version'] == null ) { $agent['version'] = "Unknown"; }
			
			return $agent;
		}
		
		public function get_Referred($default_referr = false) {
		
			if( $default_referr ) {
				if( !esc_sql(strip_tags($_SERVER['HTTP_REFERER'])) ) {
					return get_bloginfo('url');
				} else {
					return esc_sql(strip_tags($_SERVER['HTTP_REFERER']));
				}
			} else {
				return esc_sql(strip_tags($_SERVER['HTTP_REFERER']));
			}
		}
		
		public function Current_Date($format = 'Y-m-d H:i:s', $strtotime = null) {
		
			if( $strtotime ) {
				return date_i18n($format, strtotime("{$strtotime} day") ) ;
			} else {
				return date_i18n($format) ;
			}
		}
		
		public function Check_Search_Engines ($search_engine_name, $search_engine = null) {
		
			if( strstr($search_engine, $search_engine_name) ) {
				return 1;
			}
		}
		
		public function Search_Engine_Info($url = false) {
		
			if(!$url) {
				$url = isset($_SERVER['HTTP_REFERER']) ? $this->get_Referred() : false;
			}
			
			if($url == false) {
				return false;
			}
			
			$parts = parse_url($url);
			
			$search_engines = wp_statistics_searchengine_list();
			
			foreach( $search_engines as $key => $value ) {
				$search_regex = wp_statistics_searchengine_regex($key);
				
				preg_match( '/' . $search_regex . '/', $parts['host'], $matches);
				
				if( isset($matches[1]) )
					{
					return $value;
					}
			}
			
			return array('name' => 'Unknown', 'tag' => '', 'sqlpattern' => '', 'regexpattern' => '', 'querykey' => 'q', 'image' => 'unknown.png' );
		}
		
		public function Search_Engine_QueryString($url = false) {
		
			if(!$url) {
				$url = isset($_SERVER['HTTP_REFERER']) ? $this->get_Referred() : false;
			}
			
			if($url == false) {
				return false;
			}
			
			$parts = parse_url($url);
			
			if( array_key_exists('query',$parts) ) { parse_str($parts['query'], $query); } else { $query = array(); }
			
			$search_engines = wp_statistics_searchengine_list();
			
			foreach( $search_engines as $key => $value ) {
				$search_regex = wp_statistics_searchengine_regex($key);
				
				preg_match( '/' . $search_regex . '/', $parts['host'], $matches);
				
				if( isset($matches[1]) )
					{
					if( array_key_exists($search_engines[$key]['querykey'], $query) ) {
						$words = strip_tags($query[$search_engines[$key]['querykey']]);
					}
					else {
						$words = '';
					}
				
					if( $words == '' ) { $words = 'No search query found!'; }
					return $words;
					}
			}
			
			return '';
		}
	}