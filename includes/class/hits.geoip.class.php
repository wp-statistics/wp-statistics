<?php
	require_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	
	use GeoIp2\Database\Reader;

	class Hits extends WP_Statistics {
	
		public $result = null;
		
		public function __construct() {
		
			parent::__construct();
		}
		
		public function Visits() {
			
			global $wp_version;
			
			// If we're a webcrawler or referral from ourselves, don't record the visit.
			if( !$this->Check_Spiders() && !( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url("/") || $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url() )  ) {

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
		
			global $wp_version;
			
			// If we're a webcrawler or referral from ourselves, don't record the visitor.
			if( !$this->Check_Spiders() && !( $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url("/") || $_SERVER['HTTP_USER_AGENT'] == "WordPress/" . $wp_version . "; " . get_home_url() )  ) {
			
				$this->result = $this->db->get_row("SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->get_IP()}'");
				
				if( !$this->result ) {

					$agent = $this->get_UserAgent();
					$ip = $this->get_IP();
					
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
							'agent'			=>	$agent['browser'],
							'platform'		=>	$agent['platform'],
							'version'		=> 	$agent['version'],
							'ip'			=>	$ip,
							'location'		=>	$location,
							'UAString'		=>	$ua
						)
					);
				}
			}
		}
	}