<script type="text/javascript">
	jQuery(document).ready(function(){
	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	$search_engines = wp_statistics_searchengine_list();
	
	$search_result['All'] = wp_statistics_searchengine('all','total');

	foreach( $search_engines as $key => $se ) {
		$search_result[$key] = wp_statistics_searchengine($key,'total');
	}
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Browser Statistics', 'wp_statistics'); ?></h2>

	<div class="postbox-container" style="width: 48%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
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
										backgroundColor: '#FFFFFF',
									},
									credits: {
										enabled: false
									},
									title: {
										text: '<?php _e('Browsers by Type', 'wp_statistics'); ?>',
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
			</div>
		</div>
	</div>

	<div class="postbox-container" style="width: 48%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Platform', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						jQuery(function () {
							var platform_chart;
							jQuery(document).ready(function() {
								
								// Build the chart
								platform_chart = new Highcharts.Chart({
									chart: {
										renderTo: 'platform-log',
										plotBackgroundColor: null,
										plotBorderWidth: null,
										plotShadow: false,
										backgroundColor: '#FFFFFF',
									},
									credits: {
										enabled: false
									},
									title: {
										text: '<?php _e('Browsers by Platform', 'wp_statistics'); ?>',
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
										name: '<?php _e('Platform share', 'wp_statistics'); ?>',
										data: [
											<?php 
											$Platforms = wp_statistics_platform_list();

											foreach( $Platforms as $Platform ) {
												$count = wp_statistics_platform( $Platform );
												echo "['" . __( $Platform, 'wp_statistics' ) . " (" . $count . ")', " . $count . "],\r\n";
											}
											?>
										]
									}]
								});
							});
							
						});
						</script>
						<div id="platform-log"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<div style="width: 100%; clear: both;">
		<hr />
	</div>
	
	<div class="postbox-container" style="width: 30%; float: left; margin-right: 20px;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<?php
				for( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount++ )
					{
					if( $BrowserCount % 3 == 0 )
						{
						BrowserVersionStats($Browsers[$BrowserCount]);
						}
					}
				?>
			</div>
		</div>
	</div>
	
	<div class="postbox-container" style="width: 30%; float: left; margin-right: 20px;">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<?php
				for( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount++ )
					{
					if( $BrowserCount % 3 == 1 )
						{
						BrowserVersionStats($Browsers[$BrowserCount]);
						}
					}
				?>
			</div>
		</div>
	</div>

	<div class="postbox-container" style="width: 30%; float: left">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<?php
				for( $BrowserCount = 0; $BrowserCount < count( $Browsers ); $BrowserCount++ )
					{
					if( $BrowserCount % 3 == 2 )
						{
						BrowserVersionStats($Browsers[$BrowserCount]);
						}
					}
				?>
			</div>
		</div>
	</div>
</div>

<?php function BrowserVersionStats($Browser) { $Browser_tag = strtolower(preg_replace('/[^a-zA-Z]/', '', $Browser)); ?>
	<div class="postbox">
		<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
		<h3 class="hndle"><span><?php echo sprintf(__('%s Version', 'wp_statistics'), $Browser); ?></span></h3>
		<div class="inside">
			<script type="text/javascript">
			jQuery(function () {
				var <?php echo $Browser_tag;?>_chart;
				jQuery(document).ready(function() {
					
					// Build the chart
					<?php echo $Browser_tag;?>_chart = new Highcharts.Chart({
						chart: {
							renderTo: 'version-<?php echo $Browser_tag;?>-log',
							plotBackgroundColor: null,
							plotBorderWidth: null,
							plotShadow: false,
							backgroundColor: '#FFFFFF',
						},
						credits: {
							enabled: false
						},
						title: {
							text: '<?php _e($Browser, 'wp_statistics'); ?>',
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
							name: '<?php _e('Browser version share', 'wp_statistics'); ?>',
							data: [
								<?php 
								$Versions = wp_statistics_agent_version_list($Browser);
								$i = 0;
								
								foreach( $Versions as $Version )
									{
									$count = wp_statistics_agent_version( $Browser, $Version );
									echo "											['" . __( $Version, 'wp_statistics' ) . " (" . $count . ")', " . $count . "],\r\n";
									}
								?>
							]
						}]
					});
				});
				
			});
			</script>
			<div class="ltr" id="version-<?php echo $Browser_tag;?>-log"></div>
		</div>
	</div>
<?php } ?>