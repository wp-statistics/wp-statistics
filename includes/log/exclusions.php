<script type="text/javascript">
	jQuery(document).ready(function(){
		postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	if( get_option( 'wps_record_exclusions' ) != 1 ) {
		echo "<div class='updated settings-error'><p><strong>" . __('Attention: Exclusion are not currently set to be recorded, the results below may not reflect current statistics!', 'wp_statistics') . "</strong></p></div>";
	}

	$daysToDisplay = 20; if( array_key_exists('hitdays',$_GET) ) { if( $_GET['hitdays'] > 0 ) { $daysToDisplay = $_GET['hitdays']; } }

	$total_stats = get_option( 'wps_chart_totals' );
	
	$excluded_reasons = array('Robot','IP Match','Self Referral','Login Page','Admin Page','User Role');
	$excluded_results = array();
	$excluded_total = 0;
	
	foreach( $excluded_reasons as $reason ) {
	
		// The reasons array above is used both for display and internal purposes.  Internally the values are all lower case but the array
		// is created with mixed case so it looks nice to the user.  Therefore we have to convert it to lower case here.
		$thisreason = strtolower( $reason );
		
		for( $i=$daysToDisplay; $i>=0; $i--) {
		
			// We're looping through the days backwards, so let's fine out what date we want to look at.
			$thisdate = date('Y-m-d', strtotime('-'.$i." day") );
		
			// Create the SQL query string to get the data.
			$query = "SELECT count FROM {$wpdb->prefix}statistics_exclusions WHERE reason = '{$thisreason}' AND date = '{$thisdate}'";
			
			// Execute the query.
			$excluded_results[$reason][$i] = $wpdb->get_var( $query );
			
			// If we're returned an error or a FALSE value, then let's make sure it's set to a numerical 0.
			if( $excluded_results[$reason][$i] < 1 ) { $excluded_results[$reason][$i] = 0; }
			
			// We're totalling things up here for use later.
			$excluded_results['total'][$i] += $excluded_results[$reason][$i];
			$excluded_total += $excluded_results[$reason][$i];
		}
	}
	
	// If the chart totals is enabled, cheat a little and just add another reason category to the list so it get's generated later.
	if( $total_stats == 1 ) { $excluded_reasons[] = 'Total'; }
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Hit Statistics', 'wp_statistics'); ?></h2>

	<ul class="subsubsub">
		<li class="all"><a <?php if($daysToDisplay == 10) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=10"><?php _e('10 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 20) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=20"><?php _e('20 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 30) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=30"><?php _e('30 Days', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 60) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=60"><?php _e('2 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 90) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=90"><?php _e('3 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 180) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=180"><?php _e('6 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 270) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=270"><?php _e('9 Months', 'wp_statistics'); ?></a></li>
		| <li class="all"><a <?php if($daysToDisplay == 365) { echo 'class="current"'; } ?>href="?page=wps_exclusions_menu&hitdays=365"><?php _e('1 Year', 'wp_statistics'); ?></a></li>
	</ul>

	<br><br>
	<h3><?php echo sprintf(__('Total Exclusions: %s', 'wp_statistics'), $excluded_total); ?></h3>
	
	<div class="postbox-container" style="width: 100%; float: left; margin-right:20px">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Exclusions Statistical Chart', 'wp_statistics'); ?></span></h3>
					<div class="inside">
						<script type="text/javascript">
						var visit_chart;
						jQuery(document).ready(function() {
							visit_chart = new Highcharts.Chart({
								chart: {
									renderTo: 'exclusion-stats',
									type: '<?php echo get_option('wps_chart_type'); ?>',
									backgroundColor: '#FFFFFF',
									height: '600'
								},
								credits: {
									enabled: false
								},
								title: {
									text: '<?php echo __('Excluded hits chart in the last', 'wp_statistics') . ' ' . $daysToDisplay . ' ' . __('days', 'wp_statistics'); ?>',
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
										text: '<?php _e('Number of excluded hits', 'wp_statistics'); ?>',
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
									
									foreach( $excluded_reasons as $reason ) {
									
										echo "{\n";
										echo "name: '" . __($reason, 'wp_statistics') . "',\n";
										echo "data: [";

										for( $i=$daysToDisplay; $i>=0; $i--) {
											echo $excluded_results[$reason][$i];
											if( $i > 0 ) { echo ", "; }
										}
										echo "]\n";
										echo "								},\n";
									}
									?>
								]
							});
						});
						</script>
						
						<div id="exclusion-stats"></div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
