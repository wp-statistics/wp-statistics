<?php
function wp_statistics_generate_recent_postbox_content( $ISOCountryCode, $count = 10 ) {
	global $wpdb, $WP_Statistics;

	$result = $wpdb->get_results(
		"SELECT * FROM `{$wpdb->prefix}statistics_visitor` ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT 0, {$count}"
	);

	echo "<div class=\"wp-statistics-table\">";
	echo "<table width=\"100%\" class=\"widefat table-stats\" id=\"last-referrer\">
		  <tr>";
	echo "<td>" . __( 'Browser', 'wp-statistics' ) . "</td>";
	if ( $WP_Statistics->get_option( 'geoip' ) ) {
		echo "<td>" . __( 'Country', 'wp-statistics' ) . "</td>";
	}
	if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
		echo "<td>" . __( 'City', 'wp-statistics' ) . "</td>";
	}
	echo "<td>" . __( 'Date', 'wp-statistics' ) . "</td>";
	echo "<td>" . __( 'IP', 'wp-statistics' ) . "</td>";
	echo "<td>" . __( 'Referrer', 'wp-statistics' ) . "</td>";
	echo "</tr>";

	// Load city name
	$geoip_reader = false;
	if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
		$geoip_reader = $WP_Statistics::geoip_loader( 'city' );
	}

	foreach ( $result as $items ) {
		echo "<tr>";
		echo "<td style=\"text-align: left\">";
		if ( array_search(
			     strtolower( $items->agent ),
			     array(
				     "chrome",
				     "firefox",
				     "msie",
				     "opera",
				     "safari",
			     )
		     ) !== false
		) {
			$agent = "<img src='" .
			         plugins_url( 'wp-statistics/assets/images/' ) .
			         $items->agent .
			         ".png' class='log-tools' title='{$items->agent}'/>";
		} else {
			$agent = wp_statistics_icons( 'dashicons-editor-help', 'unknown' );
		}
		echo "<a href='?page=" .
		     WP_Statistics::$page['overview'] .
		     "&type=last-all-visitor&agent={$items->agent}'>{$agent}</a>";
		echo "</td>";
		$city = '';
		if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
			if ( $geoip_reader != false ) {
				try {
					$reader = $geoip_reader->city( $items->ip );
					$city   = $reader->city->name;
				} catch ( Exception $e ) {
					$city = __( 'Unknown', 'wp-statistics' );
				}

				if ( ! $city ) {
					$city = __( 'Unknown', 'wp-statistics' );
				}
			}
		}

		if ( $WP_Statistics->get_option( 'geoip' ) ) {
			echo "<td style=\"text-align: left\">";
			echo "<img src='" .
			     plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) .
			     "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
			echo "</td>";
		}

		if ( $WP_Statistics->get_option( 'geoip_city' ) ) {
			echo "<td style=\"text-align: left\">";
			echo $city;
			echo "</td>";
		}

		echo "<td style=\"text-align: left\">";
		echo date( get_option( 'date_format' ), strtotime( $items->last_counter ) );
		echo "</td>";

		echo "<td style=\"text-align: left\">";
		if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
			$ip_string = __( '#hash#', 'wp-statistics' );
		} else {
			$ip_string = "<a href='admin.php?page=" .
			             WP_Statistics::$page['visitors'] .
			             "&type=last-all-visitor&ip={$items->ip}'>{$items->ip}</a>";
		}
		echo $ip_string;
		echo "</td>";
		echo "<td style=\"text-align: left\">" . $WP_Statistics->get_referrer_link( $items->referred ) . "</td>";
		echo "</tr>";
	}

	echo "</table>";
	echo "</div>";
}
