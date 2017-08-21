<?php
include_once dirname( __FILE__ ) . '/vendor/browscap/browscap-php/src/phpbrowscap/Browscap.php';

use phpbrowscap\Browscap;

// This function downloads the GeoIP database from MaxMind.
function wp_statistics_download_geoip() {

	GLOBAL $WP_Statistics;

	// We need the download_url() and gzopen() functions, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
	// Also stop trying to update the database as it just won't work :)
	if ( false === function_exists( 'download_url' ) || false === function_exists( 'gzopen' ) ) {
		$WP_Statistics->update_option( 'update_geoip', false );

		$result = "<div class='updated settings-error'><p><strong>" . __( 'Error the download_url() or gzopen() functions do not exist!', 'wp-statistics' ) . "</strong></p></div>";

		return $result;
	}

	// If GeoIP is disabled, bail out.
	if ( $WP_Statistics->get_option( 'geoip' ) == false ) {
		return '';
	}

	// This is the location of the file to download.
	$download_url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz';

	// Get the upload directory from WordPRess.
	$upload_dir = wp_upload_dir();

	// Create a variable with the name of the database file to download.
	$DBFile = $upload_dir['basedir'] . '/wp-statistics/GeoLite2-Country.mmdb';

	// Check to see if the subdirectory we're going to upload to exists, if not create it.
	if ( ! file_exists( $upload_dir['basedir'] . '/wp-statistics' ) ) {
		if ( ! @mkdir( $upload_dir['basedir'] . '/wp-statistics', 0755 ) ) {
			$WP_Statistics->update_option( 'update_geoip', false );

			$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error creating GeoIP database directory, make sure your web server has permissions to create directories in : %s', 'wp-statistics' ), $upload_dir['basedir'] ) . "</strong></p></div>";

			return $result;
		}
	}

	if ( ! is_writable( $upload_dir['basedir'] . '/wp-statistics' ) ) {
		$WP_Statistics->update_option( 'update_geoip', false );

		$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error setting permissions of the GeoIP database directory, make sure your web server has permissions to write to directories in : %s', 'wp-statistics' ), $upload_dir['basedir'] ) . "</strong></p></div>";

		return $result;
	}

	// Download the file from MaxMind, this places it in a temporary location.
	$TempFile = download_url( $download_url );

	// If we failed, through a message, otherwise proceed.
	if ( is_wp_error( $TempFile ) ) {
		$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error downloading GeoIP database from: %s - %s', 'wp-statistics' ), $download_url, $TempFile->get_error_message() ) . "</strong></p></div>";
	} else {
		// Open the downloaded file to unzip it.
		$ZipHandle = gzopen( $TempFile, 'rb' );

		// Create th new file to unzip to.
		$DBfh = fopen( $DBFile, 'wb' );

		// If we failed to open the downloaded file, through an error and remove the temporary file.  Otherwise do the actual unzip.
		if ( ! $ZipHandle ) {
			$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error could not open downloaded GeoIP database for reading: %s', 'wp-statistics' ), $TempFile ) . "</strong></p></div>";

			unlink( $TempFile );
		} else {
			// If we failed to open the new file, throw and error and remove the temporary file.  Otherwise actually do the unzip.
			if ( ! $DBfh ) {
				$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error could not open destination GeoIP database for writing %s', 'wp-statistics' ), $DBFile ) . "</strong></p></div>";
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
				$result = "<div class='updated settings-error'><p><strong>" . __( 'GeoIP Database updated successfully!', 'wp-statistics' ) . "</strong></p></div>";

				// Update the options to reflect the new download.
				$WP_Statistics->update_option( 'last_geoip_dl', time() );
				$WP_Statistics->update_option( 'update_geoip', false );

				// Populate any missing GeoIP information if the user has selected the option.
				if ( $WP_Statistics->get_option( 'geoip' ) && wp_statistics_geoip_supported() && $WP_Statistics->get_option( 'auto_pop' ) ) {
					include_once dirname( __FILE__ ) . '/includes/functions/geoip-populate.php';
					$result .= wp_statistics_populate_geoip_info();
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

		wp_mail( $WP_Statistics->get_option( 'email_list' ), __( 'GeoIP update on', 'wp-statistics' ) . ' ' . $blogname, $result, $headers );
	}

	// All of the messages displayed above are stored in a stirng, now it's time to actually output the messages.
	return $result;
}

// This function downloads the browscap database from browscap.org.
function wp_statistics_download_browscap() {

	GLOBAL $WP_Statistics;

	// We need the download_url() function, it should exists on virtually all installs of PHP, but if it doesn't for some reason, bail out.
	if ( ! function_exists( 'download_url' ) ) {
		return '';
	}

	// If browscap is disabled, bail out.
	if ( $WP_Statistics->get_option( 'browscap' ) == false ) {
		return '';
	}

	// This is the location of the file to download.
	$download_url     = 'http://browscap.org/stream?q=Full_PHP_BrowsCapINI';
	$download_version = 'http://browscap.org/version-number';

	// Get the upload directory from WordPress.
	$upload_dir = wp_upload_dir();

	// Check to see if the subdirectory we're going to upload to exists, if not create it.
	if ( ! file_exists( $upload_dir['basedir'] . '/wp-statistics' ) ) {
		mkdir( $upload_dir['basedir'] . '/wp-statistics' );
	}

	$LocalVersion = 0;

	// Get the Browscap object, tell it NOT to autoupdate.
	$bc               = new Browscap( $upload_dir['basedir'] . '/wp-statistics' );
	$bc->doAutoUpdate = false;    // We don't want to auto update.

	// If we already have a browscap.ini file (aka we're already done a download in the past) we can get it's version number.
	// We can't execute this code if no browscap.ini exists as then the library will automatically download a full version, even
	// though we've told it not to autoupdate above.
	if ( $WP_Statistics->get_option( 'last_browscap_dl' ) > 1 ) {
		// Get the current browser so that the version information is populated.
		try {
			$bc->getBrowser();
			$LocalVersion = $bc->getSourceVersion();
		} catch ( Exception $e ) {
			$crawler      = false;
			$LocalVersion = 0;
		}

	}

	// Get the remote version info from browscap.org.
	$TempVersionFile = download_url( $download_version );

	// Read the version we just downloaded in to a string.
	$RemoteVersion = file_get_contents( $TempVersionFile );

	// Get rid of the temporary file.
	unlink( $TempVersionFile );

	// If there is a new version, let's go get it.
	if ( intval( $RemoteVersion ) > $LocalVersion ) {

		// Download the file from browscap.org, this places it in a temporary location.
		$TempFile = download_url( $download_url );

		// If we failed, through a message, otherwise proceed.
		if ( is_wp_error( $TempFile ) ) {
			$result = "<div class='updated settings-error'><p><strong>" . sprintf( __( 'Error downloading browscap database from: %s - %s', 'wp-statistics' ), $download_url, $TempFile->get_error_message() ) . "</strong></p></div>";
		} else {
			// Keep the current version just in case by renaming it.
			if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/browscap.old' ) ) {
				unlink( $upload_dir['basedir'] . '/wp-statistics/browscap.old' );
			}
			if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/cache.old' ) ) {
				unlink( $upload_dir['basedir'] . '/wp-statistics/cache.old' );
			}
			if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/browscap.ini' ) ) {
				rename( $upload_dir['basedir'] . '/wp-statistics/browscap.ini', $upload_dir['basedir'] . '/wp-statistics/browscap.old' );
			}
			if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/cache.php' ) ) {
				rename( $upload_dir['basedir'] . '/wp-statistics/cache.php', $upload_dir['basedir'] . '/wp-statistics/cache.old' );
			}

			// Setup our file handles.
			$infile  = fopen( $TempFile, 'r' );
			$outfile = fopen( $upload_dir['basedir'] . '/wp-statistics/browscap.ini', 'w' );

			// We're going to need some variables to use as we process the new browscap.ini.
			// $crawler has three possible settings:
			// 		0 = no setting found
			//		1 = setting found but not a crawler
			// 		2 = setting found and a crawler
			$parent  = '';
			$title   = '';
			$crawler = 0;
			$parents = array( '' => false );

			// Now read in the browscap.ini file we downloaded one line at a time.
			while ( ( $buffer = fgets( $infile ) ) !== false ) {
				// Let's get rid of the tailing carriage return extra spaces.
				$buffer = trim( $buffer );

				// We're going to do some things based on the first charater on the line so let's just get it once.
				$firstchar = substr( $buffer, 0, 1 );

				// The first character will tell us what kind of line we're dealing with.
				switch ( $firstchar ) {
					// A square bracket means it's a section title.
					case '[':

						// We have three sections we need to copy verbatium so don't do the standard processing for them.
						if ( $title != 'GJK_Browscap_Version' && $title != 'DefaultProperties' && $title != '*' && $title != '' ) {
							// Write out the section if:
							//     the current section is a crawler and there is no parent
							//  OR
							//     the current section is a crawler, has a parent and the parent is a crawler as well (Note, this will drop some crawlers who's parent's aren't because we haven't written out all the parent's that aren't crawlers this could cause mis-identificaton of some users as crawlers).
							//  OR
							//     the current section isn't a crawler but the parent is
							//
							if ( ( $crawler == 2 && $parent == '' ) ||
							     ( $crawler == 2 && $parent != '' && array_key_exists( $parent, $parents ) ) ||
							     ( $crawler == 0 && array_key_exists( $parent, $parents ) )
							) {
								// Write out the section with just the parent/crawler setting saved.
								fwrite( $outfile, "[" . $title . "]\n" );
								fwrite( $outfile, "Crawler=\"true\"\n" );
							}
						}

						// Reset our variables.
						$crawler = 0;
						$parent  = '';
						$title   = substr( $buffer, 1, strlen( $buffer ) - 2 );

						// We have three sections we need to copy verbatium so write out their headings immediatly instead of waiting to see if they are a crawler.
						if ( $title == 'GJK_Browscap_Version' || $title == 'DefaultProperties' || $title == "*" ) {
							fwrite( $outfile, "[" . $title . "]\n" );
						}

						break;
					// A space or semi-colan means it's a comment.
					case ' ':
					case ';':
						// Since we're hacking out lots of data the only comments we want to keep are the first few in the file before the first section is processed.
						if ( $title == '' ) {
							fwrite( $outfile, $buffer . "\n" );
						}

						break;
					// Otherwise its a real setting line.
					default:
						// If the setting is for the crawler let's inidicate we found it and it's true.  We can also set the parents array.
						if ( $buffer == 'Crawler="true"' ) {
							$crawler           = 2;
							$parents[ $title ] = true;
						}

						// If the setting for the parent then set it now.
						if ( substr( $buffer, 0, 7 ) == 'Parent=' ) {
							$parent = substr( $buffer, 8, - 1 );
						}

						// We have three sections we need to copy verbatium so write out their settings.
						if ( $title == 'GJK_Browscap_Version' || $title == 'DefaultProperties' || $title == "*" ) {
							fwrite( $outfile, $buffer . "\n" );
						}
				}
			}

			// Close the files.
			fclose( $outfile );
			fclose( $infile );

			// Delete the temporary file.
			unlink( $TempFile );

			// Check to see if an old (more than a minute old) lock file exists, if so delete it.
			$cache_lock = $upload_dir['basedir'] . '/wp-statistics/cache.lock';
			if ( file_exists( $cache_lock ) ) {
				if ( time() - filemtime( $cache_lock ) > 60 ) {
					unlink( $cache_lock );
				}
			}

			// Force the cache to be updated.
			$bc->updateCache();

			// Update the options to reflect the new download.
			$WP_Statistics->update_option( 'last_browscap_dl', time() );
			$WP_Statistics->update_option( 'update_browscap', false );

			$result = "<div class='updated settings-error'><p><strong>" . __( 'browscap database updated successfully!', 'wp-statistics' ) . "</strong></p></div>";

			// Do some sanity checks on the new ini/cache file
			$ini_fs   = filesize( $upload_dir['basedir'] . '/wp-statistics/browscap.ini' );
			$cache_fs = filesize( $upload_dir['basedir'] . '/wp-statistics/cache.php' );
			$fail     = false;

			// Check to make sure the cache file isn't any more than 15% larger than then ini file
			if ( $cache_fs - $ini_fs > $ini_fs * 0.15 ) {
				$fail   = true;
				$result = "<div class='updated settings-error'><p><strong>" . __( 'browscap database updated failed!  Cache file too large, reverting to previous browscap.ini.', 'wp-statistics' ) . "</strong></p></div>";
			} else {
				// Check to make sure we don't resolve a typical user agent as a robot.
				$test_browser = $bc->getBrowser( "Mozilla/5.0 (Windows NT 6.3; WOW64; rv:33.0) Gecko/20100101 Firefox/33.0" );
				$crawler      = $test_browser->Crawler;

				if ( $crawler == true ) {
					$fail   = true;
					$result = "<div class='updated settings-error'><p><strong>" . __( 'browscap database updated failed!  New browscap.ini is mis-identifing user agents as crawlers, reverting to previous browscap.ini.', 'wp-statistics' ) . "</strong></p></div>";
				}
			}

			// If we failed, roll back the update, otherwise just delete the old files.
			if ( $fail == true ) {
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/browscap.ini' ) ) {
					unlink( $upload_dir['basedir'] . '/wp-statistics/browscap.ini' );
				}
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/cache.php' ) ) {
					unlink( $upload_dir['basedir'] . '/wp-statistics/cache.php' );
				}
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/browscap.old' ) ) {
					rename( $upload_dir['basedir'] . '/wp-statistics/browscap.old', $upload_dir['basedir'] . '/wp-statistics/browscap.ini' );
				}
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/cache.old' ) ) {
					rename( $upload_dir['basedir'] . '/wp-statistics/cache.old', $upload_dir['basedir'] . '/wp-statistics/cache.php' );
				}
			} else {
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/browscap.old' ) ) {
					unlink( $upload_dir['basedir'] . '/wp-statistics/browscap.old' );
				}
				if ( file_exists( $upload_dir['basedir'] . '/wp-statistics/cache.old' ) ) {
					unlink( $upload_dir['basedir'] . '/wp-statistics/cache.old' );
				}
			}
		}
	} else {
		// Update the options to reflect the new download.
		$WP_Statistics->update_option( 'last_browscap_dl', time() );
		$WP_Statistics->update_option( 'update_browscap', false );

		$result = "<div class='updated settings-error'><p><strong>" . __( 'browscap already at current version!', 'wp-statistics' ) . "</strong></p></div>";
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

		wp_mail( $WP_Statistics->get_option( 'email_list' ), __( 'Browscap.ini update on', 'wp-statistics' ) . ' ' . $blogname, $result, $headers );
	}

	// All of the messages displayed above are stored in a stirng, now it's time to actually output the messages.
	return $result;
}

// This function downloads the referrerspam database from https://github.com/piwik/referrer-spam-blacklist.
function wp_statistics_download_referrerspam() {

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
}