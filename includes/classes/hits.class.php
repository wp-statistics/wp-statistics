<?php
	class Hits extends WP_Statistics {
	
		public $result = null;
		private $exclusion_match = FALSE;
		private $exclusion_reason = '';
		private $exclusion_record = FALSE;
		private $ip;
		
		public function __construct() {

			global $wp_version;

			parent::__construct();
			
			$this->ip = $this->get_IP();
			
			if( get_option( 'wps_record_exclusions' ) == 1 ) {
				$this->exclusion_record = TRUE;
			}
			
			// The follow exclusion checks are done during the class construction so we don't have to execute them twice if we're tracking visits and visitors.
			//
			// Order of exclusion checks is:
			//		1 - robots
			// 		2 - IP/Subnets
			//		3 - Self Referrals & login page
			//		4 - User roles
			//
			
			// Pull the robots from the database.
			$robots = explode( "\n", get_option('wps_robotlist') );

			// Check to see if we match any of the robots.
			foreach($robots as $robot) {
				$robot = trim($robot);
				
				// If the match case is less than 4 characters long, it might match too much so don't execute it.
				if(strlen($robot) > 3) { 
					if(stripos($_SERVER['HTTP_USER_AGENT'], $robot) !== FALSE) {
						$this->exclusion_match = TRUE;
						$this->exclusion_reason = "robot";
						break;
					}
				}
			}
		
			if( !$this->exclusion_match ) {
				// Pull the subnets from the database.
				$subnets = explode( "\n", get_option('wps_exclude_ip') );
				
				// Check to see if we match any of the excluded addresses.
				foreach($subnets as $subnet ) {
					$subnet = trim($subnet);
					
					// The shortest ip address is 1.1.1.1, anything less must be a malformed entry.
					if(strlen($subnet) > 6) {
						if( $this->net_match( $subnet, $this->ip ) ) {
							$this->exclusion_match = TRUE;
							$this->exclusion_reason = "ip match";
							break;
						}
					}
				}

				// Check to see if we are being referred to ourselves.
				if( !$this->exclusion_match ) {
					if( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url("/") ) { $this->exclusion_match = TRUE; $this->exclusion_reason = "self referral"; }
					if( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url() ) { $this->exclusion_match = TRUE; $this->exclusion_reason = "self referral"; }

					if( get_option('wps_exclude_loginpage') == 1 ) {
						$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')  === FALSE ? 'http' : 'https';
						$host     = $_SERVER['HTTP_HOST'];
						$script   = $_SERVER['SCRIPT_NAME'];

						$currentURL = $protocol . '://' . $host . $script;
						$loginURL = wp_login_url();
						
						if( $currentURL == $loginURL ) { $this->exclusion_match = TRUE; $this->exclusion_reason = "login page";}
					}

					if( get_option('wps_exclude_adminpage') == 1 ) {
						$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')  === FALSE ? 'http' : 'https';
						$host     = $_SERVER['HTTP_HOST'];
						$script   = $_SERVER['SCRIPT_NAME'];

						$currentURL = $protocol . '://' . $host . $script;
						$adminURL = get_admin_url();
						
						$currentURL = substr( $currentURL, 0, strlen( $adminURL ) );
						
						if( $currentURL == $adminURL ) { $this->exclusion_match = TRUE; $this->exclusion_reason = "admin page";}
					}
					
					// Finally check to see if we are excluding based on the user role.
					if( !$this->exclusion_match ) {
						
						if( is_user_logged_in() ) {
							$current_user = wp_get_current_user();
							
							foreach( $current_user->roles as $role ) {
								$option_name = 'wps_exclude_' . str_replace(" ", "_", strtolower($role) );
								if( get_option($option_name) == TRUE ) {
									$this->exclusion_match = TRUE;
									$this->exclusion_reason = "user role";
									break;
								}
							}
						}
					}
				}
			}
		}

		// From: http://www.php.net/manual/en/function.ip2long.php
		//

		private function net_match($network, $ip) {
			   // determines if a network in the form of 192.168.17.1/16 or
			   // 127.0.0.1/255.255.255.255 or 10.0.0.1 matches a given ip
			   $ip_arr = explode('/', $network);
			   
			   if( !isset( $ip_arr[1] ) ) { $ip_arr[1] = 0; }
			   
			   $network_long = ip2long($ip_arr[0]);

			   $x = ip2long($ip_arr[1]);
			   $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
			   $ip_long = ip2long($ip);

			   return ($ip_long & $mask) == ($network_long & $mask);
		 }	
		 
		public function Visits() {
			
			// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
			if( !$this->exclusion_match ) {

				$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visit ORDER BY `{$this->tb_prefix}statistics_visit`.`ID` DESC");
				
				if( substr($this->result->last_visit, 0, -1) != substr($this->Current_Date('Y-m-d H:i:s'), 0, -1) ) {
				
					if( $this->result->last_counter != $this->Current_Date('Y-m-d') ) {
					
						$this->db->insert(
							$this->tb_prefix . "statistics_visit",
							array(
								'last_visit'	=>	$this->Current_Date(),
								'last_counter'	=>	$this->Current_date('Y-m-d'),
								'visit'			=>	$this->coefficient
							)
						);
					} else {
					
						$this->db->update(
							$this->tb_prefix . "statistics_visit",
							array(
								'last_visit'	=>	$this->Current_Date(),
								'visit'			=>	$this->result->visit + $this->coefficient
							),
							array(
								'last_counter'	=>	$this->result->last_counter
							)
						);
					}
				}
			}
		}
		
		public function Visitors() {
		
			// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
			if( !$this->exclusion_match ) {

				$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->ip}'");
				
				if( !$this->result ) {

					if( get_option('wps_store_ua') == true ) { $ua = $_SERVER['HTTP_USER_AGENT']; } else { $ua = ''; }

					$this->db->insert(
						$this->tb_prefix . "statistics_visitor",
						array(
							'last_counter'	=>	$this->Current_date('Y-m-d'),
							'referred'		=>	$this->get_Referred(),
							'agent'			=>	$agent['browser'],
							'platform'		=>	$agent['platform'],
							'version'		=> 	$agent['version'],
							'ip'			=>	$this->ip,
							'location'		=> 	'000',
							'UAString'		=>	$ua
						)
					);
				}
			} else {
				if( $this->exclusion_record == TRUE ) {
					$this->result = $this->db->query("UPDATE {$this->tb_prefix}statistics_exclusions SET `count` = `count` + 1 WHERE `date` = '{$this->Current_Date('Y-m-d')}' AND `reason` = '{$this->exclusion_reason}'");

					if( !$this->result ) {
						$this->db->insert(
							$this->tb_prefix . "statistics_exclusions",
							array(
								'date'		=>	$this->Current_date('Y-m-d'),
								'reason'	=>	$this->exclusion_reason,
								'count'		=> 	1
							)
						);
					}
				}
			}
		}
	}