<?php
function wp_statistics_generate_users_online_postbox_content( $ISOCountryCode ) {
	global $wpdb, $WP_Statistics;

	$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_useronline` ORDER BY `ID` DESC LIMIT 10" );
	$i      = 0;
	if ( count( $result ) > 0 ) {

		?>
        <table width="100%" class="widefat table-stats" id="current_page">
        <tr>
            <td width="10%" style='text-align: left'><?php _e( 'Country', 'wp-statistics' ); ?></td>
            <td width="10%" style='text-align: left'><?php _e( 'IP', 'wp-statistics' ); ?></td>
            <td width="40%" style='text-align: left'><?php _e( 'Page', 'wp-statistics' ); ?></td>
            <td width="40%" style='text-align: left'><?php _e( 'Referrer', 'wp-statistics' ); ?></td>
        </tr>
		<?php
		foreach ( $result as $item ) {
			$i ++;

			//Get current Location info
			$item->location = strtoupper( $item->location );

			//Get current Page info
			$page_info = wp_statistics_get_page_info( $item->page_id, $item->type );

			echo "<tr>";
			echo "<td style='text-align: left'><img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $item->location . '.png' ) . "' title='{$ISOCountryCode[$item->location]}'/></td>";
			echo "<td style='text-align: left !important'>{$item->ip}</td>";
			echo "<td style='text-align: left !important'>" . ( $page_info['link'] != '' ? '<a href="' . $page_info['link'] . '" target="_blank">' : '' ) . mb_substr( $page_info['title'], 0, 200, "utf-8" ) . ( $page_info['link'] != '' ? '</a>' : '' ) . "</td>";
			echo "<td style='text-align: left !important'>" . $WP_Statistics->get_referrer_link( $item->referred ) . "</td>";
			echo "</tr>";
		}

		echo '</table>';

	} else {
		_e( "no online users yet.", 'wp-statistics' );
	}

}
