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
	<div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Top 5 Pages Trends', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var pages_chart;
						jQuery(document).ready(function() {
							pages_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'page-stats',
									type: '<?php echo get_option('wps_chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '500'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php echo __('Top 5 Page Trending Stats', 'wp_statistics'); ?>',
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
										step: <?php echo round($daysToDisplay/20);?>
										},
									categories: [
									<?php
										for( $i=$daysToDisplay; $i>=0; $i--) {
											echo '"'.$wpstats->Current_Date_i18n('Y-m-d', '-'.$i).'"';
											if( $i > 0 ) { echo ", "; }
										}
									?>]
								},
								yAxis: {
									min: 0,
									title: {
										text: '<?php _e('Number of Hits', 'wp_statistics'); ?>',
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
								$count = 0;
								foreach( $uris as $uri ) {
									$count++;
									
									echo "									{\n";
									echo "									name: 'Rank #{$count}',\n";
									echo "									data: [";

									for( $i=$daysToDisplay; $i>=0; $i--) {
										echo wp_statistics_pages('-'.$i,$uri[0]);
										if( $i > 0 ) { echo ", "; }
									}
									
									echo "]\n";
									echo "								},\n";
									
									if( $count > 5 ) { break; }
								}

?>
								]
							});
						});
						</script>
						
						<div id="page-stats"></div>

					</div>
				</div>
				
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Top Pages', 'wp_statistics'); ?></span></h3>
					<div class="inside">
							<?php
								// Instantiate pagination object with appropriate arguments
								$pagesPerSection = 10;
								$options = array(25, "All");
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
									
									echo "<div class='log-item'>";

									if( $uri[3] == '' ) { $uri[3] = '[' . __('No page title found', 'wp_statistics') . ']'; }
									
									echo "<div>{$count} - <a href='{$uri[1]}'>{$uri[3]}</a></div>";
									echo "<div style='float: right'>".__('Visits', 'wp_statistics').": {$uri[1]}</div>";
									echo "</div>";
								
								}
								
								echo "</div>";
							?>
					</div>
				</div>
				
				<div class="pagination-log">
					<?php echo $Pagination->display(); ?>
					<p id="result-log"><?php echo ' ' . __('Page', 'wp_statistics') . ' ' . $Pagination->getCurrentPage() . ' ' . __('From', 'wp_statistics') . ' ' . $Pagination->getTotalPages(); ?></p>
				</div>
			</div>
		</div>
	</div>
</div>