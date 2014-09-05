<?php
	function wp_statistics_generate_recent_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Recent Visitors', 'wp_statistics'); ?> <a href="?page=wps_visitors_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							
<?php
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 10");
								
								echo "<div class='log-latest'>";

								$dash_icon = wp_statistics_icons('dashicons-visibility', 'visibility');
								
								foreach($result as $items) {
									if( substr( $items->ip, 0, 6 ) == '#hash#' ) { 
										$ip_string = __('#hash#', 'wp_statistics'); 
										$map_string = "";
									} 
									else { 
										$ip_string = "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&ip={$items->ip}'>{$dash_icon}{$items->ip}</a>"; 
										$map_string = "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='".__('Map', 'wp_statistics')."'>".wp_statistics_icons('dashicons-location-alt', 'map')."</a>";
									}
									
									echo "<div class='log-item'>";
										echo "<div class='log-referred'>{$ip_string}</div>";
										echo "<div class='log-ip'>{$items->last_counter}</div>";
										echo "<div class='clear'></div>";
										echo "<div class='log-url'>";
										echo $map_string;
										
										if($WP_Statistics->get_option('geoip')) {
											echo "<img src='".plugins_url('wp-statistics/assets/images/flags/' . $items->location . '.png')."' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
										}
										
										if( array_search( strtolower( $items->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/>";
										} else {
											$agent = wp_statistics_icons('dashicons-editor-help', 'unknown');
										}
										
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'>{$agent}</a>";
										
										echo "<a href='{$items->referred}' title='{$items->referred}'>".wp_statistics_icons('dashicons-admin-links', 'link')." ".$items->referred."</a></div>";
									echo "</div>";
								}
								
								echo "</div>";
							?>
					</div>
				</div>
<?php		
	}
