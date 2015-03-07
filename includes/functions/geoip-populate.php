<?php
/*
	This file contains the code required to populate GeoIP infomration in to the database.
	
	It is used in two different parts of the plugin; when a user manual requests the update to happen and after a new GeoIP database has been download (if the option is selected).
*/

	// Include the MaxMind library and use it.
	include_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	use GeoIp2\Database\Reader;

	// This function does all the work.
	function wp_statistics_populate_geoip_info() {
		global $wpdb;
		
		$table_prefix = $wpdb->prefix;
		
		// Find all rows in the table that currently don't have GeoIP info or have an unknown ('000') location.
		$result = $wpdb->get_results("SELECT id,ip FROM `{$table_prefix}statistics_visitor` WHERE location = '' or location = '000' or location IS NULL");
		
		// Try create a new reader instance.
		try {
			$upload_dir =  wp_upload_dir();
			$reader = new Reader( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );
		} catch( Exception $e ) {
			return "<div class='updated settings-error'><p><strong>" . __('Unable to load the GeoIP database, make sure you have downloaded it in the settings page.', 'wp_statistics') . "</strong></p></div>";
		}
		
		$count = 0;
		
		// Loop through all the missing rows and update them if we find a locaiton for them.
		foreach( $result as $item ) {
			$count++;

			// If the IP address is only a hash, don't bother updating the record.
			if( substr( $item->ip, 0, 6 ) != '#hash#' ) { 
				try {
					$record = $reader->country( $item->ip );
					$location = $record->country->isoCode;
					if( $location == "" ) { $location = "000"; }
				} catch( Exception $e ) {
					$location = "000";
				}

				// Update the row in the database.
				$wpdb->update( $table_prefix . "statistics_visitor", array( 'location' => $location ), array( 'id' => $item->id) );
			}
		}
		
		return "<div class='updated settings-error'><p><strong>" . sprintf(__('Updated %s GeoIP records in the visitors database.', 'wp_statistics'), $count) . "</strong></p></div>";
	}
?>