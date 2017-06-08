<?php
function wp_statistics_generate_referring_postbox_content( $count = 10 ) {

	global $wpdb, $WP_Statistics;

	$get_urls = array();
	$urls     = array();
	$start    = 0;

	do {
		$result = $wpdb->get_results( "SELECT referred FROM {$wpdb->prefix}statistics_visitor WHERE referred <> '' LIMIT {$start}, 10000" );

		$start += count( $result );

		foreach ( $result as $item ) {

			$url = parse_url( $item->referred );

			if ( empty( $url['host'] ) || stristr( get_bloginfo( 'url' ), $url['host'] ) ) {
				continue;
			}

			$urls[] = $url['host'];
		}

	} while ( 10000 == count( $result ) );

	$get_urls = array_count_values( $urls );

	arsort( $get_urls );
	$get_urls = array_slice( $get_urls, 0, $count );

	?>
    <table width="100%" class="widefat table-stats" id="last-referrer">
        <tr>
            <td width="10%"><?php _e( 'References', 'wp_statistics' ); ?></td>
            <td width="90%"><?php _e( 'Address', 'wp_statistics' ); ?></td>
        </tr>

		<?php

		foreach ( $get_urls as $items => $value ) {

			$referrer_html = $WP_Statistics->html_sanitize_referrer( $items );

			echo "<tr>";
			echo "<td><a href='?page=" . WP_STATISTICS_REFERRERS_PAGE . "&referr=" . $referrer_html . "'>" . number_format_i18n( $value ) . "</a></td>";
			echo "<td>" . $WP_Statistics->get_referrer_link( $items ) . "</td>";
			echo "</tr>";
		}
		?>
    </table>
	<?php
}	