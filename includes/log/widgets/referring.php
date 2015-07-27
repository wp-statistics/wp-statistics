<?php
	function wp_statistics_generate_referring_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $WP_Statistics;

		if( $WP_Statistics->get_option( 'visitors' ) ) {
			?>
			<div class="postbox">
				<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
				<h3 class="hndle">
					<span><?php _e('Top Referring Sites', 'wp_statistics'); ?></span> <a href="?page=wps_referrers_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a>
				</h3>
				<div class="inside">
					<div class="inside">
					<?php wp_statistics_generate_referring_postbox_content(); ?>
					</div>
				</div>
			</div>
<?php
		}
	}
	
	function wp_statistics_generate_referring_postbox_content($count = 10) {
	
		global $wpdb, $WP_Statistics;
		
		if( $WP_Statistics->get_option('search_converted') ) {
			$result = $wpdb->get_results( "SELECT DISTINCT host FROM {$wpdb->prefix}statistics_search" );
			
			foreach( $result as $item ) {
				$get_urls[$item->host] = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}statistics_search WHERE host = '{$item->host}'" );
			}
			
		} else {
			$result = $wpdb->get_results( "SELECT referred FROM {$wpdb->prefix}statistics_visitor WHERE referred <> ''" );
			
			$urls = array();
			foreach( $result as $item ) {
			
				$url = parse_url($item->referred);
				
				if( empty($url['host']) || stristr(get_bloginfo('url'), $url['host']) )
					continue;
					
				$urls[] = $url['host'];
			}
			
			$get_urls = array_count_values($urls);
		}
	
		arsort( $get_urls );
		$get_urls = array_slice($get_urls, 0, $count);

?>
						<table width="100%" class="widefat table-stats" id="last-referrer">
							<tr>
								<td width="10%"><?php _e('References', 'wp_statistics'); ?></td>
								<td width="90%"><?php _e('Address', 'wp_statistics'); ?></td>
							</tr>
							
							<?php
							
								foreach( $get_urls as $items => $value) {
								
									echo "<tr>";
									echo "<td><a href='?page=wps_referrers_menu&referr=" . htmlentities($items,ENT_QUOTES) . "'>" . number_format_i18n($value) . "</a></td>";
									echo "<td><a href='http://" . htmlentities($items,ENT_QUOTES) . "' target='_blank'>" . htmlentities($items,ENT_QUOTES) . " " . wp_statistics_icons('dashicons-admin-links', 'link') . "</a></td>";
									echo "</tr>";
								}
							?>
						</table>
<?php
	}	