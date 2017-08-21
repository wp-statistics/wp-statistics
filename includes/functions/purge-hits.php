<?php
function wp_statistics_purge_visitor_hits( $purge_hits ) {
	GLOBAL $wpdb, $WP_Statistics;

	// If it's less than 10 hits, don't do anything.
	if ( $purge_hits > 9 ) {
		// Purge the visitor's with more than the defined hits.
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}statistics_visitor WHERE `hits` > %s", $purge_hits ) );

		$to_delete = array();

		// Loop through the results and store the requried information in an array.  We don't just process it now as deleting
		// the rows from the visitor table will mess up the results from our first query.
		foreach ( $result as $row ) {
			$to_delete[] = array( $row->ID, $row->last_counter, $row->hits );
		}
		if ( count( $to_delete ) > 0 ) {
			foreach ( $to_delete as $item ) {
				// First update the daily hit count.
				$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_visit SET `visit` = `visit` - %d WHERE `last_counter` = %s;", $item[2], $item[1] ) );
				// Next remove the visitor.  Note we can't do both in a single query, looks like $wpdb doesn't like executing them together.
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}statistics_visitor WHERE `id` = %s;", $item[0] ) );
			}

			$result_string = sprintf( __( '%s records purged successfully.', 'wp-statistics' ), '<code>' . count( $to_delete ) . '</code>' );
		} else {
			$result_string = __( 'No visitors found to purge.', 'wp-statistics' );
		}
	} else {
		$result_string = __( 'Number of hits must be greater than or equal to 10!', 'wp-statistics' );
	}

	if ( $WP_Statistics->get_option( 'prune_report' ) == true ) {
		$blogname  = get_bloginfo( 'name' );
		$blogemail = get_bloginfo( 'admin_email' );

		$headers[] = "From: $blogname <$blogemail>";
		$headers[] = "MIME-Version: 1.0";
		$headers[] = "Content-type: text/html; charset=utf-8";

		if ( $WP_Statistics->get_option( 'email_list' ) == '' ) {
			$WP_Statistics->update_option( 'email_list', $blogemail );
		}

		wp_mail( $WP_Statistics->get_option( 'email_list' ), __( 'Database pruned on', 'wp-statistics' ) . ' ' . $blogname, $result_string, $headers );
	}

	return $result_string;
}

?>