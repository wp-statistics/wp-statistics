<?php

use BrowscapPHP\Browscap;
use BrowscapPHP\BrowscapUpdater;
use BrowscapPHP\Helper\IniLoader;
use WurflCache\Adapter\File;
use GeoIp2\Database\Reader;

/**
 * Class WP_Statistics_Updates
 */
class WP_Statistics_Updates {

	/**
	 * This function downloads the GeoIP database from MaxMind.
	 *
	 * @return string
	 */
	static function download_geoip() {
		GLOBAL $WP_Statistics;

		// We need the download_url() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
		if ( ! function_exists( 'download_url' ) ) {
			include( ABSPATH . 'wp-admin/includes/file.php' );
		}

		// We need the wp_generate_password() function.
		if ( ! function_exists( 'wp_generate_password' ) ) {
			include( ABSPATH . 'wp-includes/pluggable.php' );
		}

		// We need the gzopen() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
		// Also stop trying to update the database as it just won't work :)
		if ( false === function_exists( 'gzopen' ) ) {
			$WP_Statistics->update_option( 'update_geoip', false );

			$result = "<div class='updated settings-error'><p><strong>" .
			          __( 'Error the gzopen() function do not exist!', 'wp-statistics' ) .
			          "</strong></p></div>";

			return $result;
		}

		// If GeoIP is disabled, bail out.
		if ( $WP_Statistics->get_option( 'geoip' ) == false ) {
			return '';
		}

		// This is the location of the file to download.
		$download_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';
		$response     = wp_remote_get( $download_url );

		// Change download url if the maxmind.com doesn't response.
		if ( wp_remote_retrieve_response_code( $response ) != '200' ) {
			$download_url = 'https://raw.githubusercontent.com/wp-statistics/GeoLite2-Country/master/GeoLite2-Country.mmdb.gz';
		}

		// Get the upload directory from WordPress.
		$upload_dir = wp_upload_dir();

		// Create a variable with the name of the database file to download.
		$DBFile = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb';

		// Check to see if the subdirectory we're going to upload to exists, if not create it.
		if ( ! file_exists( $upload_dir['basedir'] . '/wp-statistics' ) ) {
			if ( ! @mkdir( $upload_dir['basedir'] . '/wp-statistics', 0755 ) ) {
				$WP_Statistics->update_option( 'update_geoip', false );

				$result = "<div class='updated settings-error'><p><strong>" . sprintf(
						__(
							'Error creating GeoIP database directory, make sure your web server has permissions to create directories in : %s',
							'wp-statistics'
						),
						$upload_dir['basedir']
					) . "</strong></p></div>";

				return $result;
			}
		}

		if ( ! is_writable( $upload_dir['basedir'] . '/wp-statistics' ) ) {
			$WP_Statistics->update_option( 'update_geoip', false );

			$result = "<div class='updated settings-error'><p><strong>" . sprintf(
					__(
						'Error setting permissions of the GeoIP database directory, make sure your web server has permissions to write to directories in : %s',
						'wp-statistics'
					),
					$upload_dir['basedir']
				) . "</strong></p></div>";

			return $result;
		}

		// Download the file from MaxMind, this places it in a temporary location.
		$TempFile = download_url( $download_url );

		// If we failed, through a message, otherwise proceed.
		if ( is_wp_error( $TempFile ) ) {
			$WP_Statistics->update_option( 'update_geoip', false );

			$result = "<div class='updated settings-error'><p><strong>" . sprintf(
					__( 'Error downloading GeoIP database from: %s - %s', 'wp-statistics' ),
					$download_url,
					$TempFile->get_error_message()
				) . "</strong></p></div>";
		} else {
			// Open the downloaded file to unzip it.
			$ZipHandle = gzopen( $TempFile, 'rb' );

			// Create th new file to unzip to.
			$DBfh = fopen( $DBFile, 'wb' );

			// If we failed to open the downloaded file, through an error and remove the temporary file.  Otherwise do the actual unzip.
			if ( ! $ZipHandle ) {
				$WP_Statistics->update_option( 'update_geoip', false );

				$result = "<div class='updated settings-error'><p><strong>" . sprintf(
						__( 'Error could not open downloaded GeoIP database for reading: %s', 'wp-statistics' ),
						$TempFile
					) . "</strong></p></div>";

				unlink( $TempFile );
			} else {
				// If we failed to open the new file, throw and error and remove the temporary file.  Otherwise actually do the unzip.
				if ( ! $DBfh ) {
					$WP_Statistics->update_option( 'update_geoip', false );

					$result = "<div class='updated settings-error'><p><strong>" . sprintf(
							__( 'Error could not open destination GeoIP database for writing %s', 'wp-statistics' ),
							$DBFile
						) . "</strong></p></div>";
					unlink( $TempFile );
				} else {
					while ( ( $data = gzread( $ZipHandle, 4096 ) ) != false ) {
						fwrite( $DBfh, $data );
					}

					// Close the files.
					gzclose( $ZipHandle );
					fclose( $DBfh );

					// Delete the temporary file.
					unlink( $TempFile );

					// Display the success message.
					$result = "<div class='updated settings-error'><p><strong>" .
					          __( 'GeoIP Database updated successfully!', 'wp-statistics' ) .
					          "</strong></p></div>";

					// Update the options to reflect the new download.
					$WP_Statistics->update_option( 'last_geoip_dl', time() );
					$WP_Statistics->update_option( 'update_geoip', false );

					// Populate any missing GeoIP information if the user has selected the option.
					if ( $WP_Statistics->get_option( 'geoip' ) &&
					     wp_statistics_geoip_supported() &&
					     $WP_Statistics->get_option( 'auto_pop' )
					) {
						$result .= WP_Statistics_Updates::populate_geoip_info();
					}
				}
			}
		}

		if ( $WP_Statistics->get_option( 'geoip_report' ) == true ) {
			$blogname  = get_bloginfo( 'name' );
			$blogemail = get_bloginfo( 'admin_email' );

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( $WP_Statistics->get_option( 'email_list' ) == '' ) {
				$WP_Statistics->update_option( 'email_list', $blogemail );
			}

			wp_mail(
				$WP_Statistics->get_option( 'email_list' ),
				__( 'GeoIP update on', 'wp-statistics' ) . ' ' . $blogname,
				$result,
				$headers
			);
		}

		// All of the messages displayed above are stored in a string, now it's time to actually output the messages.
		return $result;
	}

	/**
	 * This function downloads the browscap database from browscap.org.
	 *
	 * @return string
	 */
	static function download_browscap() {
		global $WP_Statistics;

        // Changing PHP memory limits
        ini_set('memory_limit', '256M');

		// We need the download_url() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
		if ( ! function_exists( 'download_url' ) ) {
			include( ABSPATH . 'wp-admin/includes/file.php' );
		}

		// If browscap is disabled, bail out.
		if ( $WP_Statistics->get_option( 'browscap' ) == false ) {
			return '';
		}

		// Get the upload directory from WordPress.
		$upload_dir = wp_upload_dir();

		// Check to see if the subdirectory we're going to upload to exists, if not create it.
		if ( ! file_exists( $upload_dir['basedir'] . '/wp-statistics' ) ) {
			mkdir( $upload_dir['basedir'] . '/wp-statistics' );
		}

		// First if all update the option to reflect the new download.
		$WP_Statistics->update_option( 'update_browscap', false );

		$adapter = new File( array( File::DIR => $upload_dir['basedir'] . '/wp-statistics' ) );

		try {
			$browscap_updater = new BrowscapUpdater();
			$browscap_updater->setCache( $adapter );
			$browscap_updater->update( IniLoader::PHP_INI );

			// Update browscap last download time
			$WP_Statistics->update_option( 'last_browscap_dl', time() );

			$message = __( 'Browscap database updated successfully!', 'wp-statistics' );
		} catch ( Exception $e ) {
			$message = sprintf( __( 'Browscap database updated failed! %s', 'wp-statistics' ), $e->getMessage() );
		}

		if ( $WP_Statistics->get_option( 'browscap_report' ) == true ) {
			$blogname  = get_bloginfo( 'name' );
			$blogemail = get_bloginfo( 'admin_email' );

			$headers[] = "From: $blogname <$blogemail>";
			$headers[] = "MIME-Version: 1.0";
			$headers[] = "Content-type: text/html; charset=utf-8";

			if ( $WP_Statistics->get_option( 'email_list' ) == '' ) {
				$WP_Statistics->update_option( 'email_list', $blogemail );
			}

			wp_mail(
				$WP_Statistics->get_option( 'email_list' ),
				__( 'Browscap.ini update on', 'wp-statistics' ) . ' ' . $blogname,
				$message,
				$headers
			);
		}

		// Generate admin notice message
		$result = "<div class='updated settings-error'><p><strong>" . $message . "</strong></p></div>";

		// All of the messages displayed above are stored in a string, now it's time to actually output the messages.
		return $result;
	}

	/**
	 * Downloads the referrerspam database from https://github.com/piwik/referrer-spam-blacklist.
	 *
	 * @return string
	 */
	static function download_referrerspam() {
		GLOBAL $WP_Statistics;

		// If referrerspam is disabled, bail out.
		if ( $WP_Statistics->get_option( 'referrerspam' ) == false ) {
			return '';
		}

		// This is the location of the file to download.
		$download_url = 'https://raw.githubusercontent.com/piwik/referrer-spam-blacklist/master/spammers.txt';

		// Download the file from MaxMind, this places it in a temporary location.
		$referrerspamlist = file_get_contents( $download_url );
		if ( $referrerspamlist === false ) {
			$referrerspamlist = '';
		}

		if ( $referrerspamlist != '' || $WP_Statistics->get_option( 'referrerspamlist' ) != '' ) {
			$WP_Statistics->update_option( 'referrerspamlist', $referrerspamlist );
		}

		$WP_Statistics->update_option( 'update_referrerspam', false );

		return;
	}

	/**
	 * Populate GeoIP infomration in to the database.
	 * It is used in two different parts of the plugin;
	 * When a user manual requests the update to happen and after a new GeoIP database has been download
	 * (if the option is selected).
	 *
	 * @return string
	 */
	static function populate_geoip_info() {
		global $wpdb;

		// Find all rows in the table that currently don't have GeoIP info or have an unknown ('000') location.
		$result = $wpdb->get_results(
			"SELECT id,ip FROM `{$wpdb->prefix}statistics_visitor` WHERE location = '' or location = '000' or location IS NULL"
		);

		// Try create a new reader instance.
		try {
			$upload_dir = wp_upload_dir();
			$reader     = new Reader( $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb' );
		} catch ( Exception $e ) {
			return "<div class='updated settings-error'><p><strong>" . __(
					'Unable to load the GeoIP database, make sure you have downloaded it in the settings page.',
					'wp-statistics'
				) . "</strong></p></div>";
		}

		$count = 0;

		// Loop through all the missing rows and update them if we find a locaiton for them.
		foreach ( $result as $item ) {
			$count ++;

			// If the IP address is only a hash, don't bother updating the record.
			if ( substr( $item->ip, 0, 6 ) != '#hash#' ) {
				try {
					$record   = $reader->country( $item->ip );
					$location = $record->country->isoCode;
					if ( $location == "" ) {
						$location = "000";
					}
				} catch ( Exception $e ) {
					$location = "000";
				}

				// Update the row in the database.
				$wpdb->update(
					$wpdb->prefix . "statistics_visitor",
					array( 'location' => $location ),
					array( 'id' => $item->id )
				);
			}
		}

		return "<div class='updated settings-error'><p><strong>" .
		       sprintf( __( 'Updated %s GeoIP records in the visitors database.', 'wp-statistics' ), $count ) .
		       "</strong></p></div>";
	}
}
