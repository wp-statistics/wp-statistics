<script type="text/javascript">
	jQuery(document).ready(function(){
	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Hit Statistics', 'wp_statistics'); ?></h2>

	<ul class="subsubsub">
		<?php $daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = intval($_GET['hitdays']); } }?>
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=10"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=20"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=30"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=60"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=90"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=180"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=270"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_hits_menu&hitdays=365"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

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
									
									echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
									
								}

								echo "];\n";

								echo "var visitor_data_line = [";
								
								for( $i=$daysToDisplay; $i>=0; $i--) {
									$stat = wp_statistics_visitor('-'.$i, true);
									
									echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
									
								}

								echo "];\n";

								$tickInterval = $daysToDisplay / 20;
								if( $tickInterval < 1 ) { $tickInterval = 1; }
?>
							visit_chart = jQuery.jqplot('visits-stats', [visit_data_line, visitor_data_line], {
								title: {
									text: '<b><?php echo __('Hits in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?></b>',
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
