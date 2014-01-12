<?php
	require_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	
	use GeoIp2\Database\Reader;

	class Hits extends WP_Statistics {
	
		public $result = null;
		private $exclusion_match = FALSE;
		
		public function __construct() {

			global $wp_version;

			parent::__construct();
			
			// The follow exclusion checks are done during the class construction so we don't have to execute them twice if we're tracking visits and visitors.
			//
			// Order of exclusion checks is:
			//		1 - robots
			// 		2 - IP/Subnets
			//		3 - Self Referrals
			//		4 - User roles
			//
			
			// Pull the robots from the database.
			$robots = explode( "\n", get_option('wps_robotlist') );

			// Check to see if we match any of the robots.
			foreach($robots as $robot) {
				$robot = trim($robot);
				
				// If the match case is less than 5 characters long, it might match too much so don't execute it.
				if(strlen($robot) > 3) { 
					if(stripos($_SERVER['HTTP_USER_AGENT'], $robot) !== FALSE) {
						$this->exclusion_match = TRUE;
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
							break;
						}
					}
				}

				// Check to see if we are being referred to ourselves.
				if( !$this->exclusion_match ) {
					if( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url("/") ) { $this->exclusion_match = TRUE; }
					if( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url() ) { $this->exclusion_match = TRUE; }

					// Finally check to see if we are excluding based on the user role.
					if( !$this->exclusion_match ) {
						
						if( is_user_logged_in() ) {
							$current_user = wp_get_current_user();
							
							foreach( $current_user->roles as $role ) {
								$option_name = 'wps_exclude_' . str_replace(" ", "_", strtolower($role) );
								if( get_option($option_name) == TRUE ) {
									$this->exclusion_match = TRUE;
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
			   $network_long = ip2long($ip_arr[0]);

			   $x = ip2long($ip_arr[1]);
			   $mask =  long2ip($x) == $ip_arr[1] ? $x : 0xffffffff << (32 - $ip_arr[1]);
			   $ip_long = ip2long($ip);

			   // echo ">".$ip_arr[1]."> ".decbin($mask)."\n";
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

					try 
						{
						$reader = new Reader( plugin_dir_path( __FILE__ ) . '../../GeoIP2-db/GeoLite2-Country.mmdb' );
						$record = $reader->country( $ip );
						$location = $record->country->isoCode;
						}
					catch( Exception $e )
						{
						$location = "000";
						}
					
					$this->db->insert(
						$this->tb_prefix . "statistics_visitor",
						array(
							'last_counter'	=>	$this->Current_date('Y-m-d'),
							'referred'		=>	$this->get_Referred(true),
							'agent'			=>	$this->agent['browser'],
							'platform'		=>	$this->agent['platform'],
							'version'		=> 	$this->agent['version'],
							'ip'			=>	$this->ip,
							'location'		=>	$location,
							'UAString'		=>	$ua
						)
					);
				}
			}
		}
	}