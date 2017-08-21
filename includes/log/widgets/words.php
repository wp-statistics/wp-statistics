<?php
function wp_statistics_generate_words_postbox_content( $ISOCountryCode, $count = 10 ) {

	global $wpdb, $WP_Statistics;

	// Retrieve MySQL data for the search words.
	$search_query = wp_statistics_searchword_query( 'all' );

	// Determine if we're using the old or new method of storing search engine info and build the appropriate table name.
	$tablename = $wpdb->prefix . 'statistics_';

	if ( $WP_Statistics->get_option( 'search_converted' ) ) {
		$tabletwo = $tablename . 'visitor';
		$tablename .= 'search';
		$result = $wpdb->get_results( "SELECT * FROM `{$tablename}` INNER JOIN `{$tabletwo}` on {$tablename}.`visitor` = {$tabletwo}.`ID` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT 0, {$count}" );
	} else {
		$tablename .= 'visitor';
		$result    = $wpdb->get_results( "SELECT * FROM `{$tablename}` WHERE {$search_query} ORDER BY `{$tablename}`.`ID` DESC  LIMIT 0, {$count}" );
	}

	if ( sizeof( $result ) > 0 ) {
		echo "<div class='log-latest'>";

		foreach ( $result as $items ) {
			if ( ! $WP_Statistics->Search_Engine_QueryString( $items->referred ) ) {
				continue;
			}

			if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
				$ip_string = __( '#hash#', 'wp-statistics' );
			} else {
				$ip_string = "<a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a>";
			}

			if ( $WP_Statistics->get_option( 'search_converted' ) ) {
				$this_search_engine = $WP_Statistics->Search_Engine_Info_By_Engine( $items->engine );
				$words              = $items->words;
			} else {
				$this_search_engine = $WP_Statistics->Search_Engine_Info( $items->referred );
				$words              = $WP_Statistics->Search_Engine_QueryString( $items->referred );
			}

			echo "<div class='log-item'>";
			echo "<div class='log-referred'>" . $words . "</div>";
			echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $items->last_counter ) ) . " - {$ip_string}</div>";
			echo "<div class='clear'></div>";
			echo "<div class='log-url'>";
			echo "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp-statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";

			if ( $WP_Statistics->get_option( 'geoip' ) ) {
				echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
			}

			$this_search_engine = $WP_Statistics->Search_Engine_Info( $items->referred );
			echo "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-search&referred={$this_search_engine['tag']}'><img src='" . plugins_url( 'wp-statistics/assets/images/' . $this_search_engine['image'] ) . "' class='log-tools' title='" . $this_search_engine['translated'] . "'/></a>";

			if ( array_search( strtolower( $items->agent ), array(
					"chrome",
					"firefox",
					"msie",
					"opera",
					"safari"
				) ) !== false
			) {
				$agent = "<img src='" . plugins_url( 'wp-statistics/assets/images/' ) . $items->agent . ".png' class='log-tools' title='{$items->agent}'/>";
			} else {
				$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
			}

			echo "<a href='?page=" . WP_STATISTICS_OVERVIEW_PAGE . "&type=last-all-visitor&agent={$items->agent}'>{$agent}</a>";

			$referrer_html = $WP_Statistics->html_sanitize_referrer( $items->referred );

			echo "<a href='" . $referrer_html . "' title='" . $referrer_html . "'>" . wp_statistics_icons( 'dashicons-admin-links', 'link' ) . " " . $referrer_html . "</a></div>";
			echo "</div>";
		}

		echo "</div>";
	}
}

