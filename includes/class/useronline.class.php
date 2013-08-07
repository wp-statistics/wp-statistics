<?php
	class Useronline extends WP_Statistics {
		
		private $timestamp;
		
		public $second;
		
		public $result = null;
		
		public function __construct($second = 30) {
		
			parent::__construct();
			
			$this->timestamp = date('U');
			
			$this->second = $second;
		}
		
		public function Is_user() {
		
			$this->result = $this->db->query("SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->get_IP()}'");
			
			if($this->result) 
				return true;
		}
		
		public function Check_online() {
		
			if($this->Is_user()) {
				$this->Update_user();
			} else {
				$this->Add_user();
			}
			
			$this->Delete_user();
		}
		
		public function Add_user() {
			
			if(!$this->Is_user()) {
			
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
			
		}
		
		public function Update_user() {
		
			if($this->result) {
			
				$this->db->update(
					$this->tb_prefix . "statistics_useronline",
					array(
						'timestamp'	=>	$this->timestamp,
						'date'		=>	$this->Current_Date(),
						'referred'	=>	$this->get_Referred(),
						'agent'		=>	$this->get_UserAgent(),
					),
					array(
						'ip'		=>	$this->get_IP()
					)
				);
				
			}
		}
		
		public function Delete_user() {
			
			$this->result = $this->timestamp - $this->second;
			
			$this->db->query("DELETE FROM {$this->tb_prefix}statistics_useronline WHERE timestamp < '{$this->result}'");
		}
	}