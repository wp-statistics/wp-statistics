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

	function wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php echo sprintf(__('About WP Statistics V%s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></span></h3>
					<div class="inside">
						<div style="text-align: center;">
							<a href="http://wp-statistics.com" target="_blank"><img src="<?php echo plugins_url('wp-statistics/assets/images/logo-250.png'); ?>"></a>
						</div>

						<div id="about-links" style="text-align: center;">
							<p><a href="http://wp-statistics.com" target="_blank"><?php _e('Website', 'wp_statistics'); ?></a></p>
							| <p><a href="http://wordpress.org/support/view/plugin-reviews/wp-statistics" target="_blank"><?php _e('Rate and Review', 'wp_statistics'); ?></a></p>
<?php
							if(current_user_can(wp_statistics_validate_capability($WP_Statistics->get_option('manage_capability', 'manage_options')))) {
?>
							| <p><a href="?page=wp-statistics/settings&tab=about"><?php _e('More Information', 'wp_statistics'); ?></a></p>
<?php
							}
?>
						</div>

						<hr />
						
						<div>
							<?php echo sprintf(__('This product includes GeoLite2 data created by MaxMind, available from %s.', 'wp_statistics'), '<a href="http://www.maxmind.com" target=_blank>http://www.maxmind.com</a>'); ?>
						</div>
					</div>
				</div>
<?php		
	}

