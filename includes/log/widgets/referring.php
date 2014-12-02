<?php
	function wp_statistics_generate_referring_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;

		$result = $wpdb->get_results("SELECT `referred` FROM `{$table_prefix}statistics_visitor` WHERE referred <> ''");
		
		if( sizeof( $result ) > 0 ) {
			?>
			<div class="postbox">
				<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
				<h3 class="hndle">
					<span><?php _e('Top Referring Sites', 'wp_statistics'); ?></span> <a href="?page=wps_referers_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a>
				</h3>
				<div class="inside">
					<div class="inside">
					<?php wp_statistics_generate_referring_postbox_content($result); ?>
					</div>
				</div>
			</div>
<?php
		}
	}
	
	function wp_statistics_generate_referring_postbox_content($result, $count = 10) {
	
?>
						<table width="100%" class="widefat table-stats" id="last-referrer">
							<tr>
								<td width="10%"><?php _e('References', 'wp_statistics'); ?></td>
								<td width="90%"><?php _e('Address', 'wp_statistics'); ?></td>
							</tr>
							
							<?php
								
								$urls = array();
								foreach( $result as $items ) {
								
									$url = parse_url($items->referred);
									
									if( empty($url['host']) || stristr(get_bloginfo('url'), $url['host']) )
										continue;
										
									$urls[] = $url['host'];
								}
								
								$get_urls = array_count_values($urls);
								arsort( $get_urls );
								$get_urls = array_slice($get_urls, 0, $count);
								
								foreach( $get_urls as $items => $value) {
								
									echo "<tr>";
									echo "<td><a href='?page=wps_referers_menu&referr=" . htmlentities($items,ENT_QUOTES) . "'>" . number_format_i18n($value) . "</a></td>";
									echo "<td><a href='http://" . htmlentities($items,ENT_QUOTES) . "' target='_blank'>" . htmlentities($items,ENT_QUOTES) . " " . wp_statistics_icons('dashicons-admin-links', 'link') . "</a></td>";
									echo "</tr>";
								}
							?>
						</table>
<?php
	}	