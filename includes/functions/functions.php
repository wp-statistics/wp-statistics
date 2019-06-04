<?php
/*
	This is the primary set of functions used to calculate the statistics, they are available for other developers to call.
	
	NOTE:  Many of the functions return an MySQL result object, using this object like a variable (ie. echo $result) will output 
		   the number of rows returned, but you can also use it an a foreach loop to to get the details of the rows.
*/

/**
 * Get Current Users online
 *
 * @param array $options
 * @return mixed
 */
function wp_statistics_useronline( $options = array() ) {
	global $wpdb, $WP_Statistics;

	//Check Parameter
	$defaults = array(
		/**
		 * Type Of Page in Wordpress
		 * @See WP_Statistics_Frontend\get_page_type
		 *
		 * -- Acceptable values --
		 *
		 * post     -> WordPress Post single page From All of public post Type
		 * page     -> Wordpress page single page
		 * product  -> WooCommerce product single page
		 * home     -> Home Page website
		 * category -> Wordpress Category Page
		 * post_tag -> Wordpress Post Tags Page
		 * tax      -> Wordpress Term Page for all Taxonomies
		 * author   -> Wordpress Users page
		 * 404      -> 404 Not Found Page
		 * archive  -> Wordpress Archive Page
		 * all      -> All Site Page
		 *
		 */
		'type'         => 'all',
		/**
		 * Wordpress Query object ID
		 * @example array('type' => 'product', 'ID' => 5)
		 */
		'ID'           => 0,
		/**
		 * Get number of logged users or all users
		 *
		 * -- Acceptable values --
		 * false  -> Get Number of all users
		 * true   -> Get Number of all logged users in wordpress
		 */
		'logged_users' => false,
		/**
		 * Get number User From Custom Country
		 *
		 * -- Acceptable values --
		 * ISO Country Code -> For Get List @See \wp-statistics\includes\functions\country-code.php
		 *
		 */
		'location'     => 'all',
		/**
		 * Search Filter by User agent name
		 * e.g : Firefox , Chrome , Safari , Unknown ..
		 * @see wp_statistics_get_browser_list()
		 *
		 */
		'agent'        => 'all',
		/**
		 * Search filter by User Platform name
		 * e.g : Windows, iPad, Macintosh, Unknown, ..
		 *
		 */
		'platform'     => 'all'
	);

	// Parse incoming $args into an array and merge it with $defaults
	$arg = wp_parse_args( $options, $defaults );

	//Basic SQL
	$sql = "SELECT COUNT(*) FROM " . wp_statistics_db_table( 'useronline' );

	//Check Where Condition
	$where = false;

	//Check Type of Page
	if ( $arg['type'] != "all" ) {
		$where[] = "`type`='" . $arg['type'] . "' AND `page_id` = " . $arg['ID'];
	}

	//Check Custom user
	if ( $arg['logged_users'] === true ) {
		$where[] = "`user_id` > 0";
	}

	//Check Location
	if ( $arg['location'] != "all" ) {
		$ISOCountryCode = $WP_Statistics->get_country_codes();
		if ( array_key_exists( $arg['location'], $ISOCountryCode ) ) {
			$where[] = "`location` = '" . $arg['location'] . "'";
		}
	}

	//Check User Agent
	if ( $arg['agent'] != "all" ) {
		$where[] = "`agent` = '" . $arg['agent'] . "'";
	}

	//Check User Platform
	if ( $arg['platform'] != "all" ) {
		$where[] = "`platform` = '" . $arg['platform'] . "'";
	}

	//Push Conditions to SQL
	if ( ! empty( $where ) ) {
		$sql .= ' WHERE ' . implode( ' AND ', $where );
	}

	//Return Number od user Online
	return $wpdb->get_var( $sql );
}

/**
 * Create Condition Where Time in MySql
 *
 * @param string $field : date column name in database table
 * @param string $time : Time return
 * @param array $range : an array contain two Date e.g : array('start' => 'xx-xx-xx', 'end' => 'xx-xx-xx', 'is_day' => true, 'current_date' => true)
 *
 * ---- Time Range -----
 * today
 * yesterday
 * week
 * month
 * year
 * total
 * “-x” (i.e., “-10” for the past 10 days)
 * ----------------------
 *
 * @return string|bool
 */
function wp_statistics_mysql_time_conditions( $field = 'date', $time = 'total', $range = array() ) {
	global $WP_Statistics;

	//Get Current Date From WP
	$current_date = $WP_Statistics->Current_Date( 'Y-m-d' );

	//Create Field Sql
	$field_sql = function ( $time ) use ( $current_date, $field, $WP_Statistics, $range ) {
		$is_current = array_key_exists( 'current_date', $range );
		return "`$field` " . ( $is_current === true ? '=' : 'BETWEEN' ) . " '{$WP_Statistics->Current_Date( 'Y-m-d', (int) $time )}'" . ( $is_current === false ? " AND '{$current_date}'" : "" );
	};

	//Check Time
	switch ( $time ) {
		case 'today':
			$where = "`$field` = '{$current_date}'";
			break;
		case 'yesterday':
			$where = "`$field` = '{$WP_Statistics->Current_Date( 'Y-m-d', -1 )}'";
			break;
		case 'week':
			$where = $field_sql( - 7 );
			break;
		case 'month':
			$where = $field_sql( - 30 );
			break;
		case 'year':
			$where = $field_sql( - 365 );
			break;
		case 'total':
			$where = "";
			break;
		default:
			if ( array_key_exists( 'is_day', $range ) ) {
				//Check a day
				$where = "`$field` = '{$WP_Statistics->Current_Date( 'Y-m-d',  $time )}'";
			} elseif ( array_key_exists( 'start', $range ) and array_key_exists( 'end', $range ) ) {
				//Check Between Two Time
				$where = "`$field` BETWEEN '{$WP_Statistics->Current_Date( 'Y-m-d', '-0', strtotime( $range['start'] ) )}' AND '{$WP_Statistics->Current_Date( 'Y-m-d', '-0', strtotime( $range['end'] ) )}'";
			} else {
				//Check From a Date To Now
				$where = $field_sql( $time );
			}
	}

	return $where;
}

/**
 * This function get the visit statistics for a given time frame
 *
 * @param $time
 * @param null $daily
 * @return int
 */
function wp_statistics_visit( $time, $daily = null ) {
	global $wpdb, $WP_Statistics;

	//Date Column Name in visits table
	$table_name  = wp_statistics_db_table( 'visit' );
	$date_column = 'last_counter';

	//Prepare Selector Sql
	$selector = 'SUM(visit)';
	if ( $daily == true ) {
		$selector = '*';
	}

	//Generate Base Sql
	$sql = "SELECT {$selector} FROM {$table_name}";

	//Create Sum Visits variable
	$sum = 0;

	//Check if daily Report
	if ( $daily == true ) {

		$result = $wpdb->get_row( $sql . " WHERE `$date_column` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}'" );
		if ( null !== $result ) {
			$sum = $result->visit;
		}

	} else {

		//Generate MySql Time Conditions
		$mysql_time_sql = wp_statistics_mysql_time_conditions( $date_column, $time );
		if ( ! empty( $mysql_time_sql ) ) {
			$sql = $sql . ' WHERE ' . $mysql_time_sql;
		}

		//Request To database
		$result = $wpdb->get_var( $sql );

		//Custom Action
		if ( $time == "total" ) {
			$result += $WP_Statistics->Get_Historical_Data( 'visits' );
		}

		$sum = $result;
	}

	return ! is_numeric( $sum ) ? 0 : $sum;
}

/**
 * This function gets the visitor statistics for a given time frame.
 *
 * @param $time
 * @param null $daily
 * @param bool $count_only
 * @param array $options
 * @return int|null|string
 */
function wp_statistics_visitor( $time, $daily = null, $count_only = false, $options = array() ) {
	global $wpdb, $WP_Statistics;

	//Check Parameter
	$defaults = array(
		/**
		 * Type Of Page in Wordpress
		 * @See WP_Statistics_Frontend\get_page_type
		 *
		 * -- Acceptable values --
		 *
		 * post     -> WordPress Post single page From All of public post Type
		 * page     -> Wordpress page single page
		 * product  -> WooCommerce product single page
		 * home     -> Home Page website
		 * category -> Wordpress Category Page
		 * post_tag -> Wordpress Post Tags Page
		 * tax      -> Wordpress Term Page for all Taxonomies
		 * author   -> Wordpress Users page
		 * 404      -> 404 Not Found Page
		 * archive  -> Wordpress Archive Page
		 * all      -> All Site Page
		 *
		 */
		'type'     => 'all',
		/**
		 * Wordpress Query object ID
		 * @example array('type' => 'product', 'ID' => 5)
		 */
		'ID'       => 0,
		/**
		 * Get number User From Custom Country
		 *
		 * -- Acceptable values --
		 * ISO Country Code -> For Get List @See \wp-statistics\includes\functions\country-code.php
		 *
		 */
		'location' => 'all',
		/**
		 * Search Filter by User agent name
		 * e.g : Firefox , Chrome , Safari , Unknown ..
		 * @see wp_statistics_get_browser_list()
		 *
		 */
		'agent'    => 'all',
		/**
		 * Search filter by User Platform name
		 * e.g : Windows, iPad, Macintosh, Unknown, ..
		 *
		 */
		'platform' => 'all'
	);

	// Parse incoming $args into an array and merge it with $defaults
	$arg = wp_parse_args( $options, $defaults );

	//Create History Visitors variable
	$history = 0;

	//Prepare Selector Sql
	$date_column = 'last_counter';
	$selector    = '*';
	if ( $count_only == true ) {
		$selector = 'count(last_counter)';
	}

	//Generate Base Sql
	if ( $arg['type'] != "all" and $WP_Statistics->get_option( 'visitors_log' ) == true ) {
		$sql = "SELECT {$selector} FROM `" . wp_statistics_db_table( 'visitor' ) . "` INNER JOIN `" . wp_statistics_db_table( "visitor_relationships" ) . "` ON `" . wp_statistics_db_table( "visitor_relationships" ) . "`.`visitor_id` = `" . wp_statistics_db_table( 'visitor' ) . "`.`ID`  INNER JOIN `" . wp_statistics_db_table( 'pages' ) . "` ON `" . wp_statistics_db_table( 'pages' ) . "`.`page_id` = `" . wp_statistics_db_table( "visitor_relationships" ) . "` . `page_id`";
	} else {
		$sql = "SELECT {$selector} FROM `" . wp_statistics_db_table( 'visitor' ) . "`";
	}

	//Check Where Condition
	$where = false;

	//Check Type of Page
	if ( $arg['type'] != "all" and $WP_Statistics->get_option( 'visitors_log' ) == true ) {
		$where[] = "`" . wp_statistics_db_table( 'pages' ) . "`.`type`='" . $arg['type'] . "' AND `" . wp_statistics_db_table( 'pages' ) . "`.`page_id` = " . $arg['ID'];
	}

	//Check Location
	if ( $arg['location'] != "all" ) {
		$ISOCountryCode = $WP_Statistics->get_country_codes();
		if ( array_key_exists( $arg['location'], $ISOCountryCode ) ) {
			$where[] = "`" . wp_statistics_db_table( 'visitor' ) . "`.`location` = '" . $arg['location'] . "'";
		}
	}

	//Check User Agent
	if ( $arg['agent'] != "all" ) {
		$where[] = "`" . wp_statistics_db_table( 'visitor' ) . "`.`agent` = '" . $arg['agent'] . "'";
	}

	//Check User Platform
	if ( $arg['platform'] != "all" ) {
		$where[] = "`" . wp_statistics_db_table( 'visitor' ) . "`.`platform` = '" . $arg['platform'] . "'";
	}

	//Check Date Time report
	if ( $daily == true ) {

		//Get Only Current Day Visitors
		$where[] = "`" . wp_statistics_db_table( 'visitor' ) . "`.`last_counter` = '" . $WP_Statistics->Current_Date( 'Y-m-d', $time ) . "'";
	} else {

		//Generate MySql Time Conditions
		$mysql_time_sql = wp_statistics_mysql_time_conditions( $date_column, $time );
		if ( ! empty( $mysql_time_sql ) ) {
			$where[] = $mysql_time_sql;
		}
	}

	//Push Conditions to SQL
	if ( ! empty( $where ) ) {
		$sql .= ' WHERE ' . implode( ' AND ', $where );
	}

	//Custom Action
	if ( $time == "total" and $arg['type'] == "all" ) {
		$history = $WP_Statistics->Get_Historical_Data( 'visitors' );
	}

	// Execute the SQL call, if we're only counting we can use get_var(), otherwise we use query().
	if ( $count_only == true ) {
		$sum = $wpdb->get_var( $sql );
		$sum += $history;
	} else {
		$sum = $wpdb->query( $sql );
	}

	return $sum;
}

/**
 * This function returns the statistics for a given page.
 *
 * @param $time
 * @param string $page_uri
 * @param int $id
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @param bool $type
 * @return int|null|string
 */
function wp_statistics_pages( $time, $page_uri = '', $id = - 1, $rangestartdate = null, $rangeenddate = null, $type = false ) {
	global $wpdb, $WP_Statistics;

	//Date Column Name in visits table
	$table_name  = wp_statistics_db_table( 'pages' );
	$date_column = 'date';
	$history     = 0;

	//Check Where Condition
	$where = false;

	//Check Query By Page ID or Page Url
	if ( $type != false and $id != - 1 ) {
		$where[] = "`type`='" . $type . "' AND `page_id` = " . $id;
	} else {

		// If no page URI has been passed in, get the current page URI.
		if ( $page_uri == '' ) {
			$page_uri = wp_statistics_get_uri();
		}
		$page_uri_sql = esc_sql( $page_uri );

		// If a page/post ID has been passed, use it to select the rows, otherwise use the URI.
		if ( $id != - 1 ) {
			$where[]     = "`id`= " . absint( $id );
			$history_key = 'page';
			$history_id  = absint( $id );
		} else {
			$where[]     = "`URI` = '{$page_uri_sql}'";
			$history_key = 'uri';
			$history_id  = $page_uri;
		}

		//Custom Action
		if ( $time == "total" ) {
			$history = $WP_Statistics->Get_Historical_Data( $history_key, $history_id );
		}
	}

	//Prepare Time
	$time_array = array();
	if ( is_numeric( $time ) ) {
		$time_array['is_day'] = true;
	}
	if ( ! is_null( $rangestartdate ) and ! is_null( $rangeenddate ) ) {
		$time_array = array( 'start' => $rangestartdate, 'end' => $rangeenddate );
	}

	//Check MySql Time Conditions
	$mysql_time_sql = wp_statistics_mysql_time_conditions( $date_column, $time, $time_array );
	if ( ! empty( $mysql_time_sql ) ) {
		$where[] = $mysql_time_sql;
	}

	//Generate Base Sql
	$sql = "SELECT SUM(count) FROM {$table_name}";

	//Push Conditions to SQL
	if ( ! empty( $where ) ) {
		$sql .= ' WHERE ' . implode( ' AND ', $where );
	}

	//Request Get data
	$sum = $wpdb->get_var( $sql );
	$sum += $history;

	//Return Number Statistic
	return ( $sum == '' ? 0 : $sum );
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
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT `uri`,`id`,`type` FROM {$wpdb->prefix}statistics_pages WHERE `date` BETWEEN %s AND %s GROUP BY `uri`", $rangestartdate, $rangeenddate ), ARRAY_N );
	} else {
		$result = $wpdb->get_results( "SELECT `uri`,`id`,`type` FROM {$wpdb->prefix}statistics_pages GROUP BY `uri`", ARRAY_N );
	}

	$total = 0;
	$uris  = array();

	// Now get the total page visit count for each unique URI.
	foreach ( $result as $out ) {
		// Increment the total number of results.
		$total ++;

		//Prepare item
		list( $url, $page_id, $page_type ) = $out;

		//Get Page Title
		$page_info = wp_statistics_get_page_info( $page_id, $page_type );
		$title     = mb_substr( $page_info['title'], 0, 200, "utf-8" );
		$page_url  = $page_info['link'];

		// Check age Title if page id or type not exist
		if ( $page_info['link'] == "" ) {
			$page_url = htmlentities( path_join( get_site_url(), $url ), ENT_QUOTES );
			$id       = wp_statistics_uri_to_id( $out[0] );
			$post     = get_post( $id );
			if ( is_object( $post ) ) {
				$title = esc_html( $post->post_title );
			} else {
				if ( $out[0] == '/' ) {
					$title = get_bloginfo();
				} else {
					$title = '';
				}
			}
		}

		//Check Title is empty
		if ( empty( $title ) ) {
			$title = '-';
		}

		// Add the current post to the array.
		if ( $rangestartdate != null && $rangeenddate != null ) {
			$uris[] = array(
				$out[0],
				wp_statistics_pages( 'range', $out[0], - 1, $rangestartdate, $rangeenddate ),
				$page_id,
				$title,
				$page_url,
			);
		} else {
			$uris[] = array( $out[0], wp_statistics_pages( 'total', $out[0] ), $page_id, $title, $page_url );
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

	//Sanitize Xss injection
	$page_uri = filter_var( $page_uri, FILTER_SANITIZE_STRING );

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
		if ( $rangeenddate == 'CURDATE()' ) {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT agent FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN %s AND CURDATE()", $rangestartdate ), ARRAY_N );
		} else {
			$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT agent FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN %s AND %s", $rangestartdate, $rangeenddate ), ARRAY_N );
		}

	} else {
		$result = $wpdb->get_results( "SELECT DISTINCT agent FROM {$wpdb->prefix}statistics_visitor", ARRAY_N );
	}

	$Browsers        = array();
	$default_browser = wp_statistics_get_browser_list();

	foreach ( $result as $out ) {
		//Check Browser is defined in wp-statistics
		if ( array_key_exists( strtolower( $out[0] ), $default_browser ) ) {
			$Browsers[] = $out[0];
		}
	}

	return $Browsers;
}

/**
 * Count User By User Agent
 *
 * @param $agent
 * @param null $rangestartdate
 * @param null $rangeenddate
 * @return mixed
 */
function wp_statistics_useragent( $agent, $rangestartdate = null, $rangeenddate = null ) {
	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(agent) FROM {$wpdb->prefix}statistics_visitor WHERE `agent` = %s AND `last_counter` BETWEEN %s AND %s",
				$agent,
				$rangestartdate,
				$rangeenddate
			)
		);
	} else {
		$result = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(agent) FROM {$wpdb->prefix}statistics_visitor WHERE `agent` = %s", $agent ) );
	}

	return $result;
}

// This function returns all unique platform types from the database.
function wp_statistics_platform_list( $rangestartdate = null, $rangeenddate = null ) {

	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT platform FROM {$wpdb->prefix}statistics_visitor WHERE `last_counter` BETWEEN %s AND %s",
				$rangestartdate,
				$rangeenddate
			),
			ARRAY_N
		);
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
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(platform) FROM {$wpdb->prefix}statistics_visitor WHERE `platform` = %s AND `last_counter` BETWEEN %s AND %s",
				$platform,
				$rangestartdate,
				$rangeenddate
			)
		);
	} else {
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(platform) FROM {$wpdb->prefix}statistics_visitor WHERE `platform` = %s",
				$platform
			)
		);
	}

	return $result;
}

// This function returns all unique versions for a given agent from the database.
function wp_statistics_agent_version_list( $agent, $rangestartdate = null, $rangeenddate = null ) {
	global $wpdb;

	if ( $rangestartdate != null && $rangeenddate != null ) {
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT version FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND `last_counter` BETWEEN %s AND %s",
				$agent,
				$rangestartdate,
				$rangeenddate
			),
			ARRAY_N
		);
	} else {
		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT DISTINCT version FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s", $agent ),
			ARRAY_N
		);
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
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(version) FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND version = %s AND `last_counter` BETWEEN %s AND %s",
				$agent,
				$version,
				$rangestartdate,
				$rangeenddate
			)
		);
	} else {
		$result = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(version) FROM {$wpdb->prefix}statistics_visitor WHERE agent = %s AND version = %s",
				$agent,
				$version
			)
		);
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
	global $WP_Statistics;

	$default = $engines = array(
		'ask'        => array(
			'name'         => 'Ask.com',
			'translated'   => __( 'Ask.com', 'wp-statistics' ),
			'tag'          => 'ask',
			'sqlpattern'   => '%ask.com%',
			'regexpattern' => 'ask\.com',
			'querykey'     => 'q',
			'image'        => 'ask.png',
		),
		'baidu'      => array(
			'name'         => 'Baidu',
			'translated'   => __( 'Baidu', 'wp-statistics' ),
			'tag'          => 'baidu',
			'sqlpattern'   => '%baidu.com%',
			'regexpattern' => 'baidu\.com',
			'querykey'     => 'wd',
			'image'        => 'baidu.png',
		),
		'bing'       => array(
			'name'         => 'Bing',
			'translated'   => __( 'Bing', 'wp-statistics' ),
			'tag'          => 'bing',
			'sqlpattern'   => '%bing.com%',
			'regexpattern' => 'bing\.com',
			'querykey'     => 'q',
			'image'        => 'bing.png',
		),
		'clearch'    => array(
			'name'         => 'clearch.org',
			'translated'   => __( 'clearch.org', 'wp-statistics' ),
			'tag'          => 'clearch',
			'sqlpattern'   => '%clearch.org%',
			'regexpattern' => 'clearch\.org',
			'querykey'     => 'q',
			'image'        => 'clearch.png',
		),
		'duckduckgo' => array(
			'name'         => 'DuckDuckGo',
			'translated'   => __( 'DuckDuckGo', 'wp-statistics' ),
			'tag'          => 'duckduckgo',
			'sqlpattern'   => array( '%duckduckgo.com%', '%ddg.gg%' ),
			'regexpattern' => array( 'duckduckgo\.com', 'ddg\.gg' ),
			'querykey'     => 'q',
			'image'        => 'duckduckgo.png',
		),
		'google'     => array(
			'name'         => 'Google',
			'translated'   => __( 'Google', 'wp-statistics' ),
			'tag'          => 'google',
			'sqlpattern'   => '%google.%',
			'regexpattern' => 'google\.',
			'querykey'     => 'q',
			'image'        => 'google.png',
		),
		'yahoo'      => array(
			'name'         => 'Yahoo!',
			'translated'   => __( 'Yahoo!', 'wp-statistics' ),
			'tag'          => 'yahoo',
			'sqlpattern'   => '%yahoo.com%',
			'regexpattern' => 'yahoo\.com',
			'querykey'     => 'p',
			'image'        => 'yahoo.png',
		),
		'yandex'     => array(
			'name'         => 'Yandex',
			'translated'   => __( 'Yandex', 'wp-statistics' ),
			'tag'          => 'yandex',
			'sqlpattern'   => '%yandex.ru%',
			'regexpattern' => 'yandex\.ru',
			'querykey'     => 'text',
			'image'        => 'yandex.png',
		),
		'qwant'      => array(
			'name'         => 'Qwant',
			'translated'   => __( 'Qwant', 'wp-statistics' ),
			'tag'          => 'qwant',
			'sqlpattern'   => '%qwant.com%',
			'regexpattern' => 'qwant\.com',
			'querykey'     => 'q',
			'image'        => 'qwant.png',
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

/**
 * Get Search engine Statistics
 *
 * @param string $search_engine
 * @param string $time
 * @param string $search_by [query / name]
 * @return mixed
 */
function wp_statistics_get_search_engine_query( $search_engine = 'all', $time = 'total', $search_by = 'query' ) {
	global $wpdb, $WP_Statistics;

	//Prepare Table Name
	$table_name = $wpdb->prefix . 'statistics_';
	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		$table_name .= 'search';
	} else {
		$table_name .= 'visitor';
	}

	//Date Column table
	$date_column = 'last_counter';

	// Get a complete list of search engines
	if ( $search_by == "query" ) {
		$search_query = wp_statistics_searchengine_query( $search_engine );
	} else {
		$search_query = wp_statistics_searchword_query( $search_engine );
	}

	//Generate Base Sql
	$sql = "SELECT * FROM {$table_name} WHERE ({$search_query})";

	//Generate MySql Time Conditions
	$mysql_time_sql = wp_statistics_mysql_time_conditions( $date_column, $time, array( 'current_date' => true ) );
	if ( ! empty( $mysql_time_sql ) ) {
		$sql = $sql . ' AND (' . $mysql_time_sql . ')';
	}

	//Request Data
	$result = $wpdb->query( $sql );
	return $result;
}

/**
 * This function will return the statistics for a given search engine.
 *
 * @param string $search_engine
 * @param string $time
 * @return mixed
 */
function wp_statistics_searchengine( $search_engine = 'all', $time = 'total' ) {
	return wp_statistics_get_search_engine_query( $search_engine, $time, $search_by = 'query' );
}

//This Function will return the referrer list
function wp_statistics_referrer( $time = null ) {
	global $wpdb, $WP_Statistics;

	$timezone = array(
		'today'     => 0,
		'yesterday' => - 1,
		'week'      => - 7,
		'month'     => - 30,
		'year'      => - 365,
		'total'     => 'ALL',
	);
	$sql      = "SELECT `referred` FROM `" . $wpdb->prefix . "statistics_visitor` WHERE referred <> ''";
	if ( array_key_exists( $time, $timezone ) ) {
		if ( $time != "total" ) {
			$sql .= " AND (`last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $timezone[$time] )}')";
		}
	} else {
		//Set Default
		$sql .= " AND (`last_counter` = '{$WP_Statistics->Current_Date( 'Y-m-d', $time )}')";
	}
	$result = $wpdb->get_results( $sql );

	$urls = array();
	foreach ( $result as $item ) {
		$url = parse_url( $item->referred );
		if ( empty( $url['host'] ) || stristr( get_bloginfo( 'url' ), $url['host'] ) ) {
			continue;
		}
		$urls[] = $url['scheme'] . '://' . $url['host'];
	}
	$get_urls = array_count_values( $urls );

	return count( $get_urls );
}

/**
 * This function will return the statistics for a given search engine for a given time frame.
 *
 * @param string $search_engine
 * @param string $time
 * @return mixed
 */
function wp_statistics_searchword( $search_engine = 'all', $time = 'total' ) {
	return wp_statistics_get_search_engine_query( $search_engine, $time, $search_by = 'word' );
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

	$db_date = $wpdb->get_var(
		"SELECT post_date FROM {$wpdb->posts} WHERE post_type='post' AND post_status='publish' ORDER BY post_date DESC LIMIT 1"
	);

	$date_format = get_option( 'date_format' );

	return $WP_Statistics->Current_Date_i18n( $date_format, $db_date, false );
}

// This function will return the average number of posts per day that are published on your site.
// Alternatively if $days is set to true it returns the average number of days between posts on your site.
function wp_statistics_average_post( $days = false ) {

	global $wpdb;

	$get_first_post = $wpdb->get_var(
		"SELECT post_date FROM {$wpdb->posts} WHERE post_status = 'publish' ORDER BY post_date LIMIT 1"
	);
	$get_total_post = $wpdb->get_var(
		"SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_status = 'publish' AND post_type = 'post'"
	);

	$days_spend = intval(
		( time() - strtotime( $get_first_post ) ) / 86400
	); // 86400 = 60 * 60 * 24 = number of seconds in a day

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

	$days_spend = intval(
		( time() - strtotime( $get_first_comment ) ) / 86400
	); // 86400 = 60 * 60 * 24 = number of seconds in a day

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

	$days_spend = intval(
		( time() - strtotime( $get_first_user ) ) / 86400
	); // 86400 = 60 * 60 * 24 = number of seconds in a day

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

// This function handle's the Dashicons in the overview page.
function wp_statistics_icons( $dashicons, $icon_name = null ) {
	if ( null == $icon_name ) {
		$icon_name = $dashicons;
	}

	return '<span class="dashicons ' . $dashicons . '"></span>';
}

// This function checks to see if all the PHP modules we need for GeoIP exists.
function wp_statistics_geoip_supported() {
	// Check to see if we can support the GeoIP code, requirements are:
	$enabled = true;

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

/**
 * Convert PHP date Format to Moment js
 *
 * @param $phpFormat
 * @return string
 * @see https://stackoverflow.com/questions/30186611/php-dateformat-to-moment-js-format
 */
function wp_statistics_convert_php_to_moment_js( $phpFormat ) {
	$replacements = array(
		'A' => 'A',
		'a' => 'a',
		'B' => '',
		'c' => 'YYYY-MM-DD[T]HH:mm:ssZ',
		'D' => 'ddd',
		'd' => 'DD',
		'e' => 'zz',
		'F' => 'MMMM',
		'G' => 'H',
		'g' => 'h',
		'H' => 'HH',
		'h' => 'hh',
		'I' => '',
		'i' => 'mm',
		'j' => 'D',
		'L' => '',
		'l' => 'dddd',
		'M' => 'MMM',
		'm' => 'MM',
		'N' => 'E',
		'n' => 'M',
		'O' => 'ZZ',
		'o' => 'YYYY',
		'P' => 'Z',
		'r' => 'ddd, DD MMM YYYY HH:mm:ss ZZ',
		'S' => 'o',
		's' => 'ss',
		'T' => 'z',
		't' => '',
		'U' => 'X',
		'u' => 'SSSSSS',
		'v' => 'SSS',
		'W' => 'W',
		'w' => 'e',
		'Y' => 'YYYY',
		'y' => 'YY',
		'Z' => '',
		'z' => 'DDD'
	);

	// Converts escaped characters.
	foreach ( $replacements as $from => $to ) {
		$replacements[ '\\' . $from ] = '[' . $from . ']';
	}

	return strtr( $phpFormat, $replacements );
}


// This function creates the date range selector 'widget' used in the various statistics pages.
function wp_statistics_date_range_selector( $page, $current, $range = array(), $desc = array(), $extrafields = '', $pre_extra = '', $post_extra = '' ) {
	GLOBAL $WP_Statistics;

	//import DataPicker Jquery Ui Jquery Plugin
	wp_enqueue_script( 'jquery-ui-datepicker' );
	wp_register_style( 'jquery-ui-smoothness-css', WP_Statistics::$reg['plugin-url'] . 'assets/css/jquery-ui-smoothness.min.css' );
	wp_enqueue_style( 'jquery-ui-smoothness-css' );

	//Create Object List Of Default Hit Day to Display
	if ( $range == null or count( $range ) == 0 ) {

		//Get Number Of Time Range
		$range = array( 10, 20, 30, 60, 90, 180, 270, 365 );

		//Added All time From installed plugin to now
		$installed_date = WP_Statistics::get_number_days_install_plugin();
		array_push( $range, $installed_date['days'] );

		//Get List Of Text Lang time Range
		$desc = array(
			__( '10 Days', 'wp-statistics' ),
			__( '20 Days', 'wp-statistics' ),
			__( '30 Days', 'wp-statistics' ),
			__( '2 Months', 'wp-statistics' ),
			__( '3 Months', 'wp-statistics' ),
			__( '6 Months', 'wp-statistics' ),
			__( '9 Months', 'wp-statistics' ),
			__( '1 Year', 'wp-statistics' ),
			__( 'All', 'wp-statistics' ),
		);
	}
	if ( count( $desc ) == 0 ) {
		$desc = $range;
	}
	$rcount = count( $range );
	$bold   = true;

	// Check to see if there's a range in the URL, if so set it, otherwise use the default.
	if ( isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false ) {
		$rangestart = $_GET['rangestart'];
	} else {
		$rangestart = $WP_Statistics->Current_Date( 'm/d/Y', '-' . $current );
	}
	if ( isset( $_GET['rangeend'] ) and strtotime( $_GET['rangeend'] ) != false ) {
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
	$rangestart = $WP_Statistics->Local_Date( get_option( "date_format" ), $rangestart_utime );
	$rangeend   = $WP_Statistics->Local_Date( get_option( "date_format" ), $rangeend_utime );

	//Calculate hit day if range is exist
	if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
		$earlier = new DateTime( $_GET['rangestart'] );
		$later   = new DateTime( $_GET['rangeend'] );
		$current = $daysToDisplay = $later->diff( $earlier )->format( "%a" );
	}

	echo '<form method="get"><ul class="subsubsub wp-statistics-sub-fullwidth">' . "\r\n";
	// Output any extra HTML we've been passed after the form element but before the date selector.
	echo $pre_extra;

	for ( $i = 0; $i < $rcount; $i ++ ) {
		echo '<li class="all"><a ';
		if ( $current == $range[ $i ] ) {
			echo 'class="current" ';
			$bold = false;
		}

		// Don't bother adding he date range to the standard links as they're not needed any may confuse the custom range selector.
		echo 'href="?page=' . $page . '&hitdays=' . $range[ $i ] . esc_html( $extrafields ) . '">' . $desc[ $i ] . '</a></li>';
		if ( $i < $rcount - 1 ) {
			echo ' | ';
		}
		echo "\r\n";
	}
	echo ' | ';
	echo '<input type="hidden" name="page" value="' . $page . '">';

	parse_str( $extrafields, $parse );
	foreach ( $parse as $key => $value ) {
		echo '<input type="hidden" name="' . $key . '" value="' . esc_sql( $value ) . '">';
	}

	if ( $bold ) {
		echo ' <b>' . __( 'Time Frame', 'wp-statistics' ) . ':</b> ';
	} else {
		echo ' ' . __( 'Time Frame', 'wp-statistics' ) . ': ';
	}

	//Print Time Range Select Ui
	echo '<input type="text" size="18" name="rangestart" id="datestartpicker" value="' . $rangestart . '" placeholder="' . __( wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ), 'wp-statistics' ) . '" autocomplete="off"> ' . __( 'to', 'wp-statistics' ) . ' <input type="text" size="18" name="rangeend" id="dateendpicker" value="' . $rangeend . '" placeholder="' . __( wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ), 'wp-statistics' ) . '" autocomplete="off"> <input type="submit" value="' . __( 'Go', 'wp-statistics' ) . '" class="button-primary">' . "\r\n";

	//Sanitize Time Request
	echo '<input type="hidden" name="rangestart" id="rangestart" value="' . $WP_Statistics->Local_Date( "Y-m-d", $rangestart_utime ) . '">';
	echo '<input type="hidden" name="rangeend" id="rangeend" value="' . $WP_Statistics->Local_Date( "Y-m-d", $rangeend_utime ) . '">';

	// Output any extra HTML we've been passed after the date selector but before the submit button.
	echo $post_extra;

	echo '</form>' . "\r\n";
	echo '<script src="' . WP_Statistics::$reg['plugin-url'] . 'assets/js/moment.min.js?ver=2.24.0"></script>';
	echo '<script>
        jQuery(function() {
            
        //From Date
        jQuery( "#datestartpicker" ).datepicker({dateFormat: \'' . wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ) . '\', 
        onSelect: function(selectedDate) {
        if (selectedDate.length > 0) {
            jQuery("#rangestart").val(moment(selectedDate, \'' . wp_statistics_convert_php_to_moment_js( get_option( "date_format" ) ) . '\').format(\'YYYY-MM-DD\'));
         }
         }
        });
        //To Date
        jQuery( "#dateendpicker" ).datepicker({
        dateFormat: \'' . wp_statistics_dateformat_php_to_jqueryui( get_option( "date_format" ) ) . '\',
         onSelect: function(selectedDate) {
        if (selectedDate.length > 0) {
            jQuery("#rangeend").val(moment(selectedDate, \'' . wp_statistics_convert_php_to_moment_js( get_option( "date_format" ) ) . '\').format(\'YYYY-MM-DD\'));
         }
        }});});
        </script>' . "\r\n";
}

/*
 * Prepare Range Time For Time picker
 */
function wp_statistics_prepare_range_time_picker() {

	//Get Default Number To display in All
	$installed_date = WP_Statistics::get_number_days_install_plugin();
	$daysToDisplay  = $installed_date['days'];

	//List Of Pages For show 20 Days as First Parameter
	$list_of_pages = array( 'hits', 'searches', 'pages', 'countries', 'categories', 'tags', 'authors', 'browser', 'exclusions' );
	foreach ( $list_of_pages as $page ) {
		if ( isset( $_GET['page'] ) and $_GET['page'] == WP_Statistics::$page[ $page ] ) {
			$daysToDisplay = 30;
		}
	}

	//Set Default Object Time Range
	$rangestart = '';
	$rangeend   = '';

	//Check Hit Day
	if ( isset( $_GET['hitdays'] ) and $_GET['hitdays'] > 0 ) {
		$daysToDisplay = intval( $_GET['hitdays'] );
	}
	if ( isset( $_GET['rangeend'] ) and isset( $_GET['rangestart'] ) and strtotime( $_GET['rangestart'] ) != false and strtotime( $_GET['rangeend'] ) != false ) {
		$rangestart = $_GET['rangestart'];
		$rangeend   = $_GET['rangeend'];

		//Calculate hit day if range is exist
		$earlier       = new DateTime( $_GET['rangestart'] );
		$later         = new DateTime( $_GET['rangeend'] );
		$daysToDisplay = $later->diff( $earlier )->format( "%a" );
	}

	return array( $daysToDisplay, $rangestart, $rangeend );
}

/**
 * Convert php date format to Jquery Ui
 *
 * @param $php_format
 * @return string
 */
function wp_statistics_dateformat_php_to_jqueryui( $php_format ) {
	$SYMBOLS_MATCHING = array(
		// Day
		'd' => 'dd',
		'D' => 'D',
		'j' => 'd',
		'l' => 'DD',
		'N' => '',
		'S' => '',
		'w' => '',
		'z' => 'o',
		// Week
		'W' => '',
		// Month
		'F' => 'MM',
		'm' => 'mm',
		'M' => 'M',
		'n' => 'm',
		't' => '',
		// Year
		'L' => '',
		'o' => '',
		'Y' => 'yy',
		'y' => 'y',
		// Time
		'a' => '',
		'A' => '',
		'B' => '',
		'g' => '',
		'G' => '',
		'h' => '',
		'H' => '',
		'i' => '',
		's' => '',
		'u' => ''
	);
	$jqueryui_format  = "";
	$escaping         = false;
	for ( $i = 0; $i < strlen( $php_format ); $i ++ ) {
		$char = $php_format[ $i ];
		if ( $char === '\\' ) {
			$i ++;
			if ( $escaping ) {
				$jqueryui_format .= $php_format[ $i ];
			} else {
				$jqueryui_format .= '\'' . $php_format[ $i ];
			}
			$escaping = true;
		} else {
			if ( $escaping ) {
				$jqueryui_format .= "'";
				$escaping        = false;
			}
			if ( isset( $SYMBOLS_MATCHING[ $char ] ) ) {
				$jqueryui_format .= $SYMBOLS_MATCHING[ $char ];
			} else {
				$jqueryui_format .= $char;
			}
		}
	}

	return $jqueryui_format;
}

/**
 * This function is used to calculate the number of days and their respective unix timestamps.
 *
 * @param $days
 * @param $start
 * @param $end
 * @return array
 */
function wp_statistics_date_range_calculator( $days, $start, $end ) {
	global $WP_Statistics;

	$daysToDisplay = $days;
	$rangestart    = $start;
	$rangeend      = $end;

	//Check Exist params
	if ( ! empty( $daysToDisplay ) and ! empty( $rangestart ) and ! empty( $rangeend ) ) {
		return array( $daysToDisplay, strtotime( $rangestart ), strtotime( $rangeend ) );
	}

	//Check Not Exist day to display
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


/**
 * Delete All record From Table
 *
 * @param bool $table_name
 * @return string
 */
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


/**
 * This function creates a small JavaScript that will load the contents of a overview or dashboard widget.
 *
 * @param $widget
 * @param null $container_id
 */
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
 * @param        $num
 * @param string $opacity
 *
 * @return string
 */
function wp_statistics_generate_rgba_color( $num, $opacity = '1' ) {
	$hash = md5( 'color' . $num );

	return sprintf(
		"'rgba(%s, %s, %s, %s)'",
		hexdec( substr( $hash, 0, 2 ) ),
		hexdec( substr( $hash, 2, 2 ) ),
		hexdec( substr( $hash, 4, 2 ) ),
		$opacity
	);
}

/**
 * This function will validate that a capability exists,
 * if not it will default to returning the 'manage_options' capability.
 *
 * @param string $capability Capability
 * @return string 'manage_options'
 */
function wp_statistics_validate_capability( $capability ) {
	global $wp_roles;

	if ( ! is_object( $wp_roles ) || ! is_array( $wp_roles->roles ) ) {
		return 'manage_options';
	}

	foreach ( $wp_roles->roles as $role ) {
		$cap_list = $role['capabilities'];

		foreach ( $cap_list as $key => $cap ) {
			if ( $capability == $key ) {
				return $capability;
			}
		}
	}

	return 'manage_options';
}

/**
 * Check User Access To WP-Statistics Admin
 *
 * @param string $type [manage | read ]
 * @param string|boolean $export
 * @return bool
 */
function wp_statistics_check_access_user( $type = 'both', $export = false ) {
	global $WP_Statistics;

	//List Of Default Cap
	$list = array(
		'manage' => array( 'manage_capability', 'manage_options' ),
		'read'   => array( 'read_capability', 'manage_options' )
	);

	//User User Cap
	$cap = 'both';
	if ( ! empty( $type ) and array_key_exists( $type, $list ) ) {
		$cap = $type;
	}

	//Check Export Cap name or Validation current_can_user
	if ( $export == "cap" ) {
		return wp_statistics_validate_capability( $WP_Statistics->get_option( $list[ $cap ][0], $list[ $cap ][1] ) );
	}

	//Check Access
	switch ( $type ) {
		case "manage":
		case "read":
			return current_user_can( wp_statistics_validate_capability( $WP_Statistics->get_option( $list[ $cap ][0], $list[ $cap ][1] ) ) );
			break;
		case "both":
			foreach ( array( 'manage', 'read' ) as $c ) {
				if ( wp_statistics_check_access_user( $c ) === true ) {
					return true;
				}
			}
			break;
	}

	return false;
}

/**
 * Notices displayed near the top of admin pages.
 *
 * @param $type
 * @param $message
 * @area admin
 */
function wp_statistics_admin_notice_result( $type, $message ) {

	switch ( $type ) {
		case 'error':
			$class = 'notice notice-error';
			break;

		case 'warning':
			$class = 'notice notice-warning';
			break;

		case 'success':
			$class = 'notice notice-success';
			break;
	}

	printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
}

/**
 * Get All Browser List For Detecting
 *
 * @param bool $all
 * @area utility
 * @return array|mixed
 */
function wp_statistics_get_browser_list( $all = true ) {

	//List Of Detect Browser in WP Statistics
	$list        = array(
		"chrome"  => __( "Chrome", 'wp-statistics' ),
		"firefox" => __( "Firefox", 'wp-statistics' ),
		"msie"    => __( "Internet Explorer", 'wp-statistics' ),
		"edge"    => __( "Edge", 'wp-statistics' ),
		"opera"   => __( "Opera", 'wp-statistics' ),
		"safari"  => __( "Safari", 'wp-statistics' )
	);
	$browser_key = array_keys( $list );

	//Return All Browser List
	if ( $all === true ) {
		return $list;
		//Return Browser Keys For detect
	} elseif ( $all == "key" ) {
		return $browser_key;
	} else {
		//Return Custom Browser Name by key
		if ( array_search( strtolower( $all ), $browser_key ) !== false ) {
			return $list[ strtolower( $all ) ];
		} else {
			return __( "Unknown", 'wp-statistics' );
		}
	}
}

/**
 * Pagination Link
 *
 * @param array $args
 * @area admin
 * @return string
 */
function wp_statistics_paginate_links( $args = array() ) {

	//Prepare Arg
	$defaults   = array(
		'item_per_page' => 10,
		'container'     => 'pagination-wrap',
		'query_var'     => 'pagination-page',
		'total'         => 0,
		'current'       => 0,
		'show_now_page' => true
	);
	$args       = wp_parse_args( $args, $defaults );
	$total_page = ceil( $args['total'] / $args['item_per_page'] );

	//Show Pagination Ui
	if ( $total_page > 1 ) {
		echo '<div class="' . $args['container'] . '">';
		echo paginate_links( array(
			'base'      => add_query_arg( $args['query_var'], '%#%' ),
			'format'    => '',
			'type'      => 'list',
			'mid_size'  => 3,
			'prev_text' => __( '&laquo;' ),
			'next_text' => __( '&raquo;' ),
			'total'     => $total_page,
			'current'   => $args['current']
		) );

		if ( $args['show_now_page'] ) {
			echo '<p id="result-log">' . sprintf( __( 'Page %1$s of %2$s', 'wp-statistics' ), $args['current'], $total_page ) . '</p>';
		}

		echo '</div>';
	}
}

/**
 * Get Post List From custom Post Type
 *
 * @param array $args
 * @area utility
 * @return mixed
 */
function wp_statistics_get_post_list( $args = array() ) {

	//Prepare Arg
	$defaults = array(
		'post_type'      => 'page',
		'post_status'    => 'publish',
		'posts_per_page' => '-1',
		'order'          => 'ASC',
		'fields'         => 'ids'
	);
	$args     = wp_parse_args( $args, $defaults );

	//Get Post List
	$query = new WP_Query( $args );
	$list  = array();
	foreach ( $query->posts as $ID ) {
		$list[ $ID ] = esc_html( get_the_title( $ID ) );
	}

	return $list;
}

/**
 * Get Page information
 *
 * @param $page_id
 * @param string $type
 * @return array
 */
function wp_statistics_get_page_info( $page_id, $type = 'post' ) {

	//Create Empty Object
	$arg      = array();
	$defaults = array(
		'link'      => '',
		'edit_link' => '',
		'object_id' => $page_id,
		'title'     => '-',
		'meta'      => array()
	);

	if ( ! empty( $type ) ) {
		switch ( $type ) {
			case "product":
			case "attachment":
			case "post":
			case "page":
				$arg = array(
					'title'     => esc_html( get_the_title( $page_id ) ),
					'link'      => get_the_permalink( $page_id ),
					'edit_link' => get_edit_post_link( $page_id ),
					'meta'      => array(
						'post_type' => get_post_type( $page_id )
					)
				);
				break;
			case "category":
			case "post_tag":
			case "tax":
				$term = get_term( $page_id );
				$arg  = array(
					'title'     => esc_html( $term->name ),
					'link'      => ( is_wp_error( get_term_link( $page_id ) ) === true ? '' : get_term_link( $page_id ) ),
					'edit_link' => get_edit_term_link( $page_id ),
					'meta'      => array(
						'taxonomy'         => $term->taxonomy,
						'term_taxonomy_id' => $term->term_taxonomy_id,
						'count'            => $term->count,
					)
				);
				break;
			case "home":
				$arg = array(
					'title' => __( 'Home Page', 'wp-statistics' ),
					'link'  => get_site_url()
				);
				break;
			case "author":
				$user_info = get_userdata( $page_id );
				$arg       = array(
					'title'     => ( $user_info->display_name != "" ? esc_html( $user_info->display_name ) : esc_html( $user_info->first_name . ' ' . $user_info->last_name ) ),
					'link'      => get_author_posts_url( $page_id ),
					'edit_link' => get_edit_user_link( $page_id ),
				);
				break;
			case "search":
				$result['title'] = __( 'Search Page', 'wp-statistics' );
				break;
			case "404":
				$result['title'] = __( '404 not found', 'wp-statistics' );
				break;
			case "archive":
				$result['title'] = __( 'Post Archive', 'wp-statistics' );
				break;
		}
	}

	return wp_parse_args( $arg, $defaults );
}

/**
 * Table List Wp-statistics
 *
 * @param string $export
 * @param array $except
 * @return array|null
 */
function wp_statistics_db_table( $export = 'all', $except = array() ) {
	global $wpdb;

	//Create Empty Object
	$list = array();

	//List Of Table
	if ( is_string( $except ) ) {
		$except = array( $except );
	}
	$mysql_list_table = array_diff( WP_Statistics_Install::$db_table, $except );
	foreach ( $mysql_list_table as $tbl ) {
		$table_name = $wpdb->prefix . 'statistics_' . $tbl;
		if ( $export == "all" ) {
			if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) == $table_name ) {
				$list[ $tbl ] = $table_name;
			}
		} else {
			$list[ $tbl ] = $table_name;
		}
	}

	//Export Data
	if ( $export == 'all' ) {
		return $list;
	} else {
		if ( array_key_exists( $export, $list ) ) {
			return $list[ $export ];
		}
	}

	return null;
}

/**
 * Check WP-statistics Option Require
 *
 * @param array $item
 * @param string $condition_key
 * @return array|bool
 */
function wp_statistics_check_option_require( $item = array(), $condition_key = 'require' ) {
	global $WP_Statistics;

	$condition = true;
	if ( array_key_exists( 'require', $item ) ) {
		foreach ( $item[ $condition_key ] as $if ) {
			if ( ! $WP_Statistics->get_option( $if ) ) {
				$condition = false;
				break;
			}
		}
	}

	return $condition;
}

/**
 * Modify For IGNORE insert Query
 *
 * @hook add_action('query', function_name, 10);
 * @param $query
 * @return string
 */
function wp_statistics_ignore_insert( $query ) {
	$count = 0;
	$query = preg_replace( '/^(INSERT INTO)/i', 'INSERT IGNORE INTO', $query, 1, $count );
	return $query;
}

/**
 * Get Html Body Page By Url
 *
 * @param $url string e.g : wp-statistics.com
 * @return bool
 */
function wp_statistics_get_html_page( $url ) {

	//sanitize Url
	$parse_url = wp_parse_url( $url );
	$urls[]    = esc_url_raw( $url );

	//Check Protocol Url
	if ( ! array_key_exists( 'scheme', $parse_url ) ) {
		$urls      = array();
		$url_parse = wp_parse_url( $url );
		foreach ( array( 'http://', 'https://' ) as $scheme ) {
			$urls[] = preg_replace( '/([^:])(\/{2,})/', '$1/', $scheme . path_join( ( isset( $url_parse['host'] ) ? $url_parse['host'] : '' ), ( isset( $url_parse['path'] ) ? $url_parse['path'] : '' ) ) );
		}
	}

	//Send Request for Get Page Html
	foreach ( $urls as $page ) {
		$response = wp_remote_get( $page, array(
			'timeout'    => 30,
			'user-agent' => "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36"
		) );
		if ( is_wp_error( $response ) ) {
			continue;
		}
		$data = wp_remote_retrieve_body( $response );
		if ( is_wp_error( $data ) ) {
			continue;
		}
		return ( wp_strip_all_tags( $data ) == "" ? false : $data );
	}

	return false;
}

/**
 * Get Site title By Url
 *
 * @param $url string e.g : wp-statistics.com
 * @return bool|string
 */
function wp_statistics_get_site_title( $url ) {

	//Get ody Page
	$html = wp_statistics_get_html_page( $url );
	if ( $html === false ) {
		return false;
	}

	//Get Page Title
	if ( class_exists( 'DOMDocument' ) ) {
		$dom = new DOMDocument;
		@$dom->loadHTML( $html );
		$title = '';
		if ( isset( $dom ) and $dom->getElementsByTagName( 'title' )->length > 0 ) {
			$title = $dom->getElementsByTagName( 'title' )->item( '0' )->nodeValue;
		}
		return ( wp_strip_all_tags( $title ) == "" ? false : wp_strip_all_tags( $title ) );
	}

	return false;
}


/**
 * Get WebSite IP Server And Country Name
 *
 * @param $url string domain name e.g : wp-statistics.com
 * @return array
 */
function wp_statistics_get_domain_server( $url ) {
	global $WP_Statistics;

	//Create Empty Object
	$result = array(
		'ip'      => '',
		'country' => ''
	);

	//Get Ip by Domain
	if ( function_exists( 'gethostbyname' ) ) {
		$ip = gethostbyname( $url );
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
			$result['ip'] = $ip;
			//Get country Code
			if ( $WP_Statistics->get_option( 'geoip' ) ) {
				$geoip_reader = $WP_Statistics::geoip_loader( 'country' );
				if ( $geoip_reader != false ) {
					try {
						$record            = $geoip_reader->country( $ip );
						$result['country'] = $record->country->isoCode;
					} catch ( Exception $e ) {
					}
				}
			}
		}
	}

	return $result;
}

/**
 * Show Site Icon by Url
 *
 * @param $url
 * @param int $size
 * @param string $style
 * @return bool|string
 */
function wp_statistics_show_site_icon( $url, $size = 16, $style = '' ) {
	$url = preg_replace( '/^https?:\/\//', '', $url );
	if ( $url != "" ) {
		$imgurl = "https://www.google.com/s2/favicons?domain=" . $url;
		return '<img src="' . $imgurl . '" width="' . $size . '" height="' . $size . '" style="' . ( $style == "" ? 'vertical-align: -3px;' : '' ) . '" />';
	}

	return false;
}

/**
 * Get Number Referer Domain
 *
 * @param $url
 * @param array $time_rang
 * @return integer
 */
function wp_statistics_get_number_referer_from_domain( $url, $time_rang = array() ) {
	global $wpdb;

	//Get Domain Name
	$search_url = wp_statistics_get_domain_name( esc_url_raw( $url ) );

	//Prepare SQL
	$time_sql = '';
	if ( count( $time_rang ) > 0 and ! empty( $time_rang ) ) {
		$time_sql = sprintf( "AND `last_counter` BETWEEN '%s' AND '%s'", $time_rang[0], $time_rang[1] );
	}
	$sql = $wpdb->prepare( "SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND (`referred` LIKE  %s OR `referred` LIKE %s OR `referred` LIKE %s OR `referred` LIKE %s) " . $time_sql . " ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC", 'https://www.' . $wpdb->esc_like( $search_url ) . '%', 'https://' . $wpdb->esc_like( $search_url ) . '%', 'http://www.' . $wpdb->esc_like( $search_url ) . '%', 'http://' . $wpdb->esc_like( $search_url ) . '%' );

	//Get Count
	return $wpdb->get_var( $sql );
}

/**
 * Get Domain name from url
 * e.g : https://wp-statistics.com/add-ons/ -> wp-statistics.com
 *
 * @param $url
 * @return mixed
 */
function wp_statistics_get_domain_name( $url ) {
	//Remove protocol
	$url = preg_replace( "(^https?://)", "", trim( $url ) );
	//remove w(3)
	$url = preg_replace( '#^(http(s)?://)?w{3}\.#', '$1', $url );
	//remove all Query
	$url = explode( "/", $url );

	return $url[0];
}