<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});

	postboxes.add_postbox_toggles(pagenow);
	});
</script>
<?php
	if( array_key_exists('referr',$_GET) ) {
		$referr = $_GET['referr'];
		$title = $_GET['referr'];
	}
	else {
		$referr = '';
	}
	
	$get_urls = array();
	$total = 0;
		
	if( $WP_Statistics->get_option('search_converted') ) {
		if( $referr ) {
			$total = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM `{$wpdb->prefix}statistics_search` WHERE `host` = %s", $referr ));
		} else {
			$result = $wpdb->get_results( "SELECT DISTINCT host FROM {$wpdb->prefix}statistics_search" );
	
			foreach( $result as $item ) {
				$get_urls[$item->host] = $wpdb->get_var( "SELECT count(*) FROM {$wpdb->prefix}statistics_search WHERE host = '{$item->host}'" );
			}
			
			$total = count( $get_urls );
		}
	} else {
		if( $referr ) {
			$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}statistics_visitor` WHERE `referred` LIKE %s' AND referred <> '' ORDER BY `{$wpdb->prefix}statistics_visitor`.`ID` DESC", '%' . $referr . '%' ) );
	
			$total = count( $result );
		} else {
			$result = $wpdb->get_results( "SELECT referrer FROM {$wpdb->prefix}statistics_visitors WHERE referrer <> ''" );
			
			$urls = array();
			foreach( $result as $item ) {
			
				$url = parse_url($item->referred);
				
				if( empty($url['host']) || stristr(get_bloginfo('url'), $url['host']) )
					continue;
					
				$urls[] = $url['host'];
			}
			
			$get_urls = array_count_values($urls);

			$total = count( $get_urls );
		}
	}

	// Initiate pagination object with appropriate arguments
	$pagesPerSection = 10;
	$options = array(25, "All");
	$stylePageOff = "pageOff";
	$stylePageOn = "pageOn";
	$styleErrors = "paginationErrors";
	$styleSelect = "paginationSelect";

	$Pagination = new WP_Statistics_Pagination($total, $pagesPerSection, $options, false, $stylePageOff, $stylePageOn, $styleErrors, $styleSelect);
	
	$start = $Pagination->getEntryStart();
	$end = $Pagination->getEntryEnd();
?>
<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Top Referring Sites', 'wp_statistics'); ?></h2>
	<ul class="subsubsub">
		<?php if($referr) { ?>
		<li class="all"><a <?php if(!$referr) { echo 'class="current"'; } ?>href="?page=wps_referrers_menu"><?php _e('All', 'wp_statistics'); ?></a></li>
			| <li><a class="current" href="?page=wps_referrers_menu&referr=<?php echo $referr; ?>"> <?php echo $title; ?> <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php } else { ?>
		<li class="all"><a <?php if(!$referr) { echo 'class="current"'; } ?>href="?page=wps_referrers_menu"><?php _e('All', 'wp_statistics'); ?> <span class="count">(<?php echo $total; ?>)</span></a></li>
		<?php }?>
	</ul>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<?php if($referr) { ?>
						<h3 class="hndle"><span><?php _e('Referring sites from', 'wp_statistics'); ?>: <?php echo $referr; ?></span></h3>
					<?php } else { ?>
						<h3 class="hndle"><span><?php _e('Top Referring Sites', 'wp_statistics'); ?></span></h3>
					<?php } ?>
					<div class="inside">
							<?php
								echo "<div class='log-latest'>";

								if( $WP_Statistics->get_option('search_converted') ) {
									$result = $wpdb->get_results($wpdb->prepare("SELECT * FROM `{$wpdb->prefix}statistics_search` INNER JOIN `{$wpdb->prefix}statistics_visitor` on {$wpdb->prefix}statistics_search.`visitor` = {$wpdb->prefix}statistics_visitor.`ID` WHERE `host` = %s ORDER BY `{$wpdb->prefix}statistics_search`.`ID` DESC LIMIT %d, %d", $referr, $start, $end ) );
								}
								
								if( $referr ) {
									foreach($result as $item) {
								
										echo "<div class='log-item'>";
										echo "<div class='log-referred'><a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&ip={$item->ip}'>".wp_statistics_icons('dashicons-visibility', 'visibility')."{$item->ip}</a></div>";
										echo "<div class='log-ip'>" . date(get_option('date_format'), strtotime($item->last_counter)) . " - <a href='http://www.geoiptool.com/en/?IP={$item->ip}' target='_blank'>{$item->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map' title='".__('Map', 'wp_statistics')."'><div class='dashicons dashicons-location-alt'></div></a>";
										
										if( array_search( strtolower( $item->agent ), array( "chrome", "firefox", "msie", "opera", "safari" ) ) !== FALSE ){
											$agent = "<img src='".plugins_url('wp-statistics/assets/images/').$item->agent.".png' class='log-tools' title='{$item->agent}'/>";
										} else {
											$agent = "<div class='dashicons dashicons-editor-help'></div>";
										}
										
										echo "<div class='log-agent'><a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$item->agent}'>{$agent}</a>";
										
										echo "<a href='" . htmlentities($item->referred,ENT_QUOTES) . "'><div class='dashicons dashicons-admin-links'></div> " . htmlentities(substr($item->referred, 0, 100),ENT_QUOTES) . "[...]</a></div>";
										echo "</div>";
									
									}
								} else {
									arsort( $get_urls );
									$get_urls = array_slice($get_urls, $start, $end);
									
									$i = 0;
									foreach( $get_urls as $items => $value) {
										
										$i++;
										
										echo "<div class='log-item'>";
										echo "<div class='log-referred'>{$i} - <a href='?page=wps_referrers_menu&referr={$items}'>{$items}</a></div>";
										echo "<div class='log-ip'>".__('References', 'wp_statistics').": " . number_format_i18n($value) . "</div>";
										echo "<div class='clear'></div>";
										echo "<div class='log-url'><a href='http://" . htmlentities($items,ENT_QUOTES) . "/' title='" . htmlentities($items,ENT_QUOTES) . "'><div class='dashicons dashicons-admin-links'></div> http://" . htmlentities($items,ENT_QUOTES) . "/</a></div>";
										echo "</div>";
										
									}
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