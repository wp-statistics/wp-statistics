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
	<h2><?php _e('Hit Statistics', 'wp_statistics'); ?></h2>

	<ul class="subsubsub">
		<?php $daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = $_GET['hitdays']; } }?>
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
					<h3 class="hndle"><span><?php _e('Hits Statistical Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'visits-stats',
									type: '<?php echo get_option('wps_chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '600'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php echo __('Hits chart in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?>',
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
										text: '<?php _e('Number of visits and visitors', 'wp_statistics'); ?>',
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
								series: [{
									name: '<?php _e('Visitor', 'wp_statistics'); ?>',
									data: [<?php
										for( $i=$daysToDisplay; $i>=0; $i--) {
											echo wp_statistics_visitor('-'.$i, true);
											if( $i > 0 ) { echo ", "; }
										}
									?>]
								},
								{
									name: '<?php _e('Visit', 'wp_statistics'); ?>',
									data: [<?php
										for( $i=$daysToDisplay; $i>=0; $i--) {
											echo wp_statistics_visit('-'.$i, true);
											if( $i > 0 ) { echo ", "; }
										}
									?>]
								}]
							});
						});
						</script>
						
						<div id="visits-stats"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
