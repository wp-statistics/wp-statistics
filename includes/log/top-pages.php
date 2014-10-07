<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	list( $total, $uris ) = wp_statistics_get_top_pages();

	$daysToDisplay = 20;
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Top Pages', 'wp_statistics'); ?></h2>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Top 5 Pages Trends', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var pages_jqchart;
						jQuery(document).ready(function() {
<?php								
								$count = 0;
								
								foreach( $uris as $uri ) {
									
									$count++;
									
									echo "var pages_data_line" . $count . " = [";
									
									for( $i=$daysToDisplay; $i>=0; $i--) {
										$stat = wp_statistics_pages('-'.$i,$uri[0]);
										
										echo "['" . $WP_Statistics->Current_Date('Y-m-d', '-'.$i) . "'," . $stat . "], ";
										
									}

									echo "];\n";
									if( $count > 4 ) { break; }
								}
								
?>

							pages_jqchart = jQuery.jqplot('jqpage-stats', [pages_data_line1, pages_data_line2, pages_data_line3, pages_data_line4, pages_data_line5], {
								title: {
									text: '<b><?php echo __('Top 5 Page Trending Stats', 'wp_statistics'); ?></b>',
									fontSize: '12px',
									fontFamily: 'Tahoma',
									textColor: '#000000',
									},
								axes: {
									xaxis: {
											min: '<?php echo $WP_Statistics->Current_Date('Y-m-d', '-'.$daysToDisplay);?>',
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
									labels: [ 'Rank #1', 'Rank #2', 'Rank #3', 'Rank #4', 'Rank #5'],
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
								JQPlotPagesChartLengendClickRedraw()
							});

							function JQPlotPagesChartLengendClickRedraw() {
								pages_jqchart.replot( {resetAxes: ['yaxis'] } );
								jQuery('div[id="jqpage-stats"] .jqplot-table-legend').click(function() {
									JQPlotPagesChartLengendClickRedraw();
								});
							}
							
							jQuery('div[id="jqpage-stats"] .jqplot-table-legend').click(function() {
								JQPlotPagesChartLengendClickRedraw()
							});
						});
						</script>
						
						<div id="jqpage-stats" style="height:500px;"></div>

					</div>
				</div>

				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Top Pages', 'wp_statistics'); ?></span></h3>
					<div class="inside">
							<?php
								if( $total > 0 ) {
									// Instantiate pagination object with appropriate arguments
									$pagesPerSection = 10;
									$options = 10;
									$stylePageOff = "pageOff";
									$stylePageOn = "pageOn";
									$styleErrors = "paginationErrors";
									$styleSelect = "paginationSelect";

									$Pagination = new Pagination($total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
									
									$start = $Pagination->getEntryStart();
									$end = $Pagination->getEntryEnd();
									
									$site_url = site_url();
									
									echo "<div class='log-latest'>";
									$count = 0;
									
									foreach($uris as $uri) {
										$count++;

										if( $count >= $start ) {
											echo "<div class='log-item'>";

											if( $uri[3] == '' ) { $uri[3] = '[' . __('No page title found', 'wp_statistics') . ']'; }
											
											echo "<div class='log-page-title'>{$count} - {$uri[3]}</div>";
											echo "<div class='right-div'>".__('Visits', 'wp_statistics').": <a href='?page=wps_pages_menu&page-uri={$uri[0]}'>" . number_format_i18n($uri[1]) . "</a></div>";
											echo "<div class='left-div'><a dir='ltr' href='{$site_url}{$uri[0]}'>".urldecode($uri[0])."</a></div>";
											echo "</div>";
										}

										if( $count == $start + 10 ) { break; }
									
									}
									
									echo "</div>";
								}
							?>
					</div>
				</div>
				
<?php if( $total > 0 ) {?>
				<div class="pagination-log">
					<?php echo $Pagination->display(); ?>
					<p id="result-log"><?php echo ' ' . __('Page', 'wp_statistics') . ' ' . $Pagination->getCurrentPage() . ' ' . __('From', 'wp_statistics') . ' ' . $Pagination->getTotalPages(); ?></p>
				</div>
<?php } ?>
			</div>
		</div>
	</div>
</div>