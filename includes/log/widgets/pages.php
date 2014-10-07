<?php
	function wp_statistics_generate_pages_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;

		list( $total, $uris ) = wp_statistics_get_top_pages();
				
		if( $total > 0 ) {
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Top 10 Pages', 'wp_statistics'); ?> <a href="?page=wps_pages_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							<?php
								// Retrieve data
								
								echo "<div class='log-latest'>";
								
								$i = 0;
								
								foreach($uris as $uri) {
									$i++;
									echo "<div class='log-item'>";

									if( $uri[3] == '' ) { $uri[3] = '[' . __('No page title found', 'wp_statistics') . ']'; }
									
									echo "<div class='log-page-title'>{$i} - {$uri[3]}</div>";
									echo "<div class='right-div'>".__('Visits', 'wp_statistics').": <a href='?page=wps_pages_menu&page-uri={$uri[0]}'>" . number_format_i18n($uri[1]) . "</a></div>";
									echo "<div class='left-div'><a dir='ltr' href='{$uri[0]}'>".urldecode($uri[0])."</a></div>";
									echo "</div>";
									
									if( $i > 9 ) { break; }
								}
								
								echo "</div>";
?>
					</div>
				</div>
<?php		
		}
	}

