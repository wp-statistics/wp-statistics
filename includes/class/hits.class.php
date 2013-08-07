<?php
	class Hits extends WP_Statistics {
	
		public $result = null;
		
		public function __construct() {
		
			parent::__construct();
		}
		
		public function Visits() {
			
			$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visit ORDER BY `{$this->tb_prefix}statistics_visit`.`ID` DESC");
			
			if( substr($this->result->last_visit, 0, -1) != substr($this->Current_Date('Y-m-d H:i:s'), 0, -1) && !$this->Check_Spiders() ) {
			
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
		
		public function Visitors() {
		
			$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->get_IP()}'");
			
			if( !$this->result ) {
			
				$this->db->insert(
					$this->tb_prefix . "statistics_visitor",
					array(
						'last_counter'	=>	$this->Current_date('Y-m-d'),
						'referred'		=>	$this->get_Referred(true),
						'agent'			=>	$this->get_UserAgent(),
						'ip'			=>	$this->get_IP()
					)
				);
			}
		}
	}