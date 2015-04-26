<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Top Countries', 'wp_statistics'); ?></h2>
	<div class="postbox-container" id="last-log" style="width: 100%;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="inside">
						<div class="inside">
							<table class="widefat table-stats" id="last-referrer" style="width: 100%;">
								<tr>
									<td><?php _e('Rank', 'wp_statistics'); ?></td>
									<td><?php _e('Flag', 'wp_statistics'); ?></td>
									<td><?php _e('Country', 'wp_statistics'); ?></td>
									<td><?php _e('Visitor Count', 'wp_statistics'); ?></td>
								</tr>
								
								<?php
									$ISOCountryCode = $WP_Statistics->get_country_codes();
									
									$result = $wpdb->get_results("SELECT DISTINCT `location` FROM `{$wpdb->prefix}statistics_visitor`");
									
									foreach( $result as $item )
										{
										$Countries[$item->location] = $wpdb->get_var("SELECT count(location) FROM `{$wpdb->prefix}statistics_visitor` WHERE location='" . $item->location . "'" );
										}
										
									arsort($Countries);
									$i = 0;
									
									foreach( $Countries as $item => $value) {
										$i++;

										$item = strtoupper($item);
										
										echo "<tr>";
										echo "<td style='text-align: center;'>$i</td>";
										echo "<td style='text-align: center;'><img src='".plugins_url('wp-statistics/assets/images/flags/' . $item . '.png')."' title='{$ISOCountryCode[$item]}'/></td>";
										echo "<td style='text-align: left; direction: ltr;'>{$ISOCountryCode[$item]}</td>";
										echo "<td style='text-align: center;'>" . number_format_i18n($value) . "</td>";
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
</div>