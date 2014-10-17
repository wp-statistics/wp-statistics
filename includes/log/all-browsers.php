<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
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
<?php								
								$Browsers = wp_statistics_ua_list();
								
								natcasesort( $Browsers );
								
								echo "var browser_data = [";
								
								foreach( $Browsers as $Browser )
									{
									$count = wp_statistics_useragent( $Browser );
									echo "['" . substr( __( $Browser, 'wp_statistics' ), 0, 15 ) . " (" . number_format_i18n($count) . ")'," . $count . "], ";
									}

								echo "];\n";

								
?>

								browser_chart = jQuery.jqplot('browsers-log', [browser_data], { 
									title: {
										text: '<b><?php echo __('Browsers by type', 'wp_statistics'); ?></b>',
										fontSize: '12px',
										fontFamily: 'Tahoma',
										textColor: '#000000',
										},
									seriesDefaults: {
										// Make this a pie chart.
										renderer: jQuery.jqplot.PieRenderer, 
										rendererOptions: {
											// Put data labels on the pie slices.
											// By default, labels show the percentage of the slice.
											dataLabels: 'percent',
											showDataLabels: true,
											shadowOffset: 0,
										}
									}, 
									legend: { 
										show:true, 
										location: 's',
										renderer: jQuery.jqplot.EnhancedLegendRenderer,
										rendererOptions:
											{
												numberColumns: 3, 
												disableIEFading: false,
												border: 'none',
											},
										},
									grid: { background: 'transparent', borderWidth: 0, shadow: false },
									highlighter: {
										show: true,
										formatString:'%s', 
										tooltipLocation:'n', 
										useAxesFormatters:false,
										},
								} );
							});

							jQuery(window).resize(function() {
								browser_chart.replot( {resetAxes: true } );
							});

						});
								  
						</script>
								
						<div id="browsers-log" style="height: <?php $height = ( ceil( count($Browsers) / 3) * 27 ) + 400; if( $height < 400 ) { $height = 400; } echo $height; ?>px;"></div>
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
<?php								
								$Platforms = wp_statistics_platform_list();

								natcasesort( $Platforms );
								
								echo "var platform_data = [";
								
								foreach( $Platforms as $Platform )
									{
									$count = wp_statistics_platform( $Platform );
									echo "['" . substr( __( $Platform, 'wp_statistics' ), 0, 15) . " (" . number_format_i18n($count) . ")'," . $count . "], ";
									}

								echo "];\n";

								
?>

								platform_chart = jQuery.jqplot('platform-log', [platform_data], { 
									title: {
										text: '<b><?php echo __('Browsers by platform', 'wp_statistics'); ?></b>',
										fontSize: '12px',
										fontFamily: 'Tahoma',
										textColor: '#000000',
										},
									seriesDefaults: {
										// Make this a pie chart.
										renderer: jQuery.jqplot.PieRenderer, 
										rendererOptions: {
											// Put data labels on the pie slices.
											// By default, labels show the percentage of the slice.
											dataLabels: 'percent',
											showDataLabels: true,
											shadowOffset: 0,
										}
									}, 
									legend: { 
										show:true, 
										location: 's',
										renderer: jQuery.jqplot.EnhancedLegendRenderer,
										rendererOptions: {
											numberColumns: 3, 
											disableIEFading: false,
											border: 'none',
										},
									},
									grid: { background: 'transparent', borderWidth: 0, shadow: false },
									highlighter: {
										show: true,
										formatString:'%s', 
										tooltipLocation:'n', 
										useAxesFormatters:false,
										},
								} );
							});

							jQuery(window).resize(function() {
								platform_chart.replot( {resetAxes: true } );
							});

						});
								  
						</script>
								
						<div id="platform-log" style="height: <?php $height = ( ceil( count($Platforms) / 3 ) * 27 ) + 400; if( $height < 400 ) { $height = 400; } echo $height; ?>px;"></div>
								
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
<?php								
					$Versions = wp_statistics_agent_version_list($Browser);
					
					natcasesort( $Versions );
					
					echo "var " . $Browser_tag . "_version_data = [";
					
					foreach( $Versions as $Version )
						{
						$count = wp_statistics_agent_version( $Browser, $Version );
						echo "['" . __( $Version, 'wp_statistics' ) . " (" . number_format_i18n($count) . ")'," . $count . "], ";
						}

					echo "];\n";

								
?>
				<?php echo $Browser_tag;?>_chart = jQuery.jqplot('version-<?php echo $Browser_tag;?>-log', [<?php echo $Browser_tag;?>_version_data], { 
						title: {
							text: '<b><?php echo __($Browser, 'wp_statistics'); ?></b>',
							fontSize: '12px',
							fontFamily: 'Tahoma',
							textColor: '#000000',
							},
						seriesDefaults: {
							// Make this a pie chart.
							renderer: jQuery.jqplot.PieRenderer, 
							rendererOptions: {
							  // Put data labels on the pie slices.
							  // By default, labels show the percentage of the slice.
							  dataLabels: 'percent',
							  showDataLabels: true,
							  shadowOffset: 0,
							}
						}, 
						legend: { 
							show:true, 
							location: 's',
							renderer: jQuery.jqplot.EnhancedLegendRenderer,
							rendererOptions:
								{
									numberColumns: 2, 
									disableIEFading: false,
									border: 'none',
								},
							},
						grid: { background: 'transparent', borderWidth: 0, shadow: false },
						highlighter: {
							show: true,
							formatString:'%s', 
							tooltipLocation:'n', 
							useAxesFormatters:false,
							},
					} );
				});

				jQuery(window).resize(function() {
					<?php echo $Browser_tag;?>_chart.replot( {resetAxes: true } );
				});

			});
			</script>
			<div class="ltr" id="version-<?php echo $Browser_tag;?>-log" style="height: <?php $height = ( ceil( count($Versions) / 2 ) * 27 ) + 237; if( $height < 300 ) { $height = 300; } echo $height; ?>px;"></div>
		</div>
	</div>
<?php } ?>