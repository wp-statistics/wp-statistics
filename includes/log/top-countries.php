<script type="text/javascript">
	jQuery(document).ready(function(){
	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php include_once( dirname( __FILE__ ) . "/../functions/country-codes.php" ); ?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Top Countries', 'wp_statistics'); ?></h2>
	<div class="postbox-container" id="last-log" style="width: auto;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="inside">
						<div class="inside">
							<table class="widefat table-stats" id="last-referrer" style="width: auto;">
								<tr>
									<td style='text-align: left'><?php _e('Rank', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Flag', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Country', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Visitor Count', 'wp_statistics'); ?></td>
								</tr>
								
								<?php
									$result = $wpdb->get_results("SELECT DISTINCT `location` FROM `{$table_prefix}statistics_visitor`");
									
									foreach( $result as $item )
										{
										$Countries[$item->location] = $wpdb->get_var("SELECT count(location) FROM `{$table_prefix}statistics_visitor` WHERE location='" . $item->location . "'" );
										}
										
									arsort($Countries);
									$i = 0;
									
									foreach( $Countries as $item => $value) {
										$i++;
										
										echo "<tr>";
										echo "<td style='text-align: left'>$i</td>";
										echo "<td style='text-align: left'><img src='".plugins_url('wp-statistics/images/flags/' . $item . '.png')."' title='".__($ISOCountryCode[$item], 'wp_statistics')."'/></td>";
										echo "<td style='text-align: left'>{$ISOCountryCode[$item]}</td>";
										echo "<td style='text-align: left'>{$value}</td>";
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