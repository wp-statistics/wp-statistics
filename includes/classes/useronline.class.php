<?php
/*
	This is the primary class for recording online users on the WordPress site.  It extends the WP_Statistics class.
	
	This class handles; online users.
*/
	class Useronline extends WP_Statistics {
		
		// Setup our public/private/protected variables.
		private $timestamp;
		
		public $second;
		public $result = null;
		
		// Construction function.
		public function __construct($second = 30) {
		
			// Call the parent constructor (WP_Statistics::__construct)
			parent::__construct();
			
			// Set the timestamp value.
			$this->timestamp = date('U');
			
			// Set the default seconds a user needs to visit the site before they are considered offline.
			$this->second = $second;
			
			// Get the user set value for seconds.
			if( $this->get_option('check_online') ) {
				$this->second = $this->get_option('check_online');
				}
		}
		
		// This function checks to see if the current user (as defined by thier IP address) has an entry in the database.
		// Note we set the $this->result variable so we don't have to re-excute the query when we do the user update.
		public function Is_user() {

			if( $this->ip_hash != false ) {
				$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->ip_hash}'");
			}
			else {
				$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->ip}' AND `agent` = '{$this->agent['browser']}' AND `platform` = '{$this->agent['platform']}' AND `version` = '{$this->agent['version']}'");
			}
			
			if($this->result) 
				return true;
		}
		
		// This function add/update/delete the online users in the database.
		public function Check_online($location) {
		
			// If the current user exists in the database already, just update them, otherwise add them
			if($this->Is_user()) {
				$this->Update_user($location);
			} else {
				$this->Add_user($location);
			}
			
			// Remove users that have done offline since the last check.
			$this->Delete_user();
		}
		
		// This function adds a user to the database.
		public function Add_user($location) {
			
			if(!$this->Is_user()) {
			
				// Insert the user in to the database.
				$this->db->insert(
					$this->tb_prefix . "statistics_useronline",
					array(
						'ip'		=>	$this->ip_hash ? $this->ip_hash : $this->ip,
						'timestamp'	=>	$this->timestamp,
						'created'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$this->agent['browser'],
						'platform'	=>	$this->agent['platform'],
						'version'	=> 	$this->agent['version'],
						'location'	=>	$location,
					)
				);
			}
			
		}
		
		// This function updates a user in the database.
		public function Update_user($location) {
		
			// Make sure we found the user earlier when we called Is_user().
			if($this->result) {
			
				// Update the database with the new information.
				$this->db->update(
					$this->tb_prefix . "statistics_useronline",
					array(
						'timestamp'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
					),
					array(
						'ip'		=>	$this->ip_hash ? $this->ip_hash : $this->ip,
						'agent'		=>	$this->agent['browser'],
						'platform'	=>  $this->agent['platform'],
						'version'	=> 	$this->agent['version'],
						'location'	=>	$location,
					)
				);
			}
		}
		
		// This function removes expired users.
		public function Delete_user() {
			
			// We want to delete users that are over the number of seconds set by the admin.
			$this->result = $this->timestamp - $this->second;
			
			// Call the deletion query.
			$this->db->query("DELETE FROM {$this->tb_prefix}statistics_useronline WHERE timestamp < '{$this->result}'");
		}
	}