<?php
	class WP_Statistics {
		
		protected $db;
		protected $tb_prefix;
		
		private $ip;
		private $result;
		
		public $coefficient = 1;
		
		public function __construct() {
		
			global $wpdb, $table_prefix;
			
			$this->db = $wpdb;
			$this->tb_prefix = $table_prefix;
		}
		
		public function Primary_Values() {
		
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline");
			
			if( !$this->result ) {
				
				$this->db->insert(
					$this->tb_prefix . "statistics_useronline",
					array(
						'ip'		=>	$this->get_IP(),
						'timestamp'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$this->get_UserAgent(),
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
						'agent'			=>	$this->get_UserAgent(),
						'ip'			=>	$this->get_IP()
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
		
			static $agent = null;

			if ( empty($agent) ) {
				$agent = $_SERVER['HTTP_USER_AGENT'];

				if ( stripos($agent, 'Firefox') ) {
					$agent = 'Firefox';
				} elseif ( stripos($agent, 'MSIE') ) {
					$agent = 'IE';
				} elseif ( stripos($agent, 'iPad') ) {
					$agent = 'Ipad';
				} elseif ( stripos($agent, 'Android') ) {
					$agent = 'Android';
				} elseif ( stripos($agent, 'Chrome') ) {
					$agent = 'Chrome';
				} elseif ( stripos($agent, 'Opera') ) {
					$agent = 'Opera';
				} elseif ( stripos($agent, 'Safari') ) {
					$agent = 'Safari';
				} else {
					$agent = 'unknown';
				}
			}
			
			return $agent;
		}
		
		public function get_Referred($default_referr = false) {
		
			if( $default_referr ) {
				if( !$this->db->escape(strip_tags($_SERVER['HTTP_REFERER'])) ) {
					return get_bloginfo('url');
				} else {
					return $this->db->escape(strip_tags($_SERVER['HTTP_REFERER']));
				}
			} else {
				return $this->db->escape(strip_tags($_SERVER['HTTP_REFERER']));
			}
		}
		
		public function Check_Spiders() {
		
			$spiders = array("Teoma", "alexa", "froogle", "Gigabot", "inktomi", "looksmart", "URL_Spider_SQL", "Firefly", "NationalDirectory", "Ask Jeeves", "TECNOSEEK", "InfoSeek", "WebFindBot", "girafabot", "crawler", "www.galaxy.com", "Googlebot", "googlebot", "Scooter", "Slurp", "msnbot", "appie", "FAST", "WebBug", "Spade", "ZyBorg", "rabaz", "Baiduspider", "Feedfetcher-Google", "TechnoratiSnoop", "Rankivabot", "Mediapartners-Google", "Sogou web spider", "WebAlta Crawler","TweetmemeBot", "Butterfly","Twitturls","Me.dium","Twiceler", "Yammybot", "Openbot", "Yahoo", "ia_archiver", "Lycos", "AltaVista", "Googlebot-Mobile", "Rambler", "AbachoBOT", "accoona", "AcoiRobot", "ASPSeek", "CrocCrawler", "Dumbot", "FAST-WebCrawler", "GeonaBot", "MSRBOT", "IDBot", "eStyle", "Scrubby");

			foreach($spiders as $spider) {
				if(strpos($this->get_UserAgent(), $spider) == true)
					return true;
			}
			
			return false;
		}
		
		public function Current_Date($format = 'Y-m-d H:i:s', $strtotime = null) {
		
			if( $strtotime ) {
				return date($format, strtotime("{$strtotime} day") ) ;
			} else {
				return date($format) ;
			}
		}
		
		public function Check_Search_Engines ($search_engine_name, $search_engine = null) {
		
			if( strstr($search_engine, $search_engine_name) ) {
				return 1;
			}
		}
		
		public function Search_Engine_QueryString($url = false) {
		
			if(!$url) {
				$url = isset($_SERVER['HTTP_REFERER']) ? $this->get_Referred() : false;
			}
			
			if($url == false) {
				return false;
			}

			$parts = parse_url($url);
			parse_str($parts['query'], $query);

			$search_engines = array(
				'bing'		=>	'q',
				'google'	=>	'q',
				'yahoo'		=>	'p'
			);

			preg_match('/(' . implode('|', array_keys($search_engines)) . ')\./', $parts['host'], $matches);

			return isset($matches[1]) && isset($query[$search_engines[$matches[1]]]) ? strip_tags($query[$search_engines[$matches[1]]]) : '';
		}
	}