<?php
function wp_statistics_generate_top_visitors_postbox_content( $ISOCountryCode, $day = 'today', $count = 10, $compact = false ) {

	global $wpdb, $WP_Statistics;

	if ( $day == 'today' ) {
		$sql_time = $WP_Statistics->Current_Date( 'Y-m-d' );
	} else {
		$sql_time = date( 'Y-m-d', strtotime( $day ) );
	}

	?>
    <table width="100%" class="widefat table-stats" id="last-referrer">
        <tr>
            <td><?php _e( 'Rank', 'wp-statistics' ); ?></td>
            <td><?php _e( 'Hits', 'wp-statistics' ); ?></td>
            <td><?php _e( 'Flag', 'wp-statistics' ); ?></td>
            <td><?php _e( 'Country', 'wp-statistics' ); ?></td>
            <td><?php _e( 'IP', 'wp-statistics' ); ?></td>
			<?php if ( $compact == false ) { ?>
                <td><?php _e( 'Agent', 'wp-statistics' ); ?></td>
                <td><?php _e( 'Platform', 'wp-statistics' ); ?></td>
                <td><?php _e( 'Version', 'wp-statistics' ); ?></td>
			<?php } ?>
        </tr>

		<?php
		$result = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '{$sql_time}' ORDER BY hits DESC" );

		$i = 0;

		foreach ( $result as $visitor ) {
			$i ++;

			$item = strtoupper( $visitor->location );

			echo "<tr>";
			echo "<td>$i</td>";
			echo "<td>" . (int) $visitor->hits . "</td>";
			echo "<td><img src='" . plugins_url( 'wp-statistics/assets/images/flags/' . $item . '.png' ) . "' title='{$ISOCountryCode[$item]}'/></td>";
			echo "<td>{$ISOCountryCode[$item]}</td>";
			echo "<td>{$visitor->ip}</td>";

			if ( $compact == false ) {
				echo "<td>{$visitor->agent}</td>";
				echo "<td>{$visitor->platform}</td>";
				echo "<td>{$visitor->version}</td>";
			}
			echo "</tr>";

			if ( $i == $count ) {
				break;
			}
		}
		?>
    </table>
	<?php
}
