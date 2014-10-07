<?php
	function wp_statistics_generate_countries_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;

		if( $WP_Statistics->get_option('geoip') ) { 
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Top 10 Countries', 'wp_statistics'); ?> <a href="?page=wps_countries_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
						<div class="inside">
							<table width="100%" class="widefat table-stats" id="last-referrer">
								<tr>
									<td width="10%" style='text-align: left'><?php _e('Rank', 'wp_statistics'); ?></td>
									<td width="10%" style='text-align: left'><?php _e('Flag', 'wp_statistics'); ?></td>
									<td width="40%" style='text-align: left'><?php _e('Country', 'wp_statistics'); ?></td>
									<td width="40%" style='text-align: left'><?php _e('Visitor Count', 'wp_statistics'); ?></td>
								</tr>
								
								<?php
									$Countries = array();
									
									$result = $wpdb->get_results("SELECT DISTINCT `location` FROM `{$table_prefix}statistics_visitor`");
									
									foreach( $result as $item )
										{
										$Countries[$item->location] = $wpdb->get_var("SELECT count(location) FROM `{$table_prefix}statistics_visitor` WHERE location='" . $item->location . "'" );
										}
										
									arsort($Countries);
									$i = 0;
									
									foreach( $Countries as $item => $value) {
										$i++;
										
										$item = strtoupper($item);
										
										echo "<tr>";
										echo "<td style='text-align: left'>$i</td>";
										echo "<td style='text-align: left'><img src='".plugins_url('wp-statistics/assets/images/flags/' . $item . '.png')."' title='{$ISOCountryCode[$item]}'/></td>";
										echo "<td style='text-align: left'>{$ISOCountryCode[$item]}</td>";
										echo "<td style='text-align: left'>" . number_format_i18n($value) . "</td>";
										echo "</tr>";
										
										if( $i == 10 ) { break; }
									}
								?>
							</table>
						</div>
					</div>
				</div>
<?php 
		}
	}

