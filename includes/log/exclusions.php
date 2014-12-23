<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	if( $WP_Statistics->get_option( 'record_exclusions' ) != 1 ) {
		echo "<div class='updated settings-error'><p><strong>" . __('Attention: Exclusion are not currently set to be recorded, the results below may not reflect current statistics!', 'wp_statistics') . "</strong></p></div>";
	}

	$daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = intval($_GET['hitdays']); } }

	$total_stats = $WP_Statistics->get_option( 'chart_totals' );
	
	$excluded_reasons = array('Robot','Browscap','IP Match','Self Referral','Login Page','Admin Page','User Role','GeoIP','Hostname', 'Robot Threshold','Honey Pot');
	$excluded_reason_tags = array('Robot' => 'robot','Browscap' => 'browscap','IP Match' => 'ipmatch','Self Referral' => 'selfreferral','Login Page' => 'loginpage','Admin Page' => 'adminpage','User Role' => 'userrole','Total' => 'total','GeoIP' => 'geoip','Hostname' => 'hostname','Robot Threshold' => 'robot_threshold','Honey Pot' => 'honeypot');
	$excluded_results = array('Total' => array() );
	$excluded_total = 0;
	
	foreach( $excluded_reasons as $reason ) {
	
		// The reasons array above is used both for display and internal purposes.  Internally the values are all lower case but the array
		// is created with mixed case so it looks nice to the user.  Therefore we have to convert it to lower case here.
		$thisreason = strtolower( $excluded_reason_tags[$reason] );
		
		for( $i=$daysToDisplay; $i>=0; $i--) {
		
			// We're looping through the days backwards, so let's fine out what date we want to look at.
			$thisdate = date('Y-m-d', strtotime('-'.$i." day") );
		
			// Create the SQL query string to get the data.
			$query = "SELECT count FROM {$wpdb->prefix}statistics_exclusions WHERE reason = '{$thisreason}' AND date = '{$thisdate}'";
			
			// Execute the query.
			$excluded_results[$reason][$i] = $wpdb->get_var( $query );
			
			// If we're returned an error or a FALSE value, then let's make sure it's set to a numerical 0.
			if( $excluded_results[$reason][$i] < 1 ) { $excluded_results[$reason][$i] = 0; }
			
			// Make sure to initialize the results so we don't get warnings when WP_DEBUG is enabled.
			if( !array_key_exists( $i, $excluded_results['Total'] ) ) { $excluded_results['Total'][$i] = 0; }
			
			// We're totalling things up here for use later.
			$excluded_results['Total'][$i] += $excluded_results[$reason][$i];
			$excluded_total += $excluded_results[$reason][$i];
		}
	}
	
	// If the chart totals is enabled, cheat a little and just add another reason category to the list so it get's generated later.
	if( $total_stats == 1 ) { $excluded_reasons[] = 'Total'; }
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Exclusions Statistics', 'wp_statistics'); ?></h2>

	<ul class="subsubsub">
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=10"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=20"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=30"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=60"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=90"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=180"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=270"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=365"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

	<br><br>
	<h3><?php echo sprintf(__('Total Exclusions: %s', 'wp_statistics'), $excluded_total); ?></h3>
	
	<div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Exclusions Statistical Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
<?php								
								foreach( $excluded_reasons as $reason ) {
								
									echo "var excluded_data_line_" . $excluded_reason_tags[$reason] . " = [";

									for( $i=$daysToDisplay; $i>=0; $i--) {
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $excluded_results[$reason][$i] . "], ";
										}

									echo "];\n";
								}
								
								$tickInterval = $daysToDisplay / 20;
								if( $tickInterval < 1 ) { $tickInterval = 1; }
?>
							visit_chart = jQuery.jqplot('exclusion-stats', [<?php foreach( $excluded_reasons as $reason ) { echo "excluded_data_line_" . $excluded_reason_tags[$reason] . ", "; } ?>], {
								title: {
									text: '<b><?php echo __('Excluded hits in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-'.$daysToDisplay);?>',
											max: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '');?>',
											tickInterval: '<?php echo $tickInterval?> day',
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
											padMin: 1.0,
											label: '<?php _e('Number of excluded hits', 'wp_statistics'); ?>',
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
									labels: [<?php foreach( $excluded_reasons as $reason ) { echo "'" . __( $reason, 'wp_statistics' ) . "', "; } ?>],
									renderer: jQuery.jqplot.EnhancedLegendRenderer,
									rendererOptions:
										{
											numberColumns: <?php echo count($excluded_reasons) + 1; ?>, 
											disableIEFading: false,
											border: 'none',
										},
									},
								highlighter: {
									show: true,
									bringSeriesToFront: true,
									tooltipAxes: 'xy',
									formatString: '%s:&nbsp;<b>%i</b>&nbsp;',
									tooltipContentEditor: tooltipContentEditor,
								},
								grid: {
								 drawGridlines: true,
								 borderColor: 'transparent',
								 shadow: false,
								 drawBorder: false,
								 shadowColor: 'transparent'
								},
							} );

							function tooltipContentEditor(str, seriesIndex, pointIndex, plot) {
								// display series_label, x-axis_tick, y-axis value
								return plot.legend.labels[seriesIndex] + ", " + str;;
							}
							
							jQuery(window).resize(function() {
								JQPlotExclusionChartLengendClickRedraw()
							});
							
							function JQPlotExclusionChartLengendClickRedraw() {
								visit_chart.replot( {resetAxes: ['yaxis'] } );
								jQuery('div[id="exclusion-stats"] .jqplot-table-legend').click(function() {
									JQPlotExclusionChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="exclusion-stats"] .jqplot-table-legend').click(function() {
								JQPlotExclusionChartLengendClickRedraw()
							});

						});
						</script>
						
						<div id="exclusion-stats" style="height:500px;"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
