<?php
function wp_statistics_purge_data( $purge_days ) {
	GLOBAL $wpdb, $WP_Statistics;

	// If it's less than 30 days, don't do anything.
	if ( $purge_days > 30 ) {
		// Purge the visit data.
		$table_name  = $wpdb->prefix . 'statistics_visit';
		$date_string = $WP_Statistics->current_date( 'Y-m-d', '-' . $purge_days );

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE `last_counter` < %s", $date_string ) );

		if ( $result ) {
			// Update the historical count with what we purged.
			$historical_result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_historical SET value = value + %d WHERE `category` = 'visits'", $result ) );

			if ( $historical_result == 0 ) {
				$wpdb->insert( $wpdb->prefix . "statistics_historical", array( 'value'    => $result,
				                                                               'category' => 'visits',
				                                                               'page_id'  => - 2,
				                                                               'uri'      => '-2'
				) );
			}

			$result_string = sprintf( __( '%s data older than %s days purged successfully.', 'wp_statistics' ), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>' );
		} else {
			$result_string = sprintf( __( 'No records found to purge from %s!', 'wp_statistics' ), '<code>' . $table_name . '</code>' );
		}

		// Purge the visitors data.
		$table_name = $wpdb->prefix . 'statistics_visitor';

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE `last_counter` < %s", $date_string ) );

		if ( $result ) {
			// Update the historical count with what we purged.
			$historical_result = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_historical SET value = value + %d WHERE `category` = 'visitors'", $result ) );

			if ( $historical_result == 0 ) {
				$wpdb->insert( $wpdb->prefix . "statistics_historical", array( 'value'    => $result,
				                                                               'category' => 'visitors',
				                                                               'page_id'  => - 1,
				                                                               'uri'      => '-1'
				) );
			}

			$result_string .= '<br>' . sprintf( __( '%s data older than %s days purged successfully.', 'wp_statistics' ), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>' );
		} else {
			$result_string .= '<br>' . sprintf( __( 'No records found to purge from %s!', 'wp_statistics' ), '<code>' . $table_name . '</code>' );
		}

		// Purge the exclusions data.
		$table_name = $wpdb->prefix . 'statistics_exclusions';

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE `date` < %s", $date_string ) );

		if ( $result ) {
			$result_string .= '<br>' . sprintf( __( '%s data older than %s days purged successfully.', 'wp_statistics' ), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>' );
		} else {
			$result_string .= '<br>' . sprintf( __( 'No records found to purge from %s!', 'wp_statistics' ), '<code>' . $table_name . '</code>' );
		}

		// Purge the search data.
		$table_name = $wpdb->prefix . 'statistics_search';

		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE `last_counter` < %s", $date_string ) );

		if ( $result ) {
			$result_string .= '<br>' . sprintf( __( '%s data older than %s days purged successfully.', 'wp_statistics' ), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>' );
		} else {
			$result_string .= '<br>' . sprintf( __( 'No records found to purge from %s!', 'wp_statistics' ), '<code>' . $table_name . '</code>' );
		}

		// Purge the pages data, this is more complex as we want to save the historical data per page.
		$table_name = $wpdb->prefix . 'statistics_pages';
		$historical = 0;

		// The first thing we need to do is update the historical data by finding all the unique pages.
		$result = $wpdb->get_results( $wpdb->prepare( "SELECT DISTINCT uri FROM {$table_name} WHERE `date` < %s", $date_string ) );

		// If we have a result, let's store the historical data.
		if ( $result ) {
			// Loop through all the unique rows that were returned.
			foreach ( $result as $row ) {
				// Use the unique rows to get a total count from the database of all the data from the given URIs/Pageids that we're going to delete later.
				$historical = $wpdb->get_var( $wpdb->prepare( "SELECT sum(count) FROM {$table_name} WHERE `uri` = %s AND `date` < %s", $row->uri, $date_string ) );

				// Do an update of the historical data.
				$uresult = $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}statistics_historical SET `value` = value + %d WHERE `uri` = %s AND `category` = 'uri'", $historical, $row->uri, $date_string ) );

				// If we failed it's because this is the first time we've seen this URI/pageid so let's create a historical row for it.
				if ( $uresult == 0 ) {
					$wpdb->insert( $wpdb->prefix . "statistics_historical", array( 'value'    => $historical,
					                                                               'category' => 'uri',
					                                                               'uri'      => $row->uri,
					                                                               'page_id'  => wp_statistics_uri_to_id( $row->uri )
					) );
				}
			}
		}

		// Now that we've done all of the required historical data storage, we can actually delete the data from the database.
		$result = $wpdb->query( $wpdb->prepare( "DELETE FROM {$table_name} WHERE `date` < %s", $date_string ) );

		if ( $result ) {
			$result_string .= '<br>' . sprintf( __( '%s data older than %s days purged successfully.', 'wp_statistics' ), '<code>' . $table_name . '</code>', '<code>' . $purge_days . '</code>' );
		} else {
			$result_string .= '<br>' . sprintf( __( 'No records found to purge from %s!', 'wp_statistics' ), '<code>' . $table_name . '</code>' );
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

			wp_mail( $WP_Statistics->get_option( 'email_list' ), __( 'Database pruned on', 'wp_statistics' ) . ' ' . $blogname, $result_string, $headers );
		}

		return $result_string;
	} else {
		return __( 'Please select a value over 30 days.', 'wp_statistics' );
	}
}

?>