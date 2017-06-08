<?php
function wp_statistics_generate_recent_postbox_content( $ISOCountryCode, $count = 10 ) {
	global $wpdb, $WP_Statistics;

	$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC  LIMIT 0, {$count}" );

	echo "<div class='log-latest'>";

	$dash_icon = wp_statistics_icons( 'dashicons-visibility', 'visibility' );

	foreach ( $result as $items ) {
		if ( substr( $items->ip, 0, 6 ) == '#hash#' ) {
			$ip_string  = __( '#hash#', 'wp_statistics' );
			$map_string = "";
		} else {
			$ip_string  = "<a href='admin.php?page=" . WP_STATISTICS_VISITORS_PAGE . "&type=last-all-visitor&ip={$items->ip}'>{$dash_icon}{$items->ip}</a>";
			$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='" . __( 'Map', 'wp_statistics' ) . "'>" . wp_statistics_icons( 'dashicons-location-alt', 'map' ) . "</a>";
		}

		echo "<div class='log-item'>";
		echo "<div class='log-referred'>{$ip_string}</div>";
		echo "<div class='log-ip'>" . date( get_option( 'date_format' ), strtotime( $items->last_counter ) ) . "</div>";
		echo "<div class='clear'></div>";
		echo "<div class='log-url'>";
		echo $map_string;

		if ( $WP_Statistics->get_option( 'geoip' ) ) {
			echo "<img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $items->location . '.png' ) . "' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
		}

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

		echo $WP_Statistics->get_referrer_link( $items->referred );

		echo "</div>";
		echo "</div>";
	}

	echo "</div>";
}
