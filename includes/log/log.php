<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});

	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php 
	include_once( dirname( __FILE__ ) . "/../functions/country-codes.php" ); 

	$search_engines = wp_statistics_searchengine_list();
	
	$search_result['All'] = wp_statistics_searchengine('all','total');

	foreach( $search_engines as $key => $se ) {
		$search_result[$key] = wp_statistics_searchengine($key,'total');
	}

	wp_enqueue_script('highcharts', plugin_dir_url(__FILE__) . '../../js/highcharts.js', true, '2.3.5');
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
									<th class="th-center"><span><?php echo wp_statistics_visitor('today',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('today'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Yesterday', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('yesterday',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('yesterday'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Week', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('week',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('week'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Month', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('month',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('month'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Year', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('year',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('year'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Total', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('total',null,true); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('total'); ?></span></th>
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
									<th><img src='<?php echo plugins_url('wp-statistics/images/' . $se['image'] );?>'> <?php _e($se['name'], 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php $se_temp = wp_statistics_searchengine($se['tag'], 'today'); $se_today_total += $se_temp; echo $se_temp;?></span></th>
									<th class="th-center"><span><?php $se_temp = wp_statistics_searchengine($se['tag'], 'yesterday'); $se_yesterday_total += $se_temp; echo $se_temp;?></span></th>
								</tr>
								
								<?php
								}
								?>
								<tr>
									<th><?php _e('Daily Total', 'wp_statistics'); ?>:</th>
									<td id="th-colspan" class="th-center"><span><?php echo $se_today_total; ?></span></td>
									<td id="th-colspan" class="th-center"><span><?php echo $se_yesterday_total; ?></span></td>
								</tr>

								<tr>
									<th><?php _e('Total', 'wp_statistics'); ?>:</th>
									<th colspan="2" id="th-colspan"><span><?php echo wp_statistics_searchengine('all'); ?></span></th>
								</tr>
								<tr>
									<th colspan="3"><br><hr></th>
								</tr>

								<tr>
									<th colspan="3" style="text-align: center;"><?php _e('Current Time and Date', 'wp_statistics'); ?> <span id="time_zone"><a href="<?php echo admin_url('options-general.php'); ?>"><?php _e('(Adjustment)', 'wp_statistics'); ?></a></span></th>
								</tr>

								<tr>
									<th colspan="3"><?php $wpstats = new WP_Statistics(); echo sprintf(__('Date: <code dir="ltr">%s</code></code>', 'wp_statistics'), $wpstats->Current_Date(get_option('date_format'))); ?></th>
								</tr>

								<tr>
									<th colspan="3"><?php echo sprintf(__('Time: <code dir="ltr">%s</code>', 'wp_statistics'), $wpstats->Current_Date(get_option('time_format'))); ?></th>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Browsers', 'wp_statistics'); ?> <a href="?page=wps_browsers_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span></h3>
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
												echo "											['" . __( $Browser, 'wp_statistics' ) . " (" . $count . ")', " . $count . "],\r\n";
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
						<span><?php _e('Top referring sites', 'wp_statistics'); ?></span> <a href="?page=wps_referers_menu"><?php _e('(See more)', 'wp_statistics'); ?></a>
					</h3>
					<div class="inside">
						<div class="inside">
							<table width="100%" class="widefat table-stats" id="last-referrer">
								<tr>
									<td width="10%"><?php _e('Reference', 'wp_statistics'); ?></td>
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
										echo "<td>{$value}</td>";
										echo "<td>{$items}</td>";
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
						<span><?php _e('Top 10 Countries', 'wp_statistics'); ?> <a href="?page=wps_countries_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span>
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
										echo "<td style='text-align: left'><img src='".plugins_url('wp-statistics/images/flags/' . $item . '.png')."' title='".__($ISOCountryCode[$item], 'wp_statistics')."'/></td>";
										echo "<td style='text-align: left'>{$ISOCountryCode[$item]}</td>";
										echo "<td style='text-align: left'>{$value}</td>";
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
					<h3 class="hndle"><span><?php _e('About plugin', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<div id="about-links">
							<p><?php echo sprintf(__('Plugin version: %s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></p> |
							<p><a href="http://teamwork.wp-parsi.com/projects/wp-statistics/" target="_blank"><?php _e('Translations', 'wp_statistics'); ?></a></p> |
							<p><a href="http://wordpress.org/support/plugin/wp-statistics" target="_blank"><?php _e('Support', 'wp_statistics'); ?></a> / <a href="http://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/" target="_blank"><?php _e('Farsi', 'wp_statistics'); ?></a></p> |
							<p><a href="https://www.facebook.com/pages/Wordpress-Statistics/546922341997898?ref=stream" target="_blank"><?php _e('Facebook', 'wp_statistics'); ?></a></p> |
							<p><a href="http://iran98.org/" target="_blank"><?php _e('Weblog', 'wp_statistics'); ?></a></p>
						</div>
						
						<hr />
						
						<p><?php _e('Please donate to WP Statistics. With your help WP Statistics will rule the world!', 'wp_statistics'); ?></p>
						
						<div id="donate-button">
							<div class="left-div">
								<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
									<input type="hidden" name="cmd" value="_s-xclick">
									<input type="hidden" name="hosted_button_id" value="Z959U3RPCC9WG">
									<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
									<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
								</form>
							</div>
							
							<div class="right-div">
								<a href="http://iran98.org/donate/" target="_blank"><img src="<?php echo plugins_url('wp-statistics/images/donate/donate.png'); ?>" id="donate" alt="<?php _e('Donate', 'wp_statistics'); ?>"/><br /><img src="<?php echo plugins_url('wp-statistics/images/donate/tdCflg.png'); ?>" id="donate" alt="<?php _e('Donate', 'wp_statistics'); ?>"/></a>
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
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Hits Statistical Chart', 'wp_statistics'); ?> <a href="?page=wps_hits_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span></h3>
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
											echo '"'.$wpstats->Current_Date('M d', '-'.$i).'"';
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
					<h3 class="hndle"><span><?php _e('Search Engine Referrers Statistical Chart', 'wp_statistics'); ?> <a href="?page=wps_searches_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span></h3>
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
											echo '"'.$wpstats->Current_Date('M d', '-'.$i).'"';
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
								foreach( $search_engines as $se ) {
									echo "								{\n";
									echo "									name: '" . __($se['name'], 'wp_statistics') . "',\n";
									echo "									data: [";

									for( $i=20; $i>=0; $i--) {
										echo wp_statistics_searchengine($se['tag'], '-'.$i) . ", ";
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
						<span><?php _e('Latest search words', 'wp_statistics'); ?> <a href="?page=wps_words_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span>
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
										echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
										
										$this_search_engine = $wpstats->Search_Engine_Info($items->referred);
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred={$this_search_engine['tag']}'><img src='".plugins_url('wp-statistics/images/' . $this_search_engine['image'])."' class='log-tools' title='".__($this_search_engine['name'], 'wp_statistics')."'/></a>";
										
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/></a>";
										echo "<div class='log-url'><a href='{$items->referred}'><img src='".plugins_url('wp-statistics/images/link.png')."' title='{$items->referred}'/> ".substr($items->referred, 0, 100)."[...]</a></div>";
									echo "</div>";
								}
								
								echo "</div>";
							?>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle">
						<span><?php _e('Recent Visitors', 'wp_statistics'); ?> <a href="?page=wps_visitors_menu"><?php _e('(See more)', 'wp_statistics'); ?></a></span>
					</h3>
					<div class="inside">
							
							<?php
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 10");
								
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
								
									echo "<div class='log-item'>";
										echo "<div class='log-referred'><a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
										echo "<img src='".plugins_url('wp-statistics/images/flags/' . $items->location . '.png')."' title='".__($ISOCountryCode[$items->location], 'wp_statistics')."' class='log-tools'/>";
										
										if( array_search( strtolower( $items->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ) 
											{
											$AgentImage = $items->agent . ".png";
											}
										else
											{
											$AgentImage = "unknown.png";
											}
										
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$AgentImage."' class='log-tools' title='{$items->agent}'/></a>";
										echo "<div class='log-url'><a href='{$items->referred}'><img src='".plugins_url('wp-statistics/images/link.png')."' title='{$items->referred}'/> ".substr($items->referred, 0, 80)."[...]</a></div>";
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