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
		
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->get_IP()}'");
			
			if($this->result) 
				return true;
		}
		
		// This function add/update/delete the online users in the database.
		public function Check_online() {
		
			// If the current user exists in the database already, just update them, otherwise add them
			if($this->Is_user()) {
				$this->Update_user();
			} else {
				$this->Add_user();
			}
			
			// Remove users that have done offline since the last check.
			$this->Delete_user();
		}
		
		// This function adds a user to the database.
		public function Add_user() {
			
			if(!$this->Is_user()) {
			
				// Parse the user agent string.
				$agent = $this->get_UserAgent();

				// Insert the user in to the database.
				$this->db->insert(
					$this->tb_prefix . "statistics_useronline",
					array(
						'ip'		=>	$this->get_IP(),
						'timestamp'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$agent['browser'],
						'platform'	=>	$agent['platform'],
						'version'	=> 	$agent['version'],
					)
				);
			}
			
		}
		
		// This function updates a user in the database.
		public function Update_user() {
		
			// Make sure we found the user earlier when we called Is_user().
			if($this->result) {
			
				// Parse the user agent.
				$agent = $this->get_UserAgent();
			
				// Update the database with the new information.
				$this->db->update(
					$this->tb_prefix . "statistics_useronline",
					array(
						'timestamp'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$agent['browser'],
						'platform'	=>  	$agent['platform'],
						'version'	=> 	$agent['version'],
					),
					array(
						'ip'		=>	$this->get_IP()
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