<?php
	function wp_statistics_generate_words_postbox($ISOCountryCode, $search_engines) {
	
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Latest Search Words', 'wp_statistics'); ?> <a href="?page=wps_words_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
					<?php wp_statistics_generate_words_postbox_content($ISOCountryCode); ?>
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_words_postbox_content($ISOCountryCode, $count = 10) {
	
		global $wpdb, $table_prefix, $WP_Statistics;

		// Retrieve MySQL data for the search words.
		$search_query = wp_statistics_searchword_query('all');

		$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE {$search_query} ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, {$count}");
		
		if( sizeof($result) > 0 ) {
			echo "<div class='log-latest'>";
			
			foreach($result as $items) {
				if( !$WP_Statistics->Search_Engine_QueryString($items->referred) ) continue;
				
				if( substr( $items->ip, 0, 6 ) == '#hash#' ) { $ip_string = __('#hash#', 'wp_statistics'); } else { $ip_string = "<a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a>"; }
				
				echo "<div class='log-item'>";
					echo "<div class='log-referred'>".$WP_Statistics->Search_Engine_QueryString($items->referred)."</div>";
					echo "<div class='log-ip'>{$items->last_counter} - {$ip_string}</div>";
					echo "<div class='clear'></div>";
					echo "<div class='log-url'>";
					echo "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='".__('Map', 'wp_statistics')."'>".wp_statistics_icons('dashicons-location-alt', 'map')."</a>";
					
					if($WP_Statistics->get_option('geoip')) {
						echo "<img src='".plugins_url('wp-statistics/assets/images/flags/' . $items->location . '.png')."' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
					}
					
					$this_search_engine = $WP_Statistics->Search_Engine_Info($items->referred);
					echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred={$this_search_engine['tag']}'><img src='".plugins_url('wp-statistics/assets/images/' . $this_search_engine['image'])."' class='log-tools' title='".__($this_search_engine['name'], 'wp_statistics')."'/></a>";
					
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
		}
	}

