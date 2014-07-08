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
	<h2><?php _e('Search Engine Referrers Statistics', 'wp_statistics'); ?></h2>

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
					<h3 class="hndle"><span><?php _e('Search Engine Referrers Statistical Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'search-stats',
									type: '<?php echo $WP_Statistics->get_option('chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '600'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php echo __('Referrer search engine chart in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?>',
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
										text: '<?php _e('Number of referrer', 'wp_statistics'); ?>',
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
								$total_stats = $WP_Statistics->get_option( 'chart_totals' );
								$total_daily = array();

								foreach( $search_engines as $se ) {
									echo "								{\n";
									echo "									name: '" . __($se['name'], 'wp_statistics') . "',\n";
									echo "									data: [";

									for( $i=20; $i>=0; $i--) {
										$result = wp_statistics_searchengine($se['tag'], '-'.$i) . ", ";
										$total_daily[$i] += $result;
										echo $result;
									}
									
									echo "]\n";
									echo "								},\n";
								}
								
								if( $total_stats == 1 ) {
									echo "								{\n";
									echo "									name: '" . __('Total', 'wp_statistics') . "',\n";
									echo "									data: [";

									for( $i=20; $i>=0; $i--) {
										echo $total_daily[$i] . ", ";
									}
									
									echo "]\n";
									echo "								},\n";
								}
?>
								]
							});
						});
						</script>
						
						<div id="search-stats"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
