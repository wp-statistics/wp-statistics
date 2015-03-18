<?php
	function wp_statistics_generate_top_visitors_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Top 10 Visitors Today', 'wp_statistics'); ?> <a href="?page=wps_top_visitors_menu"> <?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
<?php								
					wp_statistics_generate_top_visitors_postbox_content($ISOCountryCode)
?>						
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_top_visitors_postbox_content($ISOCountryCode, $day='today', $count=10, $compact=false) {
	
		global $wpdb, $WP_Statistics;
		
		if( $day == 'today' ) { $sql_time = $WP_Statistics->Current_Date('Y-m-d'); } else { $sql_time = date( 'Y-m-d', strtotime( $day ) ); } 
		
?>
							<table width="100%" class="widefat table-stats" id="last-referrer">
								<tr>
									<td style='text-align: left'><?php _e('Rank', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Hits', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Flag', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Country', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('IP', 'wp_statistics'); ?></td>
<?php if( $compact == false ) { ?>
									<td style='text-align: left'><?php _e('Agent', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Platform', 'wp_statistics'); ?></td>
									<td style='text-align: left'><?php _e('Version', 'wp_statistics'); ?></td>
<?php } ?>
								</tr>
								
								<?php
									$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '{$sql_time}' ORDER BY hits DESC");
									
									$i = 0;
									
									foreach( $result as $visitor) {
										$i++;
										
										$item = strtoupper($visitor->location);
										
										echo "<tr>";
										echo "<td style='text-align: left'>$i</td>";
										echo "<td style='text-align: left'>" . (int)$visitor->hits . "</td>";
										echo "<td style='text-align: left'><img src='".plugins_url('wp-statistics/assets/images/flags/' . $item . '.png')."' title='{$ISOCountryCode[$item]}'/></td>";
										echo "<td style='text-align: left'>{$ISOCountryCode[$item]}</td>";
										echo "<td style='text-align: left'>{$visitor->ip}</td>";
										
										if( $compact == false ) {
											echo "<td style='text-align: left'>{$visitor->agent}</td>";
											echo "<td style='text-align: left'>{$visitor->platform}</td>";
											echo "<td style='text-align: left'>{$visitor->version}</td>";
										}
										echo "</tr>";
										
										if( $i == $count ) { break; }
									}
								?>
							</table>
<?php		
	}
