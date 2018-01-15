<?php
function wp_statistics_generate_searched_phrases_postbox_content() {

	global $wpdb;

	$result = $wpdb->get_results(
		"SELECT `words` , count(`words`) as `count` FROM `{$wpdb->prefix}statistics_search` WHERE `words` <> '' AND `last_counter` BETWEEN DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND CURDATE() GROUP BY `words` order by `count` DESC limit 10"
	);

	?>
    <table width="100%" class="widefat table-stats" id="searched-phrases">
        <tr>
            <td width="90%"><?php _e( 'Phrase', 'wp-statistics' ); ?></td>
            <td width="10%"><?php _e( 'Count', 'wp-statistics' ); ?></td>
        </tr>

		<?php

		foreach ( $result as $item ) {

			echo "<tr>";
			echo "<td>{$item->words}</td>";
			echo "<td>{$item->count}</td>";
			echo "</tr>";
		}
		?>
    </table>
	<?php
}