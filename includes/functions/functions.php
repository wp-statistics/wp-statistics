<?php
	function wp_statistics_useronline() {
		
		global $wpdb, $table_prefix;
		
		return $wpdb->query("SELECT * FROM {$table_prefix}statistics_useronline");
	}
	
	function wp_statistics_visit($time, $daily = null) {
	
		global $wpdb, $table_prefix;
		
		$s = new WP_Statistics();
		
		if( $daily == true ) {
		
			$result = $wpdb->get_row("SELECT * FROM {$table_prefix}statistics_visit WHERE `last_counter` = '{$s->Current_Date('Y-m-d', $time)}'");
			
			if( $result) {
				return $result->visit;
			} else {
				return 0;
			}
			
		} else {
		
			switch($time) {
				case 'today':
					$result = $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` = '{$s->Current_Date('Y-m-d')}'");
					break;
					
				case 'yesterday':
					$result = $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -1)}'");
					break;
					
				case 'week':
					$result[0] = array_sum( $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -7)}' AND '{$s->Current_Date('Y-m-d')}'") );
					break;
					
				case 'month':
					$result[0] = array_sum( $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -30)}' AND '{$s->Current_Date('Y-m-d')}'") );
					break;
					
				case 'year':
					$result[0] = array_sum( $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -360)}' AND '{$s->Current_Date('Y-m-d')}'") );
					break;
					
				case 'total':
					$result = $wpdb->get_col("SELECT SUM(visit) FROM {$table_prefix}statistics_visit");
					break;
					
				default:
					$result[0] = array_sum( $wpdb->get_col("SELECT `visit` FROM {$table_prefix}statistics_visit WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', $time)}' AND '{$s->Current_Date('Y-m-d')}'") );
					break;
			}
		}
		
		return $result[0];
	}
	
	function wp_statistics_visitor($time, $daily = null) {
	
		global $wpdb, $table_prefix;
		
		$s = new WP_Statistics();
		
		if( $daily == true ) {
		
			$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` = '{$s->Current_Date('Y-m-d', $time)}'");
			
			return $result;
				
		} else {
		
			switch($time) {
				case 'today':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` = '{$s->Current_Date('Y-m-d')}'");
					break;
					
				case 'yesterday':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -1)}'");
					break;
					
				case 'week':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -7)}' AND '{$s->Current_Date('Y-m-d')}'");
					break;
					
				case 'month':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -30)}' AND '{$s->Current_Date('Y-m-d')}'");
					break;
					
				case 'year':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', -360)}' AND '{$s->Current_Date('Y-m-d')}'");
					break;
					
				case 'total':
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor");
					break;
					
				default:
					$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `last_counter` BETWEEN '{$s->Current_Date('Y-m-d', $time)}' AND '{$s->Current_Date('Y-m-d')}'");
					break;
			}
		}
		
		return $result;
	}
	
	function wp_statistics_useragent($agent) {
	
		global $wpdb, $table_prefix;
		
		$result = $wpdb->query("SELECT * FROM {$table_prefix}statistics_visitor WHERE `agent` = '{$agent}'");
		
		return $result;
	}
	
	function wp_statistics_searchengine($search_engine = 'all', $time = 'total') {
	
		global $wpdb, $table_prefix;
		
		$s = new WP_Statistics();
		
		if( $search_engine == 'google' ) {
			$search_engine = "`referred` LIKE '%google.com%'";
		} else if( $search_engine == 'yahoo' ) {
			$search_engine = "`referred` LIKE '%yahoo.com%'";
		} else if( $search_engine == 'bing' ) {
			$search_engine = "`referred` LIKE '%bing.com%'";
		} else {
			$search_engine = "`referred` LIKE '%google.com%' OR `referred` LIKE '%yahoo.com%' OR `referred` LIKE '%bing.com%'";
		}
		
		switch($time) {
			case 'today':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d')}' AND {$search_engine}");
				break;
				
			case 'yesterday':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -1)}' AND {$search_engine}");
				
				break;
				
			case 'week':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -7)}' AND {$search_engine}");
				
				break;
				
			case 'month':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -30)}' AND {$search_engine}");
				
				break;
				
			case 'year':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d', -360)}' AND {$search_engine}");
				
				break;
				
			case 'total':
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE {$search_engine}");
				
				break;
				
			default:
				$result = $wpdb->query("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `last_counter` = '{$s->Current_Date('Y-m-d', $time)}' AND {$search_engine}");
				
				break;
		}
		
		return $result;
	}
	
	function wp_statistics_countposts() {
	
		$count_posts = wp_count_posts('post');
		return $count_posts->publish;
	}

	function wp_statistics_countpages() {
	
		$count_pages = wp_count_posts('page');
		return $count_pages->publish;
	}

	function wp_statistics_countcomment() {
	
		global $wpdb;
		
		$countcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		
		return $countcomms;
	}

	function wp_statistics_countspam() {
	
		return number_format_i18n(get_option('akismet_spam_count'));
	}

	function wp_statistics_countusers() {
	
		$result = count_users();
		return $result['total_users'];
	}

	function wp_statistics_lastpostdate( $type='english' ) {
	
		global $wpdb;
		
		$db_date = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_type='post' AND post_status='publish' ORDER BY ID DESC LIMIT 1");
		
		$date_format = get_option('date_format');
		
		if ( $type == 'farsi' ) {
		
			return jdate($date_format, strtotime($db_date));
			
		} else {
		
			return date($date_format, strtotime($db_date));
			
		}
	}
	
	function wp_statistics_average_post() {
	
		global $wpdb;
		
		$get_first_post = $wpdb->get_var("SELECT post_date FROM $wpdb->posts WHERE post_status = 'publish' ORDER BY post_date LIMIT 1");
		$get_total_post = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
		
		$days_spend = intval((time() - strtotime($get_first_post) ) / (60*60*24));
		
		return round($get_total_post / $days_spend, 2);
	}

	function wp_statistics_average_comment() {
	
		global $wpdb;
		
		$get_first_comment = $wpdb->get_var("SELECT comment_date FROM $wpdb->comments ORDER BY comment_date LIMIT 1");
		$get_total_comment = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");

		$days_spend = intval((time() - strtotime($get_first_comment) ) / (60*60*24));
		
		return round($get_total_comment / $days_spend, 2);
	}

	function wp_statistics_average_registeruser() {
	
		global $wpdb;
		
		$get_first_user = $wpdb->get_var("SELECT user_registered FROM $wpdb->users ORDER BY user_registered LIMIT 1");
		$get_total_user = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->users");

		$days_spend = intval((time() - strtotime($get_first_user) ) / (60*60*24));
		
		return round($get_total_user / $days_spend, 2);
	}
	
	function objectToArray($d) {
		if (is_object($d)) {
			// Gets the properties of the given object
			// with get_object_vars function
			$d = get_object_vars($d);
		}
 
		if (is_array($d)) {
			/*
			* Return array converted to object
			* Using __FUNCTION__ (Magic constant)
			* for recursive call
			*/
			return array_map(__FUNCTION__, $d);
		}
		else {
			// Return array
			return $d;
		}
	}