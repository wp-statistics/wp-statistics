<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});

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
	
	wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . '../../assets/js/highcharts.js', true, '3.0.9');
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php echo get_admin_page_title(); ?></h2>
	<div class="postbox-container" id="right-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Summary Statistics', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<table width="100%" class="widefat table-stats" id="summary-stats">
							<tbody>
								<tr>
									<th><?php _e('User Online', 'wp_statistics'); ?>:</th>
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
									<th colspan="3"><?php echo sprintf(__('Date: <code dir="ltr">%s</code></code>', 'wp_statistics'), $wpstats->Current_Date_i18n(get_option('date_format'))); ?></th>
								</tr>

								<tr>
									<th colspan="3"><?php echo sprintf(__('Time: <code dir="ltr">%s</code>', 'wp_statistics'), $wpstats->Current_Date_i18n(get_option('time_format'))); ?></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Browsers', 'wp_statistics'); ?> <a href="?page=wps_browsers_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						jQuery(function () {
							var browser_chart;
							jQuery(document).ready(function() {
								
								// Radialize the colors
								Highcharts.getOptions().colors = jQuery.map(Highcharts.getOptions().colors, function(color) {
									return {
										radialGradient: { cx: 0.5, cy: 0.3, r: 0.7 },
										stops: [
											[0, color],
											[1, Highcharts.Color(color).brighten(-0.3).get('rgb')] // darken
										]
									};
								});
								
								// Build the chart
								browser_chart = new Highcharts.Chart({
									chart: {
										renderTo: 'browsers-log',
										plotBackgroundColor: null,
										plotBorderWidth: null,
										plotShadow: false,
										backgroundColor: '#FFFFFF',
									},
									credits: {
										enabled: false
									},
									title: {
										text: '<?php _e('Graph of Browsers', 'wp_statistics'); ?>',
										style: {
											fontSize: '12px',
											fontFamily: 'Tahoma',
											fontWeight: 'bold'
										}
									},
									<?php if( is_rtl() ) { ?>
									legend: {
										rtl: true,
										itemStyle: {
											fontSize: '11px',
											fontFamily: 'Tahoma'
										}
									},
									<?php } ?>
									tooltip: {
										formatter: function () {
											return this.point.name + ': <b>' + Highcharts.numberFormat(this.percentage, 1) + '%</b>';
									   },
										percentageDecimals: 1,
										style: {
											fontSize: '12px',
											fontFamily: 'Tahoma'
										},
										useHTML: true
									},
									plotOptions: {
										pie: {
											allowPointSelect: true,
											cursor: 'pointer',
											dataLabels: {
												enabled: true,
												color: '#000000',
												connectorColor: '#000000',
												style: {
													fontSize: '11px',
													fontFamily: 'Tahoma',
												}
											}
										}
									},
									series: [{
										type: 'pie',
										name: '<?php _e('Browser share', 'wp_statistics'); ?>',
										data: [
											<?php 
											$Browsers = wp_statistics_ua_list();
											
											foreach( $Browsers as $Browser )
												{
												$count = wp_statistics_useragent( $Browser );
												echo "											['" . __( $Browser, 'wp_statistics' ) . " (" . number_format_i18n($count) . ")', " . $count . "],\r\n";
												}
											?>
										]
									}]
								});
							});
							
						});
						</script>
						
						<div id="browsers-log"></div>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Top referring sites', 'wp_statistics'); ?></span> <a href="?page=wps_referers_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a>
					</h3>
					<div class="inside">
						<div class="inside">
							<table width="100%" class="widefat table-stats" id="last-referrer">
								<tr>
									<td width="10%"><?php _e('References', 'wp_statistics'); ?></td>
									<td width="90%"><?php _e('Address', 'wp_statistics'); ?></td>
								</tr>
								
								<?php
									$result = $wpdb->get_results("SELECT `referred` FROM `{$table_prefix}statistics_visitor` WHERE referred <> ''");
									
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
				
				<?php if( get_option('wps_geoip') ) { ?>
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
									$result = $wpdb->get_results("SELECT DISTINCT `location` FROM `{$table_prefix}statistics_visitor`");
									
									foreach( $result as $item )
										{
										$Countries[$item->location] = $wpdb->get_var("SELECT count(location) FROM `{$table_prefix}statistics_visitor` WHERE location='" . $item->location . "'" );
										}
										
									arsort($Countries);
									$i = 0;
									
									foreach( $Countries as $item => $value) {
										$i++;
										
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
				<?php } ?>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php echo sprintf(__('About WP Statistics V%s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></span></h3>
					<div class="inside">
						<div id="about-links" style="text-align: center;">
							<p><a href="http://wp-statistics.com" target="_blank"><?php _e('Website', 'wp_statistics'); ?></a></p> |
							<p><a href="http://teamwork.wp-parsi.com/projects/wp-statistics" target="_blank"><?php _e('Translations', 'wp_statistics'); ?></a></p> |
							<p><a href="http://wordpress.org/support/plugin/wp-statistics" target="_blank"><?php _e('Support', 'wp_statistics'); ?></a> / <a href="http://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/" target="_blank"><?php _e('Farsi', 'wp_statistics'); ?></a></p>
						</div>
						
						<hr />
						
						<p><?php _e('Please donate to WP Statistics. With your help WP Statistics will rule the world!', 'wp_statistics'); ?></p>
						
						<div id="donate-button" style="width: 100%;">
							<div class="left-div" style="width: 100%; text-align: center;">
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="hidden" name="hosted_button_id" value="Z959U3RPCC9WG">
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>

								<br>
								
								<a href="http://mostafa-soufi.ir/donate/" target="_blank"><img src="<?php echo plugins_url('wp-statistics/assets/images/donate/donate.png'); ?>" id="donate" alt="<?php _e('Donate', 'wp_statistics'); ?>"/><br /><img src="<?php echo plugins_url('wp-statistics/assets/images/donate/tdCflg.png'); ?>" id="donate" alt="<?php _e('Donate', 'wp_statistics'); ?>"/></a>

							</div>
						</div>
						
						<div class="clear"></div>
						
						<div>
							<br/>This product includes GeoLite2 data created by MaxMind, available from <a href="http://www.maxmind.com">http://www.maxmind.com</a>.
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div class="postbox-container" id="left-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<?php if( !get_option('wps_map_location') ) { generate_map_html($wpstats, $ISOCountryCode); } ?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Hits Statistical Chart', 'wp_statistics'); ?> <a href="?page=wps_hits_menu"> <?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'visits-log',
									type: '<?php echo get_option('wps_chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '300'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php _e('Hits chart in the last 20 days', 'wp_statistics'); ?>',
									style: {
										fontSize: '12px',
										fontFamily: 'Tahoma',
										fontWeight: 'bold'
									}
								},
								xAxis: {
									type: 'datetime',
									labels: {
										rotation: -45
										},
									categories: [
									<?php
										for( $i=20; $i>=0; $i--) {
											echo '"'.$wpstats->Current_Date_i18n('Y-m-d', '-'.$i).'"';
											echo ", ";
										}
									?>]
								},
								yAxis: {
									min: 0,
									title: {
										text: '<?php _e('Number of visits and visitors', 'wp_statistics'); ?>',
										style: {
											fontSize: '12px',
											fontFamily: 'Tahoma'
										}
									}
								},
								<?php if( is_rtl() ) { ?>
								legend: {
									rtl: true,
									itemStyle: {
											fontSize: '11px',
											fontFamily: 'Tahoma'
										}
								},
								<?php } ?>
								tooltip: {
									crosshairs: true,
									shared: true,
									style: {
										fontSize: '12px',
										fontFamily: 'Tahoma'
									},
									useHTML: true
								},
								series: [{
									name: '<?php _e('Visitor', 'wp_statistics'); ?>',
									data: [
									<?php
										for( $i=20; $i>=0; $i--) {
											echo wp_statistics_visitor('-'.$i, true);
											echo ", ";
										}
									?>]
								},
								{
									name: '<?php _e('Visit', 'wp_statistics'); ?>',
									data: [
									<?php
										for( $i=20; $i>=0; $i--) {
											echo wp_statistics_visit('-'.$i, true);
											echo ", ";
										}
									?>]
								}]
							});
						});
						</script>
						
						<div id="visits-log"></div>
						
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Search Engine Referrers Statistical Chart', 'wp_statistics'); ?> <a href="?page=wps_searches_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'search-engine-log',
									type: '<?php echo get_option('wps_chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '300'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php _e('Referrer search engine chart in the last 20 days', 'wp_statistics'); ?>',
									style: {
										fontSize: '12px',
										fontFamily: 'Tahoma',
										fontWeight: 'bold'
									}
								},
								xAxis: {
									type: 'datetime',
									labels: {
										rotation: -45,
										},
									categories: [
									<?php
										for( $i=20; $i>=0; $i--) {
											echo '"'.$wpstats->Current_Date_i18n('Y-m-d', '-'.$i).'"';
											echo ", ";
										}
									?>]
								},
								yAxis: {
									min: 0,
									title: {
										text: '<?php _e('Number of referrer', 'wp_statistics'); ?>',
										style: {
											fontSize: '12px',
											fontFamily: 'Tahoma'
										}
									}
								},
								<?php if( is_rtl() ) { ?>
								legend: {
									rtl: true,
									itemStyle: {
											fontSize: '11px',
											fontFamily: 'Tahoma'
										}
								},
								<?php } ?>
								tooltip: {
									crosshairs: true,
									shared: true,
									style: {
										fontSize: '12px',
										fontFamily: 'Tahoma'
									},
									useHTML: true
								},
								series: [
<?php
								$total_stats = get_option( 'wps_chart_totals' );
								$total_daily = array();

								foreach( $search_engines as $se ) {
									echo "								{\n";
									echo "									name: '" . __($se['name'], 'wp_statistics') . "',\n";
									echo "									data: [";

									for( $i=20; $i>=0; $i--) {
										$result = wp_statistics_searchengine($se['tag'], '-'.$i) . ", ";
										$total_daily[$i] += $result;
										echo $result;
									}
									
									echo "]\n";
									echo "								},\n";
								}
								
								if( $total_stats == 1 ) {
									echo "								{\n";
									echo "									name: '" . __('Total', 'wp_statistics') . "',\n";
									echo "									data: [";

									for( $i=20; $i>=0; $i--) {
										echo $total_daily[$i] . ", ";
									}
									
									echo "]\n";
									echo "								},\n";
								}
?>
								]
							});
						});
						
						</script>
						
						<div id="search-engine-log"></div>
						
					</div>
				</div>

				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Latest search words', 'wp_statistics'); ?> <a href="?page=wps_words_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							<?php
								// Retrieve MySQL data
								$search_query = wp_statistics_searchword_query('all');

								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE {$search_query} ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 10");
								
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
									if( !$wpstats->Search_Engine_QueryString($items->referred) ) continue;
									
									echo "<div class='log-item'>";
										echo "<div class='log-referred'>".substr($wpstats->Search_Engine_QueryString($items->referred), 0, 100)."</div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map' title='".__('Map', 'wp_statistics')."'>".wp_statistics_icons('dashicons-location-alt', 'map')."</a>";
										
										if(get_option('wps_geoip')) {
											echo "<img src='".plugins_url('wp-statistics/assets/images/flags/' . $items->location . '.png')."' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
										}
										
										$this_search_engine = $wpstats->Search_Engine_Info($items->referred);
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred={$this_search_engine['tag']}'><img src='".plugins_url('wp-statistics/assets/images/' . $this_search_engine['image'])."' class='log-tools' title='".__($this_search_engine['name'], 'wp_statistics')."'/></a>";
										
										if( array_search( strtolower( $items->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/>";
										} else {
											$agent = wp_statistics_icons('dashicons-editor-help', 'unknown');
										}
										
										echo "<div class='log-agent'><a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'>{$agent}</a></div>";
										
										echo "<div class='log-url'><a href='{$items->referred}' title='{$items->referred}'>".wp_statistics_icons('dashicons-admin-links', 'link')." ".substr($items->referred, 0, 100)."[...]</a></div>";
									echo "</div>";
								}
								
								echo "</div>";
							?>
					</div>
				</div>

				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Top Pages Visited', 'wp_statistics'); ?> <a href="?page=wps_pages_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							<?php
								// Retrieve data
								list( $total, $uris ) = wp_statistics_get_top_pages();
								
								echo "<div class='log-latest'>";
								
								$i = 0;
								
								foreach($uris as $uri) {
									$i++;
									echo "<div class='log-item'>";

									if( $uri[3] == '' ) { $uri[3] = '[' . __('No page title found', 'wp_statistics') . ']'; }
									
									echo "<div>{$i} - {$uri[3]}</div>";
									echo "<div class='right-div'>".__('Visits', 'wp_statistics').": <a href='?page=wps_pages_menu&page-uri={$uri[0]}'>" . number_format_i18n($uri[1]) . "</a></div>";
									echo "<div class='left-div'><a href='{$site_url}{$uri[0]}'>{$uri[0]}</a></div>";
									echo "</div>";
									
									if( $i > 9 ) { break; }
								}
								
								echo "</div>";
							?>
					</div>
				</div>

				<?php if( get_option('wps_map_location') ) { generate_map_html($wpstats, $ISOCountryCode); } ?>
				
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
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map' title='".__('Map', 'wp_statistics')."'>".wp_statistics_icons('dashicons-location-alt', 'map')."</a>";
										
										if(get_option('wps_geoip')) {
											echo "<img src='".plugins_url('wp-statistics/assets/images/flags/' . $items->location . '.png')."' title='{$ISOCountryCode[$items->location]}' class='log-tools'/>";
										}
										
										if( array_search( strtolower( $items->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/>";
										} else {
											$agent = wp_statistics_icons('dashicons-editor-help', 'unknown');
										}
										
										echo "<div class='log-agent'><a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'>{$agent}</a></div>";
										
										echo "<div class='log-url'><a href='{$items->referred}' title='{$items->referred}'>".wp_statistics_icons('dashicons-admin-links', 'link')." ".substr($items->referred, 0, 100)."[...]</a></div>";
									echo "</div>";
								}
								
								echo "</div>";
							?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	function generate_map_html($wpstats, $ISOCountryCode) {
	
		global $wpdb, $table_prefix;
		
		if(get_option('wps_geoip') && !get_option('wps_disable_map') ) { ?>
			<div class="postbox">
				<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
				<h3 class="hndle"><span><?php _e('Today visitors on map', 'wp_statistics'); ?></span></h3>
				<div class="inside">
					<script src="http://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>
					<div id="map_canvas">Google Map</div>
					
					<?php $result = $wpdb->get_row("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE last_counter = '{$wpstats->Current_Date('Y-m-d')}'"); ?>
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
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE last_counter = '{$wpstats->Current_Date('Y-m-d')}'");
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
										
										$get_ipp[$markets['location']][] = "<p>{$agent} {$markets[ip]}</p>";
									}
									?>
										t.push("<?php echo $ISOCountryCode[$markets['location']]; ?>");
										x.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lat'); ?>");
										y.push("<?php echo wp_statistics_get_gmap_coordinate($markets['location'], 'lng'); ?>");
										h.push("<div class='map-html-marker'><?php echo $flag . '<hr />' . implode('', $get_ipp[$markets['location']]); ?></div>");
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
						
						initialize();
					</script>
				</div>
			</div>
			<?php 
		}
	}
?>