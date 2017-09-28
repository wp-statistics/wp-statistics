<?php
/*
	This is the primary set of functions used to calculate the statistics, they are available for other developers to call.
	
	NOTE:  Many of the functions return an MySQL result object, using this object like a variable (ie. echo $result) will output 
		   the number of rows returned, but you can also use it an a foreach loop to to get the details of the rows.
*/

// This function returns the current users online.
function wp_statistics_useronline() {

	global $wpdb;

	return $wpdb->query( "SELECT * FROM {$wpdb->prefix}statistics_useronline" );
}

// This function get the visit statistics for a given time frame.
function wp_statistics_visit( $time, $daily = null ) {

	// We need database and the global $WP_Statistics object access.
	global $wpdb, $WP_Statistics;

	// If we've been asked to do a daily count, it's a slightly different SQL query, so handle it separately.
	if ( $daily == true ) {

		// Fetch the results from the database.
		$result = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}'" );

		// If we have a result, return it, otherwise force a 0 to be returned instead of the logical FALSE that would otherwise be the case.
		if ( $result ) {
			return $result->visit;
		} else {
			return 0;
		}

	} else {

		// This function accepts several options for time parameter, each one has a unique SQL query string.
		// They're pretty self explanatory.

		switch ( $time ) {
			case 'today':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}'" );
				break;

			case 'yesterday':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}'" );
				break;

			case 'week':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -7 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'" );
				break;

			case 'month':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -30 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'" );
				break;

			case 'year':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'" );
				break;

			case 'total':
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit" );
				$result += $WP_Statistics->Get_Historical_Data( 'visits' );
				break;

			default:
				$result = $wpdb->get_var( "SELECT SUM(visit) FROM {$wpdb->prefix}statistics_visit WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'" );
				break;
		}
	}

	// If we have a result, return it, otherwise force a 0 to be returned instead of the logical FALSE that would otherwise be the case.
	if ( $result == null ) {
		$result = 0;
	}

	return $result;
}

// This function gets the visitor statistics for a given time frame.
function wp_statistics_visitor( $time, $daily = null, $countonly = false ) {

	// We need database and the global $WP_Statistics object access.
	global $wpdb, $WP_Statistics;

	$history      = 0;
	$select       = '*';
	$sqlstatement = '';

	// We often don't need the complete results but just the count of rows, if that's the case, let's have MySQL just count the results for us.
	if ( $countonly == true ) {
		$select = 'count(last_counter)';
	}

	// If we've been asked to do a daily count, it's a slightly different SQL query, so handle it seperatly.
	if ( $daily == true ) {

		// Fetch the results from the database.
		$result = $wpdb->query( "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}'" );

		return $result;

	} else {

		// This function accepts several options for time parameter, each one has a unique SQL query string.
		// They're pretty self explanatory.
		switch ( $time ) {
			case 'today':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}'";
				break;

			case 'yesterday':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}'";
				break;

			case 'week':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -7 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'";
				break;

			case 'month':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -30 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'";
				break;

			case 'year':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'";
				break;

			case 'total':
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor";
				$history      = $WP_Statistics->Get_Historical_Data( 'visitors' );
				break;

			default:
				$sqlstatement = "SELECT {$select} FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}'";
				break;
		}
	}

	// Execute the SQL call, if we're only counting we can use get_var(), otherwise we use query().
	if ( $countonly == true ) {
		$result = $wpdb->get_var( $sqlstatement );
		$result += $history;
	} else {
		$result = $wpdb->query( $sqlstatement );
	}

	return $result;
}

// This function returns the statistics for a given page.
function wp_statistics_pages( $time, $page_uri = '', $id = - 1, $rangestartdate = null, $rangeenddate = null ) {

	// We need database and the global $WP_Statistics object access.
	global $wpdb, $WP_Statistics;

	$history      = 0;
	$sqlstatement = '';

	// If no page URI has been passed in, get the current page URI.
	if ( $page_uri == '' ) {
		$page_uri = wp_statistics_get_uri();
	}

	$page_uri_sql = esc_sql( $page_uri );

	// If a page/post ID has been passed, use it to select the rows, otherwise use the URI.
	//  Note that a single page/post ID can have multiple URI's associated with it.
	if ( $id != - 1 ) {
		$page_sql    = '`id` = ' . absint( $id );
		$history_key = 'page';
		$history_id  = absint( $id );
	} else {
		$page_sql    = "`URI` = '{$page_uri_sql}'";
		$history_key = 'uri';
		$history_id  = $page_uri;
	}

	// This function accepts several options for time parameter, each one has a unique SQL query string.
	// They're pretty self explanatory.
	switch ( $time ) {
		case 'today':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$page_sql}";
			break;

		case 'yesterday':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}' AND {$page_sql}";
			break;

		case 'week':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -7 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$page_sql}";
			break;

		case 'month':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -30 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$page_sql}";
			break;

		case 'year':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$page_sql}";
			break;

		case 'total':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE {$page_sql}";
			$history      = $WP_Statistics->Get_Historical_Data( $history_key, $history_id );
			break;
		case 'range':
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN '" . $WP_Statistics->Current_Date( 'Y-m-d', '-0', strtotime( $rangestartdate ) ) . "' AND '" . $WP_Statistics->Current_Date( 'Y-m-d', '-0', strtotime( $rangeenddate ) ) . "' AND {$page_sql}";

			break;
		default:
			$sqlstatement = "SELECT SUM(count) FROM {$wpdb->prefix}statistics_pages WHERE `date` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}' AND {$page_sql}";
			break;
	}

	// Since this function only every returns a count, just use get_var().
	$result = $wpdb->get_var( $sqlstatement );
	$result += $history;

	// If we have an empty result, return 0 instead of a blank.
	if ( $result == '' ) {
		$result = 0;
	}

	return $result;
}

// This function converts a page URI to a page/post ID.  It does this by looking up in the pages database
// the URI and getting the associated ID.  This will only work if the page has been visited at least once.
function wp_statistics_uri_to_id( $uri ) {
	global $wpdb;

	// Create the SQL query to use.
	$sqlstatement = $wpdb->prepare( "SELECT id FROM {$wpdb->prefix}statistics_pages WHERE `URI` = %s AND id > 0 ORDER BY date DESC", $uri );

	// Execute the query.
	$result = $wpdb->get_var( $sqlstatement );

	// If we returned a false or some other 0 equivalent value, make sure $result is set to an integer 0.
	if ( $result == 0 ) {
		$result = 0;
	}

	return $result;
}

// We need a quick function to pass to usort to properly sort the most popular pages.
function wp_stats_compare_uri_hits( $a, $b ) {
	return $a[1] < $b[1];
}

// This function returns a multi-dimensional array, with the total number of pages and an array or URI's sorted in order with their URI, count, id and title.
function wp_statistics_get_top_pages( $rangestartdate = null, $rangeenddate = null ) {
	global $wpdb;

	// Get every unique URI from the pages database.
	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT uri FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN %s AND %s", $rangestartdate, $rangeenddate ), ARRAY_N );
	} else {
		$result = $wpdb->get_results( "SELECT DISTINCT uri FROM {$wpdb->prefix}statistics_pages", ARRAY_N );
	}

	$total = 0;
	$uris  = array();

	// Now get the total page visit count for each unique URI.
	foreach ( $result as $out ) {
		// Increment the total number of results.
		$total ++;

		// Retreive the post ID for the URI.
		$id = wp_statistics_uri_to_id( $out[0] );

		// Lookup the post title.
		$post = get_post( $id );

		if ( is_object( $post ) ) {
			$title = $post->post_title;
		} else {
			if ( $out[0] == '/' ) {
				$title = get_bloginfo();
			} else {
				$title = '';
			}
		}

		// Add the current post to the array.
		if ( $rangestartdate != null && $rangeenddate != null ) {
			$uris[] = array(
				$out[0],
				wp_statistics_pages( 'range', $out[0], - 1, $rangestartdate, $rangeenddate ),
				$id,
				$title
			);
		} else {
			$uris[] = array( $out[0], wp_statistics_pages( 'total', $out[0] ), $id, $title );
		}
	}

	// If we have more than one result, let's sort them using usort.
	if ( count( $uris ) > 1 ) {
		// Sort the URI's based on their hit count.
		usort( $uris, 'wp_stats_compare_uri_hits' );
	}

	return array( $total, $uris );
}

// This function gets the current page URI.
function wp_statistics_get_uri() {
	// Get the site's path from the URL.
	$site_uri     = parse_url( site_url(), PHP_URL_PATH );
	$site_uri_len = strlen( $site_uri );

	// Get the site's path from the URL.
	$home_uri     = parse_url( home_url(), PHP_URL_PATH );
	$home_uri_len = strlen( $home_uri );

	// Get the current page URI.
	$page_uri = $_SERVER["REQUEST_URI"];

	/*
	 * We need to check which URI is longer in case one contains the other.
	 *
	 * For example home_uri might be "/site/wp" and site_uri might be "/site".
	 *
	 * In that case we want to check to see if the page_uri starts with "/site/wp" before
	 * we check for "/site", but in the reverse case, we need to swap the order of the check.
	 */
	if ( $site_uri_len > $home_uri_len ) {
		if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
			$page_uri = substr( $page_uri, $site_uri_len );
		}

		if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
			$page_uri = substr( $page_uri, $home_uri_len );
		}
	} else {
		if ( substr( $page_uri, 0, $home_uri_len ) == $home_uri ) {
			$page_uri = substr( $page_uri, $home_uri_len );
		}

		if ( substr( $page_uri, 0, $site_uri_len ) == $site_uri ) {
			$page_uri = substr( $page_uri, $site_uri_len );
		}
	}

	// If we're at the root (aka the URI is blank), let's make sure to indicate it.
	if ( $page_uri == '' ) {
		$page_uri = '/';
	}

	return $page_uri;
}

// This function returns all unique user agents in the database.
function wp_statistics_ua_list( $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT agent FROM {$wpdb->prefix}statistics_visitor AND `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate ), ARRAY_N );
	} else {
		$result = $wpdb->get_results( "SELECT DISTINCT agent FROM {$wpdb->prefix}statistics_visitor", ARRAY_N );
	}

	$Browers = array();

	foreach ( $result as $out ) {
		$Browsers[] = $out[0];
	}

	return $Browsers;
}

// This function returns the count of a given user agent in the database.
function wp_statistics_useragent( $agent, $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(agent) FROM {$wpdb->prefix}statistics_visitor WHERE `agent` = %s AND `last_counter` BETWEEN %s AND %s", $agent, $rangestartdate, $rangeenddate ) );
	} else {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(agent) FROM {$wpdb->prefix}statistics_visitor WHERE `agent` = %s", $agent ) );
	}

	return $result;
}

// This function returns all unique platform types from the database.
function wp_statistics_platform_list( $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT platform FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate ), ARRAY_N );
	} else {
		$result = $wpdb->get_results( "SELECT DISTINCT platform FROM {$wpdb->prefix}statistics_visitor", ARRAY_N );
	}

	$Platforms = array();

	foreach ( $result as $out ) {
		$Platforms[] = $out[0];
	}

	return $Platforms;
}

// This function returns the count of a given platform in the database.
function wp_statistics_platform( $platform, $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(platform) FROM {$wpdb->prefix}statistics_visitor WHERE `platform` = %s AND `last_counter` BETWEEN %s AND %s", $platform, $rangestartdate, $rangeenddate ) );
	} else {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(platform) FROM {$wpdb->prefix}statistics_visitor WHERE `platform` = %s", $platform ) );
	}

	return $result;
}

// This function returns all unique versions for a given agent from the database.
function wp_statistics_agent_version_list( $agent, $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT version FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND `last_counter` BETWEEN %s AND %s", $agent, $rangestartdate, $rangeenddate ), ARRAY_N );
	} else {
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT version FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s", $agent ), ARRAY_N );
	}

	$Versions = array();

	foreach ( $result as $out ) {
		$Versions[] = $out[0];
	}

	return $Versions;
}

// This function returns the statistics for a given agent/version pair from the database.
function wp_statistics_agent_version( $agent, $version, $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(version) FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND version = %s AND `last_counter` BETWEEN %s AND %s", $agent, $version, $rangestartdate, $rangeenddate ) );
	} else {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(version) FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND version = %s", $agent, $version ) );
	}

	return $result;
}

// This function returns an array or array's which define what search engines we should look for.
//
// By default will only return ones that have not been disabled by the user, this can be overridden by the $all parameter.
//
// Each sub array is made up of the following items:
//		name 		 = The proper name of the search engine
//		translated   = The proper name translated to the local language
//		tag 		 = a short one word, all lower case, representation of the search engine
//		sqlpattern   = either a single SQL style search pattern OR an array or search patterns to match the hostname in a URL against
//		regexpattern = either a single regex style search pattern OR an array or search patterns to match the hostname in a URL against
//		querykey 	 = the URL key that contains the search string for the search engine
//		image		 = the name of the image file to associate with this search engine (just the filename, no path info)
//
function wp_statistics_searchengine_list( $all = false ) {
	GLOBAL $WP_Statistics;

	$default = $engines = array(
		'ask'        => array(
			'name'         => 'Ask.com',
			'translated'   => __( 'Ask.com', 'wp-statistics' ),
			'tag'          => 'ask',
			'sqlpattern'   => '%ask.com%',
			'regexpattern' => 'ask\.com',
			'querykey'     => 'q',
			'image'        => 'ask.png'
		),
		'baidu'      => array(
			'name'         => 'Baidu',
			'translated'   => __( 'Baidu', 'wp-statistics' ),
			'tag'          => 'baidu',
			'sqlpattern'   => '%baidu.com%',
			'regexpattern' => 'baidu\.com',
			'querykey'     => 'wd',
			'image'        => 'baidu.png'
		),
		'bing'       => array(
			'name'         => 'Bing',
			'translated'   => __( 'Bing', 'wp-statistics' ),
			'tag'          => 'bing',
			'sqlpattern'   => '%bing.com%',
			'regexpattern' => 'bing\.com',
			'querykey'     => 'q',
			'image'        => 'bing.png'
		),
		'clearch'    => array(
			'name'         => 'clearch.org',
			'translated'   => __( 'clearch.org', 'wp-statistics' ),
			'tag'          => 'clearch',
			'sqlpattern'   => '%clearch.org%',
			'regexpattern' => 'clearch\.org',
			'querykey'     => 'q',
			'image'        => 'clearch.png'
		),
		'duckduckgo' => array(
			'name'         => 'DuckDuckGo',
			'translated'   => __( 'DuckDuckGo', 'wp-statistics' ),
			'tag'          => 'duckduckgo',
			'sqlpattern'   => array( '%duckduckgo.com%', '%ddg.gg%' ),
			'regexpattern' => array( 'duckduckgo\.com', 'ddg\.gg' ),
			'querykey'     => 'q',
			'image'        => 'duckduckgo.png'
		),
		'google'     => array(
			'name'         => 'Google',
			'translated'   => __( 'Google', 'wp-statistics' ),
			'tag'          => 'google',
			'sqlpattern'   => '%google.%',
			'regexpattern' => 'google\.',
			'querykey'     => 'q',
			'image'        => 'google.png'
		),
		'yahoo'      => array(
			'name'         => 'Yahoo!',
			'translated'   => __( 'Yahoo!', 'wp-statistics' ),
			'tag'          => 'yahoo',
			'sqlpattern'   => '%yahoo.com%',
			'regexpattern' => 'yahoo\.com',
			'querykey'     => 'p',
			'image'        => 'yahoo.png'
		),
		'yandex'     => array(
			'name'         => 'Yandex',
			'translated'   => __( 'Yandex', 'wp-statistics' ),
			'tag'          => 'yandex',
			'sqlpattern'   => '%yandex.ru%',
			'regexpattern' => 'yandex\.ru',
			'querykey'     => 'text',
			'image'        => 'yandex.png'
		)
	);

	if ( $all == false ) {
		foreach ( $engines as $key => $engine ) {
			if ( $WP_Statistics->get_option( 'disable_se_' . $engine['tag'] ) ) {
				unset( $engines[ $key ] );
			}
		}

		// If we've disabled all the search engines, reset the list back to default.
		if ( count( $engines ) == 0 ) {
			$engines = $default;
		}
	}

	return $engines;
}

// This function will return the SQL WHERE clause for getting the search words for a given search engine.
function wp_statistics_searchword_query( $search_engine = 'all' ) {
	GLOBAL $WP_Statistics;

	// Get a complete list of search engines
	$searchengine_list = wp_statistics_searchengine_list();
	$search_query      = '';

	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		// Are we getting results for all search engines or a specific one?
		if ( strtolower( $search_engine ) == 'all' ) {
			// For all of them?  Ok, look through the search engine list and create a SQL query string to get them all from the database.
			foreach ( $searchengine_list as $key => $se ) {
				$search_query .= "( `engine` = '{$key}' AND `words` <> '' ) OR ";
			}

			// Trim off the last ' OR ' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
		} else {
			$search_query .= "`engine` = '{$search_engine}' AND `words` <> ''";
		}
	} else {
		// Are we getting results for all search engines or a specific one?
		if ( strtolower( $search_engine ) == 'all' ) {
			// For all of them?  Ok, look through the search engine list and create a SQL query string to get them all from the database.
			// NOTE:  This SQL query can be *VERY* long.
			foreach ( $searchengine_list as $se ) {
				// The SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
				if ( is_array( $se['sqlpattern'] ) ) {
					foreach ( $se['sqlpattern'] as $subse ) {
						$search_query .= "(`referred` LIKE '{$subse}{$se['querykey']}=%' AND `referred` NOT LIKE '{$subse}{$se['querykey']}=&%' AND `referred` NOT LIKE '{$subse}{$se['querykey']}=') OR ";
					}
				} else {
					$search_query .= "(`referred` LIKE '{$se['sqlpattern']}{$se['querykey']}=%' AND `referred` NOT LIKE '{$se['sqlpattern']}{$se['querykey']}=&%' AND `referred` NOT LIKE '{$se['sqlpattern']}{$se['querykey']}=')  OR ";
				}
			}

			// Trim off the last ' OR ' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
		} else {
			// For just one?  Ok, the SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
			if ( is_array( $searchengine_list[ $search_engine ]['sqlpattern'] ) ) {
				foreach ( $searchengine_list[ $search_engine ]['sqlpattern'] as $se ) {
					$search_query .= "(`referred` LIKE '{$se}{$searchengine_list[$search_engine]['querykey']}=%' AND `referred` NOT LIKE '{$se}{$searchengine_list[$search_engine]['querykey']}=&%' AND `referred` NOT LIKE '{$se}{$searchengine_list[$search_engine]['querykey']}=') OR ";
				}

				// Trim off the last ' OR ' for the loop above.
				$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
			} else {
				$search_query .= "(`referred` LIKE '{$searchengine_list[$search_engine]['sqlpattern']}{$searchengine_list[$search_engine]['querykey']}=%' AND `referred` NOT LIKE '{$searchengine_list[$search_engine]['sqlpattern']}{$searchengine_list[$search_engine]['querykey']}=&%' AND `referred` NOT LIKE '{$searchengine_list[$search_engine]['sqlpattern']}{$searchengine_list[$search_engine]['querykey']}=')";
			}
		}
	}

	return $search_query;
}

// This function will return the SQL WHERE clause for getting the search engine.
function wp_statistics_searchengine_query( $search_engine = 'all' ) {
	GLOBAL $WP_Statistics;

	// Get a complete list of search engines
	$searchengine_list = wp_statistics_searchengine_list();
	$search_query      = '';

	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		// Are we getting results for all search engines or a specific one?
		if ( strtolower( $search_engine ) == 'all' ) {
			// For all of them?  Ok, look through the search engine list and create a SQL query string to get them all from the database.
			foreach ( $searchengine_list as $key => $se ) {
				$key          = esc_sql( $key );
				$search_query .= "`engine` = '{$key}' OR ";
			}

			// Trim off the last ' OR ' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
		} else {
			$search_engine = esc_sql( $search_engine );
			$search_query  .= "`engine` = '{$search_engine}'";
		}
	} else {
		// Are we getting results for all search engines or a specific one?
		if ( strtolower( $search_engine ) == 'all' ) {
			// For all of them?  Ok, look through the search engine list and create a SQL query string to get them all from the database.
			// NOTE:  This SQL query can be long.
			foreach ( $searchengine_list as $se ) {
				// The SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
				if ( is_array( $se['sqlpattern'] ) ) {
					foreach ( $se['sqlpattern'] as $subse ) {
						$subse        = esc_sql( $subse );
						$search_query .= "`referred` LIKE '{$subse}' OR ";
					}
				} else {
					$se['sqlpattern'] = esc_sql( $se['sqlpattern'] );
					$search_query     .= "`referred` LIKE '{$se['sqlpattern']}' OR ";
				}
			}

			// Trim off the last ' OR ' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
		} else {
			// For just one?  Ok, the SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
			if ( is_array( $searchengine_list[ $search_engine ]['sqlpattern'] ) ) {
				foreach ( $searchengine_list[ $search_engine ]['sqlpattern'] as $se ) {
					$se           = esc_sql( $se );
					$search_query .= "`referred` LIKE '{$se}' OR ";
				}

				// Trim off the last ' OR ' for the loop above.
				$search_query = substr( $search_query, 0, strlen( $search_query ) - 4 );
			} else {
				$searchengine_list[ $search_engine ]['sqlpattern'] = esc_sql( $searchengine_list[ $search_engine ]['sqlpattern'] );
				$search_query                                      .= "`referred` LIKE '{$searchengine_list[$search_engine]['sqlpattern']}'";
			}
		}
	}

	return $search_query;
}

// This function will return a regular expression clause for matching one or more search engines.
function wp_statistics_searchengine_regex( $search_engine = 'all' ) {

	// Get a complete list of search engines
	$searchengine_list = wp_statistics_searchengine_list();
	$search_query      = '';

	// Are we getting results for all search engines or a specific one?
	if ( strtolower( $search_engine ) == 'all' ) {
		foreach ( $searchengine_list as $se ) {
			// The SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
			if ( is_array( $se['regexpattern'] ) ) {
				foreach ( $se['regexpattern'] as $subse ) {
					$search_query .= "{$subse}|";
				}
			} else {
				$search_query .= "{$se['regexpattern']}|";
			}
		}

		// Trim off the last '|' for the loop above.
		$search_query = substr( $search_query, 0, strlen( $search_query ) - 1 );
	} else {
		// For just one?  Ok, the SQL pattern for a search engine may be an array if it has to handle multiple domains (like google.com and google.ca) or other factors.
		if ( is_array( $searchengine_list[ $search_engine ]['regexpattern'] ) ) {
			foreach ( $searchengine_list[ $search_engine ]['regexpattern'] as $se ) {
				$search_query .= "{$se}|";
			}

			// Trim off the last '|' for the loop above.
			$search_query = substr( $search_query, 0, strlen( $search_query ) - 1 );
		} else {
			$search_query .= $searchengine_list[ $search_engine ]['regexpattern'];
		}
	}

	// Add the brackets and return
	return "({$search_query})";
}

// This function will return the statistics for a given search engine.
function wp_statistics_searchengine( $search_engine = 'all', $time = 'total' ) {

	global $wpdb, $WP_Statistics;

	// Determine if we're using the old or new method of storing search engine info and build the appropriate table name.
	$tablename = $wpdb->prefix . 'statistics_';

	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		$tablename .= 'search';
	} else {
		$tablename .= 'visitor';
	}

	// Get a complete list of search engines
	$search_query = wp_statistics_searchengine_query( $search_engine );

	// This function accepts several options for time parameter, each one has a unique SQL query string.
	// They're pretty self explanatory.
	switch ( $time ) {
		case 'today':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$search_query}" );
			break;

		case 'yesterday':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}' AND {$search_query}" );

			break;

		case 'week':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -7 )}' AND {$search_query}" );

			break;

		case 'month':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -30 )}' AND {$search_query}" );

			break;

		case 'year':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND {$search_query}" );

			break;

		case 'total':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE {$search_query}" );

			break;

		default:
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time)}' AND {$search_query}" );

			break;
	}

	return $result;
}

// This function will return the statistics for a given search engine for a given time frame.
function wp_statistics_searchword( $search_engine = 'all', $time = 'total' ) {

	global $wpdb, $WP_Statistics;

	// Determine if we're using the old or new method of storing search engine info and build the appropriate table name.
	$tablename = $wpdb->prefix . 'statistics_';

	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		$tablename .= 'search';
	} else {
		$tablename .= 'visitor';
	}

	// Get a complete list of search engines
	$search_query = wp_statistics_searchword_query( $search_engine );

	// This function accepts several options for time parameter, each one has a unique SQL query string.
	// They're pretty self explanatory.
	switch ( $time ) {
		case 'today':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d' )}' AND {$search_query}" );
			break;

		case 'yesterday':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}' AND {$search_query}" );

			break;

		case 'week':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -7 )}' AND {$search_query}" );

			break;

		case 'month':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -30 )}' AND {$search_query}" );

			break;

		case 'year':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', -365 )}' AND {$search_query}" );

			break;

		case 'total':
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE {$search_query}" );

			break;

		default:
			$result = $wpdb->query( "SELECT * FROM `{$tablename}` WHERE `last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}' AND {$search_query}" );

			break;
	}

	return $result;
}

// This function will return the total number of posts in WordPress.
function wp_statistics_countposts() {

	$count_posts = wp_count_posts( 'post' );

	$ret = 0;

	if ( is_object( $count_posts ) ) {
		$ret = $count_posts->publish;
	}

	return $ret;
}

// This function will return the total number of pages in WordPress.
function wp_statistics_countpages() {

	$count_pages = wp_count_posts( 'page' );

	$ret = 0;

	if ( is_object( $count_pages ) ) {
		$ret = $count_pages->publish;
	}

	return $ret;
}

// This function will return the total number of comments in WordPress.
function wp_statistics_countcomment() {

	global $wpdb;

	$countcomms = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'" );

	return $countcomms;
}

// This function will return the total number of spam comments *IF* akismet is installed.
function wp_statistics_countspam() {

	return number_format_i18n( get_option( 'akismet_spam_count' ) );
}

// This function will return the total number of users in WordPress.
function wp_statistics_countusers() {

	$result = count_users();

	return $result['total_users'];
}

// This function will return the last date a post was published on your site.
function wp_statistics_lastpostdate() {

	global $wpdb, $WP_Statistics;

	$db_date = $wpdb->get_var( "SELECT post_date FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish' ORDER BY post_date DESC LIMIT 1" );

	$date_format = get_option( 'date_format' );

	return $WP_Statistics->Current_Date_i18n( $date_format, $db_date, false );
}

// This function will return the average number of posts per day that are published on your site.
// Alternatively if $days is set to true it returns the average number of days between posts on your site.
function wp_statistics_average_post( $days = false ) {

	global $wpdb;

	$get_first_post = $wpdb->get_var( "SELECT post_date FROM {$wpdb->posts} WHERE post_status = 'publish' ORDER BY post_date LIMIT 1" );
	$get_total_post = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'" );

	$days_spend = intval( ( time() - strtotime( $get_first_post ) ) / 86400 ); // 86400 = 60 * 60 * 24 = number of seconds in a day

	if ( $days == true ) {
		if ( $get_total_post == 0 ) {
			$get_total_post = 1;
		} // Avoid divide by zero errors.

		return round( $days_spend / $get_total_post, 0 );
	} else {
		if ( $days_spend == 0 ) {
			$days_spend = 1;
		} // Avoid divide by zero errors.

		return round( $get_total_post / $days_spend, 2 );
	}
}

// This function will return the average number of comments per day that are published on your site.
// Alternatively if $days is set to true it returns the average number of days between comments on your site.
function wp_statistics_average_comment( $days = false ) {

	global $wpdb;

	$get_first_comment = $wpdb->get_var( "SELECT comment_date FROM {$wpdb->comments} ORDER BY comment_date LIMIT 1" );
	$get_total_comment = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->comments} WHERE comment_approved = '1'" );

	$days_spend = intval( ( time() - strtotime( $get_first_comment ) ) / 86400 ); // 86400 = 60 * 60 * 24 = number of seconds in a day

	if ( $days == true ) {
		if ( $get_total_comment == 0 ) {
			$get_total_comment = 1;
		} // Avoid divide by zero errors.

		return round( $days_spend / $get_total_comment, 0 );
	} else {
		if ( $days_spend == 0 ) {
			$days_spend = 1;
		} // Avoid divide by zero errors.

		return round( $get_total_comment / $days_spend, 2 );
	}
}

// This function will return the average number of users per day that are registered on your site.
// Alternatively if $days is set to true it returns the average number of days between user registrations on your site.
function wp_statistics_average_registeruser( $days = false ) {

	global $wpdb;

	$get_first_user = $wpdb->get_var( "SELECT user_registered FROM {$wpdb->users} ORDER BY user_registered LIMIT 1" );
	$get_total_user = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->users}" );

	$days_spend = intval( ( time() - strtotime( $get_first_user ) ) / 86400 ); // 86400 = 60 * 60 * 24 = number of seconds in a day

	if ( $days == true ) {
		if ( $get_total_user == 0 ) {
			$get_total_user = 1;
		} // Avoid divide by zero errors.

		return round( $days_spend / $get_total_user, 0 );
	} else {
		if ( $days_spend == 0 ) {
			$days_spend = 1;
		} // Avoid divide by zero errors.

		return round( $get_total_user / $days_spend, 2 );
	}
}

// This function handle's the dashicons in the overview page.
function wp_statistics_icons( $dashicons, $icon_name = null ) {

	global $wp_version;

	if ( null == $icon_name ) {
		$icon_name = $dashicons;
	}

	// Since versions of WordPress before 3.8 didn't have dashicons, don't use them in those versions.
	if ( version_compare( $wp_version, '3.8-RC', '>=' ) || version_compare( $wp_version, '3.8', '>=' ) ) {
		return '<span class="dashicons ' . $dashicons . '"></span>';
	} else {
		return '<img src="' . plugins_url( 'wp-statistics/assets/images/' ) . $icon_name . '.png"/>';
	}
}

// This function checks to see if all the PHP modules we need for GeoIP exists.
function wp_statistics_geoip_supported() {
	// Check to see if we can support the GeoIP code, requirements are:
	$enabled = true;

	// PHP 5.3
	if ( ! version_compare( phpversion(), WP_STATISTICS_REQUIRED_GEOIP_PHP_VERSION, '>' ) ) {
		$enabled = false;
	}

	// PHP's cURL extension installed
	if ( ! function_exists( 'curl_init' ) ) {
		$enabled = false;
	}

	// PHP NOT running in safe mode
	if ( ini_get( 'safe_mode' ) ) {
		// Double check php version, 5.4 and above don't support safe mode but the ini value may still be set after an upgrade.
		if ( ! version_compare( phpversion(), '5.4', '<' ) ) {
			$enabled = false;
		}
	}

	return $enabled;
}

// This function creates the date range selector 'widget' used in the various statistics pages.
function wp_statistics_date_range_selector( $page, $current, $range = array(), $desc = array(), $extrafields = '', $pre_extra = '', $post_extra = '' ) {
	GLOBAL $WP_Statistics;

	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_style( 'jquery-ui-smoothness-css', $WP_Statistics->plugin_url . 'assets/css/jquery-ui-smoothness.min.css' );
	wp_enqueue_style( 'jquery-ui-smoothness-css' );

	if ( count( $range ) == 0 ) {
		$range = array( 10, 20, 30, 60, 90, 180, 270, 365 );
		$desc  = array(
			__( '10 Days', 'wp-statistics' ),
			__( '20 Days', 'wp-statistics' ),
			__( '30 Days', 'wp-statistics' ),
			__( '2 Months', 'wp-statistics' ),
			__( '3 Months', 'wp-statistics' ),
			__( '6 Months', 'wp-statistics' ),
			__( '9 Months', 'wp-statistics' ),
			__( '1 Year', 'wp-statistics' )
		);
	}

	if ( count( $desc ) == 0 ) {
		$desc = $range;
	}

	$rcount = count( $range );

	$bold = true;

	// Check to see if there's a range in the URL, if so set it, otherwise use the default.
	if ( array_key_exists( 'rangestart', $_GET ) ) {
		$rangestart = $_GET['rangestart'];
	} else {
		$rangestart = $WP_Statistics->Current_Date( 'm/d/Y', '-' . $current );
	}
	if ( array_key_exists( 'rangeend', $_GET ) ) {
		$rangeend = $_GET['rangeend'];
	} else {
		$rangeend = $WP_Statistics->Current_Date( 'm/d/Y' );
	}

	// Convert the text dates to unix timestamps and do some basic sanity checking.
	$rangestart_utime = $WP_Statistics->strtotimetz( $rangestart );
	if ( false === $rangestart_utime ) {
		$rangestart_utime = time();
	}
	$rangeend_utime = $WP_Statistics->strtotimetz( $rangeend );
	if ( false === $rangeend_utime || $rangeend_utime < $rangestart_utime ) {
		$rangeend_utime = time();
	}

	// Now get the number of days in the range.
	$daysToDisplay = (int) ( ( $rangeend_utime - $rangestart_utime ) / 24 / 60 / 60 );
	$today         = $WP_Statistics->Current_Date( 'm/d/Y' );

	// Re-create the range start/end strings from our utime's to make sure we get ride of any cruft and have them in the format we want.
	$rangestart = $WP_Statistics->Local_Date( 'm/d/Y', $rangestart_utime );
	$rangeend   = $WP_Statistics->Local_Date( 'm/d/Y', $rangeend_utime );

	// If the rangeend isn't today OR it is but not one of the standard range values, then it's a custom selected value and we need to flag it as such.
	if ( $rangeend != $today || ( $rangeend == $today && ! in_array( $current, $range ) ) ) {
		$current = - 1;
	} else {
		// If on the other hand we are a standard range, let's reset the custom range selector to match it.
		$rangestart = $WP_Statistics->Current_Date( 'm/d/Y', '-' . $current );
		$rangeend   = $WP_Statistics->Current_Date( 'm/d/Y' );
	}

	echo '<form method="get"><ul class="subsubsub wp-statistics-sub-fullwidth">' . "\r\n";

	// Output any extra HTML we've been passed after the form element but before the date selector.
	echo $pre_extra;

	for ( $i = 0; $i < $rcount; $i ++ ) {
		echo '		<li class="all"><a ';

		if ( $current == $range[ $i ] ) {
			echo 'class="current" ';
			$bold = false;
		}

		// Don't bother adding he date range to the standard links as they're not needed any may confuse the custom range selector.
		echo 'href="?page=' . $page . '&hitdays=' . $range[ $i ] . esc_url( $extrafields ) . '">' . $desc[ $i ] . '</a></li>';

		if ( $i < $rcount - 1 ) {
			echo ' | ';
		}

		echo "\r\n";
	}

	echo ' | ';

	echo '<input type="hidden" name="hitdays" value="-1"><input type="hidden" name="page" value="' . $page . '">';

	parse_str( $extrafields, $parse );

	foreach ( $parse as $key => $value ) {
		echo '<input type="hidden" name="' . $key . '" value="' . esc_url( $value ) . '">';
	}

	if ( $bold ) {
		echo ' <b>' . __( 'Time Frame', 'wp-statistics' ) . ':</b> ';
	} else {
		echo ' ' . __( 'Time Frame', 'wp-statistics' ) . ': ';
	}

	echo '<input type="text" size="10" name="rangestart" id="datestartpicker" value="' . $rangestart . '" placeholder="' . __( 'MM/DD/YYYY', 'wp-statistics' ) . '"> ' . __( 'to', 'wp-statistics' ) . ' <input type="text" size="10" name="rangeend" id="dateendpicker" value="' . $rangeend . '" placeholder="' . __( 'MM/DD/YYYY', 'wp-statistics' ) . '"> <input type="submit" value="' . __( 'Go', 'wp-statistics' ) . '" class="button-primary">' . "\r\n";

	// Output any extra HTML we've been passed after the date selector but before the submit button.
	echo $post_extra;

	echo '</form>' . "\r\n";

	echo '<script>jQuery(function() { jQuery( "#datestartpicker" ).datepicker(); jQuery( "#dateendpicker" ).datepicker(); });</script>' . "\r\n";
}

// This function is used to calculate the number of days and thier respective unix timestamps.
function wp_statistics_date_range_calculator( $days, $start, $end ) {
	GLOBAL $WP_Statistics;

	$daysToDisplay = $days;
	$rangestart    = $start;
	$rangeend      = $end;

	if ( $daysToDisplay == - 1 ) {
		$rangestart_utime = $WP_Statistics->strtotimetz( $rangestart );
		$rangeend_utime   = $WP_Statistics->strtotimetz( $rangeend );
		$daysToDisplay    = (int) ( ( $rangeend_utime - $rangestart_utime ) / 24 / 60 / 60 );

		if ( $rangestart_utime == false || $rangeend_utime == false ) {
			$daysToDisplay    = 20;
			$rangeend_utime   = $WP_Statistics->timetz();
			$rangestart_utime = $rangeend_utime - ( $daysToDisplay * 24 * 60 * 60 );
		}
	} else {
		$rangeend_utime   = $WP_Statistics->timetz();
		$rangestart_utime = $rangeend_utime - ( $daysToDisplay * 24 * 60 * 60 );
	}

	return array( $daysToDisplay, $rangestart_utime, $rangeend_utime );
}

// This function will empty a table based on the table name.
function wp_statitiscs_empty_table( $table_name = false ) {
	global $wpdb;

	if ( $table_name ) {
		$result = $wpdb->query( 'DELETE FROM ' . $table_name );

		if ( $result ) {
			return sprintf( __( '%s table data deleted successfully.', 'wp-statistics' ), '<code>' . $table_name . '</code>' );
		}
	}

	return sprintf( __( 'Error, %s not emptied!', 'wp-statistics' ), $table_name );
}

// This function creates a small JavaScript snipit that will load the contents of a overview or dashboard widget.
function wp_statistics_generate_widget_load_javascript( $widget, $container_id = null ) {
	if ( null == $container_id ) {
		$container_id = str_replace( '.', '_', $widget . '_postbox' );
	}
	?>
    <script type="text/javascript">
        jQuery(document).ready(function () {
            wp_statistics_get_widget_contents('<?php echo $widget; ?>', '<?php echo $container_id; ?>');
        });
    </script>
	<?php
}

/**
 * Generate RGBA colors
 *
 * @param $num
 * @param string $opacity
 *
 * @return string
 */
function wp_statistics_generate_rgba_color( $num, $opacity = '1' ) {
	$hash = md5( 'color' . $num );

	return sprintf( "'rgba(%s, %s, %s, %s)'", hexdec( substr( $hash, 0, 2 ) ), hexdec( substr( $hash, 2, 2 ) ), hexdec( substr( $hash, 4, 2 ) ), $opacity );
}