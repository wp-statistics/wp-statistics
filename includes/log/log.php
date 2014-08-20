<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php 
	include_once( dirname( __FILE__ ) . "/../functions/country-codes.php");
	
	$search_engines = wp_statistics_searchengine_list();
	
	$search_result['All'] = wp_statistics_searchengine('all','total');
	
	foreach( $search_engines as $key => $se ) {
		$search_result[$key] = wp_statistics_searchengine($key,'total');
	}

?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<div class="postbox-container" id="right-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">

			<?php $ret  = wp_statistics_display_column_a(1, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(2, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(3, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(4, $ISOCountryCode, $search_engines); ?>

			<?php $ret += wp_statistics_display_column_a(5, $ISOCountryCode, $search_engines); ?>
			
			<?php if( $ret == 0 ) { wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines); } ?>

			</div>
		</div>
	</div>
	
	<div class="postbox-container" id="left-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">

			<?php wp_statistics_display_column_b(1, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(2, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(3, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(4, $ISOCountryCode, $search_engines); ?>

			<?php wp_statistics_display_column_b(5, $ISOCountryCode, $search_engines); ?>
			
			<?php wp_statistics_display_column_b(6, $ISOCountryCode, $search_engines); ?>
			
			</div>
		</div>
	</div>
</div>
<?php
	function wp_statistics_display_column_a($slot, $ISOCountryCode, $search_engines) {
		GLOBAL $WP_Statistics;
			
		$display = $WP_Statistics->get_user_option('overview_display');
			
		if( $display['A'][$slot] == '' ) { $display['A'][$slot] = $slot; }
		
		$ret = 0;
		
		switch( $display['A'][$slot] ) {
			case 1:
			case 'summary':
				wp_statistics_generate_summary_postbox($ISOCountryCode, $search_engines);
				
				break;
			case 2:
			case 'browsers':
				wp_statistics_generate_browsers_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 3:
			case 'referring':
				wp_statistics_generate_referring_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 4:
			case 'countries':
				wp_statistics_generate_countries_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 5:
			case 'about':
				wp_statistics_generate_about_postbox($ISOCountryCode, $search_engines);
		
				$ret = 1;
				
				break;
			default:
		}

		return $ret;
	}

	function wp_statistics_display_column_b($slot, $ISOCountryCode, $search_engines) {
		GLOBAL $WP_Statistics;
			
		$display = $WP_Statistics->get_user_option('overview_display');
			
		if( $display['B'][$slot] == '' ) { $display['B'][$slot] = $slot; }
		
		switch( $display['B'][$slot] ) {
			case 1:
			case 'map':
				wp_statistics_generate_map_postbox($ISOCountryCode, $search_engines);
				
				break;
			case 2:
			case 'hits':
				wp_statistics_generate_hits_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 3:
			case 'search':
				wp_statistics_generate_search_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 4:
			case 'words':
				wp_statistics_generate_words_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 5:
			case 'pages':
				wp_statistics_generate_pages_postbox($ISOCountryCode, $search_engines);
			
				break;
			case 6:
			case 'recent':
				wp_statistics_generate_recent_postbox($ISOCountryCode, $search_engines);
			
				break;
			default:
			
		}
	}

	function wp_statistics_generate_map_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
		
		if($WP_Statistics->get_option('geoip') && !$WP_Statistics->get_option('disable_map') ) { ?>
			<div class="postbox">
				<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
				<h3 class="hndle"><span><?php _e('Today Visitors Map', 'wp_statistics'); ?></span></h3>
				<div class="inside">
					<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
					<div id="map_canvas">Google Map</div>
					
					<?php $result = $wpdb->get_row("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE last_counter = '{$WP_Statistics->Current_Date('Y-m-d')}'"); ?>
					<script type="text/javascript">
						function initialize() {
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
										
										$get_ipp[$markets['location']][] = "<p>{$agent} {$markets['ip']}</p>";
									}

									$summary = ' [' . count($get_ipp[$markets['location']]) . ']';
									?>
									
										t.push("<?php echo $ISOCountryCode[$markets['location']] . $summary; ?>");
										x.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lat'); ?>");
										y.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lng'); ?>");
										h.push("<div class='map-html-marker'><?php echo $flag . $summary . '<hr />' . implode('', $get_ipp[$markets['location']]); ?></div>");
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
						}
						
						jQuery(document).ready(function(){
							initialize();
						});
					</script>
				</div>
			</div>
<?php 
		}
	}

	function wp_statistics_generate_summary_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>		
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Summary', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<table width="100%" class="widefat table-stats" id="summary-stats">
							<tbody>
								<tr>
									<th><?php _e('User(s) Online', 'wp_statistics'); ?>:</th>
									<th colspan="2" id="th-colspan"><span><?php echo wp_statistics_useronline(); ?></span></th>
								</tr>
								
								<tr>
									<th width="60%"></th>
									<th class="th-center"><?php _e('Visitor', 'wp_statistics'); ?></th>
									<th class="th-center"><?php _e('Visit', 'wp_statistics'); ?></th>
								</tr>
								
								<tr>
									<th><?php _e('Today', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('today',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('today')); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Yesterday', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('yesterday',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('yesterday')); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Week', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('week',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('week')); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Month', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('month',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('month')); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Year', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('year',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('year')); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Total', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visitor('total',null,true)); ?></span></th>
									<th class="th-center"><span><?php echo number_format_i18n(wp_statistics_visit('total')); ?></span></th>
								</tr>
								
								<tr>
									<th colspan="3"><br><hr></th>
								</tr>

								<tr>
									<th colspan="3" style="text-align: center;"><?php _e('Search Engine Referrals', 'wp_statistics'); ?></th>
								</tr>
								
								<tr>
									<th width="60%"></th>
									<th class="th-center"><?php _e('Today', 'wp_statistics'); ?></th>
									<th class="th-center"><?php _e('Yesterday', 'wp_statistics'); ?></th>
								</tr>
								
								<?php
								$se_today_total = 0;
								$se_yesterday_total = 0;
								foreach( $search_engines as $se ) {
								?>
								<tr>
									<th><img src='<?php echo plugins_url('wp-statistics/assets/images/' . $se['image'] );?>'> <?php _e($se['name'], 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php $se_temp = wp_statistics_searchengine($se['tag'], 'today'); $se_today_total += $se_temp; echo number_format_i18n($se_temp);?></span></th>
									<th class="th-center"><span><?php $se_temp = wp_statistics_searchengine($se['tag'], 'yesterday'); $se_yesterday_total += $se_temp; echo number_format_i18n($se_temp);?></span></th>
								</tr>
								
								<?php
								}
								?>
								<tr>
									<th><?php _e('Daily Total', 'wp_statistics'); ?>:</th>
									<td id="th-colspan" class="th-center"><span><?php echo number_format_i18n($se_today_total); ?></span></td>
									<td id="th-colspan" class="th-center"><span><?php echo number_format_i18n($se_yesterday_total); ?></span></td>
								</tr>

								<tr>
									<th><?php _e('Total', 'wp_statistics'); ?>:</th>
									<th colspan="2" id="th-colspan"><span><?php echo number_format_i18n(wp_statistics_searchengine('all')); ?></span></th>
								</tr>
								<tr>
									<th colspan="3"><br><hr></th>
								</tr>

								<tr>
									<th colspan="3" style="text-align: center;"><?php _e('Current Time and Date', 'wp_statistics'); ?> <span id="time_zone"><a href="<?php echo admin_url('options-general.php'); ?>"><?php _e('(Adjustment)', 'wp_statistics'); ?></a></span></th>
								</tr>

								<tr>
									<th colspan="3"><?php echo sprintf(__('Date: <code dir="ltr">%s</code></code>', 'wp_statistics'), $WP_Statistics->Current_Date_i18n(get_option('date_format'))); ?></th>
								</tr>

								<tr>
									<th colspan="3"><?php echo sprintf(__('Time: <code dir="ltr">%s</code>', 'wp_statistics'), $WP_Statistics->Current_Date_i18n(get_option('time_format'))); ?></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
<?php
	}

	function wp_statistics_generate_browsers_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Browsers', 'wp_statistics'); ?> <a href="?page=wps_browsers_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						jQuery(function () {
							var browser_chart;
							jQuery(document).ready(function() {
<?php								
								$Browsers = wp_statistics_ua_list();
								$BrowserVisits = array();
								$total = 0;
								
								foreach( $Browsers as $Browser ) {
									$BrowserVisits[$Browser] = wp_statistics_useragent( $Browser );
									$total += $BrowserVisits[$Browser];
								}
								
								arsort( $BrowserVisits );
								
								echo "var browser_data = [";
								$count = 0;
								$topten = 0;
								
								foreach( $BrowserVisits as $key => $value ) {
									echo "['" . substr( $key, 0, 15 ) . " (" . number_format_i18n($value) . ")'," . $value . "], ";

									$topten += $value;
									$count++;
									if( $count > 9 ) { break; }
								}

								echo "['" . __('Other', 'wp_statistics') . " (" . number_format_i18n($total - $topten) . ")'," . ( $total - $topten ) . "], ";

								echo "];\n";
?>

								browser_chart = jQuery.jqplot('browsers-log', [browser_data], { 
									title: {
										text: '<b><?php echo __('Top 10 Browsers', 'wp_statistics'); ?></b>',
										fontSize: '12px',
										fontFamily: 'Tahoma',
										textColor: '#000000',
										},
									seriesDefaults: {
										// Make this a pie chart.
										renderer: jQuery.jqplot.PieRenderer, 
										rendererOptions: {
											// Put data labels on the pie slices.
											// By default, labels show the percentage of the slice.
											dataLabels: 'percent',
											showDataLabels: true,
											shadowOffset: 0,
										}
									}, 
									legend: { 
										show:true, 
										location: 's',
										renderer: jQuery.jqplot.EnhancedLegendRenderer,
										rendererOptions:
											{
												numberColumns: 2, 
												disableIEFading: false,
												border: 'none',
											},
										},
									grid: { background: 'transparent', borderWidth: 0, shadow: false },
									highlighter: {
										show: true,
										formatString:'%s', 
										tooltipLocation:'n', 
										useAxesFormatters:false,
										},
								} );
							});

							jQuery(window).resize(function() {
								browser_chart.replot( {resetAxes: true } );
							});
						});
								  
						</script>
								
						<div id="browsers-log" style="height: <?php $height = ( count($Browsers) / 2 * 27 ) + 300; if( $height > 462 ) { $height = 462; } echo $height; ?>px;"></div>
					</div>
				</div>
<?php		
	}

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
									$get_urls = array_slice($get_urls, 0, 10);
									
									foreach( $get_urls as $items => $value) {
									
										echo "<tr>";
										echo "<td>" . number_format_i18n($value) . "</td>";
										echo "<td><a href='?page=wps_referers_menu&referr={$items}'>{$items}</a></td>";
										echo "</tr>";
									}
								?>
							</table>
						</div>
					</div>
				</div>
<?php		
		}				

	}

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

	function wp_statistics_generate_hits_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Hit Statistics', 'wp_statistics'); ?> <a href="?page=wps_hits_menu"> <?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
<?php								
								echo "var visit_data_line = [";
								
								for( $i=20; $i>=0; $i--) {
									$stat = wp_statistics_visit('-'.$i, true);
									
									echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
									
								}

								echo "];\n";

								echo "var visitor_data_line = [";
								
								for( $i=20; $i>=0; $i--) {
									$stat = wp_statistics_visitor('-'.$i, true);
									
									echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
									
								}

								echo "];\n";

?>
							visit_chart = jQuery.jqplot('visits-stats', [visit_data_line, visitor_data_line], {
								title: {
									text: '<b><?php echo __('Hits in the last', 'wp_statistics') . ' 20 ' . __('days', 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-20');?>',
											max: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '');?>',
											tickInterval: '1 day',
											renderer:jQuery.jqplot.DateAxisRenderer,
											tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
											tickOptions: { 
												angle: -45,
												formatString:'%b %#d',
												showGridline: false, 
												},
										},										
									yaxis: {
											min: 0,
											label: '<?php _e('Number of visits and visitors', 'wp_statistics'); ?>',
											labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
											labelOptions: {
												angle: -90,
												fontSize: '12px',
												fontFamily: 'Tahoma',
												fontWeight: 'bold',
											},
										}
									},
								legend: {
									show: true,
									location: 's',
									placement: 'outsideGrid',
									labels: ['<?php _e('Visit', 'wp_statistics'); ?>', '<?php _e('Visitor', 'wp_statistics'); ?>'],
									renderer: jQuery.jqplot.EnhancedLegendRenderer,
									rendererOptions:
										{
											numberColumns: 2, 
											disableIEFading: false,
											border: 'none',
										},
									},
								highlighter: {
									show: true,
									bringSeriesToFront: true,
									tooltipAxes: 'xy',
									formatString: '%s:&nbsp;<b>%i</b>&nbsp;',
								},
								grid: {
								 drawGridlines: true,
								 borderColor: 'transparent',
								 shadow: false,
								 drawBorder: false,
								 shadowColor: 'transparent'
								},
							} );
							
							jQuery(window).resize(function() {
								visit_chart.replot( {resetAxes: true } );
							});
						});
						</script>
						
						<div id="visits-stats" style="height:300px;"></div>
						
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_search_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Search Engine Referrals', 'wp_statistics'); ?> <a href="?page=wps_searches_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var referral_chart;
						jQuery(document).ready(function() {
<?php								
								$total_stats = $WP_Statistics->get_option( 'chart_totals' );
								$total_daily = array();

								foreach( $search_engines as $se ) {
									
									echo "var searches_data_line_" . $se['tag'] . " = [";
									
									for( $i=20; $i>=0; $i--) {
										if( !array_key_exists( $i, $total_daily ) ) { $total_daily[$i] = 0; }
										$stat = wp_statistics_searchengine($se['tag'], '-'.$i);
										$total_daily[$i] += $stat;
										
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
										
									}

									echo "];\n";
								}

								if( $total_stats == 1 ) {
									echo "var searches_data_line_total = [";

									for( $i=20; $i>=0; $i--) {
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $total_daily[$i] . "], ";
									}
									
									echo "];\n";
								}
								
?>
							referral_chart = jQuery.jqplot('search-stats', [<?php foreach( $search_engines as $se ) { echo "searches_data_line_" . $se['tag'] . ", "; } if( $total_stats == 1 ) { echo 'searches_data_line_total'; }?>], {
								title: {
									text: '<b><?php echo __('Search engine referrals in the last', 'wp_statistics') . ' 20 ' . __('days', 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-20');?>',
											max: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '');?>',
											tickInterval: '1 day',
											renderer:jQuery.jqplot.DateAxisRenderer,
											tickRenderer: jQuery.jqplot.CanvasAxisTickRenderer,
											tickOptions: { 
												angle: -45,
												formatString:'%b %#d',
												showGridline: false, 
												},
										},										
									yaxis: {
											min: 0,
											label: '<?php _e('Number of referrals', 'wp_statistics'); ?>',
											labelRenderer: jQuery.jqplot.CanvasAxisLabelRenderer,
											labelOptions: {
												angle: -90,
												fontSize: '12px',
												fontFamily: 'Tahoma',
												fontWeight: 'bold',
											},
										}
									},
								legend: {
									show: true,
									location: 's',
									placement: 'outsideGrid',
									labels: [<?php foreach( $search_engines as $se ) { echo "'" . __( $se['name'], 'wp_statistics' ) . "', "; } if( $total_stats == 1 ) { echo "'" . __('Total', 'wp_statistics') . "'"; }?>],
									renderer: jQuery.jqplot.EnhancedLegendRenderer,
									rendererOptions:
										{
											numberColumns: <?php echo count( $search_engines ) + 1;?>, 
											disableIEFading: false,
											border: 'none',
										},
									},
								highlighter: {
									show: true,
									bringSeriesToFront: true,
									tooltipAxes: 'xy',
									formatString: '%s:&nbsp;<b>%i</b>&nbsp;',
								},
								grid: {
								 drawGridlines: true,
								 borderColor: 'transparent',
								 shadow: false,
								 drawBorder: false,
								 shadowColor: 'transparent'
								},
							} );
						
							jQuery(window).resize(function() {
								referral_chart.replot( {resetAxes: true } );
							});

						});

						</script>
						
						<div id="search-stats" style="height:300px;"></div>
						
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_words_postbox($ISOCountryCode, $search_engines) {
	
		global $wpdb, $table_prefix, $WP_Statistics;

		// Retrieve MySQL data for the search words.
		$search_query = wp_statistics_searchword_query('all');

		$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE {$search_query} ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 10");
		
		if( sizeof($result) > 0 ) {
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Latest Search Words', 'wp_statistics'); ?> <a href="?page=wps_words_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							<?php
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
									if( !$WP_Statistics->Search_Engine_QueryString($items->referred) ) continue;
									
									echo "<div class='log-item'>";
										echo "<div class='log-referred'>".$WP_Statistics->Search_Engine_QueryString($items->referred)."</div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
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
							?>
					</div>
				</div>
<?php		
		}
	}

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
									echo "<div class='left-div'><a href='{$uri[0]}'>{$uri[0]}</a></div>";
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
								
								foreach($result as $items) {
									echo "<div class='log-item'>";
										echo "<div class='log-referred'><a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&ip={$items->ip}'>".wp_statistics_icons('dashicons-visibility', 'visibility')."{$items->ip}</a></div>";
										echo "<div class='log-ip'>{$items->last_counter}</div>";
										echo "<div class='clear'></div>";
										echo "<div class='log-url'>";
										echo "<a class='show-map' href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank' title='".__('Map', 'wp_statistics')."'>".wp_statistics_icons('dashicons-location-alt', 'map')."</a>";
										
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
?>