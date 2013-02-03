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
									<th colspan="2" id="user_online"><span><?php echo wp_statistics_useronline(); ?></span></th>
								</tr>
								
								<tr>
									<th width="60%"></th>
									<th class="th-center"><?php _e('Visitor', 'wp_statistics'); ?></th>
									<th class="th-center"><?php _e('Visit', 'wp_statistics'); ?></th>
								</tr>
								
								<tr>
									<th><?php _e('Today', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('today'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('today'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Yesterday', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('yesterday'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('yesterday'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Week', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('week'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('week'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Month', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('month'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('month'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Year', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('year'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('year'); ?></span></th>
								</tr>
								
								<tr>
									<th><?php _e('Total', 'wp_statistics'); ?>:</th>
									<th class="th-center"><span><?php echo wp_statistics_visitor('total'); ?></span></th>
									<th class="th-center"><span><?php echo wp_statistics_visit('total'); ?></span></th>
								</tr>
							</tbody>
						</table>
						
						<strong><?php global $s; echo sprintf(__('Today date: <code dir="ltr">%s</code>, Time: <code dir="ltr">%s</code>', 'wp_statistics'), $s->Current_Date('Y-m-d'), $s->Current_Date('H-i')); ?></strong>
						
						<span id="time_zone"><a href="<?php echo admin_url('options-general.php'); ?>"><?php _e('(Adjustment)', 'wp_statistics'); ?></a></span>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Browsers', 'wp_statistics'); ?></span></h3>
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
										backgroundColor: '#F8F8F8',
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
										pointFormat: '{series.name}: <b>{point.percentage}%</b>',
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
											['<?php _e('Firefox', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('Firefox'); ?>],
											['<?php _e('IE', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('IE'); ?>],
											['<?php _e('Ipad', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('Ipad'); ?>],
											['<?php _e('Android', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('Android'); ?>],
											['<?php _e('Chrome', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('Chrome'); ?>],
											['<?php _e('Safari', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('Safari'); ?>],
											['<?php _e('Other', 'wp_statistics'); ?>', <?php echo wp_statistics_useragent('unknown'); ?>]
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
					<h3 class="hndle"><span><?php _e('Top referring sites', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<div class="inside">
							<table width="100%" class="widefat table-stats" id="last-referrer">
								<tr>
									<td width="10%"><?php _e('Reference', 'wp_statistics'); ?></td>
									<td width="90%"><?php _e('Address', 'wp_statistics'); ?></td>
								</tr>
								
								<?php
									global $wpdb, $table_prefix;
									
									$result = $wpdb->get_results("SELECT `referred` FROM `{$table_prefix}statistics_visitor`");
									
									$urls = array();
									foreach( $result as $items ) {
									
										$url = parse_url($items->referred);
										
										if( empty($url['host']) )
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
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('About plugin', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<div id="about-links">
							<p><?php echo sprintf(__('Plugin version: %s', 'wp_statistics'), WP_STATISTICS_VERSION); ?></p>|
							<p><a href="http://teamwork.wp-parsi.com/projects/wp-statistics/" target="_blank"><?php _e('Translation plugin languages', 'wp_statistics'); ?></a></p>|
							<p><a href="http://forum.wp-parsi.com/forum/17-%D9%85%D8%B4%DA%A9%D9%84%D8%A7%D8%AA-%D8%AF%DB%8C%DA%AF%D8%B1/" target="_blank"><?php _e('Support plugin', 'wp_statistics'); ?></a></p>|
							<p><a href="http://iran98.org/" target="_blank"><?php _e('Weblog', 'wp_statistics'); ?></a></p>
						</div>
						
						<hr />
						
						<p><?php _e('Please donate to the plugin. With the help of plug-ins you can quickly spread.', 'wp_statistics'); ?></p>
						
						<div id="donate-button">
							<a href="http://iran98.org/donate/" target="_blank"><img src="<?php echo plugins_url('wp-statistics/images/donate.png'); ?>" id="donate" alt="<?php _e('Donate', 'wp_statistics'); ?>"/></a>
						</div>
						
						<div class="clear"></div>
						
						<div class="ads">
							<a href="http://ads.iran98.org/view/link/11" target="_blank" alt="ads-link" title="محل نمایش تبلیغات - افزونه آماره وردپرس"><img src="http://ads.iran98.org/view/banner/11" alt="ads-img" title="محل نمایش تبلیغات - افزونه آماره وردپرس"/></a>
							<a href="http://ads.iran98.org/view/link/12" target="_blank" alt="ads-link" title="محل نمایش تبلیغات 2 - افزونه آماره وردپرس"><img src="http://ads.iran98.org/view/banner/12" alt="ads-img" title="محل نمایش تبلیغات 2 - افزونه آماره وردپرس"/></a>
							<a href="http://ads.iran98.org/view/link/13" target="_blank" alt="ads-link" title="محل نمایش تبلیغات 3 - افزونه آماره وردپرس"><img src="http://ads.iran98.org/view/banner/13" alt="ads-img" title="محل نمایش تبلیغات 3 - افزونه آماره وردپرس"/></a>
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
					<h3 class="hndle"><span><?php _e('Statistical Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'visits-log',
									type: 'spline',
									backgroundColor: '#F8F8F8'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php _e('Chart hit in the last 20 days', 'wp_statistics'); ?>',
									style: {
										fontSize: '12px',
										fontFamily: 'Tahoma',
										fontWeight: 'bold'
									}
								},
								xAxis: {
									type: 'datetime',
									categories: [
									<?php
										for( $i=20; $i>=0; $i--) {
											echo '"'.$s->Current_Date('m/d', '-'.$i).'"';
											echo ", ";
										}
									?>]
								},
								yAxis: {
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
					<h3 class="hndle"><span><?php _e('Latest search words', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<table width="100%" class="widefat table-stats" id="last-search">
							<tr>
								<td width="80%"><?php _e('Word', 'wp_statistics'); ?></td>
								<td width="10%"><?php _e('Date', 'wp_statistics'); ?></td>
								<td width="5%"><?php _e('Site', 'wp_statistics'); ?></td>
								<td width="5%"><?php _e('Referrer', 'wp_statistics'); ?></td>
							</tr>
							
							<?php
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%google.com%' OR `referred` LIKE '%yahoo.com%' OR `referred` LIKE '%bing.com%' ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 15");
								
								foreach($result as $items) {
								
									echo "<tr>";
									echo "<td class='td-align'>".$s->Search_Engine_QueryString($items->referred)."</td>";
									echo "<td>$items->last_counter</td>";
									if( $s->Check_Search_Engines('google.com', $items->referred) ) {
										echo "<td><img src='".plugins_url('wp-statistics/images/google.com.png')."' title='".__('Google', 'wp_statistics')."'/></td>";
									} else if( $s->Check_Search_Engines('yahoo.com', $items->referred) ) {
										echo "<td><img src='".plugins_url('wp-statistics/images/yahoo.com.png')."' title='".__('Yahoo!', 'wp_statistics')."'/></td>";
									} else if( $s->Check_Search_Engines('bing.com', $items->referred) ) {
										echo "<td><img src='".plugins_url('wp-statistics/images/bing.com.png')."' title='".__('Bing', 'wp_statistics')."'/></td>";
									}
									if($items->referred){
										echo "<td><a href='$items->referred'><img src='".plugins_url('wp-statistics/images/link.png')."' title='".$items->referred."'/></a></td>";
									} else {
										echo "<td><img src='".plugins_url('wp-statistics/images/unknown.png')."' title='".__('Unknown', 'wp_statistics')."'/></td>";
									}
									echo "</tr>";
									echo "</tr>";
								}
							?>
						</table>
					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Recent Visitors', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<table width="100%" class="widefat table-stats" id="last-visitor">
							<tr>
								<td width="30%"><?php _e('IP', 'wp_statistics'); ?></td>
								<?php if( get_option('ip_information') ) { ?>
								<td width="30%"><?php _e('Country', 'wp_statistics'); ?></td>
								<td width="20%"><?php _e('Province', 'wp_statistics'); ?></td>
								<?php } ?>
								<td width="10%"><?php _e('Date', 'wp_statistics'); ?></td>
								<td width="5%"><?php _e('Browser', 'wp_statistics'); ?></td>
								<td width="5%"><?php _e('Reference', 'wp_statistics'); ?></td>
							</tr>
							
							<?php
								global $ipLite;
								
								$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT 0, 15");
								
								foreach($result as $items) {
									echo "<tr>";
									echo "<td>$items->ip</td>";
									
									if( get_option('ip_information') ) {
										if( $s->IP_Location($items->ip)->country_code == 'XX' ) {
											echo "<td><img src='".plugins_url('wp-statistics/images/unknown.png')."' title='".__('Unknown', 'wp_statistics')."'/></td>";
										} else {
											echo "<td><img src='".plugins_url('wp-statistics/images/flags/').$s->IP_Location($items->ip)->country_code.".png' title='".$s->IP_Location($items->ip)->country_name."'/></td>";
										}
									}
									
									if( get_option('ip_information') ) {
										if( $s->IP_Location($items->ip)->city == '(Unknown City?)' || $s->IP_Location($items->ip)->city == '(Unknown city)' ) {
											echo "<td>" . __('Unknown', 'wp_statistics') . "</td>";
										} else {
											echo "<td>{$s->IP_Location($items->ip)->city}</td>";
										}
									}
									
									echo "<td>$items->last_counter</td>";
									echo "<td><img src='".plugins_url('wp-statistics/images/').$items->agent.".png' title='".$items->agent."'/></td>";
									
									if($items->referred){
										echo "<td><a href='$items->referred'><img src='".plugins_url('wp-statistics/images/link.png')."' title='".$items->referred."'/></a></td>";
									} else {
										echo "<td><img src='".plugins_url('wp-statistics/images/unknown.png')."' title='".__('Unknown', 'wp_statistics')."'/></td>";
									}
									
									echo "</tr>";
									
								}
							?>
							
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>