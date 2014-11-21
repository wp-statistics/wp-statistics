<?php
	function wp_statistics_generate_search_postbox($ISOCountryCode, $search_engines) {
	
?>
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Search Engine Referrals', 'wp_statistics'); ?> <a href="?page=wps_searches_menu"><?php echo wp_statistics_icons('dashicons-visibility', 'visibility'); ?><?php _e('More', 'wp_statistics'); ?></a></span></h3>
					<div class="inside">
					<?php wp_statistics_generate_search_postbox_content($search_engines); ?>
					</div>
				</div>
<?php		
	}

	function wp_statistics_generate_search_postbox_content($search_engines, $size = "300px", $days = 20) {
	
		global $wpdb, $table_prefix, $WP_Statistics;
?>
						<script type="text/javascript">
						var referral_chart;
						jQuery(document).ready(function() {
<?php								
								$total_stats = $WP_Statistics->get_option( 'chart_totals' );
								$total_daily = array();

								foreach( $search_engines as $se ) {
									
									echo "var searches_data_line_" . $se['tag'] . " = [";
									
									for( $i=$days; $i>=0; $i--) {
										if( !array_key_exists( $i, $total_daily ) ) { $total_daily[$i] = 0; }
										$stat = wp_statistics_searchengine($se['tag'], '-'.$i);
										$total_daily[$i] += $stat;
										
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
										
									}

									echo "];\n";
								}

								if( $total_stats == 1 ) {
									echo "var searches_data_line_total = [";

									for( $i=$days; $i>=0; $i--) {
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $total_daily[$i] . "], ";
									}
									
									echo "];\n";
								}
								
?>
							referral_chart = jQuery.jqplot('search-stats', [<?php foreach( $search_engines as $se ) { echo "searches_data_line_" . $se['tag'] . ", "; } if( $total_stats == 1 ) { echo 'searches_data_line_total'; }?>], {
								title: {
									text: '<b><?php echo __('Search engine referrals in the last', 'wp_statistics') . ' ' . $days . ' ' . __('days', 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-' . $days);?>',
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
								JQPlotReferralChartLengendClickRedraw()
							});

							function JQPlotReferralChartLengendClickRedraw() {
								referral_chart.replot( {resetAxes: ['yaxis'] } );
								jQuery('div[id="search-stats"] .jqplot-table-legend').click(function() {
									JQPlotReferralChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="search-stats"] .jqplot-table-legend').click(function() {
								JQPlotReferralChartLengendClickRedraw()
							});

						});

						</script>
						
						<div id="search-stats" style="height:<?php echo $size; ?>;"></div>
						
<?php		
	}

