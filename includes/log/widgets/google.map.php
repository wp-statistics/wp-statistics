<?php
	function wp_statistics_generate_map_postbox($ISOCountryCode, $search_engines) {
	
		global $WP_Statistics;
		
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
	
		global $wpdb, $table_prefix, $WP_Statistics;
		
		// Some clients can't handle mixed http/https pages so check to see if the page we're on has http
		// enabled, if so, use https instead just in case for the Google script.
		$protocol = "http";
	
		if( array_key_exists( 'HTTPS', $_SERVER ) ) {
			if( $_SERVER['HTTPS'] == 'on' ) { $protocol .= 's'; }
		}
?>
					<script src="<?php echo $protocol; ?>://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
					<div id="map_canvas">Google Map</div>
					
					<?php $result = $wpdb->get_row("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE last_counter = '{$WP_Statistics->Current_Date('Y-m-d')}'"); ?>
					<script type="text/javascript">
						jQuery(document).ready(function(){
							var map_options = {
								center: new google.maps.LatLng("<?php echo wp_statistics_get_gmap_coordinate($result->location, 'lat'); ?>", "<?php echo wp_statistics_get_gmap_coordinate($result->location, 'lng'); ?>"),
								zoom: 3,
								mapTypeId: google.maps.MapTypeId.ROADMAP
							};
							
							var google_map = new google.maps.Map(document.getElementById("map_canvas"), map_options);
							
							var info_window = new google.maps.InfoWindow({
								content: 'loading'
							});
							
							var t = [];
							var x = [];
							var y = [];
							var h = [];
							
							<?php
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE last_counter = '{$WP_Statistics->Current_Date('Y-m-d')}'");
								$final_result = array();
								
								if( $result ) {
									foreach($result as $new_r) {
										$final_result[$new_r->location][] = array
										(
											'location' => $new_r->location,
											'agent' => $new_r->agent,
											'ip' => $new_r->ip
										);
									}
								}
								
								unset($final_result['000']);
								
								foreach($final_result as $items) {
								
									foreach($items as $markets) {
									
										if($markets['location'] == '000') continue;
										
										$flag = "<img src='".plugins_url('wp-statistics/assets/images/flags/' . $markets['location'] . '.png')."' title='{$ISOCountryCode[$markets['location']]}' class='log-tools'/> {$ISOCountryCode[$markets['location']]}";
										
										if( array_search( strtolower( $markets['agent'] ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
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
									?>
									
										t.push("<?php echo $ISOCountryCode[$markets['location']] . $summary; ?>");
										x.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lat'); ?>");
										y.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lng'); ?>");
										h.push("<div class='map-html-marker'><?php echo $flag . $summary . '<hr />' . $last_five; ?></div>");
										<?php
								}
							?>
							var i = 0;
							for ( item in t ) {
								var m = new google.maps.Marker({
									map:		google_map,
									animation:	google.maps.Animation.DROP,
									title:		t[i],
									position:	new google.maps.LatLng(x[i],y[i]),
									html:		h[i],
									icon:		'<?php echo plugins_url('wp-statistics/assets/images/marker.png'); ?>'
								});

								google.maps.event.addListener(m, 'click', function() {
									info_window.setContent(this.html);
									info_window.open(google_map, this);
								});
								i++;
							}
						
						});
					</script>
<?php 
	}