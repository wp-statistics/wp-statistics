<?php

/**
 * Class WP_Statistics_Updates
 */
class WP_Statistics_Updates {

	//List Geo ip Library
	public static $geoip = array();
	
	/**
	 * Update option process.
	 */
	static function do_upgrade() {

	}

	/**
	 * This function downloads the GeoIP database from MaxMind.
	 *
	 * @param $pack
	 * @param string $type
	 *
	 * @return string
	 */
	static function download_geoip( $pack, $type = "enable" ) {}

	/**
	 * Downloads the referrer spam database from https://github.com/matomo-org/referrer-spam-blacklist.
	 * @return string
	 */
	static function download_referrerspam() {}

	/**
	 * Populate GeoIP information in to the database.
	 * It is used in two different parts of the plugin;
	 * When a user manual requests the update to happen and after a new GeoIP database has been download
	 * (if the option is selected).
	 *
	 * @return string
	 */
	static function populate_geoip_info() {}
}
