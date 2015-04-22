<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Hit Statistics', 'wp_statistics'); ?></h2>

	<?php 
		$daysToDisplay = 20; 
		if( array_key_exists('hitdays',$_GET) ) { $daysToDisplay = intval($_GET['hitdays']); }

		if( array_key_exists('rangestart', $_GET ) ) { $rangestart = $_GET['rangestart']; } else { $rangestart = ''; }
		if( array_key_exists('rangeend', $_GET ) ) { $rangeend = $_GET['rangeend']; } else { $rangeend = ''; }

		list( $daysToDisplay, $rangestart_utime, $rangeend_utime ) = wp_statistics_date_range_calculator( $daysToDisplay, $rangestart, $rangeend );
	
		wp_statistics_date_range_selector( 'wps_hits_menu', $daysToDisplay );
	?>

	<div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Hits Statistics Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
<?php								
								echo "var visit_data_line = [";
								
								for( $i=$daysToDisplay; $i>=0; $i--) {
									$stat = wp_statistics_visit('-'.$i, true);
									
									echo "['" . $WP_Statistics->Real_Current_Date('Y-m-d', '-'.$i, $rangeend_utime) . "'," . $stat . "], ";
									
								}

								echo "];\n";

								echo "var visitor_data_line = [";
								
								for( $i=$daysToDisplay; $i>=0; $i--) {
									$stat = wp_statistics_visitor('-'.$i, true);
									
									echo "['" . $WP_Statistics->Real_Current_Date('Y-m-d', '-'.$i, $rangeend_utime) . "'," . $stat . "], ";
									
								}

								echo "];\n";

								$tickInterval = $daysToDisplay / 20;
								if( $tickInterval < 1 ) { $tickInterval = 1; }
?>
							visit_chart = jQuery.jqplot('visits-stats', [visit_data_line, visitor_data_line], {
								title: {
									text: '<b>' + <?php echo json_encode(__('Hits in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics')); ?> + '</b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Real_Current_Date('Y-m-d', '-'.$daysToDisplay, $rangeend_utime);?>',
											max: '<?php echo $WP_Statistics->Real_Current_Date('Y-m-d', '-0', $rangeend_utime);?>',
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
											label: <?php echo json_encode(__('Number of visits and visitors', 'wp_statistics')); ?>,
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
									labels: [<?php echo json_encode(__('Visit', 'wp_statistics')); ?>, <?php echo json_encode(__('Visitor', 'wp_statistics')); ?>],
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
								JQPlotVisitChartLengendClickRedraw()
							});

							function JQPlotVisitChartLengendClickRedraw() {
								visit_chart.replot( {resetAxes: ['yaxis'] } );
								
								jQuery('div[id="visits-stats"] .jqplot-table-legend').click(function() {
									JQPlotVisitChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="visits-stats"] .jqplot-table-legend').click(function() {
								JQPlotVisitChartLengendClickRedraw()
							});

						});

						</script>
						
						<div id="visits-stats" style="height:500px;"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
