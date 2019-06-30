<?php
/*
	This is the primary class for recording hits on the WordPress site.  It extends the WP_Statistics class and is itself extended by the GEO_IP_Hits class.
	This class handles; visits, visitors and pages.
*/

use Jaybizzle\CrawlerDetect\CrawlerDetect;
use IPTools\IP;
use IPTools\Network;
use IPTools\Range;

class WP_Statistics_Hits {

	// Setup our public/private/protected variables.
	public $result = null;

	protected $location = '000';
	public $exclusion_match = false;
	public $exclusion_reason = '';

	public $exclusion_record = false;
	private $timestamp;
	private $current_page_id;
	private $current_page_type;
	public $current_visitor_id = 0;

	// Construction function.
	public function __construct() {
		global $wp_version, $WP_Statistics;

		// Set the timestamp value.
		$this->timestamp = $WP_Statistics->current_date( 'U' );
		if ( WP_Statistics_Rest::is_rest() ) {
			$this->timestamp = WP_Statistics_Rest::params( 'timestamp' );
		}

		// Check to see if the user wants us to record why we're excluding hits.
		if ( $WP_Statistics->get_option( 'record_exclusions' ) ) {
			$this->exclusion_record = true;
		}

		// Create a IP Tools instance from the current IP address for use later.
		// Fall back to the localhost if it can't be parsed.
		try {
			$ip = new IP( $WP_Statistics->ip );
		} catch ( Exception $e ) {
			$ip = new IP( '127.0.0.1' );
		}

		// Let's check to see if our subnet matches a private IP address range, if so go ahead and set the location information now.
		if ( $WP_Statistics->get_option( 'private_country_code' ) != '000' &&
		     $WP_Statistics->get_option( 'private_country_code' ) != ''
		) {
			$private_subnets = array( '10.0.0.0/8', '172.16.0.0/12', '192.168.0.0/16', '127.0.0.1/24', 'fc00::/7' );

			foreach ( $private_subnets as $psub ) {

				try {
					$contains_ip = Range::parse( $psub )->contains( $ip );
				} catch ( Exception $e ) {
					$contains_ip = false;
				}

				if ( $contains_ip ) {
					$this->location = $WP_Statistics->get_option( 'private_country_code' );
					break;
				}
			}
		}

		/*
		 * The follow exclusion checks are done during the class construction so we don't have to execute them twice if we're tracking visits and visitors.
		 *
		 * Order of exclusion checks is:
		 *		1 - AJAX calls
		 * 		2 - CronJob
		 *		3 - Robots
		 * 		4 - IP/Subnets
		 *		5 - Self Referrals, Referrer Spam & login page
		 *		6 - User roles
		 *		7 - Host name list
		 *      8 - Broken link file
		 *
		 * The GoeIP exclusions will be processed in the GeoIP hits class constructor.
		 *
		 * Note that we stop processing as soon as a match is made by executing a `return` from the function constructor.
		 *
		 */
		if ( WP_Statistics_Rest::is_rest() ) {
			$this->exclusion_match  = ( WP_Statistics_Rest::params( 'exclude' ) == 1 ? true : false );
			$this->exclusion_reason = WP_Statistics_Rest::params( 'exclude_reason' );

			if ( $this->exclusion_match === true ) {
				return;
			}
		} else {

			// Detect if we're running an ajax request.
			if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'ajax';

				return;
			}

			if ( ( defined( 'DOING_CRON' ) && DOING_CRON === true ) || ( function_exists( 'wp_doing_cron' ) && wp_doing_cron() === true ) ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'cronjob';

				return;
			}

			$crawler   = false;
			$ua_string = '';

			if ( array_key_exists( 'HTTP_USER_AGENT', $_SERVER ) ) {
				$ua_string = $_SERVER['HTTP_USER_AGENT'];
			}

			/*
			 * Check Is robot
			 */
			$CrawlerDetect = new CrawlerDetect;
			if ( $CrawlerDetect->isCrawler() ) {
				$crawler = true;
			}

			// If we're a crawler as per whichbrowser, exclude us, otherwise double check based on the WP Statistics robot list.
			if ( $crawler == true ) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'CrawlerDetect';

				return;
			} else {
				// Pull the robots from the database.
				$robots = explode( "\n", $WP_Statistics->get_option( 'robotlist' ) );

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
				if ( ! $this->exclusion_match && $WP_Statistics->get_option( 'corrupt_browser_info' ) ) {
					if ( $ua_string == '' || $WP_Statistics->ip == '' ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'robot';

						return;
					}
				}
			}

			//Check Broken Link File
			if ( is_404() ) {

				//Check Current Page
				if ( isset( $_SERVER["HTTP_HOST"] ) and isset( $_SERVER["REQUEST_URI"] ) ) {

					//Get Full Url Page
					$page_url = ( isset( $_SERVER['HTTPS'] ) && $_SERVER['HTTPS'] === 'on' ? "https" : "http" ) . "://{$_SERVER["HTTP_HOST"]}{$_SERVER["REQUEST_URI"]}";

					//Check Link file
					$page_url = parse_url( $page_url, PHP_URL_PATH );
					$ext      = pathinfo( $page_url, PATHINFO_EXTENSION );
					if ( ! empty( $ext ) and $ext != 'php' ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'BrokenFile';

						return;
					}
				}
			}

			// Pull the subnets from the database.
			$subnets = explode( "\n", $WP_Statistics->get_option( 'exclude_ip' ) );

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
			if ( $ua_string == 'WordPress/' . $wp_version . '; ' . get_home_url( null, '/' ) ||
			     $ua_string == 'WordPress/' . $wp_version . '; ' . get_home_url()
			) {
				$this->exclusion_match  = true;
				$this->exclusion_reason = 'self referral';

				return;
			}

			// Check to see if we're excluding the login page.
			if ( $WP_Statistics->get_option( 'exclude_loginpage' ) ) {
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

			// Check to see if we're excluding the Admin page.
			if ( $WP_Statistics->get_option( 'exclude_adminpage' ) ) {
				if ( stristr( $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'], "wp-admin" ) ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'admin page';

					return;
				}
			}

			// Check to see if we're excluding referrer spam.
			if ( $WP_Statistics->get_option( 'referrerspam' ) ) {
				$referrer = $WP_Statistics->get_Referred();

				// Pull the referrer spam list from the database.
				$referrerspamlist = explode( "\n", $WP_Statistics->get_option( 'referrerspamlist' ) );

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
			if ( $WP_Statistics->get_option( 'exclude_feeds' ) ) {
				if ( is_feed() ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'feed';

					return;
				}
			}

			// Check to see if we're excluding 404 pages.
			if ( $WP_Statistics->get_option( 'exclude_404s' ) ) {
				if ( is_404() ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = '404';

					return;
				}
			}

			// Check to see if we're excluding the current page url.
			if ( $WP_Statistics->get_option( 'excluded_urls' ) ) {
				$script    = $_SERVER['REQUEST_URI'];
				$delimiter = strpos( $script, '?' );
				if ( $delimiter > 0 ) {
					$script = substr( $script, 0, $delimiter );
				}

				$excluded_urls = explode( "\n", $WP_Statistics->get_option( 'excluded_urls' ) );

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
					if ( $WP_Statistics->get_option( $option_name ) == true ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'user role';

						return;
					}
				}
			}

			// Check to see if we are excluded by the host name.
			if ( ! $this->exclusion_match ) {
				$excluded_host = explode( "\n", $WP_Statistics->get_option( 'excluded_hosts' ) );

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
					if ( in_array( $WP_Statistics->ip, $hostname_cache ) ) {
						$this->exclusion_match  = true;
						$this->exclusion_reason = 'hostname';

						return;
					}
				}
			}
		}
	}

	// This function records visits to the site.
	public function Visits() {
		global $wpdb, $WP_Statistics;

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		if ( ! $this->exclusion_match ) {

			// Check to see if we're a returning visitor.
			$this->result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}statistics_visit ORDER BY `{$wpdb->prefix}statistics_visit`.`ID` DESC" );

			// If we're a returning visitor, update the current record in the database, otherwise, create a new one.
			if ( $this->result->last_counter != $WP_Statistics->Current_Date( 'Y-m-d' ) ) {

				// We'd normally use the WordPress insert function, but since we may run in to a race condition where another hit to the site has already created a new entry in the database
				// for this IP address we want to do an "INSERT ... ON DUPLICATE KEY" which WordPress doesn't support.
				$sqlstring = $wpdb->prepare(
					'INSERT INTO ' . $wpdb->prefix . 'statistics_visit (last_visit, last_counter, visit) VALUES ( %s, %s, %d) ON DUPLICATE KEY UPDATE visit = visit + ' . $WP_Statistics->coefficient,
					$WP_Statistics->Current_Date(),
					$WP_Statistics->Current_date( 'Y-m-d' ),
					$WP_Statistics->coefficient
				);

				$wpdb->query( $sqlstring );
			} else {
				$sqlstring = $wpdb->prepare(
					'UPDATE ' . $wpdb->prefix . 'statistics_visit SET `visit` = `visit` + %d, `last_visit` = %s WHERE `last_counter` = %s',
					$WP_Statistics->coefficient,
					$WP_Statistics->Current_Date(),
					$this->result->last_counter
				);

				$wpdb->query( $sqlstring );
			}
		}
	}

	//Get current Page detail
	public function get_page_detail() {

		//if is Cache enable
		if ( WP_Statistics_Rest::is_rest() ) {
			$this->current_page_id   = WP_Statistics_Rest::params( 'current_page_id' );
			$this->current_page_type = WP_Statistics_Rest::params( 'current_page_type' );
		} else {
			//Get Page Type
			$get_page_type           = WP_Statistics_Frontend::get_page_type();
			$this->current_page_id   = $get_page_type['id'];
			$this->current_page_type = $get_page_type['type'];
		}

	}


	// This function records unique visitors to the site.
	public function Visitors() {
		global $wpdb, $WP_Statistics;

		//Get Current Page detail
		$this->get_page_detail();

		//Check honeypot Page
		if ( $WP_Statistics->get_option( 'use_honeypot' ) && $WP_Statistics->get_option( 'honeypot_postid' ) > 0 && $WP_Statistics->get_option( 'honeypot_postid' ) == $this->current_page_id && $this->current_page_id > 0 ) {
			$this->exclusion_match  = true;
			$this->exclusion_reason = 'honeypot';
		}

		// If we're a webcrawler or referral from ourselves or an excluded address don't record the visit.
		// The exception here is if we've matched a honey page, we want to lookup the user and flag them
		// as having been trapped in the honey pot for later exclusions.
		if ( $this->exclusion_reason == 'honeypot' || ! $this->exclusion_match ) {

			// Check to see if we already have an entry in the database.
			$check_ip_db = $WP_Statistics->store_ip_to_db();
			if ( $WP_Statistics->ip_hash != false ) {
				$check_ip_db = $WP_Statistics->ip_hash;
			}

			//Check Exist This User in Current Day
			$this->result = $wpdb->get_row( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `last_counter` = '{$WP_Statistics->Current_Date('Y-m-d')}' AND `ip` = '{$check_ip_db}'" );

			// Check to see if this is a visit to the honey pot page, flag it when we create the new entry.
			$honeypot = 0;
			if ( $this->exclusion_reason == 'honeypot' ) {
				$honeypot = 1;
			}

			// If we don't create a new one, otherwise update the old one.
			if ( ! $this->result ) {

				// If we've been told to store the entire user agent, do so.
				if ( $WP_Statistics->get_option( 'store_ua' ) == true ) {
					if ( WP_Statistics_Rest::is_rest() ) {
						$ua = WP_Statistics_Rest::params( 'ua' );
					} else {
						$ua = ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '' );
					}
				} else {
					$ua = '';
				}

				// Store the result.
				add_filter( 'query', 'wp_statistics_ignore_insert', 10 );
				$wpdb->insert(
					$wpdb->prefix . 'statistics_visitor',
					array(
						'last_counter' => $WP_Statistics->Current_date( 'Y-m-d' ),
						'referred'     => $WP_Statistics->get_Referred(),
						'agent'        => $WP_Statistics->agent['browser'],
						'platform'     => $WP_Statistics->agent['platform'],
						'version'      => $WP_Statistics->agent['version'],
						'ip'           => $WP_Statistics->ip_hash ? $WP_Statistics->ip_hash : $WP_Statistics->store_ip_to_db(),
						'location'     => $this->location,
						'UAString'     => $ua,
						'hits'         => 1,
						'honeypot'     => $honeypot,
					),
					array( '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
				);
				$this->current_visitor_id = $wpdb->insert_id;
				remove_filter( 'query', 'wp_statistics_ignore_insert', 10 );

				// Now parse the referrer and store the results in the search table if the database has been converted.
				// Also make sure we actually inserted a row on the INSERT IGNORE above or we'll create duplicate entries.
				if ( $wpdb->insert_id ) {

					$search_engines = wp_statistics_searchengine_list();
					if ( WP_Statistics_Rest::is_rest() ) {
						$referred = WP_Statistics_Rest::params( 'referred' );
					} else {
						$referred = $WP_Statistics->get_Referred();
					}

					// Parse the URL in to it's component parts.
					if ( wp_http_validate_url( $referred ) ) {
						$parts = parse_url( $referred );

						// Loop through the SE list until we find which search engine matches.
						foreach ( $search_engines as $key => $value ) {
							$search_regex = wp_statistics_searchengine_regex( $key );

							preg_match( '/' . $search_regex . '/', $parts['host'], $matches );

							if ( isset( $matches[1] ) ) {
								$data['last_counter'] = $WP_Statistics->Current_date( 'Y-m-d' );
								$data['engine']       = $key;
								$data['words']        = $WP_Statistics->Search_Engine_QueryString( $referred );
								$data['host']         = $parts['host'];
								$data['visitor']      = $wpdb->insert_id;

								if ( $data['words'] == 'No search query found!' ) {
									$data['words'] = '';
								}

								$wpdb->insert( $wpdb->prefix . 'statistics_search', $data );
							}
						}
					}
				}
			} else {

				// Normally we've done all of our exclusion matching during the class creation, however for the robot threshold is calculated here to avoid another call the database.
				if ( $WP_Statistics->get_option( 'robot_threshold' ) > 0 && $this->result->hits + 1 > $WP_Statistics->get_option( 'robot_threshold' ) ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'robot_threshold';
				} else if ( $this->result->honeypot ) {
					$this->exclusion_match  = true;
					$this->exclusion_reason = 'honeypot';
				} else {

					//Get Current Visitors ID
					$this->current_visitor_id = $this->result->ID;

					$sqlstring = $wpdb->prepare(
						'UPDATE `' . $wpdb->prefix . 'statistics_visitor` SET `hits` = `hits` + %d, `honeypot` = %d WHERE `ID` = %d',
						1,
						$honeypot,
						$this->result->ID
					);

					$wpdb->query( $sqlstring );
				}
			}
		}

		if ( $this->exclusion_match ) {
			$this->RecordExclusion();
		}
	}

	private function RecordExclusion() {
		global $wpdb, $WP_Statistics;
		// If we're not storing exclusions, just return.
		if ( $this->exclusion_record != true ) {
			return;
		}

		$this->result = $wpdb->query( "UPDATE {$wpdb->prefix}statistics_exclusions SET `count` = `count` + 1 WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND `reason` = '{$this->exclusion_reason}'" );
		if ( ! $this->result ) {
			$wpdb->insert(
				$wpdb->prefix . 'statistics_exclusions',
				array(
					'date'   => $WP_Statistics->Current_date( 'Y-m-d' ),
					'reason' => $this->exclusion_reason,
					'count'  => 1,
				)
			);
		}
	}

	// Check is Track All Page
	static public function is_track_page() {
		global $WP_Statistics;

		//Check if Track All
		if ( $WP_Statistics->get_option( 'track_all_pages' ) || is_single() || is_page() || is_front_page() ) {
			return true;
		}

		return false;
	}

	// This function records page hits.
	public function Pages() {
		global $wpdb, $WP_Statistics;

		// If we're a web crawler or referral from ourselves or an excluded address don't record the page hit.
		if ( ! $this->exclusion_match ) {

			// Don't track anything but actual pages and posts, unless we've been told to.
			$is_track_all = false;
			if ( WP_Statistics_Rest::is_rest() ) {
				if ( WP_Statistics_Rest::params( 'track_all' ) == 1 ) {
					$is_track_all = true;
				}
			} else {
				if ( self::is_track_page() ) {
					$is_track_all = true;
				}
			}

			if ( $is_track_all === true ) {

				// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
				$this->get_page_detail();

				// If we didn't find a page id, we don't have anything else to do.
				if ( $this->current_page_type == "unknown" ) {
					return;
				}

				// Get the current page URI.
				if ( WP_Statistics_Rest::is_rest() ) {
					$page_uri = WP_Statistics_Rest::params( 'page_uri' );
				} else {
					$page_uri = wp_statistics_get_uri();
				}

				//Get String Search Wordpress
				$is_search = false;
				if ( WP_Statistics_Rest::is_rest() ) {
					if ( WP_Statistics_Rest::params( 'search_query' ) != "" ) {
						$page_uri  = "?s=" . WP_Statistics_Rest::params( 'search_query' );
						$is_search = true;
					}
				} else {
					$get_page_type = WP_Statistics_Frontend::get_page_type();
					if ( array_key_exists( "search_query", $get_page_type ) ) {
						$page_uri  = "?s=" . $get_page_type['search_query'];
						$is_search = true;
					}
				}

				if ( $WP_Statistics->get_option( 'strip_uri_parameters' ) and $is_search === false ) {
					$temp = explode( '?', $page_uri );
					if ( $temp !== false ) {
						$page_uri = $temp[0];
					}
				}

				// Limit the URI length to 255 characters, otherwise we may overrun the SQL field size.
				$page_uri = substr( $page_uri, 0, 255 );

				// If we have already been to this page today (a likely scenario), just update the count on the record.
				$exist = $wpdb->get_row( "SELECT `page_id` FROM {$wpdb->prefix}statistics_pages WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' " . ( $is_search === true ? "AND `uri` = '" . esc_sql( $page_uri ) . "'" : "" ) . "AND `type` = '{$this->current_page_type}' AND `id` = {$this->current_page_id}", ARRAY_A );
				if ( null !== $exist ) {
					$sql          = $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_pages SET `count` = `count` + 1 WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' " . ( $is_search === true ? "AND `uri` = '" . esc_sql( $page_uri ) . "'" : "" ) . "AND `type` = '{$this->current_page_type}' AND `id` = %d", $this->current_page_id );
					$this->result = $wpdb->query( $sql );
					$page_id      = $exist['page_id'];

				} else {
					add_filter( 'query', 'wp_statistics_ignore_insert', 10 );
					$wpdb->insert(
						$wpdb->prefix . 'statistics_pages',
						array(
							'uri'   => $page_uri,
							'date'  => $WP_Statistics->Current_date( 'Y-m-d' ),
							'count' => 1,
							'id'    => $this->current_page_id,
							'type'  => $this->current_page_type
						)
					);
					$page_id = $wpdb->insert_id;
					remove_filter( 'query', 'wp_statistics_ignore_insert', 10 );
				}

				//Set Visitor Relationships
				if ( $WP_Statistics->get_option( 'visitors' ) == true and $WP_Statistics->get_option( 'visitors_log' ) == true and $this->current_visitor_id > 0 ) {
					$this->visitors_relationships( $page_id, $this->current_visitor_id );
				}

			}
		}
	}

	//Set Visitor Relationships
	public function visitors_relationships( $page_id, $visitor_id ) {
		global $wpdb;
		$wpdb->insert(
			$wpdb->prefix . 'statistics_visitor_relationships',
			array(
				'visitor_id' => $visitor_id,
				'page_id'    => $page_id,
				'date'       => current_time( 'mysql' )
			),
			array( '%d', '%d', '%s' )
		);
	}

	// This function checks to see if the current user (as defined by their IP address) has an entry in the database.
	// Note we set the $this->result variable so we don't have to re-execute the query when we do the user update.
	public function Is_user() {
		global $wpdb, $WP_Statistics;

		// Check to see if we already have an entry in the database.
		$check_ip_db = $WP_Statistics->store_ip_to_db();
		if ( $WP_Statistics->ip_hash != false ) {
			$check_ip_db = $WP_Statistics->ip_hash;
		}

		//Check Exist
		$this->result = $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_useronline WHERE `ip` = '{$check_ip_db}'" );

		if ( $this->result ) {
			return true;
		}
	}

	// This function add/update/delete the online users in the database.
	public function Check_online() {
		global $WP_Statistics;

		// If we're a web crawler or referral from ourselves or an excluded address don't record the user as online, unless we've been told to anyway.
		if ( ! $this->exclusion_match || $WP_Statistics->get_option( 'all_online' ) ) {

			// If the current user exists in the database already,
			// Just update them, otherwise add them
			if ( $this->Is_user() ) {
				$this->Update_user();
			} else {
				$this->Add_user();
			}
		}

	}

	// This function adds a user to the database.
	public function Add_user() {
		global $wpdb, $WP_Statistics;

		//Check is User
		if ( ! $this->Is_user() ) {

			// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
			$this->get_page_detail();

			// Insert the user in to the database.
			$wpdb->insert(
				$wpdb->prefix . 'statistics_useronline',
				array(
					'ip'        => $WP_Statistics->ip_hash ? $WP_Statistics->ip_hash : $WP_Statistics->store_ip_to_db(),
					'timestamp' => $this->timestamp,
					'created'   => $this->timestamp,
					'date'      => $WP_Statistics->Current_Date(),
					'referred'  => $WP_Statistics->get_Referred(),
					'agent'     => $WP_Statistics->agent['browser'],
					'platform'  => $WP_Statistics->agent['platform'],
					'version'   => $WP_Statistics->agent['version'],
					'location'  => $this->location,
					'user_id'   => self::get_user_id(),
					'page_id'   => $this->current_page_id,
					'type'      => $this->current_page_type
				)
			);
		}
	}

	/**
	 * Get User ID
	 */
	public static function get_user_id() {

		//create Empty
		$user_id = 0;

		//if Rest Request
		if ( WP_Statistics_Rest::is_rest() ) {
			if ( WP_Statistics_Rest::params( 'user_id' ) != "" ) {
				$user_id = WP_Statistics_Rest::params( 'user_id' );
			}
		} else {
			if ( is_user_logged_in() ) {
				return get_current_user_id();
			}
		}

		return $user_id;
	}

	// This function updates a user in the database.
	public function Update_user() {
		global $wpdb, $WP_Statistics;

		// Make sure we found the user earlier when we called Is_user().
		if ( $this->result ) {

			// Get the pages or posts ID if it exists and we haven't set it in the visitors code.
			$this->get_page_detail();

			// Update the database with the new information.
			$wpdb->update(
				$wpdb->prefix . 'statistics_useronline',
				array(
					'timestamp' => $this->timestamp,
					'date'      => $WP_Statistics->Current_Date(),
					'referred'  => $WP_Statistics->get_Referred(),
					'user_id'   => self::get_user_id(),
					'page_id'   => $this->current_page_id,
					'type'      => $this->current_page_type
				),
				array( 'ip' => $WP_Statistics->ip_hash ? $WP_Statistics->ip_hash : $WP_Statistics->store_ip_to_db() )
			);
		}
	}

}