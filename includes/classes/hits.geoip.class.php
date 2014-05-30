<?php
	require_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	
	use GeoIp2\Database\Reader;

	class GeoIPHits extends Hits {
		public function __construct() {

			parent::__construct();

			try 
				{
				$upload_dir =  wp_upload_dir();
				$reader = new Reader( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );
				$record = $reader->country( $this->ip );
				$location = $record->country->isoCode;
				}
			catch( Exception $e )
				{
				$location = "000";
				}
			
			$this->location = $location;
		}
	}