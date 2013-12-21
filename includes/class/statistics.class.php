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
		
			$spiders = array("AbachoBOT","accoona","AcoiRobot","AhrefsBot","alexa","AltaVista","appie","Ask Jeeves","ASPSeek","Baiduspider","bingbot","Butterfly","clam antivirus","crawler","CrocCrawler","Dumbot","eStyle","ezooms.bot","FAST","FAST-WebCrawler","Feedfetcher-Google","Firefly","froogle","GeonaBot","Gigabot","girafabot","Googlebot","ia_archiver","IDBot","InfoSeek","inktomi","linkdexbot","looksmart","Lycos","Mail.RU_Bot","Me.dium","Mediapartners-Google","MJ12bot","msnbot","MSRBOT","NationalDirectory","nutch","Openbot","proximic","rabaz","Rambler","Rankivabot","Scooter","Scrubby","Slurp","SocialSearch","Sogou web spider","Spade","TechnoratiSnoop","TECNOSEEK","Teoma","TweetmemeBot","Twiceler","Twitturls","URL_Spider_SQL","WebAlta Crawler","WebBug","WebFindBot","www.galaxy.com","yandex","Yahoo","Yammybot","ZyBorg");
			
			foreach($spiders as $spider) {
				if(stripos($_SERVER['HTTP_USER_AGENT'], $spider) !== FALSE)
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