<?php
function wp_statistics_generate_referring_postbox_content( $count = 10 ) {
	global $wpdb, $WP_Statistics;

	//Get Top Referring
	if ( false === ( $get_urls = get_transient( 'wps_top_referring' ) ) ) {

		//Get Wordpress Domain
		$site_url = wp_parse_url( get_site_url() );
		$site_url = $site_url['scheme'] . "://" . $site_url['host'];
		$result   = $wpdb->get_results( "SELECT SUBSTRING_INDEX(REPLACE( REPLACE( referred, 'http://', '') , 'https://' , '') , '/', 1 ) as `domain`, count(referred) as `number` FROM {$wpdb->prefix}statistics_visitor WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 AND `referred` NOT LIKE '{$site_url}%' GROUP BY domain ORDER BY `number` DESC LIMIT $count" );
		foreach ( $result as $items ) {
			$get_urls[ $items->domain ] = $items->number;
		}

		// Put the results in a transient. Expire after 12 hours.
		set_transient( 'wps_top_referring', $get_urls, 12 * HOUR_IN_SECONDS );
	}
	?>
    <table width="100%" class="widefat table-stats" id="top-referrer">
        <tr>
            <td width="10%"><?php _e( 'References', 'wp-statistics' ); ?></td>
            <td width="90%"><?php _e( 'Address', 'wp-statistics' ); ?></td>
        </tr>

		<?php
		foreach ( $get_urls as $domain => $number ) {
			$referrer_html = $WP_Statistics->html_sanitize_referrer( $domain );
			echo "<tr>";
			echo "<td><a href='" . WP_Statistics_Admin_Pages::admin_url( 'referrers', array( 'referr' => $referrer_html ) ) . "'>" . number_format_i18n( $number ) . "</a></td>";
			echo "<td>" . $WP_Statistics->get_referrer_link( $domain ) . "</td>";
			echo "</tr>";
		}
		?>
    </table>
	<?php
}