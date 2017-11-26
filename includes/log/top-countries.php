<script type="text/javascript">
	jQuery(document).ready(function () {
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
$daysToDisplay = 20;
if ( array_key_exists('hitdays', $_GET) ) {
	$daysToDisplay = intval($_GET['hitdays']);
}

if ( array_key_exists('rangestart', $_GET) ) {
	$rangestart = $_GET['rangestart'];
} else {
	$rangestart = '';
}
if ( array_key_exists('rangeend', $_GET) ) {
	$rangeend = $_GET['rangeend'];
} else {
	$rangeend = '';
}

list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator(
	$daysToDisplay,
	$rangestart,
	$rangeend
);

?>
<div class="wrap">
	<h2><?php _e('Top Countries', 'wp-statistics'); ?></h2>
	<?php wp_statistics_date_range_selector(WP_Statistics::$page['countries'], $daysToDisplay); ?>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<?php $paneltitle = __('Top Countries', 'wp-statistics'); ?>
					<button class="handlediv" type="button" aria-expanded="true">
						<span class="screen-reader-text"><?php printf(
								__('Toggle panel: %s', 'wp-statistics'),
								$paneltitle
							); ?></span>
						<span class="toggle-indicator" aria-hidden="true"></span>
					</button>
					<h2 class="hndle"><span><?php echo $paneltitle; ?></h2>

					<div class="inside">
						<table class="widefat table-stats" id="last-referrer" style="width: 100%;">
							<tr>
								<td><?php _e('Rank', 'wp-statistics'); ?></td>
								<td><?php _e('Flag', 'wp-statistics'); ?></td>
								<td><?php _e('Country', 'wp-statistics'); ?></td>
								<td><?php _e('Visitor Count', 'wp-statistics'); ?></td>
							</tr>

							<?php
							$ISOCountryCode = $WP_Statistics->get_country_codes();
							$rangestartdate = $WP_Statistics->real_current_date('Y-m-d', '-0', $rangestart_utime);
							$rangeenddate   = $WP_Statistics->real_current_date('Y-m-d', '-0', $rangeend_utime);

							$result = $wpdb->get_results(
								sprintf("SELECT `location`, COUNT(`location`) AS `count` FROM `{$wpdb->prefix}statistics_visitor` WHERE `last_counter` BETWEEN '%s' AND '%s' GROUP BY `location` ORDER BY `count` DESC",
									$rangestartdate,
									$rangeenddate
									)
								);
							$i = 0;

							foreach ( $result as $item ) {
								$i++;
								$item->location = strtoupper($item->location);

								echo "<tr>";
								echo "<td>$i</td>";
								echo "<td><img src='" .
								     plugins_url('wp-statistics/assets/images/flags/' . $item->location . '.png') .
								     "' title='{$ISOCountryCode[$item->location]}'/></td>";
								echo "<td style='direction: ltr;'>{$ISOCountryCode[$item->location]}</td>";
								echo "<td>" . number_format_i18n($item->count) . "</td>";
								echo "</tr>";
							}
							?>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
