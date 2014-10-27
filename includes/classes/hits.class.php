<?php
/*
	This is the primary class for recording hits on the WordPress site.  It extends the WP_Statistics class and is itself extended by the GeoIPHits class.
	
	This class handles; visits, visitors and pages.
*/
	use phpbrowscap\Browscap;

	class Hits extends WP_Statistics {
	
		// Setup our public/private/protected variables.
		public $result = null;

		protected $location = "000";

		private $exclusion_match = FALSE;
		private $exclusion_reason = '';
		private $exclusion_record = FALSE;
		
		// Construction function.
		public function __construct() {

			global $wp_version;

			// Call the parent constructor (WP_Statistics::__construct)
			parent::__construct();
			
			// Check to see if the user wants us to record why we're excluding hits.
			if( $this->get_option('record_exclusions' ) == 1 ) {
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
			
			// Get the upload directory from WordPRess.
			$upload_dir = wp_upload_dir();
			 
			// Create a variable with the name of the database file to download.
			$BrowscapFile = $upload_dir['basedir'] . '/wp-statistics';
			
			$crawler = false;
			
			if( $this->get_option('last_browscap_dl') > 1 && $this->get_option('browscap') ) { 
				// Get the Browser Capabilities use Browscap.
				$bc = new Browscap($BrowscapFile);
				$bc->doAutoUpdate = false; 	// We don't want to auto update.
				$current_browser = $bc->getBrowser();
				$crawler = $current_browser->Crawler;
			}
			else {
				$this->update_option('update_browscap', true);
			}

			// If we're a crawler as per browscap, exclude us, otherwise double check based on the WP Statistics robot list.
			if( $crawler == true ) {
				$this->exclusion_match = TRUE;
				$this->exclusion_reason = "browscap";
			}
			else {
				// Pull the robots from the database.
				$robots = explode( "\n", $this->get_option('robotlist') );

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
			}
			
			// If we didn't match a robot, check ip subnets.
			if( !$this->exclusion_match ) {
				// Pull the subnets from the database.
				$subnets = explode( "\n", $this->get_option('exclude_ip') );
				
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

					if( $this->get_option('exclude_loginpage') == 1 ) {
						$protocol = strpos(strtolower($_SERVER['SERVER_PROTOCOL']),'https')  === FALSE ? 'http' : 'https';
						$host     = $_SERVER['HTTP_HOST'];
						$script   = $_SERVER['SCRIPT_NAME'];

						$currentURL = $protocol . '://' . $host . $script;
						$loginURL = wp_login_url();
						
						if( $currentURL == $loginURL ) { $this->exclusion_match = TRUE; $this->exclusion_reason = "login page";}
					}

					if( $this->get_option('exclude_adminpage') == 1 ) {
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
								$option_name = 'exclude_' . str_replace(" ", "_", strtolower($role) );
								if( $this->get_option($option_name) == TRUE ) {
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
		
		// This function records visits to the site.
		public function Visits() {
			
			// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
			if( !$this->exclusion_match ) {

				// Check to see if we're a returning visitor.
				$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visit ORDER BY `{$this->tb_prefix}statistics_visit`.`ID` DESC");
				
				// If we're a returning visitor, update the current record in the database, otherwise, create a new one.
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
		
		// This function records unique visitors to the site.
		public function Visitors() {
	
			// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
			if( !$this->exclusion_match ) {

				// Check to see if we already have an entry in the database.
				if( $this->ip_hash != false ) {
					$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->ip_hash}'");
				}
				else {
					$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->ip}' AND `agent` = '{$this->agent['browser']}' AND `platform` = '{$this->agent['platform']}' AND `version` = '{$this->agent['version']}'");
				}
				
				// If we don't create a new one, otherwise update the old one.
				if( !$this->result ) {

					// If we've been told to store the entire user agent, do so.
					if( $this->get_option('store_ua') == true ) { $ua = $_SERVER['HTTP_USER_AGENT']; } else { $ua = ''; }
					
					// Store the result.
					$this->db->insert(
						$this->tb_prefix . "statistics_visitor",
						array(
							'last_counter'	=>	$this->Current_date('Y-m-d'),
							'referred'		=>	$this->get_Referred(),
							'agent'			=>	$this->agent['browser'],
							'platform'		=>	$this->agent['platform'],
							'version'		=> 	$this->agent['version'],
							'ip'			=>	$this->ip_hash ? $this->ip_hash : $this->ip,
							'location'		=> 	$this->location,
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

		// This function records page hits.
		public function Pages() {
	
			// If we're a webcrawler or referral from ourselves or an excluded address don't record the page hit.
			if( !$this->exclusion_match ) {

				// Don't track anything but actual pages and posts, unless we've been told to.
				if( $this->get_option('track_all_pages') || is_page() || is_single() || is_front_page() ) {
					global $wp_query;
					
					// Many of the URI's we hit will be pages or posts, get their ID if it exists.
					$current_page_id = $wp_query->get_queried_object_id();

					// Get the current page URI.
					$page_uri = wp_statistics_get_uri();

					// If we have already been to this page today (a likely senerio), just update the count on the record.
					$this->result = $this->db->query("UPDATE {$this->tb_prefix}statistics_pages SET `count` = `count` + 1 WHERE `date` = '{$this->Current_Date('Y-m-d')}' AND `uri` = '{$page_uri}'");

					// If the update failed (aka the record doesn't exist), insert a new one.  Note this may drop a page hit if a race condition
					// exists where two people load the same page a the roughly the same time.  In that case two insers would be attempted but
					// there is a unique index requirement on the database and one of them would fail.
					if( !$this->result ) {

						$this->db->insert(
							$this->tb_prefix . "statistics_pages",
							array(
								'uri'		=>	$page_uri,
								'date'		=>	$this->Current_date('Y-m-d'),
								'count'		=>	1,
								'id'		=>	$current_page_id
								)
							);
					}
				}
			}
		}
	}