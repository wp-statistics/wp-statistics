<?php
/*
	This is the primary class for recording hits on the WordPress site.  It extends the WP_Statistics class and is itself extended by the GeoIPHits class.

	This class handles; visits, visitors and pages.
*/

// The GeoIP library from MaxMind and the IP Tools library are complex and have many dependencies, use the autoload.php file to handle them all.
require_once( plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php' );

use phpbrowscap\Browscap;
use IPTools\IP;
use IPTools\Network;
use IPTools\Range;

class Hits extends WP_Statistics {

	// Setup our public/private/protected variables.
	public $result = null;

	protected $location = '000';
	protected $exclusion_match = false;
	protected $exclusion_reason = '';

	private $exclusion_record = false;
	private $timestamp;
	private $second;
	private $current_page_id;

	// Construction function.
	public function __construct() {
		global $wp_version, $WP_Statistics;

		// Call the parent constructor (WP_Statistics::__construct)
		parent::__construct();

		// Set the timestamp value.
		$this->timestamp = $this->current_date( 'U' );

		// Set the default seconds a user needs to visit the site before they are considered offline.
		$this->second = 30;

		// Get the user set value for seconds to check for users online.
		if ( $this->get_option( 'check_online' ) ) {
			$this->second = $this->get_option( 'check_online' );
		}

		// Check to see if the user wants us to record why we're excluding hits.
		if ( $this->get_option( 'record_exclusions' ) ) {
			$this->exclusion_record = true;
		}

		// Create a IP Tools instance from the current IP address for use later.
		// Fall back to the localhost if it can't be parsed.
		try {
			$ip = new IP( $this->ip );
		} catch ( Exception $e ) {
			$ip = new IP( '127.0.0.1' );
		}

		// Let's check to see if our subnet matches a private IP address range, if so go ahead and set the location information now.
		if ( $this->get_option( 'private_country_code' ) != '000' && $this->get_option( 'private_country_code' ) != '' ) {
			$private_subnets = array( '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1/24', 'fc00::/7' );

			foreach ( $private_subnets as $psub ) {
				$contains_ip = false;

				try {
					$contains_ip = Range::parse( $psub )->contains( $ip );
				} catch ( Exception $e ) {
					$contains_ip = false;
				}

				if ( $contains_ip ) {
					$this->location = $this->get_option( 'private_country_code' );
					break;
				}
			}
		}

		/*
		 * The follow exclusion checks are done during the class construction so we don't have to execute them twice if we're tracking visits and visitors.
		 *
		 * Order of exclusion checks is:
		 *		1 - AJAX calls
		 *		2 - Robots
		 * 		3 - IP/Subnets
		 *		4 - Self Referrals, Referrer Spam & login page
		 *		5 - User roles
		 *		6 - Host name list
		 *
		 * The GoeIP exclusions will be processed in the GeoIP hits class constructor.
		 *
		 * Note that we stop processing as soon as a match is made by executing a `return` from the function constructor.
		 *
		 */

		// Detect if we're running an ajax request.
		$page_uri    = wp_statistics_get_uri();
		$ajax_string = 'admin-ajax.php';

		if ( substr( $page_uri, 0, strlen( $ajax_string ) ) === $ajax_string ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'ajax';

			return;
		}

		// Detect if the user is a crawler.
		$crawler   = false;
		$ua_string = '';

		if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
			$ua_string = $_SERVER['HTTP_USER_AGENT'];
		}

		if ( $this->get_option( 'last_browscap_dl' ) > 1 && $this->get_option( 'browscap' ) ) {
			// Get the upload directory from WordPress.
			$upload_dir = wp_upload_dir();

			// Create a variable with the name of the database file to download.
			$BrowscapFile = $upload_dir['basedir'] . '/wp-statistics';

			// Get the Browser Capabilities use Browscap.
			$bc               = new Browscap( $BrowscapFile );
			$bc->doAutoUpdate = false;    // We don't want to auto update.
			try {
				$current_browser = $bc->getBrowser();
				// Make sure we got an object back and it has the Crawler property before accessing it.
				if ( is_object( $current_browser ) && property_exists( $current_browser, 'Crawler' ) ) {
					$crawler = $current_browser->Crawler;
				} else {
					$crawler = false;
				}
			} catch ( Exception $e ) {
				$crawler = false;
			}
		} else {
			$this->update_option( 'update_browscap', true );
		}

		// If we're a crawler as per browscap, exclude us, otherwise double check based on the WP Statistics robot list.
		if ( $crawler == true ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'browscap';

			return;
		} else {
			// Pull the robots from the database.
			$robots = explode( "\n", $this->get_option( 'robotlist' ) );

			// Check to see if we match any of the robots.
			foreach ( $robots as $robot ) {
				$robot = trim( $robot );

				// If the match case is less than 4 characters long, it might match too much so don't execute it.
				if ( strlen( $robot ) > 3 ) {
					if ( stripos( $ua_string, $robot ) !== false ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'robot';

						return;
					}
				}
			}

			// Finally check to see if we have corrupt header information.
			if ( ! $this->exclusion_match && $this->get_option( 'corrupt_browser_info' ) ) {
				if ( $ua_string == '' || $this->ip == '' ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'robot';

					return;
				}
			}
		}

		// If we didn't match a robot, check ip subnets.

		// Pull the subnets from the database.
		$subnets = explode( "\n", $this->get_option( 'exclude_ip' ) );

		// Check to see if we match any of the excluded addresses.
		foreach ( $subnets as $subnet ) {
			$subnet = trim( $subnet );

			// The shortest ip address is 1.1.1.1, anything less must be a malformed entry.
			if ( strlen( $subnet ) > 6 ) {
				$range_prased = false;

				try {
					$range_prased = Range::parse( $subnet )->contains( $ip );
				} catch ( Exception $e ) {
					$range_parased = false;
				}

				if ( $range_prased ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'ip match';

					return;
				}
			}
		}

		// Check to see if we are being referred to ourselves.
		if ( $ua_string == 'WordPress/' . $wp_version . '; ' . get_home_url( null, '/' ) || $ua_string == 'WordPress/' . $wp_version . '; ' . get_home_url() ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'self referral';

			return;
		}

		// Check to see if we're excluding the login page.
		if ( $this->get_option( 'exclude_loginpage' ) ) {
			$protocol = strpos( strtolower( $_SERVER['SERVER_PROTOCOL'] ), 'https' ) === false ? 'http' : 'https';
			$host     = $_SERVER['HTTP_HOST'];
			$script   = $_SERVER['SCRIPT_NAME'];

			$currentURL = $protocol . '://' . $host . $script;
			$loginURL   = wp_login_url();

			if ( $currentURL == $loginURL ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'login page';

				return;
			}
		}

		// Check to see if we're excluding the admin pages.
		if ( $this->get_option( 'exclude_adminpage' ) ) {
			$protocol = strpos( strtolower( $_SERVER['SERVER_PROTOCOL'] ), 'https' ) === false ? 'http' : 'https';
			$host     = $_SERVER['HTTP_HOST'];
			$script   = $_SERVER['SCRIPT_NAME'];

			$currentURL = $protocol . '://' . $host . $script;
			$adminURL   = get_admin_url();

			$currentURL = substr( $currentURL, 0, strlen( $adminURL ) );

			if ( $currentURL == $adminURL ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'admin page';

				return;
			}
		}

		// Check to see if we're excluding referrer spam.
		if ( $this->get_option( 'referrerspam' ) ) {
			$referrer = $this->get_Referred();

			// Pull the referrer spam list from the database.
			$referrerspamlist = explode( "\n", $this->get_option( 'referrerspamlist' ) );

			// Check to see if we match any of the robots.
			foreach ( $referrerspamlist as $item ) {
				$item = trim( $item );

				// If the match case is less than 4 characters long, it might match too much so don't execute it.
				if ( strlen( $item ) > 3 ) {
					if ( stripos( $referrer, $item ) !== false ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'referrer_spam';

						return;
					}
				}
			}
		}

		// Check to see if we're excluding RSS feeds.
		if ( $this->get_option( 'exclude_feeds' ) ) {
			if ( is_object( $WP_Statistics ) ) {
				if ( $WP_Statistics->check_feed() ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'feed';

					return;
				}
			}
		}

		// Check to see if we're excluding 404 pages.
		if ( $this->get_option( 'exclude_404s' ) ) {
			if ( is_404() ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = '404';

				return;
			}
		}

		// Check to see if we're excluding the current page url.
		if ( $this->get_option( 'excluded_urls' ) ) {
			$script    = $_SERVER['REQUEST_URI'];
			$delimiter = strpos( $script, '?' );
			if ( $delimiter > 0 ) {
				$script = substr( $script, 0, $delimiter );
			}

			$excluded_urls = explode( "\n", $this->get_option( 'excluded_urls' ) );

			foreach ( $excluded_urls as $url ) {
				$this_url = trim( $url );

				if ( strlen( $this_url ) > 2 ) {
					if ( stripos( $script, $this_url ) === 0 ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'excluded url';

						return;
					}
				}
			}
		}

		// Check to see if we are excluding based on the user role.
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();

			foreach ( $current_user->roles as $role ) {
				$option_name = 'exclude_' . str_replace( ' ', '_', strtolower( $role ) );
				if ( $this->get_option( $option_name ) == true ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'user role';

					return;
				}
			}
		}

		// Check to see if we are excluded by the host name.
		if ( ! $this->exclusion_match ) {
			$excluded_host = explode( "\n", $this->get_option( 'excluded_hosts' ) );

			// If there's nothing in the excluded host list, don't do anything.
			if ( count( $excluded_host ) > 0 ) {
				$transient_name = 'wps_excluded_hostname_to_ip_cache';

				// Get the transient with the hostname cache.
				$hostname_cache = get_transient( $transient_name );

				// If the transient has expired (or has never been set), create one now.
				if ( $hostname_cache === false ) {
					// Flush the failed cache variable.
					$hostname_cache = array();

					// Loop through the list of hosts and look them up.
					foreach ( $excluded_host as $host ) {
						if ( strpos( $host, '.' ) > 0 ) {
							// We add the extra period to the end of the host name to make sure we don't append the local dns suffix to the resolution cycle.
							$hostname_cache[ $host ] = gethostbyname( $host . '.' );
						}
					}

					// Set the transient and store it for 1 hour.
					set_transient( $transient_name, $hostname_cache, 360 );
				}

				// Check if the current IP address matches one of the ones in the excluded hosts list.
				if ( in_array( $this->ip, $hostname_cache ) ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'hostname';

					return;
				}
			}
		}
	}

	// This function records visits to the site.
	public function Visits() {

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		if ( ! $this->exclusion_match ) {

			// Check to see if we're a returning visitor.
			$this->result = $this->db->get_row( "SELECT * FROM {$this->tb_prefix}statistics_visit ORDER BY `{$this->tb_prefix}statistics_visit`.`ID` DESC" );

			// If we're a returning visitor, update the current record in the database, otherwise, create a new one.
			if ( $this->result->last_counter != $this->Current_Date( 'Y-m-d' ) ) {
				// We'd normally use the WordPress insert function, but since we may run in to a race condition where another hit to the site has already created a new entry in the database
				// for this IP address we want to do an "INSERT ... ON DUPLICATE KEY" which WordPress doesn't support.
				$sqlstring = $this->db->prepare( 'INSERT INTO ' . $this->tb_prefix . 'statistics_visit (last_visit, last_counter, visit) VALUES ( %s, %s, %d) ON DUPLICATE KEY UPDATE visit = visit + ' . $this->coefficient, $this->Current_Date(), $this->Current_date( 'Y-m-d' ), $this->coefficient );

				$this->db->query( $sqlstring );
			} else {
				$sqlstring = $this->db->prepare( 'UPDATE ' . $this->tb_prefix . 'statistics_visit SET `visit` = `visit` + %d, `last_visit` = %s WHERE `last_counter` = %s', $this->coefficient, $this->Current_Date(), $this->result->last_counter );

				$this->db->query( $sqlstring );
			}
		}
	}

	// This function records unique visitors to the site.
	public function Visitors() {
		global $wp_query, $WP_Statistics;

		// Get the pages or posts ID if it exists.
		if ( is_object( $wp_query ) ) {
			$this->current_page_id = $wp_query->get_queried_object_id();
		}

		if ( $this->get_option( 'use_honeypot' ) && $this->get_option( 'honeypot_postid' ) > 0 && $this->get_option( 'honeypot_postid' ) == $this->current_page_id && $this->current_page_id > 0 ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'honeypot';
		}

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		// The exception here is if we've matched a honey page, we want to lookup the user and flag them
		// as having been trapped in the honey pot for later exclusions.
		if ( $this->exclusion_reason == 'honeypot' || ! $this->exclusion_match ) {

			// Check to see if we already have an entry in the database.
			if ( $this->ip_hash != false ) {
				$this->result = $this->db->get_row( "SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->ip_hash}'" );
			} else {
				$this->result = $this->db->get_row( "SELECT * FROM {$this->tb_prefix}statistics_visitor WHERE `last_counter` = '{$this->Current_Date('Y-m-d')}' AND `ip` = '{$this->ip}' AND `agent` = '{$this->agent['browser']}' AND `platform` = '{$this->agent['platform']}' AND `version` = '{$this->agent['version']}'" );
			}

			// Check to see if this is a visit to the honey pot page, flag it when we create the new entry.
			$honeypot = 0;
			if ( $this->exclusion_reason == 'honeypot' ) {
				$honeypot = 1;
			}

			// If we don't create a new one, otherwise update the old one.
			if ( ! $this->result ) {

				// If we've been told to store the entire user agent, do so.
				if ( $this->get_option( 'store_ua' ) == true ) {
					$ua = $_SERVER['HTTP_USER_AGENT'];
				} else {
					$ua = '';
				}

				// Store the result.
				// We'd normally use the WordPress insert function, but since we may run in to a race condition where another hit to the site has already created a new entry in the database
				// for this IP address we want to do an "INSERT IGNORE" which WordPress doesn't support.
				$sqlstring = $this->db->prepare( 'INSERT IGNORE INTO ' . $this->tb_prefix . 'statistics_visitor (last_counter, referred, agent, platform, version, ip, location, UAString, hits, honeypot) VALUES ( %s, %s, %s, %s, %s, %s, %s, %s, 1, %s )', $this->Current_date( 'Y-m-d' ), $this->get_Referred(), $this->agent['browser'], $this->agent['platform'], $this->agent['version'], $this->ip_hash ? $this->ip_hash : $this->ip, $this->location, $ua, $honeypot );

				$this->db->query( $sqlstring );

				// Now parse the referrer and store the results in the search table if the database has been converted.
				// Also make sure we actually inserted a row on the INSERT IGNORE above or we'll create duplicate entries.
				if ( $this->get_option( 'search_converted' ) && $this->db->insert_id ) {

					$search_engines = wp_statistics_searchengine_list();
					$referred       = $this->get_Referred();

					// Parse the URL in to it's component parts.
					$parts = parse_url( $referred );

					// Loop through the SE list until we find which search engine matches.
					foreach ( $search_engines as $key => $value ) {
						$search_regex = wp_statistics_searchengine_regex( $key );

						preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

						if ( isset( $matches[1] ) ) {
							$data['last_counter'] = $this->Current_date( 'Y-m-d' );
							$data['engine']       = $key;
							$data['words']        = $WP_Statistics->Search_Engine_QueryString( $referred );
							$data['host']         = $parts['host'];
							$data['visitor']      = $this->db->insert_id;

							if ( $data['words'] == 'No search query found!' ) {
								$data['words'] = '';
							}

							$this->db->insert( $this->db->prefix . 'statistics_search', $data );
						}
					}
				}
			} else {
				// Normally we've done all of our exclusion matching during the class creation, however for the robot threshold is calculated here to avoid another call the database.
				if ( $this->get_option( 'robot_threshold' ) > 0 && $this->result->hits + 1 > $this->get_option( 'robot_threshold' ) ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'robot_threshold';
				} else if ( $this->result->honeypot ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'honeypot';
				} else {

					$sqlstring = $this->db->prepare( 'UPDATE ' . $this->tb_prefix . 'statistics_visitor SET `hits` = `hits` + %d, `honeypot` = %d WHERE `ID` = %d', 1 - $honeypot, $honeypot, $this->result->ID );

					$this->db->query( $sqlstring );
				}
			}
		}

		if ( $this->exclusion_match ) {
			$this->RecordExclusion();
		}
	}

	private function RecordExclusion() {
		// If we're not storing exclusions, just return.
		if ( $this->exclusion_record != true ) {
			return;
		}

		$this->result = $this->db->query( "UPDATE {$this->tb_prefix}statistics_exclusions SET `count` = `count` + 1 WHERE `date` = '{$this->Current_Date( 'Y-m-d' )}' AND `reason` = '{$this->exclusion_reason}'" );

		if ( ! $this->result ) {
			$this->db->insert(
				$this->tb_prefix . 'statistics_exclusions',
				array(
					'date'   => $this->Current_date( 'Y-m-d' ),
					'reason' => $this->exclusion_reason,
					'count'  => 1
				)
			);
		}
	}

	// This function records page hits.
	public function Pages() {
		global $wp_query;

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the page hit.
		if ( ! $this->exclusion_match ) {

			// Don't track anything but actual pages and posts, unless we've been told to.
			if ( $this->get_option( 'track_all_pages' ) || is_page() || is_single() || is_front_page() ) {
				// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
				if ( ! $this->current_page_id && is_object( $wp_query ) ) {
					$this->current_page_id = $wp_query->get_queried_object_id();
				}

				// If we didn't find a page id, we don't have anything else to do.
				if ( ! $this->current_page_id ) {
					return;
				}

				// Get the current page URI.
				$page_uri = wp_statistics_get_uri();

				if ( $this->get_option( 'strip_uri_parameters' ) ) {
					$temp = explode( '?', $page_uri );
					if ( $temp !== false ) {
						$page_uri = $temp[0];
					}
				}

				// Limit the URI length to 255 characters, otherwise we may overrun the SQL field size.
				$page_uri = substr( $page_uri, 0, 255 );

				// If we have already been to this page today (a likely scenario), just update the count on the record.
				$sql          = $this->db->prepare( "UPDATE {$this->tb_prefix}statistics_pages SET `count` = `count` + 1 WHERE `date` = '{$this->Current_Date( 'Y-m-d' )}' AND `uri` = %s", $page_uri );
				$this->result = $this->db->query( $sql );

				// If the update failed (aka the record doesn't exist), insert a new one.  Note this may drop a page hit if a race condition
				// exists where two people load the same page a the roughly the same time.  In that case two inserts would be attempted but
				// there is a unique index requirement on the database and one of them would fail.
				if ( ! $this->result ) {

					$this->db->insert(
						$this->tb_prefix . 'statistics_pages',
						array(
							'uri'   => $page_uri,
							'date'  => $this->Current_date( 'Y-m-d' ),
							'count' => 1,
							'id'    => $this->current_page_id
						)
					);
				}
			}
		}
	}

	// This function checks to see if the current user (as defined by their IP address) has an entry in the database.
	// Note we set the $this->result variable so we don't have to re-execute the query when we do the user update.
	public function Is_user() {

		if ( $this->ip_hash != false ) {
			$this->result = $this->db->query( "SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->ip_hash}'" );
		} else {
			$this->result = $this->db->query( "SELECT * FROM {$this->tb_prefix}statistics_useronline WHERE `ip` = '{$this->ip}' AND `agent` = '{$this->agent['browser']}' AND `platform` = '{$this->agent['platform']}' AND `version` = '{$this->agent['version']}'" );
		}

		if ( $this->result ) {
			return true;
		}
	}

	// This function add/update/delete the online users in the database.
	public function Check_online() {
		// If we're a webcrawler or referral from ourselves or an excluded address don't record the user as online, unless we've been told to anyway.
		if ( ! $this->exclusion_match || $this->get_option( 'all_online' ) ) {

			// If the current user exists in the database already, just update them, otherwise add them
			if ( $this->Is_user() ) {
				$this->Update_user();
			} else {
				$this->Add_user();
			}
		}

		// Remove users that have gone offline since the last check.
		$this->Delete_user();
	}

	// This function adds a user to the database.
	public function Add_user() {
		if ( ! $this->Is_user() ) {

			// Insert the user in to the database.
			$this->db->insert(
				$this->tb_prefix . 'statistics_useronline',
				array(
					'ip'        => $this->ip_hash ? $this->ip_hash : $this->ip,
					'timestamp' => $this->timestamp,
					'created'   => $this->timestamp,
					'date'      => $this->Current_Date(),
					'referred'  => $this->get_Referred(),
					'agent'     => $this->agent['browser'],
					'platform'  => $this->agent['platform'],
					'version'   => $this->agent['version'],
					'location'  => $this->location,
				)
			);
		}

	}

	// This function updates a user in the database.
	public function Update_user() {
		// Make sure we found the user earlier when we called Is_user().
		if ( $this->result ) {

			// Update the database with the new information.
			$this->db->update(
				$this->tb_prefix . 'statistics_useronline',
				array(
					'timestamp' => $this->timestamp,
					'date'      => $this->Current_Date(),
					'referred'  => $this->get_Referred(),
				),
				array(
					'ip'       => $this->ip_hash ? $this->ip_hash : $this->ip,
					'agent'    => $this->agent['browser'],
					'platform' => $this->agent['platform'],
					'version'  => $this->agent['version'],
					'location' => $this->location,
				)
			);
		}
	}

	// This function removes expired users.
	public function Delete_user() {
		// We want to delete users that are over the number of seconds set by the admin.
		$timediff = $this->timestamp - $this->second;

		// Call the deletion query.
		$this->db->query( "DELETE FROM {$this->tb_prefix}statistics_useronline WHERE timestamp < '{$timediff}'" );
	}
}