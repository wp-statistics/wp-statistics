<?php
function wp_statistics_generate_countries_postbox_content() {
	global $wpdb, $WP_Statistics;
	$ISOCountryCode = $WP_Statistics->get_country_codes();
	?>
	<table width="100%" class="widefat table-stats" id="last-referrer">
		<tr>
			<td width="10%" style='text-align: left'><?php _e('Rank', 'wp-statistics'); ?></td>
			<td width="10%" style='text-align: left'><?php _e('Flag', 'wp-statistics'); ?></td>
			<td width="40%" style='text-align: left'><?php _e('Country', 'wp-statistics'); ?></td>
			<td width="40%" style='text-align: left'><?php _e('Visitor Count', 'wp-statistics'); ?></td>
		</tr>
		<?php
		$result = $wpdb->get_results("SELECT `location`, COUNT(`location`) AS `count` FROM `{$wpdb->prefix}statistics_visitor` GROUP BY `location` ORDER BY `count` DESC LIMIT 10");
		$i = 0;
		foreach ( $result as $item ) {
			$i++;
			$item->location = strtoupper($item->location);
			echo "<tr>";
			echo "<td style='text-align: left'>$i</td>";
			echo "<td style='text-align: left'><img src='" .
			     plugins_url('wp-statistics/assets/images/flags/' . $item->location . '.png') .
			     "' title='{$ISOCountryCode[$item->location]}'/></td>";
			echo "<td style='text-align: left !important'>{$ISOCountryCode[$item->location]}</td>";
			echo "<td style='text-align: left !important'>" . number_format_i18n($item->count) . "</td>";
			echo "</tr>";
		}
		?>
	</table>
	<?php
}
