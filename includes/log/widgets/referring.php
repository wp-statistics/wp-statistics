<?php
function wp_statistics_generate_referring_postbox_content( $count = 10 ) {

	global $wpdb, $WP_Statistics;

	$result = $wpdb->get_results(
			"SELECT
			SUBSTRING_INDEX(
				REPLACE(REPLACE(REPLACE(`referred`, 'http://', ''), 'https://', ''), 'www.', ''),
				'/', 1
			) as 'ref',
			count(
				SUBSTRING_INDEX(
					REPLACE(REPLACE(REPLACE(`referred`, 'http://', ''), 'https://', ''), 'www.', ''),
					'/', 1
				)
			) as `count`
			FROM `{$wpdb->prefix}statistics_visitor`
			where `referred` <> ''
			group by `ref`
			order by `count` desc
			limit {$count}
		"
	);

	?>
	<table width="100%" class="widefat table-stats left-align" id="last-referrer">
		<tr>
			<td width="10%"><?php _e('References', 'wp-statistics'); ?></td>
			<td width="90%"><?php _e('Address', 'wp-statistics'); ?></td>
		</tr>

		<?php
		foreach ( $result as $item ) {

			echo "<tr>";
			echo "<td><a href='?page=" .
			     WP_Statistics::$page['referrers'] .
			     "&referr=" .
			     $item->ref .
			     "'>" .
			     number_format_i18n($item->count) .
			     "</a></td>";
			echo "<td>" . $WP_Statistics->get_referrer_link($item->ref) . "</td>";
			echo "</tr>";
		}
		?>
	</table>
	<?php
}	
