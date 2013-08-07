<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('.show-map').click(function(){
			alert('<?php _e('To be added soon', 'wp_statistics'); ?>');
		});
	});
</script>

<div class="wrap">
	<?php screen_icon('options-general'); ?>
	<h2><?php _e('Latest search words', 'wp_statistics'); ?></h2>
	<ul class="subsubsub">
		<li class="all"><a <?php if(!$referred) { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-search"><?php _e('All', 'wp_statistics'); ?> <span class="count">(<?php echo $result['all']; ?>)</span></a>|</li>
		<li><a <?php if($referred == 'google.com') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=google.com"> <?php echo _e('Google', 'wp_statistics'); ?> <span class="count">(<?php echo $result['google']; ?>)</span></a>|</li>
		<li><a <?php if($referred == 'yahoo.com') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=yahoo.com"> <?php echo _e('Yahoo!', 'wp_statistics'); ?> <span class="count">(<?php echo $result['yahoo']; ?>)</span></a>|</li>
		<li><a <?php if($referred == 'bing.com') { echo 'class="current"'; } ?>href="?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=bing.com"> <?php echo _e('Bing', 'wp_statistics'); ?> <span class="count">(<?php echo $result['bing']; ?>)</span></a></li>
	</ul>
	<div class="postbox-container" id="last-log">
		<div class="metabox-holder">
			<div class="meta-box-sortables">
				<div class="postbox">
					<div class="handlediv" title="<?php _e('Click to toggle', 'wp_statistics'); ?>"><br /></div>
					<h3 class="hndle"><span><?php _e('Latest search words', 'wp_statistics'); ?></span></h3>
					<div class="inside">
							<?php
								global $s;
								
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

								// Retrieve MySQL data
								if( $referred ) {
									$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%{$referred}%' ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}");
								} else {
									$result = $wpdb->get_results("SELECT * FROM `{$table_prefix}statistics_visitor` WHERE `referred` LIKE '%google.com%' OR `referred` LIKE '%yahoo.com%' OR `referred` LIKE '%bing.com%' ORDER BY `{$table_prefix}statistics_visitor`.`ID` DESC  LIMIT {$start}, {$end}");
								}
								
								echo "<div class='log-latest'>";
								
								foreach($result as $items) {
								
									if( !$s->Search_Engine_QueryString($items->referred) ) continue;
									
									echo "<div class='log-item'>";
										echo "<div class='log-referred'>".substr($s->Search_Engine_QueryString($items->referred), 0, 100)."</div>";
										echo "<div class='log-ip'>{$items->last_counter} - <a href='http://www.geoiptool.com/en/?IP={$items->ip}' target='_blank'>{$items->ip}</a></div>";
										echo "<div class='clear'></div>";
										echo "<a class='show-map'><img src='".plugins_url('wp-statistics/images/map.png')."' class='log-tools' title='".__('Map', 'wp_statistics')."'/></a>";
										
										if( $s->Check_Search_Engines('google.com', $items->referred) ) {
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=google.com'><img src='".plugins_url('wp-statistics/images/google.com.png')."' class='log-tools' title='".__('Google', 'wp_statistics')."'/></a>";
										} else if( $s->Check_Search_Engines('yahoo.com', $items->referred) ) {
											echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=yahoo.com'><img src='".plugins_url('wp-statistics/images/yahoo.com.png')."' class='log-tools' title='".__('Yahoo!', 'wp_statistics')."'/></a>";
										} else if( $s->Check_Search_Engines('bing.com', $items->referred) ) {
											echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-search&referred=bing.com'><img src='".plugins_url('wp-statistics/images/bing.com.png')."' class='log-tools' title='".__('Bing', 'wp_statistics')."'/></a>";
										}
										
										echo "<a href='?page=wp-statistics/wp-statistics.php&type=last-all-visitor&agent={$items->agent}'><img src='".plugins_url('wp-statistics/images/').$items->agent.".png' class='log-tools' title='{$items->agent}'/></a>";
										echo "<div class='log-url'><a href='{$items->referred}'><img src='".plugins_url('wp-statistics/images/link.png')."' title='{$items->referred}'/> ".substr($items->referred, 0, 100)."[...]</a></div>";
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