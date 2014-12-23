<?php

	function wp_statistics_generate_page_postbox_content($pageuri, $pageid, $days = 20, $chart_title = 'Page Trending Stats') {
		GLOBAL $WP_Statistics;
		
		if( $pageuri && !$pageid ) { $pageid = wp_statistics_uri_to_id( $pageuri ); }
		
		$post = get_post($pageid);
		if( is_object($post) ) { $title = $post->post_title; } else { $title = ""; }
		
		$urlfields = "&page-id={$pageid}";
		if( $pageuri ) { $urlfields .= "&page-uri={$pageuri}"; }
		
		$daysToDisplay = $days; 
		
?>
						<script type="text/javascript">
						var pages_chart;
						jQuery(document).ready(function() {
<?php								
						echo "var page_data_line = [";
									
						for( $i=$daysToDisplay; $i>=0; $i--) {
							$stat = wp_statistics_pages( '-'.$i, $pageuri, $pageid );
							
							echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
							
						}

						echo "];\n";
						
						$tickInterval = $daysToDisplay / 20;
								
?>
							pages_jqchart = jQuery.jqplot('page-stats', [page_data_line], {
								title: {
									text: '<b><?php echo __($chart_title, 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-'.$daysToDisplay);?>',
											max: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '');?>',
											tickInterval:  '<?php echo $tickInterval?> day',
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
											label: '<?php _e('Number of Hits', 'wp_statistics'); ?>',
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
									labels: [ '<?php echo  $pageid . ' - ' . $title; ?>' ],
									renderer: jQuery.jqplot.EnhancedLegendRenderer,
									rendererOptions:
										{
											numberColumns: 5, 
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
								JQPlotPagesChartLengendClickRedraw()
							});

							function JQPlotPagesChartLengendClickRedraw() {
								pages_jqchart.replot( {resetAxes: ['yaxis'] } );
								jQuery('div[id="page-stats"] .jqplot-table-legend').click(function() {
									JQPlotPagesChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="page-stats"] .jqplot-table-legend').click(function() {
								JQPlotPagesChartLengendClickRedraw()
							});
						});
						</script>
						
						<div id="page-stats" style="height:500px;"></div>

<?php 
	}