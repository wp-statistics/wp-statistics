<?php
	include_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );
	use GeoIp2\Database\Reader;

	function wp_statistics_populate_geoip_info() {
		global $wpdb;
		
		$table_prefix = $wpdb->prefix;
		
		$result = $wpdb->get_results("SELECT id,ip FROM `{$table_prefix}statistics_visitor` WHERE location = '' or location = '000' or location IS NULL");
		
		try {
			$upload_dir =  wp_upload_dir();
			$reader = new Reader( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );
		} catch( Exception $e ) {
			return "<div class='updated settings-error'><p><strong>" . __('Unable to load the GeoIP database, make sure you have downloaded it in the settings page.', 'wp_statistics') . "</strong></p></div>";
		}
		
		$count = 0;
		
		foreach( $result as $item ) {
			$count++;
			try {
				$record = $reader->country( $item->ip );
				$location = $record->country->isoCode;
				if( $location == "" ) { $location = "000"; }
			} catch( Exception $e ) {
				$location = "000";
			}

			$wpdb->update( $table_prefix . "statistics_visitor", array( 'location' => $location ), array( 'id' => $item->id) );
		}
		
		return "<div class='updated settings-error'><p><strong>" . sprintf(__('Updated %s GeoIP records in the visitors database.', 'wp_statistics'), $count) . "</strong></p></div>";
	}
?>