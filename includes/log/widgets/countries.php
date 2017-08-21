<?php
function wp_statistics_generate_countries_postbox_content( $ISOCountryCode, $count = 10 ) {

	global $wpdb, $WP_Statistics;

	?>
    <table width="100%" class="widefat table-stats" id="last-referrer">
        <tr>
            <td width="10%" style='text-align: left'><?php _e( 'Rank', 'wp-statistics' ); ?></td>
            <td width="10%" style='text-align: left'><?php _e( 'Flag', 'wp-statistics' ); ?></td>
            <td width="40%" style='text-align: left'><?php _e( 'Country', 'wp-statistics' ); ?></td>
            <td width="40%" style='text-align: left'><?php _e( 'Visitor Count', 'wp-statistics' ); ?></td>
        </tr>

		<?php
		$Countries = array();

		$result = $wpdb->get_results( "SELECT DISTINCT `location` FROM `{$wpdb->prefix}statistics_visitor`" );

		foreach ( $result as $item ) {
			$Countries[ $item->location ] = $wpdb->get_var( $wpdb->prepare( "SELECT count(location) FROM `{$wpdb->prefix}statistics_visitor` WHERE location=%s", $item->location ) );
		}

		arsort( $Countries );
		$i = 0;

		foreach ( $Countries as $item => $value ) {
			$i ++;

			$item = strtoupper( $item );

			echo "<tr>";
			echo "<td style='text-align: left'>$i</td>";
			echo "<td style='text-align: left'><img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $item . '.png' ) . "' title='{$ISOCountryCode[$item]}'/></td>";
			echo "<td style='text-align: left'>{$ISOCountryCode[$item]}</td>";
			echo "<td style='text-align: left'>" . number_format_i18n( $value ) . "</td>";
			echo "</tr>";

			if ( $i == $count ) {
				break;
			}
		}
		?>
    </table>
	<?php
}

