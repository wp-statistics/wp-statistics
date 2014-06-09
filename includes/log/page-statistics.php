<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	$pageuri = $_GET['page-uri'];
	$pageid = $_GET['page-id'];

	if( $pageuri && !$pageid ) { $pageid = wp_statistics_uri_to_id( $pageuri ); }
	
	$post = get_post($pageid);
	$title = $post->post_title;
	
	$urlfields = "&page-id={$pageid}";
	if( $pageuri ) { $urlfields .= "&page-uri={$pageuri}"; }
	
	$daysToDisplay = 20; 
	
	if( array_key_exists('hitdays',$_GET) ) { 
		if( $_GET['hitdays'] > 0 ) { 
			$daysToDisplay = $_GET['hitdays']; 
		} 
	}
	
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php echo __('Page Trend for Post ID', 'wp_statistics') . ' ' .  $pageid . ' - ' . $title; ?></h2>

	<ul class="subsubsub">
		<?php $daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = $_GET['hitdays']; } }?>
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=10<?php echo $urlfields;?>"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=20<?php echo $urlfields;?>"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=30<?php echo $urlfields;?>"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=60<?php echo $urlfields;?>"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=90<?php echo $urlfields;?>"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=180<?php echo $urlfields;?>"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=270<?php echo $urlfields;?>"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_pages_menu&hitdays=365<?php echo $urlfields;?>"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Page Trend', 'wp_statistics'); ?></span></h3>
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
									text: '<?php echo __('Page Trending Stats', 'wp_statistics'); ?>',
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
								echo "									{\n";
								echo "									name: '" . $pageid . ' - ' . $title . "',\n";
								echo "									data: [";

								for( $i=$daysToDisplay; $i>=0; $i--) {
									echo wp_statistics_pages( '-'.$i, $pageuri, $pageid );
									if( $i > 0 ) { echo ", "; }
								}
								
								echo "]\n";
								echo "								},\n";
?>
								]
							});
						});
						</script>
						
						<div id="page-stats"></div>

					</div>
				</div>
			</div>
		</div>
	</div>
</div>