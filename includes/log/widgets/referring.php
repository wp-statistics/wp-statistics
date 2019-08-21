<?php
function wp_statistics_generate_referring_postbox_content( $count = 10 ) {
	global $wpdb, $WP_Statistics;

	//Get Top Referring
	if ( false === ( $get_urls = get_transient( 'wps_top_referring' ) ) ) {

		//Get Wordpress Domain
		$where       = '';
		$domain_name = rtrim( preg_replace( '/^https?:\/\//', '', get_site_url() ), " / " );
		foreach ( array( "http", "https", "ftp" ) as $protocol ) {
			foreach ( array( '', 'www.' ) as $w3 ) {
				$where = " AND `referred` NOT LIKE '{$protocol}://{$w3}{$domain_name}%' ";
			}
		}
		$result = $wpdb->get_results( "SELECT SUBSTRING_INDEX(REPLACE( REPLACE( referred, 'http://', '') , 'https://' , '') , '/', 1 ) as `domain`, count(referred) as `number` FROM {$wpdb->prefix}statistics_visitor WHERE `referred` REGEXP \"^(https?://|www\\.)[\.A-Za-z0-9\-]+\\.[a-zA-Z]{2,4}\" AND referred <> '' AND LENGTH(referred) >=12 {$where} GROUP BY domain ORDER BY `number` DESC LIMIT $count" );
		foreach ( $result as $items ) {
			$get_urls[ $items->domain ] = wp_statistics_get_number_referer_from_domain( $items->domain );
		}

		// Put the results in a transient. Expire after 12 hours.
		set_transient( 'wps_top_referring', $get_urls, 12 * HOUR_IN_SECONDS );
	}
	?>
    <table width="100%" class="widefat table-stats" id="top-referrer">
        <tr>
            <td width="50%"><?php _e( 'Address', 'wp-statistics' ); ?></td>
            <td width="40%"><?php _e( 'Server IP', 'wp-statistics' ); ?></td>
            <td width="10%"><?php _e( 'References', 'wp-statistics' ); ?></td>
        </tr>
		<?php

		//Load country Code
		$ISOCountryCode = $WP_Statistics->get_country_codes();

		//Get Refer Site Detail
		$refer_opt     = get_option( 'wp_statistics_referrals_detail' );
		$referrer_list = ( empty( $refer_opt ) ? array() : $refer_opt );

		if ( ! $get_urls ) {
			return;
		}

		foreach ( $get_urls as $domain => $number ) {

			//Get Site Link
			$referrer_html = $WP_Statistics->html_sanitize_referrer( $domain );

			//Get Site information if Not Exist
			if ( ! array_key_exists( $domain, $referrer_list ) ) {
				$get_site_inf             = wp_statistics_get_domain_server( $domain );
				$get_site_title           = wp_statistics_get_site_title( $domain );
				$referrer_list[ $domain ] = array(
					'ip'      => $get_site_inf['ip'],
					'country' => $get_site_inf['country'],
					'title'   => ( $get_site_title === false ? '' : $get_site_title ),
				);
			}

			echo "<tr>";
			echo "<td>" . wp_statistics_show_site_icon( $domain ) . " " . $WP_Statistics->get_referrer_link( $domain, $referrer_list[ $domain ]['title'], true ) . "</td>";
			echo "<td><span class='wps-cursor-default' " . ( $referrer_list[ $domain ]['country'] != "" ? 'title="' . $ISOCountryCode[ $referrer_list[ $domain ]['country'] ] . '"' : '' ) . ">" . ( $referrer_list[ $domain ]['ip'] != "" ? $referrer_list[ $domain ]['ip'] : '-' ) . "</span></td>";
			echo "<td><a href='" . WP_Statistics_Admin_Pages::admin_url( 'referrers', array( 'referr' => $referrer_html ) ) . "'>" . number_format_i18n( $number ) . "</a></td>";
			echo "</tr>";
		}

		//Save Referrer List Update
		update_option( 'wp_statistics_referrals_detail', $referrer_list, 'no' );

		?>
    </table>
	<?php
}