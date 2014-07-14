<?php
/*
	This is the class for recording GeoIP infomration for hits on the WordPress site.  It extends the Hits class.
*/

	// The GeoIP libary from MaxMind is complete has many dependencies, use the autoload.php file to handle them all.
	require_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	
	// Load the classes.
	use GeoIp2\Database\Reader;

	class GeoIPHits extends Hits {
		public function __construct() {

			// Call the parent constructor (WP_Statistics::__constructor).
			parent::__construct();

			// Now get the location information from the MaxMind database.
			try 
				{
				// Get the WordPress upload directory information, which is where we have stored the MaxMind database.
				$upload_dir =  wp_upload_dir();
				
				// Create a new Reader and point it to the database.
				$reader = new Reader( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );
				
				// Look up the IP address
				$record = $reader->country( $this->ip );
				
				// Get the location.
				$location = $record->country->isoCode;
				
				// MaxMind returns a blank for location if it can't find it, but we want to use 000 so replace it.
				if( $location == "" ) { $location = "000"; }
				}
			catch( Exception $e )
				{
				$location = "000";
				}
			
			// Store the location in the protected $location variable from the parent class.
			$this->location = $location;
		}
	}