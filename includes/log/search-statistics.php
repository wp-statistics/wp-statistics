<script type="text/javascript">
	jQuery(document).ready(function(){
	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	$search_engines = wp_statistics_searchengine_list();
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Search Engine Referral Statistics', 'wp_statistics'); ?></h2>

	<ul class="subsubsub">
		<?php $daysToDisplay = 20; if( array_key_exists('hitdays',$_GET)) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = $_GET['hitdays']; } } ?>
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=10"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=20"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=30"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=60"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=90"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=180"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=270"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_searches_menu&hitdays=365"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

	<div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Search Engine Referral Statistics', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
<?php								
								$total_stats = $WP_Statistics->get_option( 'chart_totals' );
								$total_daily = array();

								foreach( $search_engines as $se ) {
									
									echo "var searches_data_line_" . $se['tag'] . " = [";
									
									for( $i=$daysToDisplay; $i>=0; $i--) {
										if( !array_key_exists( $i, $total_daily ) ) { $total_daily[$i] = 0; }
										
										$stat = wp_statistics_searchengine($se['tag'], '-'.$i);
										$total_daily[$i] += $stat;
										
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
										
									}

									echo "];\n";
								}

								if( $total_stats == 1 ) {
									echo "var searches_data_line_total = [";

									for( $i=$daysToDisplay; $i>=0; $i--) {
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $total_daily[$i] . "], ";
									}
									
									echo "];\n";
								}
								
								$tickInterval = $daysToDisplay / 20;
								if( $tickInterval < 1 ) { $tickInterval = 1; }
?>
							visit_chart = jQuery.jqplot('search-stats', [<?php foreach( $search_engines as $se ) { echo "searches_data_line_" . $se['tag'] . ", "; } if( $total_stats == 1 ) { echo 'searches_data_line_total'; }?>], {
								title: {
									text: '<b><?php echo __('Search engine referrals in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?></b>',
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
											numberColumns: <?php echo count($search_engines) + 1; ?>, 
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
								JQPlotSearchChartLengendClickRedraw()
							});

							function JQPlotSearchChartLengendClickRedraw() {
								visit_chart.replot( {resetAxes: ['yaxis'] } );
								jQuery('div[id="search-stats"] .jqplot-table-legend').click(function() {
									JQPlotSearchChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="search-stats"] .jqplot-table-legend').click(function() {
								JQPlotSearchChartLengendClickRedraw()
							});

						});

						</script>
						
						<div id="search-stats" style="height:500px;"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
