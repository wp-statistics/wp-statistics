<?php
	function wp_statistics_generate_map_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $WP_Statistics;
		
		if($WP_Statistics->get_option('geoip') && !$WP_Statistics->get_option('disable_map') ) { ?>
			<div class="postbox">
				<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
				<h3 class="hndle"><span><?php _e('Today Visitors Map', 'wp_statistics'); ?></span></h3>
				<div class="inside">
				<?php wp_statistics_generate_map_postbox_content($ISOCountryCode); ?>
				</div>
			</div>
<?php 
		}
	}
	
	function wp_statistics_generate_map_postbox_content($ISOCountryCode) {
	
		global $wpdb, $WP_Statistics;
		
		if($WP_Statistics->get_option('geoip') && !$WP_Statistics->get_option('disable_map') ) { ?>
					<div id="map_canvas"></div>
					
					<?php $result = $wpdb->get_row("SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '{$WP_Statistics->Current_Date('Y-m-d')}'"); ?>
					<script type="text/javascript">
						var country_pin = Array();
						var country_color = Array();
					
						jQuery(document).ready(function(){
						
							<?php
								$result = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE last_counter = '{$WP_Statistics->Current_Date('Y-m-d')}'");
								$final_result = array();
								$final_result['000'] = array();
								
								if( $result ) {
									foreach($result as $new_r) {
										$new_r->location = strtolower( $new_r->location );
										
										$final_result[$new_r->location][] = array
										(
											'location' => $new_r->location,
											'agent' => $new_r->agent,
											'ip' => $new_r->ip
										);
									}
								}

								$final_total = count( $result ) - count( $final_result['000'] );
								
								unset($final_result['000']);
								
								$startColor = array(200, 238, 255);
								$endColor = array(0, 100, 145);
								
								foreach($final_result as $items) {
								
									foreach($items as $markets) {
									
										if($markets['location'] == '000') continue;
										
										$flag = "<img src='".plugins_url('wp-statistics/assets/images/flags/' . strtoupper($markets['location']) . '.png')."' title='{$ISOCountryCode[strtoupper($markets['location'])]}' class='log-tools'/> {$ISOCountryCode[strtoupper($markets['location'])]}";
										
										if( array_search( strtolower($markets['agent']), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/').$markets['agent'].".png' class='log-tools' title='{$markets['agent']}'/>";
										} else {
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/unknown.png')."' class='log-tools' title='{$markets['agent']}'/>";
										}
										
										if( substr( $markets['ip'], 0, 6 ) == '#hash#' ) { $markets['ip'] = __('#hash#', 'wp_statistics'); } 
									
										$get_ipp[$markets['location']][] = "<p>{$agent} {$markets['ip']}</p>";
									}

									$market_total = count($get_ipp[$markets['location']]);
									$last_five = "";
									
									// Only show the last five visitors, more just makes the map a mess.
									for( $i = $market_total; $i > $market_total - 6; $i-- ) {
										if( array_key_exists( $i, $get_ipp[$markets['location']]) ) {
											$last_five .= $get_ipp[$markets['location']][$i];
										}
									}									
									
									$summary = ' [' . $market_total . ']';
									
									$color = sprintf( "#%02X%02X%02X", round($startColor[0] + ($endColor[0] - $startColor[0]) * $market_total / $final_total), round($startColor[1] + ($endColor[1] - $startColor[1]) * $market_total / $final_total), round($startColor[2] + ($endColor[2] - $startColor[2]) * $market_total / $final_total));
							?>
							country_pin['<?php echo $markets['location'];?>'] = "<div class='map-html-marker'><?php echo $flag . $summary . '<hr />' . $last_five; ?></div>";
							country_color['<?php echo $markets['location'];?>'] = "<?php echo $color;?>";
							<?php
								}
							?>
							var data_total = <?php echo $final_total;?>;
						
							jQuery('#map_canvas').vectorMap({ 
								map: 'world_en', 
							    colors: country_color,
								onLabelShow: function(element, label, code)
									{
										if( country_pin[code] !== undefined ) 
											{
											label.html( country_pin[code] );
											}
										else
											{
											label.html( label.html() + ' [0]<hr />');
											}
									},
							});

						
						});
					</script>
<?php 
		}
	}