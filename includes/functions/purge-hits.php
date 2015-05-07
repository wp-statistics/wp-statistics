<?php
	function wp_statistics_purge_visitor_hits( $purge_hits ) {
		GLOBAL $wpdb, $WP_Statistics;
		
		// If it's less than 10 hits, don't do anything.
		if( $purge_hits > 9 ) {
			// Purge the visitor's with more than the defined hits.
			$result = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'statistics_visitor WHERE `hits` > \'' . $purge_hits . '\'');
			
			$to_delete = array();
			
			// Loop through the results and store the requried information in an array.  We don't just process it now as deleting 
			// the rows from the visitor table will mess up the results from our first query.
			foreach( $result as $row ) {
				$to_delete[] = array( $row->ID, $row->last_counter, $row->hits );
			}
			if( count( $to_delete ) > 0 ) {
				foreach( $to_delete as $item ) {
					// First update the daily hit count.
					$wpdb->query( "UPDATE {$wpdb->prefix}statistics_visit SET `visit` = `visit` - {$item[2]} WHERE `last_counter` = '{$item[1]}';" );
					// Next remove the visitor.  Note we can't do both in a single query, looks like $wpdb doesn't like executing them together.
					$wpdb->query( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `id` = '{$item[0]}';" );
				}
				
				$result_string = sprintf(__('%s records purged successfully.', 'wp_statistics'), '<code>' . count( $to_delete ) . '</code>');
			}
			else {
				$result_string = __('No visitors found to purge.', 'wp_statistics' );
			}
		}
		else {
			$result_string = __('Number of hits must be greater than or equal to 10!', 'wp_statistics');
		}

		return $result_string;
	}
?>